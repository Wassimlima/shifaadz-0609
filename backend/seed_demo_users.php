<?php
/**
 * Create/update Shifaa demo accounts with valid bcrypt passwords.
 * Run after schema install: php backend/seed_demo_users.php
 */
$conn = new mysqli('127.0.0.1', 'root', '', 'shifaa_dizad', 3306);
if ($conn->connect_error) {
    fwrite(STDERR, "DB connect failed: {$conn->connect_error}\n");
    exit(1);
}
$conn->set_charset('utf8mb4');

$accounts = [
    ['Admin Shifaa', 'admin@shifaa.dz', '0555000001', 'Admin123!', 'admin'],
    ['صيدلية النور', 'pharma@shifaa.dz', '0555000010', 'Demo123!', 'pharmacist'],
    ['كريم بن يوسف', 'medrep@shifaa.dz', '0555000011', 'Demo123!', 'med_rep'],
    ['مخبر ابن سينا', 'lab@shifaa.dz', '0555000012', 'Demo123!', 'lab'],
    ['ميدي ستور', 'medservices@shifaa.dz', '0555000013', 'Demo123!', 'medical_services'],
    ['Ahmed Benali', 'ahmed@shifaa.dz', '0555000002', 'Demo123!', 'patient'],
];

$stmt = $conn->prepare('
    INSERT INTO users (full_name, email, phone, password_hash, role, is_verified, is_active)
    VALUES (?, ?, ?, ?, ?, 1, 1)
    ON DUPLICATE KEY UPDATE
        full_name = VALUES(full_name),
        password_hash = VALUES(password_hash),
        role = VALUES(role),
        phone = VALUES(phone),
        is_verified = 1,
        is_active = 1
');

foreach ($accounts as $a) {
    $hash = password_hash($a[3], PASSWORD_BCRYPT);
    $stmt->bind_param('sssss', $a[0], $a[1], $a[2], $hash, $a[4]);
    $stmt->execute();
}

function userId(mysqli $c, string $email): ?int
{
    $st = $c->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $st->bind_param('s', $email);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    return $row ? (int) $row['id'] : null;
}

// Pharmacist: ensure at least one owned pharmacy
$pharmaId = userId($conn, 'pharma@shifaa.dz');
if ($pharmaId) {
    $chk = $conn->query("SELECT id FROM pharmacies WHERE owner_user_id = $pharmaId LIMIT 1");
    if ($chk && $chk->num_rows === 0) {
        $conn->query("UPDATE pharmacies SET owner_user_id = $pharmaId WHERE owner_user_id = 3 LIMIT 1");
    }
    if ($conn->query("SELECT id FROM pharmacies WHERE owner_user_id = $pharmaId LIMIT 1")->num_rows === 0) {
        $conn->query("
            INSERT INTO pharmacies (owner_user_id, name, address, wilaya, city, phone, email, description, rating, review_count, is_open, opening_hours, plan, is_verified)
            VALUES ($pharmaId, 'صيدلية النور', '5 طريق الاستقلال', 'الجزائر', 'الجزائر العاصمة', '0555000010', 'pharma@shifaa.dz', 'حساب تجريبي', 4.5, 10, 1, '08:00 - 22:00', 'professional', 1)
        ");
    }
}

// Med rep profile
$repUserId = userId($conn, 'medrep@shifaa.dz');
if ($repUserId) {
    $chk = $conn->query("SELECT id FROM med_reps WHERE user_id = $repUserId LIMIT 1");
    if ($chk && $chk->num_rows === 0) {
        $conn->query("
            INSERT INTO med_reps (user_id, company_name, region, email, phone)
            VALUES ($repUserId, 'BioSanté Algeria', 'الجزائر', 'medrep@shifaa.dz', '0555000011')
        ");
    }
}

// Lab profile
$labUserId = userId($conn, 'lab@shifaa.dz');
if ($labUserId) {
    $chk = $conn->query("SELECT id FROM labs WHERE owner_user_id = $labUserId LIMIT 1");
    if ($chk && $chk->num_rows === 0) {
        $conn->query("
            INSERT INTO labs (owner_user_id, name, address, wilaya, city, phone, email, opening_hours, is_open, rating, review_count)
            VALUES ($labUserId, 'مخبر ابن سينا', '12 شارع العربي بن مهيدي', 'الجزائر', 'الجزائر العاصمة', '0555000012', 'lab@shifaa.dz', '08:00 - 18:00', 1, 4.5, 0)
        ");
    }
}

// Medical services: pharmacy + inventory
$msId = userId($conn, 'medservices@shifaa.dz');
if ($msId) {
    $conn->query("
        INSERT INTO pharmacies (owner_user_id, name, address, wilaya, city, phone, email, description, rating, review_count, is_open, opening_hours, plan, is_verified)
        SELECT $msId, 'ميدي ستور', '15 شارع ديدوش مراد', 'الجزائر', 'الجزائر العاصمة', '0555000013', 'medservices@shifaa.dz',
               'متجر خدمات طبية', 4.8, 95, 1, '08:00 - 20:00', 'enterprise', 1
        FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pharmacies WHERE owner_user_id = $msId LIMIT 1)
    ");
    $phRow = $conn->query("SELECT id FROM pharmacies WHERE owner_user_id = $msId LIMIT 1")->fetch_assoc();
    if ($phRow) {
        $phId = (int) $phRow['id'];
        $conn->query("
            INSERT INTO inventory (pharmacy_id, product_name, category, quantity, minimum_stock, status, price)
            SELECT $phId, 'جهاز قياس الضغط', 'device', 25, 5, 'available', 7500
            FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM inventory WHERE pharmacy_id = $phId LIMIT 1)
        ");
    }
}

echo "Demo accounts ready:\n";
echo "  admin@shifaa.dz       / Admin123!  (admin)\n";
echo "  pharma@shifaa.dz      / Demo123!   (pharmacist)\n";
echo "  medrep@shifaa.dz      / Demo123!   (med_rep)\n";
echo "  lab@shifaa.dz         / Demo123!   (lab)\n";
echo "  medservices@shifaa.dz / Demo123!   (medical_services)\n";
echo "  ahmed@shifaa.dz       / Demo123!   (patient)\n";

$conn->close();