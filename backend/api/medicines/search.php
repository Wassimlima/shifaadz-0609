<?php
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../config/database.php';

$db = getDB();
$db->set_charset('utf8mb4');

$qRaw = trim((string) ($_GET['q'] ?? ''));
$q = $qRaw !== '' ? '%' . $db->real_escape_string($qRaw) . '%' : '%';
$wilaya = trim((string) ($_GET['wilaya'] ?? ''));
$module = $_GET['module'] ?? 'medicine'; // medicine, lab, service

$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = min(20, (int) ($_GET['limit'] ?? 10));
$offset = ($page - 1) * $limit;

if ($module === 'lab') {
    $where = "WHERE (name LIKE ? OR name_ar LIKE ? OR address LIKE ?)";
    $params = [$q, $q, $q];
    $types = "sss";
    if ($wilaya !== '') {
        $where .= " AND wilaya = ?";
        $params[] = $db->real_escape_string($wilaya);
        $types .= "s";
    }
    $sql = "SELECT * FROM labs $where ORDER BY rating DESC LIMIT ? OFFSET ?";
    $params[] = $limit; $params[] = $offset; $types .= "ii";
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $countSql = "SELECT COUNT(*) as total FROM labs $where";
    $countStmt = $db->prepare($countSql);
    $countStmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2));
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];
    
    $db->close();
    sendJSON(['results' => $results, 'total' => (int)$total, 'module' => 'lab']);

} elseif ($module === 'service') {
    $where = "WHERE (p.name LIKE ? OR p.name_ar LIKE ? OR s.name LIKE ? OR s.name_ar LIKE ?)";
    $params = [$q, $q, $q, $q];
    $types = "ssss";
    if ($wilaya !== '') {
        $where .= " AND p.wilaya = ?";
        $params[] = $db->real_escape_string($wilaya);
        $types .= "s";
    }
    $sql = "SELECT s.*, p.name as provider_name, p.wilaya, p.city, p.phone 
            FROM medical_services s 
            JOIN medical_service_providers p ON s.provider_id = p.id 
            $where LIMIT ? OFFSET ?";
    $params[] = $limit; $params[] = $offset; $types .= "ii";
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $countSql = "SELECT COUNT(*) as total FROM medical_services s JOIN medical_service_providers p ON s.provider_id = p.id $where";
    $countStmt = $db->prepare($countSql);
    $countStmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2));
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];
    
    $db->close();
    sendJSON(['results' => $results, 'total' => (int)$total, 'module' => 'service']);

} else {
    // Default: Medicine
    $type = $_GET['type'] ?? '';
    $availability = $_GET['availability'] ?? '';
    
    $where = "WHERE (m.name LIKE ? OR m.name_ar LIKE ? OR m.active_ingredient LIKE ?)";
    $params = [$q, $q, $q];
    $types = "sss";
    
    if ($wilaya !== '') {
        $where .= " AND p.wilaya = ?";
        $params[] = $db->real_escape_string($wilaya);
        $types .= "s";
    }
    if ($type !== '') {
        $where .= " AND m.type = ?";
        $params[] = $db->real_escape_string($type);
        $types .= "s";
    }
    
    $sql = "SELECT m.*, p.name as pharmacy_name, p.wilaya, p.city, p.phone as pharmacy_phone 
            FROM medicines m 
            JOIN pharmacies p ON m.pharmacy_id = p.id 
            $where ORDER BY m.availability ASC LIMIT ? OFFSET ?";
    $params[] = $limit; $params[] = $offset; $types .= "ii";
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $countSql = "SELECT COUNT(*) as total FROM medicines m JOIN pharmacies p ON m.pharmacy_id = p.id $where";
    $countStmt = $db->prepare($countSql);
    $countStmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2));
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];
    
    $db->close();
    sendJSON(['results' => $results, 'total' => (int)$total, 'module' => 'medicine']);
}
