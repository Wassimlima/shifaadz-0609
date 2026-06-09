<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../utils/auth.php';

requireAdmin();

$db = getDbConnection();

$users      = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$pharmacies = $db->query("SELECT COUNT(*) FROM pharmacies")->fetchColumn();
$labs       = $db->query("SELECT COUNT(*) FROM labs")->fetchColumn();
$med_reps   = $db->query("SELECT COUNT(*) FROM med_reps")->fetchColumn();
$medicines  = $db->query("SELECT COUNT(*) FROM medicines")->fetchColumn();
$subs       = $db->query("SELECT COUNT(*) FROM subscriptions WHERE status='active'")->fetchColumn();
$ads_pending= $db->query("SELECT COUNT(*) FROM advertisements WHERE status='pending'")->fetchColumn();
$donations  = $db->query("SELECT COUNT(*) FROM donations WHERE is_available=1")->fetchColumn();
$contact    = $db->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$med_services = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='medical_services'")->fetchColumn();
$subs_expiring = (int)$db->query("
    SELECT COUNT(*) FROM subscriptions
    WHERE status IN ('active','trial') AND expires_at IS NOT NULL
      AND expires_at <= DATE_ADD(NOW(), INTERVAL 30 DAY)
")->fetchColumn();

$recent_users = $db->query("
    SELECT id, full_name, email, role, is_verified, is_active, created_at
    FROM users ORDER BY created_at DESC LIMIT 10
")->fetchAll();

$activity = [];
foreach ($recent_users as $u) {
    $roleAr = ['admin'=>'مدير','pharmacist'=>'صيدلاني','med_rep'=>'مندوب','lab'=>'مخبر','medical_services'=>'خدمات طبية','patient'=>'مريض'];
    $activity[] = [
        'type' => 'user',
        'icon' => 'blue',
        'title' => 'مستخدم جديد — ' . $u['full_name'],
        'meta'  => $roleAr[$u['role']] ?? $u['role'],
        'time'  => $u['created_at'],
    ];
}
try {
    $adsRecent = $db->query("
        SELECT a.title, a.status, a.created_at, u.full_name AS advertiser
        FROM advertisements a
        LEFT JOIN users u ON u.id = a.advertiser_user_id
        ORDER BY a.created_at DESC LIMIT 5
    ")->fetchAll();
    foreach ($adsRecent as $a) {
        $activity[] = [
            'type' => 'ad',
            'icon' => $a['status'] === 'pending' ? 'purple' : ($a['status'] === 'rejected' ? 'red' : 'green'),
            'title' => ($a['status'] === 'pending' ? 'طلب إعلان — ' : 'إعلان — ') . ($a['title'] ?? ''),
            'meta'  => $a['advertiser'] ?? '',
            'time'  => $a['created_at'],
        ];
    }
} catch (Throwable $e) { /* advertisements table optional */ }
usort($activity, fn($a, $b) => strcmp($b['time'] ?? '', $a['time'] ?? ''));
$activity = array_slice($activity, 0, 8);

$top_medicines = [];
try {
    $top_medicines = $db->query("
        SELECT name, COALESCE(rating, 0) AS score FROM medicines
        ORDER BY rating DESC, name ASC LIMIT 5
    ")->fetchAll();
} catch (Throwable $e) {
    $top_medicines = $db->query("
        SELECT product_name AS name, COUNT(*) AS score FROM inventory
        GROUP BY product_name ORDER BY score DESC LIMIT 5
    ")->fetchAll();
}

$wilaya_activity = $db->query("
    SELECT wilaya, COUNT(*) AS cnt FROM pharmacies
    WHERE wilaya IS NOT NULL AND wilaya != ''
    GROUP BY wilaya ORDER BY cnt DESC LIMIT 5
")->fetchAll();

$sub_distribution = [];
try {
    $sub_distribution = $db->query("
        SELECT COALESCE(plan_name, 'free') AS plan_name, COUNT(*) AS cnt
        FROM subscriptions WHERE status IN ('active','trial')
        GROUP BY plan_name ORDER BY cnt DESC
    ")->fetchAll();
} catch (Throwable $e) {}

$maxWilaya = max(1, ...array_map(fn($w) => (int)$w['cnt'], $wilaya_activity ?: [['cnt'=>1]]));
$maxMed = max(1, ...array_map(fn($m) => (int)($m['score'] ?? 1), $top_medicines ?: [['score'=>1]]));

sendSuccess([
    'totals' => [
        'users'          => (int)$users,
        'pharmacies'     => (int)$pharmacies,
        'labs'           => (int)$labs,
        'med_reps'       => (int)$med_reps,
        'med_services'   => $med_services,
        'medicines'      => (int)$medicines,
        'subscriptions'  => (int)$subs,
        'subs_expiring'  => $subs_expiring,
        'ads_pending'    => (int)$ads_pending,
        'donations'      => (int)$donations,
        'contact_msgs'   => (int)$contact,
    ],
    'recent_users'      => $recent_users,
    'recent_activity'   => $activity,
    'top_medicines'     => $top_medicines,
    'wilaya_activity'   => $wilaya_activity,
    'wilaya_max'        => $maxWilaya,
    'medicine_max'      => $maxMed,
    'sub_distribution'  => $sub_distribution,
]);
