<?php
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../config/database.php';

$db     = getDB();
$q      = isset($_GET['q']) ? '%' . $db->real_escape_string($_GET['q']) . '%' : '%';
$wilaya = isset($_GET['wilaya']) ? $db->real_escape_string($_GET['wilaya']) : '';
$cat    = isset($_GET['category']) ? $db->real_escape_string($_GET['category']) : '';

$where = "WHERE (la.name LIKE '$q' OR la.name_ar LIKE '$q')";
if ($wilaya) $where .= " AND l.wilaya = '$wilaya'";
if ($cat)    $where .= " AND la.category = '$cat'";

$sql = "SELECT la.*, l.name AS lab_name, l.name_ar AS lab_name_ar,
               l.address, l.wilaya, l.city, l.phone,
               l.is_open, l.opening_hours, l.maps_link, l.rating
        FROM lab_analyses la
        LEFT JOIN labs l ON la.lab_id = l.id
        $where
        ORDER BY la.name ASC
        LIMIT 50";

$result = $db->query($sql);
$analyses = [];
while ($row = $result->fetch_assoc()) $analyses[] = $row;

$total = $db->query("SELECT COUNT(*) as c FROM lab_analyses la LEFT JOIN labs l ON la.lab_id=l.id $where")->fetch_assoc()['c'];
$db->close();
sendJSON(['analyses' => $analyses, 'total' => intval($total)]);
