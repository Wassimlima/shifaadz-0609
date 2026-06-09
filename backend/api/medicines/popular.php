<?php
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../config/database.php';

$db = getDB();
$result = $db->query("SELECT m.*, p.name AS pharmacy_name FROM medicines m LEFT JOIN pharmacies p ON m.pharmacy_id = p.id WHERE m.availability = 'available' ORDER BY m.rating DESC LIMIT 6");
$medicines = [];
while ($row = $result->fetch_assoc()) $medicines[] = $row;
$db->close();
sendJSON($medicines);
