<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../utils/auth.php';

$user = requireAuth(['med_rep', 'admin']);

$db = getDB();
$repId = resolveRepId($db, $user, isset($_GET['repId']) ? (int) $_GET['repId'] : null);

$stmt = $db->prepare('SELECT *, company_name AS name FROM med_reps WHERE id = ?');
$stmt->bind_param('i', $repId);
$stmt->execute();
$rep = $stmt->get_result()->fetch_assoc();

if (!$rep) {
    sendError('Rep not found', 404);
}

$productsResult = $db->prepare('SELECT * FROM rep_products WHERE rep_id = ?');
$productsResult->bind_param('i', $repId);
$productsResult->execute();
$products = $productsResult->get_result()->fetch_all(MYSQLI_ASSOC);

$alertsResult = $db->prepare("SELECT * FROM rep_alerts WHERE rep_id = ? ORDER BY FIELD(severity,'high','medium','low'), created_at DESC");
$alertsResult->bind_param('i', $repId);
$alertsResult->execute();
$alerts = $alertsResult->get_result()->fetch_all(MYSQLI_ASSOC);

$partnersResult = $db->prepare('
    SELECT pr.*, p.name AS pharmacy_name, p.city, p.phone AS pharmacy_phone
    FROM partnership_requests pr
    LEFT JOIN pharmacies p ON pr.pharmacy_id = p.id
    WHERE pr.rep_id = ?
    ORDER BY pr.created_at DESC
');
$partnersResult->bind_param('i', $repId);
$partnersResult->execute();
$partners = $partnersResult->get_result()->fetch_all(MYSQLI_ASSOC);

$acceptedCount = count(array_filter($partners, fn ($p) => $p['status'] === 'accepted'));
$stats = [
    'totalProducts'      => count($products),
    'partnerPharmacies'  => $acceptedCount,
    'urgentAlerts'       => count(array_filter($alerts, fn ($a) => $a['severity'] === 'high')),
    'pendingResupply'    => 0,
];

$db->close();
sendJSON([
    'rep'                => $rep,
    'stats'              => $stats,
    'products'           => $products,
    'alerts'             => $alerts,
    'partnerPharmacies'  => $partners,
    'repId'              => $repId,
]);