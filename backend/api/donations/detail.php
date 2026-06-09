<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) sendError('معرّف التبرع مطلوب', 400);

$db = getDbConnection();
$stmt = $db->prepare("SELECT * FROM donations WHERE id = ?");
$stmt->execute([$id]);
$donation = $stmt->fetch();
if (!$donation) sendError('التبرع غير موجود', 404);

sendSuccess($donation);
