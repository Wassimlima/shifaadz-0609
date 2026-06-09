<?php
/**
 * Tests subscription API returns correct role + redirect per plan.
 */
require_once __DIR__ . '/utils/redirects.php';

$base = 'http://localhost/shifaa_dizad/backend/api/subscriptions/subscribe.php';

$plans = [
    'pharmacy-free'         => ['role' => 'pharmacist',       'dashboard' => '/shifaa_dizad/frontend/pages/professional/pharmacy-dashboard.html'],
    'pharmacy-pro'           => ['role' => 'pharmacist',       'dashboard' => '/shifaa_dizad/frontend/pages/professional/pharmacy-dashboard.html'],
    'medrep-monthly'         => ['role' => 'med_rep',          'dashboard' => '/shifaa_dizad/frontend/pages/professional/medrep-dashboard.html'],
    'lab-premium'            => ['role' => 'lab',              'dashboard' => '/shifaa_dizad/frontend/pages/professional/laboratory-dashboard.html'],
    'medservices-pro'        => ['role' => 'medical_services', 'dashboard' => '/shifaa_dizad/frontend/pages/professional/medical-services-dashboard.html'],
];

$adminDash = '/shifaa_dizad/frontend/pages/admin/dashboard.html';
$ok = 0;
$fail = 0;

echo "=== Subscription redirect tests ===\n\n";

foreach ($plans as $planId => $exp) {
    $email = 'subtest_' . str_replace('-', '_', $planId) . '_' . uniqid('', true) . '@shifaa.test';
    $payload = json_encode([
        'plan_id'           => $planId,
        'full_name'         => 'Test ' . $planId,
        'email'             => $email,
        'phone'             => '05' . substr(str_replace('.', '', uniqid('', true)), -8),
        'password'          => 'Test123!',
        'organization_name' => 'Test Org ' . $planId,
    ]);

    $ch = curl_init($base);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => $payload,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($body, true);
    $roleOk = ($data['role'] ?? '') === $exp['role'];
    $redirectOk = ($data['redirect'] ?? '') === $exp['dashboard'];
    $notAdmin = ($data['redirect'] ?? '') !== $adminDash;
    $pass = $code === 200 && $roleOk && $redirectOk && $notAdmin;

    echo sprintf(
        "%-22s HTTP=%d role=%-18s (exp %-18s) redirect=%s %s\n",
        $planId,
        $code,
        $data['role'] ?? 'NULL',
        $exp['role'],
        $pass ? 'OK' : 'FAIL got ' . ($data['redirect'] ?? 'NULL'),
        $pass ? '✓' : '✗'
    );

    if (!$pass && !empty($data['error'])) {
        echo "  error: {$data['error']}\n";
    }

    $pass ? $ok++ : $fail++;
}

echo "\nResult: {$ok} passed, {$fail} failed\n";
exit($fail > 0 ? 1 : 0);