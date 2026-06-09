<?php
/**
 * Dashboard completion smoke test — all roles + admin management APIs.
 * Run: php backend/test_dashboard_complete.php
 */
$site = 'http://localhost/shifaa_dizad';
$api  = $site . '/backend/api';
$passed = 0;
$failed = 0;

function check(string $label, bool $ok, string $detail = ''): void
{
    global $passed, $failed;
    if ($ok) {
        echo "[PASS] $label\n";
        $passed++;
    } else {
        echo "[FAIL] $label" . ($detail ? " — $detail" : '') . "\n";
        $failed++;
    }
}

function http(string $url, string $method = 'GET', ?array $body = null, ?string $cookie = null): array
{
    $ch = curl_init($url);
    $opts = [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_TIMEOUT => 15];
    if ($cookie) {
        $opts[CURLOPT_COOKIEJAR] = $cookie;
        $opts[CURLOPT_COOKIEFILE] = $cookie;
    }
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    curl_setopt_array($ch, $opts);
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => json_decode($raw, true), 'raw' => $raw];
}

function login(string $email, string $pass): ?string
{
    global $api;
    $cookie = tempnam(sys_get_temp_dir(), 'dash_');
    $r = http("$api/auth/login.php", 'POST', ['email' => $email, 'password' => $pass], $cookie);
    if ($r['code'] !== 200 || empty($r['body']['role'])) {
        @unlink($cookie);
        return null;
    }
    return $cookie;
}

echo "=== Dashboard Completion Tests ===\n\n";

$demos = [
    'admin' => ['admin@shifaa.dz', 'Admin123!', [
        '/admin/stats.php',
        '/admin/users.php',
        '/admin/pharmacies.php',
        '/admin/labs.php',
        '/admin/med-reps.php',
        '/admin/advertisements-manage.php',
        '/admin/subscriptions.php',
        '/admin/donations-manage.php',
        '/platform/settings.php',
    ]],
    'pharmacist' => ['pharma@shifaa.dz', 'Demo123!', ['/dashboard/stats.php', '/inventory/index.php', '/partnerships/pharmacy.php', '/resupply/requests.php']],
    'med_rep' => ['medrep@shifaa.dz', 'Demo123!', ['/med-rep/dashboard.php', '/resupply/requests.php', '/pharmacies/index.php']],
    'lab' => ['lab@shifaa.dz', 'Demo123!', ['/labs/my-stats.php', '/labs/my-analyses.php']],
    'medical_services' => ['medservices@shifaa.dz', 'Demo123!', ['/dashboard/stats.php', '/inventory/index.php', '/advertisements/my.php']],
];

foreach ($demos as $role => [$email, $pass, $endpoints]) {
    echo "--- $role ---\n";
    $cookie = login($email, $pass);
    check("$role login", $cookie !== null, $email);
    if (!$cookie) continue;

    foreach ($endpoints as $path) {
        $r = http($api . $path, 'GET', null, $cookie);
        check("$role GET $path", $r['code'] === 200, "HTTP {$r['code']}");
    }

    if ($role === 'lab') {
        $r = http("$api/labs/my-analyses.php", 'POST', [
            'action' => 'create',
            'name' => 'Test CBC ' . uniqid(),
            'category' => 'عام',
            'price' => 1500,
        ], $cookie);
        check('lab create analysis', $r['code'] === 200 && empty($r['body']['error']));
    }

    if ($role === 'medical_services') {
        $r = http("$api/advertisements/my.php", 'POST', [
            'title' => 'حملة اختبار ' . uniqid(),
            'ad_type' => 'featured_product',
        ], $cookie);
        check('MS create campaign', $r['code'] === 200 || $r['code'] === 201);
    }

    http("$api/auth/logout.php", 'POST', [], $cookie);
    @unlink($cookie);
}

$pages = [
    '/frontend/pages/admin/dashboard.html',
    '/frontend/pages/professional/pharmacy-dashboard.html',
    '/frontend/pages/professional/medrep-dashboard.html',
    '/frontend/pages/professional/laboratory-dashboard.html',
    '/frontend/pages/professional/medical-services-dashboard.html',
];
foreach ($pages as $p) {
    $ch = curl_init($site . $p);
    curl_setopt_array($ch, [CURLOPT_NOBODY => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8]);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    check("page $p", $code === 200, "HTTP $code");
}

echo "\n=== Summary: $passed passed, $failed failed ===\n";
exit($failed > 0 ? 1 : 0);