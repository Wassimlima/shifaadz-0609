<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';

require_once __DIR__ . '/../../utils/auth.php';
requireAdmin();

$db = getDbConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->query("SELECT * FROM donations ORDER BY created_at DESC");
    sendSuccess($stmt->fetchAll());
}

if ($method === 'POST') {
    $body   = getBody();
    $action = $body['action'] ?? '';
    if ($action === 'toggle') {
        $stmt = $db->prepare("UPDATE donations SET is_available = NOT is_available WHERE id = ?");
        $stmt->execute([$body['id']]);
        sendSuccess(['message' => 'تم التحديث']);
    }
    if ($action === 'delete') {
        $stmt = $db->prepare("DELETE FROM donations WHERE id = ?");
        $stmt->execute([$body['id']]);
        sendSuccess(['message' => 'تم الحذف']);
    }
}

sendError('طريقة غير مدعومة', 405);
