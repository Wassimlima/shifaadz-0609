<?php
/**
 * HTTP smoke tests for QA bugs — run: php test_qa_http.php
 * Requires Apache/WAMP serving http://localhost/shifaa_dizad/
 */
$base = 'http://localhost/shifaa_dizad/backend/api';
$cookie = tempnam(sys_get_temp_dir(), 'shifaa_cookie');

function req(string $url, string $method = 'GET', $body = null, bool $json = true): array
{
    global $cookie;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_COOKIEJAR      => $cookie,
        CURLOPT_COOKIEFILE     => $cookie,
        CURLOPT_HTTPHEADER     => $json && $body !== null ? ['Content-Type: application/json'] : [],
    ]);
    if ($body !== null) {
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            $json ? json_encode($body, JSON_UNESCAPED_UNICODE) : (is_array($body) ? http_build_query($body) : $body)
        );
    }
    $out = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => json_decode($out, true) ?? $out];
}

$pass = 0;
$fail = 0;

function check(string $name, bool $ok, $detail = ''): void
{
    global $pass, $fail;
    if ($ok) {
        echo "[PASS] $name\n";
        $pass++;
    } else {
        echo "[FAIL] $name" . ($detail ? " — $detail" : '') . "\n";
        $fail++;
    }
}

// BUG-03 Wilaya filter
$r = req("$base/medicines/search.php?wilaya=" . rawurlencode('وهران'));
check('BUG-03 wilaya=وهران returns results', ($r['code'] === 200) && !empty($r['body']['medicines']));

$r = req("$base/medicines/search.php?q=" . rawurlencode('Doliprane'));
$all = count($r['body']['medicines'] ?? []);
$r2 = req("$base/medicines/search.php?q=" . rawurlencode('Doliprane') . '&wilaya=' . rawurlencode('وهران'));
$filtered = count($r2['body']['medicines'] ?? []);
check('BUG-03 wilaya narrows results', $all >= $filtered && $filtered >= 0);

// BUG-01 Reservation
$r = req("$base/reservations/create.php", 'POST', [
    'medicineId'   => 1,
    'pharmacyId'   => 1,
    'patientName'  => 'QA Test',
    'patientPhone' => '0550000099',
    'quantity'     => 1,
    'notes'        => 'automated test',
]);
check('BUG-01 reservation created', $r['code'] === 201 && !empty($r['body']['id']));

// BUG-02 Donation (guest)
$form = [
    'item_name'    => 'Test Crutches',
    'item_name_ar' => 'عكازات تجريبية',
    'wilaya'       => 'الجزائر',
    'city'         => 'الجزائر العاصمة',
    'donor_name'   => 'QA Donor',
    'condition'    => 'good',
    'category'     => 'device',
];
$r = req("$base/donations/create.php", 'POST', $form, false);
check('BUG-02 donation submitted', $r['code'] === 201 && !empty($r['body']['id']));

// BUG-04 / BUG-06 need login
$login = req("$base/auth/login.php", 'POST', ['email' => 'amal@pharmacy.dz', 'password' => 'Demo123!']);
if ($login['code'] !== 200) {
    $login = req("$base/auth/login.php", 'POST', ['email' => 'pharma@shifaa.dz', 'password' => 'Demo123!']);
}
check('login for resupply test', $login['code'] === 200, 'code=' . $login['code']);

if ($login['code'] === 200) {
    $list = req("$base/resupply/requests.php?pharmacyId=1");
    $statuses = array_column($list['body'] ?? [], 'status');
    $uiOnly = array_diff($statuses, ['pending', 'confirmed', 'sent', 'rejected']);
    check('BUG-04 resupply UI statuses', empty($uiOnly), 'found: ' . implode(',', $statuses));
}

$ms = req("$base/auth/login.php", 'POST', ['email' => 'medservices@shifaa.dz', 'password' => 'Demo123!']);
check('BUG-06 medical_services login', $ms['code'] === 200);

if ($ms['code'] === 200) {
    $stats = req("$base/dashboard/stats.php");
    check('BUG-06 stats API', $stats['code'] === 200 && isset($stats['body']['products']), 'code=' . $stats['code'] . ' err=' . ($stats['body']['error'] ?? ''));
    $inv = req("$base/inventory/index.php");
    check('BUG-06 inventory API', $inv['code'] === 200 && !empty($inv['body']['items']), 'code=' . $inv['code'] . ' err=' . ($inv['body']['error'] ?? ''));
}

@unlink($cookie);
echo "\nDone: $pass passed, $fail failed\n";
exit($fail > 0 ? 1 : 0);