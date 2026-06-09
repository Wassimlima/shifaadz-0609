<?php
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../config/database.php';

$db = getDB();
$result = $db->query("SELECT * FROM categories ORDER BY id");
$cats = [];
while ($row = $result->fetch_assoc()) $cats[] = $row;
$db->close();
sendJSON($cats);
