<?php
define('DB_SOCK', '/home/runner/mysql-run/mysql.sock');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'shifaa_dizad');

$conn = new mysqli(null, DB_USER, DB_PASS, DB_NAME, null, DB_SOCK);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error . "\n"); }
$conn->set_charset('utf8mb4');

$accounts = [
    ['Admin Shifaa',     'admin@shifaa.dz',       '0555000001', 'Admin123!',  'admin',             1],
    ['صيدلية النور',     'pharma@shifaa.dz',       '0555000010', 'Demo123!',   'pharmacist',        1],
    ['كريم بن يوسف',    'medrep@shifaa.dz',        '0555000011', 'Demo123!',   'med_rep',           1],
    ['مخبر ابن سينا',   'lab@shifaa.dz',           '0555000012', 'Demo123!',   'lab',               1],
    ['ميدي ستور',       'medservices@shifaa.dz',   '0555000013', 'Demo123!',   'medical_services',  1],
];

$stmt = $conn->prepare("
    INSERT INTO users (full_name, email, phone, password_hash, role, is_verified, is_active)
    VALUES (?, ?, ?, ?, ?, 1, 1)
    ON DUPLICATE KEY UPDATE
        full_name = VALUES(full_name),
        password_hash = VALUES(password_hash),
        role = VALUES(role),
        is_verified = 1,
        is_active = 1
");

foreach ($accounts as $a) {
    $hash = password_hash($a[3], PASSWORD_BCRYPT);
    $stmt->bind_param('sssss', $a[0], $a[1], $a[2], $hash, $a[4]);
    $stmt->execute();
}

echo "Users created successfully.\n";
$conn->close();
