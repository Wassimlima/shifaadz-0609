<?php
/**
 * UI audit: pages reachable + API smoke + broken handler scan.
 * Run: php backend/test_ui_audit.php
 */
$base = 'http://localhost/shifaa_dizad';
$pages = [
    '/frontend/index.html',
    '/frontend/pages/search.html',
    '/frontend/pages/pharmacies.html',
    '/frontend/pages/labs.html',
    '/frontend/pages/donations.html',
    '/frontend/pages/contact.html',
    '/frontend/pages/pricing.html',
    '/frontend/pages/subscription.html',
    '/frontend/pages/login.html',
    '/frontend/pages/prescription.html',
    '/frontend/pages/professional/pharmacy-dashboard.html',
    '/frontend/pages/professional/medrep-dashboard.html',
    '/frontend/pages/professional/medical-services-dashboard.html',
    '/frontend/pages/professional/laboratory-dashboard.html',
    '/frontend/pages/admin/dashboard.html',
];

$broken = [];
$ok = 0;

foreach ($pages as $p) {
    $url = $base . $p;
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true, CURLOPT_TIMEOUT => 8]);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code >= 200 && $code < 400) {
        $ok++;
        echo "[PASS] $p ($code)\n";
    } else {
        $broken[] = "$p HTTP $code";
        echo "[FAIL] $p ($code)\n";
    }
}

$htmlChecks = [
    ['pharmacy-dashboard.html', 'inventory-tbody', 'dashboard-pharmacy.js'],
    ['medrep-dashboard.html', 'products-content', 'dashboard-medrep.js'],
    ['medical-services-dashboard.html', 'ms-inventory-tbody', 'dashboard-medical-services.js'],
    ['laboratory-dashboard.html', 'analyses-tbody', 'dashboard-lab.js'],
];

foreach ($htmlChecks as [$file, $id, $js]) {
    $path = __DIR__ . '/../frontend/pages/professional/' . $file;
    if ($file === 'laboratory-dashboard.html') {
        $path = __DIR__ . '/../frontend/pages/professional/laboratory-dashboard.html';
    }
    if (strpos($file, 'admin') !== false) {
        $path = __DIR__ . '/../frontend/pages/admin/dashboard.html';
    }
    $html = @file_get_contents($path);
    if (!$html) {
        $broken[] = "Missing file $file";
        continue;
    }
    if (strpos($html, "id=\"$id\"") === false && strpos($html, "id='$id'") === false) {
        $broken[] = "$file missing #$id";
        echo "[FAIL] $file missing element #$id\n";
    } elseif (strpos($html, $js) === false) {
        $broken[] = "$file missing script $js";
        echo "[FAIL] $file missing $js\n";
    } else {
        echo "[PASS] $file wired to $js (#$id)\n";
    }
}

echo "\nPages OK: $ok/" . count($pages) . "\n";
if ($broken) {
    echo "Issues:\n- " . implode("\n- ", $broken) . "\n";
    exit(1);
}
echo "All UI audit checks passed.\n";