<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) sendError('معرّف الصيدلية مطلوب', 400);

$db = getDbConnection();
$stmt = $db->prepare("SELECT * FROM pharmacies WHERE id = ?");
$stmt->execute([$id]);
$pharmacy = $stmt->fetch();
if (!$pharmacy) sendError('الصيدلية غير موجودة', 404);

// Get medicines
$meds = $db->prepare("SELECT id, name, name_ar, price, availability, dosage, form_type, type FROM medicines WHERE pharmacy_id = ? ORDER BY name LIMIT 50");
$meds->execute([$id]);
$pharmacy['medicines'] = $meds->fetchAll();

sendSuccess($pharmacy);
