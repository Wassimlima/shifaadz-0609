<?php
/**
 * GET|POST /api/medical-services/my-services.php
 * Medical Services: full CRUD for services.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

session_start();

$user = requireRole('medical_services');
$db   = getDbConnection();
$data = json_decode(file_get_contents('php://input'), true) ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    try {
        if ($id) {
            $stmt = $db->prepare(\"SELECT * FROM medical_services WHERE id = ? AND provider_user_id = ?\");
            $stmt->execute([$id, $user['id']]);
            $service = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$service) Response::error('الخدمة غير موجودة', 404);
            Response::success($service);
        } else {
            $stmt = $db->prepare(\"SELECT * FROM medical_services WHERE provider_user_id = ? ORDER BY created_at DESC\");
            $stmt->execute([$user['id']]);
            Response::success($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    } catch (PDOException $e) {
        Response::serverError('خطأ في جلب البيانات');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $data['action'] ?? 'create';

    try {
        if ($action === 'create') {
            $stmt = $db->prepare(\"
                INSERT INTO medical_services (provider_user_id, name, name_ar, description, category, price, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            \");
            $stmt->execute([
                $user['id'],
                $data['name'],
                $data['name_ar'] ?? null,
                $data['description'] ?? null,
                $data['category'] ?? null,
                $data['price'] ?? 0
            ]);
            Response::success(['id' => $db->lastInsertId()], 'تمت إضافة الخدمة بنجاح', 201);
        } 
        elseif ($action === 'update') {
            $id = (int)($data['id'] ?? 0);
            $stmt = $db->prepare(\"
                UPDATE medical_services 
                SET name=?, name_ar=?, description=?, category=?, price=?
                WHERE id=? AND provider_user_id=?
            \");
            $stmt->execute([
                $data['name'],
                $data['name_ar'],
                $data['description'],
                $data['category'],
                $data['price'],
                $id,
                $user['id']
            ]);
            Response::success(null, 'تم تحديث الخدمة بنجاح');
        } 
        elseif ($action === 'delete') {
            $id = (int)($data['id'] ?? 0);
            $stmt = $db->prepare(\"DELETE FROM medical_services WHERE id=? AND provider_user_id=?\");
            $stmt->execute([$id, $user['id']]);
            Response::success(null, 'تم الحذف بنجاح');
        }
    } catch (PDOException $e) {
        Response::serverError('خطأ في تنفيذ العملية');
    }
    exit;
}

Response::error('طريقة غير مدعومة', 405);
