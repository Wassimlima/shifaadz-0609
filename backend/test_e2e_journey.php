<?php
/**
 * End-to-end user journey test for all professional roles + admin.
 * Run: php backend/test_e2e_journey.php
 */
require_once __DIR__ . '/utils/redirects.php';

$site = 'http://localhost/shifaa_dizad';
$api  = $site . '/backend/api';

$passed = 0;
$failed = 0;
$issues = [];

function check(string $journey, string $step, bool $ok, string $detail = ''): void
{
    global $passed, $failed, $issues;
    $label = "[$journey] $step";
    if ($ok) {
        echo "[PASS] $label\n";
        $passed++;
    } else {
        echo "[FAIL] $label" . ($detail ? " — $detail" : '') . "\n";
        $failed++;
        $issues[] = "$label" . ($detail ? " — $detail" : '');
    }
}

function http(string $url, string $method = 'GET', ?array $body = null, ?string $cookieFile = null, bool $json = true): array
{
    $ch = curl_init($url);
    $headers = [];
    if ($json && $body !== null) {
        $headers[] = 'Content-Type: application/json';
    }
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 15,
    ];
    if ($cookieFile) {
        $opts[CURLOPT_COOKIEJAR]  = $cookieFile;
        $opts[CURLOPT_COOKIEFILE] = $cookieFile;
    }
    curl_setopt_array($ch, $opts);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json ? json_encode($body, JSON_UNESCAPED_UNICODE) : http_build_query($body));
    }
    $raw  = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $decoded = json_decode($raw, true);
    return ['code' => $code, 'body' => is_array($decoded) ? $decoded : $raw, 'raw' => $raw];
}

function pageOk(string $path): bool
{
    global $site;
    $ch = curl_init($site . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true, CURLOPT_TIMEOUT => 8]);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code === 200;
}

function newCookie(): string
{
    return tempnam(sys_get_temp_dir(), 'e2e_');
}

echo "=== Shifaa DZ — End-to-End Journey Tests ===\n\n";

// ── 1. Visitor (anonymous) ─────────────────────────────────────────────────
echo "--- Visitor (public) ---\n";
$pubCookie = newCookie();
$r = http("$api/medicines/search.php?q=Doliprane", 'GET', null, $pubCookie);
check('visitor', '1. search medicines', $r['code'] === 200 && !empty($r['body']['medicines']));

$r = http("$api/pharmacies/index.php", 'GET', null, $pubCookie);
check('visitor', '2. list pharmacies', $r['code'] === 200);

$r = http("$api/subscriptions/plans.php", 'GET', null, $pubCookie);
check('visitor', '3. subscription plans', $r['code'] === 200 && !empty($r['body']['plans']));

check('visitor', '4. home page', pageOk('/frontend/index.html'));
check('visitor', '5. pricing page', pageOk('/frontend/pages/pricing.html'));
check('visitor', '6. subscription page', pageOk('/frontend/pages/subscription.html'));

$r = http("$api/auth/check.php", 'GET', null, $pubCookie);
check('visitor', '7. not logged in', ($r['body']['logged_in'] ?? true) === false);

// ── Professional roles via subscribe → dashboard → actions → logout → login ─
$roles = [
    'pharmacist' => [
        'plan'      => 'pharmacy-pro',
        'dashboard' => '/frontend/pages/professional/pharmacy-dashboard.html',
        'redirect'  => getRoleRedirect('pharmacist'),
        'stats'     => '/dashboard/stats.php',
        'action'    => ['/inventory/index.php', 'GET', null],
    ],
    'med_rep' => [
        'plan'      => 'medrep-monthly',
        'dashboard' => '/frontend/pages/professional/medrep-dashboard.html',
        'redirect'  => getRoleRedirect('med_rep'),
        'stats'     => '/med-rep/dashboard.php',
        'action'    => ['/resupply/requests.php', 'GET', null],
    ],
    'lab' => [
        'plan'      => 'lab-premium',
        'dashboard' => '/frontend/pages/professional/laboratory-dashboard.html',
        'redirect'  => getRoleRedirect('lab'),
        'stats'     => '/labs/my-stats.php',
        'action'    => ['/labs/my-analyses.php', 'GET', null],
    ],
    'medical_services' => [
        'plan'      => 'medservices-pro',
        'dashboard' => '/frontend/pages/professional/medical-services-dashboard.html',
        'redirect'  => getRoleRedirect('medical_services'),
        'stats'     => '/dashboard/stats.php',
        'action'    => ['/inventory/index.php', 'GET', null],
    ],
];

foreach ($roles as $role => $cfg) {
    echo "\n--- Role: $role ---\n";
    $cookie = newCookie();
    $uid    = str_replace('.', '', uniqid('', true));
    $email  = "e2e_{$role}_{$uid}@shifaa.test";
    $userPass = 'E2eTest123!';

    // 2–3. Registration + Subscription
    $sub = http("$api/subscriptions/subscribe.php", 'POST', [
        'plan_id'           => $cfg['plan'],
        'full_name'         => "E2E $role",
        'email'             => $email,
        'phone'             => '05' . substr($uid, -8),
        'password'          => $userPass,
        'organization_name' => "E2E Org $role",
    ], $cookie);

    check($role, '2–3. subscribe (register)', $sub['code'] === 200 && ($sub['body']['role'] ?? '') === $role,
        "HTTP {$sub['code']} role=" . ($sub['body']['role'] ?? 'null'));
    check($role, '3b. redirect after subscribe', ($sub['body']['redirect'] ?? '') === $cfg['redirect']);

    // 4. Session / login state
    $chk = http("$api/auth/check.php", 'GET', null, $cookie);
    check($role, '4a. auth check logged in', ($chk['body']['logged_in'] ?? false) === true);
    check($role, '4b. auth check role', ($chk['body']['role'] ?? '') === $role);

    // 5. Dashboard page + API
    check($role, '5a. dashboard HTML', pageOk($cfg['dashboard']));
    $stats = http("$api{$cfg['stats']}", 'GET', null, $cookie);
    check($role, '5b. dashboard stats API', $stats['code'] === 200, "HTTP {$stats['code']}");
    if (!str_ends_with($cfg['plan'], '-free') && in_array($role, ['pharmacist', 'medical_services', 'lab'], true)) {
        $planKey = $stats['body']['subscription_plan'] ?? $stats['body']['plan'] ?? null;
        check($role, '5c. paid plan on dashboard', $planKey !== null && $planKey !== 'free',
            'plan=' . var_export($planKey, true));
    }

    // 6. Main action
    [$actionPath, $method, $actionBody] = $cfg['action'];
    $act = http("$api{$actionPath}", $method, $actionBody, $cookie);
    check($role, '6. main dashboard action', $act['code'] === 200, "HTTP {$act['code']} path=$actionPath");

    // 7. Logout
    $out = http("$api/auth/logout.php", 'POST', [], $cookie);
    check($role, '7a. logout', $out['code'] === 200);
    $chk2 = http("$api/auth/check.php", 'GET', null, $cookie);
    check($role, '7b. session cleared', ($chk2['body']['logged_in'] ?? true) === false);

    // 8. Login again
    $login = http("$api/auth/login.php", 'POST', ['email' => $email, 'password' => $userPass], $cookie);
    check($role, '8a. login again', $login['code'] === 200 && ($login['body']['role'] ?? '') === $role);
    check($role, '8b. login redirect', ($login['body']['redirect'] ?? '') === $cfg['redirect']);

    $stats2 = http("$api{$cfg['stats']}", 'GET', null, $cookie);
    check($role, '8c. dashboard after re-login', $stats2['code'] === 200);

    @unlink($cookie);
}

// ── Admin journey (no public subscription) ─────────────────────────────────
echo "\n--- Role: admin ---\n";
$adminCookie = newCookie();
$adminEmail  = 'admin@shifaa.dz';
$adminPass   = 'Admin123!';

// Admin cannot subscribe as professional
$badSub = http("$api/subscriptions/subscribe.php", 'POST', [
    'plan_id'  => 'pharmacy-pro',
    'full_name'=> 'Fake Admin',
    'email'    => $adminEmail,
    'password' => 'Test123!',
], $adminCookie);
check('admin', '2. subscription blocked for admin email', $badSub['code'] === 403 || !empty($badSub['body']['error']));

check('admin', '3. admin login page', pageOk('/frontend/pages/admin-login.html'));

$login = http("$api/auth/login.php", 'POST', ['email' => $adminEmail, 'password' => $adminPass], $adminCookie);
check('admin', '4. login', $login['code'] === 200 && ($login['body']['role'] ?? '') === 'admin');
check('admin', '4b. redirect', ($login['body']['redirect'] ?? '') === getRoleRedirect('admin'));

check('admin', '5a. dashboard HTML', pageOk('/frontend/pages/admin/dashboard.html'));
$stats = http("$api/admin/stats.php", 'GET', null, $adminCookie);
check('admin', '5b. admin stats API', $stats['code'] === 200 && isset($stats['body']['totals']['users']));

$users = http("$api/admin/users.php", 'GET', null, $adminCookie);
check('admin', '6. list users action', $users['code'] === 200);

$out = http("$api/auth/logout.php", 'POST', [], $adminCookie);
check('admin', '7a. logout', $out['code'] === 200);
$chk = http("$api/auth/check.php", 'GET', null, $adminCookie);
check('admin', '7b. session cleared', ($chk['body']['logged_in'] ?? true) === false);

$login2 = http("$api/auth/login.php", 'POST', ['email' => $adminEmail, 'password' => $adminPass], $adminCookie);
check('admin', '8a. login again', $login2['code'] === 200);
$stats2 = http("$api/admin/stats.php", 'GET', null, $adminCookie);
check('admin', '8b. stats after re-login', $stats2['code'] === 200);

@unlink($adminCookie);
@unlink($pubCookie);

echo "\n=== Summary: $passed passed, $failed failed ===\n";
if ($issues) {
    echo "\nBroken flows:\n";
    foreach ($issues as $i) {
        echo "  - $i\n";
    }
}
exit($failed > 0 ? 1 : 0);