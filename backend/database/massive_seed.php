<?php
require_once __DIR__ . '/../config/database.php';
$db = getDB();
$db->set_charset('utf8mb4');

echo "Starting massive seeding...\n";

$wilayas = ['الجزائر', 'وهران', 'قسنطينة', 'عنابة', 'البليدة', 'باتنة', 'سطيف', 'الشلف', 'سيدي بلعباس', 'بسكرة', 'سكيكدة', 'تبسة', 'جيجل', 'بجاية', 'تلمسان', 'ورقلة'];

$medNames = [
  ['Doliprane', 'دولوبران', 'Paracetamol', 'Analgesic'],
  ['Ventoline', 'فنتولين', 'Salbutamol', 'Respiratory'],
  ['Augmentin', 'أوغمونتان', 'Amoxicillin/Clavulanic acid', 'Antibiotic'],
  ['Spasfon', 'سباسمون', 'Phloroglucinol', 'Antispasmodic'],
  ['Mopral', 'موبرال', 'Omeprazole', 'Gastric'],
  ['Lasilix', 'لازيليكس', 'Furosemide', 'Diuretic'],
  ['Gaviscon', 'جافيسكون', 'Alginate', 'Gastric'],
  ['Inexium', 'إينكسيوم', 'Esomeprazole', 'Gastric'],
  ['Kardegic', 'كارديجيك', 'Aspirin', 'Cardiovascular'],
  ['Lovenox', 'لوفينوكس', 'Enoxaparin', 'Anticoagulant']
];

echo "Seeding Pharmacies...\n";
for ($i = 1; $i <= 50; $i++) {
    $wilaya = $wilayas[array_rand($wilayas)];
    $email = "pharmacy{$i}@example.com";
    $name = "صيدلية البركة $i";
    
    $db->query("INSERT IGNORE INTO users (full_name, email, phone, role, password_hash, is_verified) 
                VALUES ('Pharmacy $i', '$email', '05500000{$i}', 'pharmacist', '\$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1)");
    $userId = $db->insert_id;
    if ($userId) {
        $db->query("INSERT INTO pharmacies (owner_user_id, name, address, wilaya, city, phone, email, is_verified, plan) 
                    VALUES ($userId, '$name', 'Street $i', '$wilaya', '$wilaya', '05500000{$i}', '$email', 1, 'enterprise')");
        $phId = $db->insert_id;
        for ($j = 0; $j < 10; $j++) {
            $med = $medNames[array_rand($medNames)];
            $qty = rand(5, 100);
            $price = rand(200, 5000);
            $db->query("INSERT INTO inventory (pharmacy_id, product_name, category, quantity, price, status) 
                        VALUES ($phId, '{$med[0]}', 'medicine', $qty, $price, 'available')");
            $db->query("INSERT INTO medicines (pharmacy_id, name, name_ar, active_ingredient, dosage, type, availability, quantity, price) 
                        VALUES ($phId, '{$med[0]}', '{$med[1]}', '{$med[2]}', '500mg', 'medicine', 'available', $qty, $price)");
        }
    }
}

// Seed Labs
echo "Seeding Labs...\n";
for ($i = 1; $i <= 40; $i++) {
    $wilaya = $wilayas[array_rand($wilayas)];
    $email = "lab{$i}@example.com";
    $name = "مخبر $i";
    $db->query("INSERT IGNORE INTO users (full_name, email, phone, role, password_hash, is_verified) 
                VALUES ('Lab $i', '$email', '06600000{$i}', 'lab', '\$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1)");
    $userId = $db->insert_id;
    if ($userId) {
        $db->query("INSERT INTO labs (owner_user_id, name, name_ar, address, wilaya, city, phone, email, is_open) 
                    VALUES ($userId, 'Lab $i', '$name', 'Street $i', '$wilaya', '$wilaya', '06600000{$i}', '$email', 1)");
    }
}

// Seed Medical Services
echo "Seeding Medical Services...\n";
for ($i = 1; $i <= 30; $i++) {
    $wilaya = $wilayas[array_rand($wilayas)];
    $email = "service{$i}@example.com";
    $name = "مركز خدمات $i";
    $db->query("INSERT IGNORE INTO users (full_name, email, phone, role, password_hash, is_verified) 
                VALUES ('Service $i', '$email', '07700000{$i}', 'medical_services', '\$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1)");
    $userId = $db->insert_id;
    if ($userId) {
        $db->query("INSERT INTO medical_service_providers (owner_user_id, name, name_ar, address, wilaya, city, phone, email) 
                    VALUES ($userId, 'Service $i', '$name', 'Street $i', '$wilaya', '$wilaya', '07700000{$i}', '$email')");
    }
}

echo "Seeding completed successfully!\n";
$db->close();
?>
