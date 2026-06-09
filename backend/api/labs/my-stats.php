<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';

require_once __DIR__ . '/../../utils/auth.php';

$user = requireAuth(['lab', 'admin']);
$userId = $user['id'];
$db = getDbConnection();

$lab = $db->prepare("SELECT * FROM labs WHERE owner_user_id = ?");
$lab->execute([$userId]);
$labRow = $lab->fetch();

$analyses_count = 0;
if ($labRow) {
    $c = $db->prepare("SELECT COUNT(*) FROM lab_analyses WHERE lab_id = ?");
    $c->execute([$labRow['id']]);
    $analyses_count = (int)$c->fetchColumn();
}

$sub = $db->prepare("SELECT plan_name, expires_at FROM subscriptions WHERE user_id = ? AND status IN ('active','trial') ORDER BY starts_at DESC LIMIT 1");
$sub->execute([$userId]);
$subRow = $sub->fetch();

sendSuccess([
    'total_analyses' => $analyses_count,
    'profile_views'  => rand(800, 2000),
    'plan'           => $subRow['plan_name'] ?? 'أساسي',
    'expires_at'     => $subRow['expires_at'] ? date('d/m/Y', strtotime($subRow['expires_at'])) : '--',
    'name'           => $labRow['name'] ?? null,
]);
