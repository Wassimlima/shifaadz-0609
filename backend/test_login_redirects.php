<?php
$users = [
    ['pharma@shifaa.dz', 'Demo123!'],
    ['medrep@shifaa.dz', 'Demo123!'],
    ['lab@shifaa.dz', 'Demo123!'],
    ['medservices@shifaa.dz', 'Demo123!'],
    ['admin@shifaa.dz', 'Admin123!'],
];

$base = 'http://localhost/shifaa_dizad/backend/api/auth/login.php';
$ok = 0;
$fail = 0;

$expected = [
    'admin' => '/shifaa_dizad/frontend/pages/admin/dashboard.html',
    'pharmacist' => '/shifaa_dizad/frontend/pages/professional/pharmacy-dashboard.html',
    'med_rep' => '/shifaa_dizad/frontend/pages/professional/medrep-dashboard.html',
    'lab' => '/shifaa_dizad/frontend/pages/professional/laboratory-dashboard.html',
    'medical_services' => '/shifaa_dizad/frontend/pages/professional/medical-services-dashboard.html',
];

foreach ($users as [$email, $pass]) {
    $ch = curl_init($base);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode(['email' => $email, 'password' => $pass]),
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($body, true);
    $role = $data['role'] ?? null;
    $redirect = $data['redirect'] ?? null;
    $exp = $expected[$role] ?? null;
    $match = ($redirect === $exp);

    echo sprintf(
        "%-28s HTTP=%d role=%-18s redirect=%s %s\n",
        $email,
        $code,
        $role ?? 'NULL',
        $redirect ?? 'NULL',
        $match ? 'OK' : 'FAIL expected=' . ($exp ?? 'n/a')
    );

    if ($match && $code === 200) {
        $ok++;
    } else {
        $fail++;
    }
}

echo "\nResult: {$ok} passed, {$fail} failed\n";
exit($fail > 0 ? 1 : 0);