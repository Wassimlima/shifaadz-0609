<?php
/**
 * GET|POST /api/labs/my-analyses.php
 * Lab: full CRUD for analyses.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

session_start();

$user = requireRole('lab');
$db   = getDbConnection();
$data = json_decode(file_get_contents('php://input'), true) ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    try {
        if ($id) {
            $stmt = $db->prepare("SELECT * FROM lab_analyses WHERE id = ? AND lab_id = (SELECT id FROM labs WHERE owner_user_id = ?)");
            $stmt->execute([$id, $user['id']]);
            $analysis = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$analysis) Response::error('التحليل غير موجود', 404);
            Response::success($analysis);
        } else {
            $stmt = $db->prepare("SELECT * FROM lab_analyses WHERE lab_id = (SELECT id FROM labs WHERE owner_user_id = ?) ORDER BY created_at DESC");
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
        // Get lab_id for this owner
        $labStmt = $db->prepare("SELECT id FROM labs WHERE owner_user_id = ?");
        $labStmt->execute([$user['id']]);
        $lab = $labStmt->fetch();
        if (!$lab) Response::error('لم يتم العثور على ملف المخبر المهني', 404);
        $labId = $lab['id'];

        if ($action === 'create') {
            $stmt = $db->prepare("
                INSERT INTO lab_analyses (lab_id, name, name_ar, category, price, preparation_time, description, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $labId,
                $data['name'],
                $data['name_ar'] ?? null,
                $data['category'] ?? null,
                $data['price'] ?? 0,
                $data['preparation_time'] ?? null,
                $data['description'] ?? null
            ]);
            Response::success(['id' => $db->lastInsertId()], 'تمت الإضافة بنجاح', 201);
        } 
        elseif ($action === 'update') {
            $id = (int)($data['id'] ?? 0);
            $stmt = $db->prepare("
                UPDATE lab_analyses 
                SET name=?, name_ar=?, category=?, price=?, preparation_time=?, description=?
                WHERE id=? AND lab_id=?
            ");
            $stmt->execute([
                $data['name'],
                $data['name_ar'],
                $data['category'],
                $data['price'],
                $data['preparation_time'],
                $data['description'],
                $id,
                $labId
            ]);
            Response::success(null, 'تم التحديث بنجاح');
        } 
        elseif ($action === 'delete') {
            $id = (int)($data['id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM lab_analyses WHERE id=? AND lab_id=?");
            $stmt->execute([$id, $labId]);
            Response::success(null, 'تم الحذف بنجاح');
        }
    } catch (PDOException $e) {
        Response::serverError('خطأ في تنفيذ العملية');
    }
    exit;
}

Response::error('طريقة غير مدعومة', 405);
