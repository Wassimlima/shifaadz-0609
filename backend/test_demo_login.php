<?php
/**
 * Real HTTP login test for all demo accounts.
 * Run: php backend/test_demo_login.php
 */
$api = 'http://localhost/shifaa_dizad/backend/api/auth/login.php';

$accounts = [
    ['admin@shifaa.dz',       'Admin123!', 'admin'],
    ['pharma@shifaa.dz',      'Demo123!',  'pharmacist'],
    ['medrep@shifaa.dz',      'Demo123!',  'med_rep'],
    ['lab@shifaa.dz',         'Demo123!',  'lab'],
    ['medservices@shifaa.dz', 'Demo123!',  'medical_services'],
];

$pass = 0;
$fail = 0;

echo "=== Demo account API login tests ===\n\n";

foreach ($accounts as [$email, $password, $expectedRole]) {
    $ch = curl_init($api);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode(['email' => $email, 'password' => $password]),
    ]);
    $raw  = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($raw, true);
    $role = $data['role'] ?? null;
    $err  = $data['error'] ?? null;
    $ok   = $code === 200 && $role === $expectedRole && empty($err);

    echo sprintf(
        "%-28s HTTP=%-3d role=%-18s %s%s\n",
        $email,
        $code,
        $role ?? '—',
        $ok ? 'PASS' : 'FAIL',
        $err ? " — $err" : (!$ok && !$err ? " — raw: " . substr($raw, 0, 120) : '')
    );

    $ok ? $pass++ : $fail++;
}

echo "\nResult: $pass passed, $fail failed\n";
exit($fail > 0 ? 1 : 0);