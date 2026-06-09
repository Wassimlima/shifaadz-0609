<?php

/**
 * Session, authentication, and authorization helpers.
 */

function initSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

/** @return array{id:int,email:string,name:string,role:string} */
function currentUser(): ?array
{
    initSecureSession();

    if (empty($_SESSION['user_id'])) {
        return null;
    }

    return [
        'id'    => (int) $_SESSION['user_id'],
        'email' => (string) ($_SESSION['user_email'] ?? ''),
        'name'  => (string) ($_SESSION['user_name'] ?? ''),
        'role'  => (string) ($_SESSION['user_role'] ?? ''),
    ];
}

/**
 * @param list<string>|null $roles
 * @return array{id:int,email:string,name:string,role:string}
 */
function requireAuth(?array $roles = null): array
{
    $user = currentUser();

    if ($user === null) {
        sendError('غير مصرح', 401);
    }

    if ($roles !== null && !in_array($user['role'], $roles, true)) {
        sendError('غير مصرح', 403);
    }

    return $user;
}

/** @return array{id:int,email:string,name:string,role:string} */
function requireAdmin(): array
{
    return requireAuth(['admin']);
}

function loginUser(array $user): void
{
    initSecureSession();
    session_regenerate_id(true);

    $_SESSION['user_id']    = (int) $user['id'];
    $_SESSION['user_email'] = (string) $user['email'];
    $_SESSION['user_name']  = (string) $user['full_name'];
    $_SESSION['user_role']  = (string) $user['role'];
}

function logoutUser(): void
{
    initSecureSession();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $p['path'],
            $p['domain'],
            $p['secure'],
            $p['httponly']
        );
    }

    session_destroy();
}

function isAdmin(array $user): bool
{
    return $user['role'] === 'admin';
}

/** @return list<int> */
function getPharmacyIdsForUser(mysqli $db, int $userId): array
{
    $stmt = $db->prepare('SELECT id FROM pharmacies WHERE owner_user_id = ? ORDER BY id ASC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $ids = [];

    while ($row = $result->fetch_assoc()) {
        $ids[] = (int) $row['id'];
    }

    $stmt->close();
    return $ids;
}

function getRepIdForUser(mysqli $db, int $userId): ?int
{
    $stmt = $db->prepare('SELECT id FROM med_reps WHERE user_id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $row ? (int) $row['id'] : null;
}

function getRepIdForUserPdo(PDO $pdo, int $userId): ?int
{
    $stmt = $pdo->prepare('SELECT id FROM med_reps WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();

    return $row ? (int) $row['id'] : null;
}

function assertPharmacyOwnership(mysqli $db, int $pharmacyId, array $user): void
{
    if (isAdmin($user)) {
        return;
    }

    if (!in_array($user['role'], ['pharmacist', 'medical_services'], true)) {
        sendError('غير مصرح', 403);
    }

    $stmt = $db->prepare('SELECT id FROM pharmacies WHERE id = ? AND owner_user_id = ? LIMIT 1');
    $stmt->bind_param('ii', $pharmacyId, $user['id']);
    $stmt->execute();

    if (!$stmt->get_result()->fetch_assoc()) {
        sendError('غير مصرح', 403);
    }

    $stmt->close();
}

function resolvePharmacyId(mysqli $db, array $user, ?int $requestedId = null): int
{
    if (isAdmin($user)) {
        if ($requestedId) {
            return $requestedId;
        }
        sendError('pharmacyId required', 400);
    }

    $owned = getPharmacyIdsForUser($db, $user['id']);

    if ($owned === []) {
        sendError('لا توجد صيدلية مرتبطة بحسابك', 403);
    }

    if ($requestedId) {
        assertPharmacyOwnership($db, $requestedId, $user);
        return $requestedId;
    }

    return $owned[0];
}

function assertRepOwnership(mysqli $db, int $repId, array $user): void
{
    if (isAdmin($user)) {
        return;
    }

    if ($user['role'] !== 'med_rep') {
        sendError('غير مصرح', 403);
    }

    $ownRepId = getRepIdForUser($db, $user['id']);

    if ($ownRepId === null || $ownRepId !== $repId) {
        sendError('غير مصرح', 403);
    }
}

function resolveRepId(mysqli $db, array $user, ?int $requestedId = null): int
{
    if (isAdmin($user)) {
        if ($requestedId) {
            return $requestedId;
        }
        sendError('repId required', 400);
    }

    $ownRepId = getRepIdForUser($db, $user['id']);

    if ($ownRepId === null) {
        sendError('لا يوجد ملف ممثل طبي مرتبط بحسابك', 403);
    }

    if ($requestedId && $requestedId !== $ownRepId) {
        sendError('غير مصرح', 403);
    }

    return $ownRepId;
}

function getInventoryPharmacyId(mysqli $db, int $inventoryId): ?int
{
    $stmt = $db->prepare('SELECT pharmacy_id FROM inventory WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $inventoryId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $row ? (int) $row['pharmacy_id'] : null;
}

function assertInventoryAccess(mysqli $db, int $inventoryId, array $user): void
{
    $pharmacyId = getInventoryPharmacyId($db, $inventoryId);

    if ($pharmacyId === null) {
        sendError('Item not found', 404);
    }

    assertPharmacyOwnership($db, $pharmacyId, $user);
}

function assertPartnershipRequestAccess(mysqli $db, int $requestId, array $user): void
{
    $stmt = $db->prepare('SELECT rep_id, pharmacy_id FROM partnership_requests WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $requestId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        sendError('Request not found', 404);
    }

    if (isAdmin($user)) {
        return;
    }

    if ($user['role'] === 'med_rep') {
        assertRepOwnership($db, (int) $row['rep_id'], $user);
        return;
    }

    if (in_array($user['role'], ['pharmacist', 'medical_services'], true)) {
        assertPharmacyOwnership($db, (int) $row['pharmacy_id'], $user);
        return;
    }

    sendError('غير مصرح', 403);
}

function assertResupplyRequestAccess(mysqli $db, int $requestId, array $user): void
{
    $stmt = $db->prepare('SELECT rep_id, pharmacy_id FROM resupply_requests WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $requestId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        sendError('Request not found', 404);
    }

    if (isAdmin($user)) {
        return;
    }

    if ($user['role'] === 'med_rep') {
        assertRepOwnership($db, (int) $row['rep_id'], $user);
        return;
    }

    if (in_array($user['role'], ['pharmacist', 'medical_services'], true)) {
        assertPharmacyOwnership($db, (int) $row['pharmacy_id'], $user);
        return;
    }

    sendError('غير مصرح', 403);
}

function assertSupplyRequestAccessPdo(PDO $pdo, int $requestId, array $user): void
{
    $stmt = $pdo->prepare('SELECT pharmacy_id, target_rep_id, assigned_rep_id FROM supply_requests WHERE id = ? LIMIT 1');
    $stmt->execute([$requestId]);
    $row = $stmt->fetch();

    if (!$row) {
        sendError('الطلب غير موجود', 404);
    }

    if (isAdmin($user)) {
        return;
    }

    if (in_array($user['role'], ['pharmacist', 'medical_services'], true)) {
        $db = getDB();
        assertPharmacyOwnership($db, (int) $row['pharmacy_id'], $user);
        $db->close();
        return;
    }

    if ($user['role'] === 'med_rep') {
        $db = getDB();
        $repId = getRepIdForUser($db, $user['id']);
        $db->close();

        if ($repId === null) {
            sendError('غير مصرح', 403);
        }

        $target = (int) ($row['target_rep_id'] ?? 0);
        $assigned = (int) ($row['assigned_rep_id'] ?? 0);

        if ($repId !== $target && $repId !== $assigned) {
            sendError('غير مصرح', 403);
        }
        return;
    }

    sendError('غير مصرح', 403);
}

function assertProfessionalProfileAccessPdo(PDO $pdo, int $profileId, array $user): void
{
    if (isAdmin($user)) {
        return;
    }

    $stmt = $pdo->prepare('SELECT user_id FROM professional_profiles WHERE id = ? LIMIT 1');
    $stmt->execute([$profileId]);
    $row = $stmt->fetch();

    if (!$row) {
        sendError('الملف غير موجود', 404);
    }

    if ((int) $row['user_id'] !== $user['id']) {
        sendError('غير مصرح', 403);
    }
}

/** @return array{pharmacy_id:?int,rep_id:?int} */
function getUserContext(mysqli $db, array $user): array
{
    $pharmacyId = null;
    $repId = null;

    if (in_array($user['role'], ['pharmacist', 'medical_services'], true)) {
        $ids = getPharmacyIdsForUser($db, $user['id']);
        $pharmacyId = $ids[0] ?? null;
    }

    if ($user['role'] === 'med_rep') {
        $repId = getRepIdForUser($db, $user['id']);
    }

    return ['pharmacy_id' => $pharmacyId, 'rep_id' => $repId];
}