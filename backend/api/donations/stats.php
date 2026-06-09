<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';

$db = getDbConnection();

$total     = $db->query("SELECT COUNT(*) FROM donations")->fetchColumn();
$available = $db->query("SELECT COUNT(*) FROM donations WHERE is_available=1")->fetchColumn();
$wilayas   = $db->query("SELECT COUNT(DISTINCT wilaya) FROM donations WHERE wilaya IS NOT NULL")->fetchColumn();
$beneficiaries = (int)$available * 3; // estimated

sendSuccess([
    'total'        => (int)$total,
    'available'    => (int)$available,
    'wilayas'      => (int)$wilayas,
    'beneficiaries'=> $beneficiaries,
]);
