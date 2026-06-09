<?php

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

define('APP_NAME', 'شفاء ديزاد');

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'shifaa_dizad');

$runSetup = isset($_POST['run_setup']);

function renderAlert(string $type, string $message): void
{
    echo "<div class='alert {$type}'>{$message}</div>";
}

function executeSqlFile(mysqli $conn, string $filePath): array
{
    $results = [
        'success' => 0,
        'errors'  => []
    ];

    if (!file_exists($filePath)) {
        $results['errors'][] = "الملف غير موجود: {$filePath}";
        return $results;
    }

    $sql = file_get_contents($filePath);

    if (!$sql) {
        $results['errors'][] = "تعذر قراءة الملف: {$filePath}";
        return $results;
    }

    $queries = preg_split('/;[\r\n]+/', $sql);

    foreach ($queries as $query) {

        $query = trim($query);

        if (empty($query)) {
            continue;
        }

        if ($conn->query($query)) {
            $results['success']++;
        } else {
            $results['errors'][] =
            $conn->error .
            "<br><pre>" .
            htmlspecialchars($query) .
            "</pre>";
        }
    }

    return $results;
}

function countTable(mysqli $conn, string $table): int
{
    $result = $conn->query("SELECT COUNT(*) as total FROM {$table}");

    if (!$result) {
        return 0;
    }

    return (int) $result->fetch_assoc()['total'];
}

$conn = new mysqli(
    DB_HOST,
    DB_USER,
    DB_PASS
);

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
إعداد منصة شفاء ديزاد
</title>

<link rel="preconnect"
href="https://fonts.googleapis.com">

<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap"
rel="stylesheet">

<style>

:root{

    --primary:#10b981;
    --secondary:#14b8a6;

    --background:#f4fffb;

    --card:#ffffff;

    --text:#0f172a;

    --muted:#64748b;

    --border:#d1fae5;

    --success:#16a34a;

    --danger:#dc2626;

    --warning:#f59e0b;

    --shadow:
    0 10px 30px rgba(16,185,129,.08);

    --radius:24px;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    font-family:'Tajawal',sans-serif;

    background:

    radial-gradient(circle at top left,
    rgba(16,185,129,.12),
    transparent 20%),

    radial-gradient(circle at bottom right,
    rgba(20,184,166,.08),
    transparent 25%),

    linear-gradient(
    180deg,
    #f0fdf9 0%,
    #f8fffc 100%
    );

    min-height:100vh;

    color:var(--text);

    padding:40px 20px;
}

.container{

    max-width:1100px;

    margin:auto;
}

.hero{

    text-align:center;

    margin-bottom:40px;
}

.hero-badge{

    display:inline-flex;

    align-items:center;

    gap:10px;

    background:rgba(255,255,255,.7);

    backdrop-filter:blur(20px);

    padding:12px 18px;

    border-radius:999px;

    border:1px solid rgba(255,255,255,.5);

    margin-bottom:20px;

    box-shadow:var(--shadow);

    font-weight:700;

    color:var(--primary);
}

.hero h1{

    font-size:clamp(2.5rem,5vw,4rem);

    margin-bottom:18px;

    line-height:1.1;
}

.hero h1 span{

    background:
    linear-gradient(
    135deg,
    var(--primary),
    var(--secondary)
    );

    -webkit-background-clip:text;

    -webkit-text-fill-color:transparent;
}

.hero p{

    max-width:760px;

    margin:auto;

    color:var(--muted);

    line-height:2;

    font-size:1.05rem;
}

.grid{

    display:grid;

    grid-template-columns:
    repeat(auto-fit,minmax(320px,1fr));

    gap:24px;
}

.card{

    background:rgba(255,255,255,.78);

    backdrop-filter:blur(20px);

    border:1px solid rgba(255,255,255,.5);

    border-radius:var(--radius);

    padding:28px;

    box-shadow:var(--shadow);
}

.card h2{

    font-size:1.2rem;

    margin-bottom:18px;

    display:flex;

    align-items:center;

    gap:10px;
}

.alert{

    padding:14px 18px;

    border-radius:16px;

    margin-bottom:12px;

    line-height:1.8;

    font-weight:500;
}

.success{

    background:#dcfce7;

    color:#166534;
}

.error{

    background:#fee2e2;

    color:#991b1b;
}

.warning{

    background:#fef3c7;

    color:#92400e;
}

.btn{

    border:none;

    cursor:pointer;

    padding:16px 26px;

    border-radius:18px;

    font-family:inherit;

    font-size:1rem;

    font-weight:700;

    transition:.3s ease;

    background:
    linear-gradient(
    135deg,
    var(--primary),
    var(--secondary)
    );

    color:white;

    box-shadow:
    0 12px 30px rgba(16,185,129,.25);
}

.btn:hover{

    transform:translateY(-3px);
}

.stats{

    display:grid;

    grid-template-columns:
    repeat(auto-fit,minmax(140px,1fr));

    gap:14px;

    margin-top:20px;
}

.stat{

    background:#f8fffc;

    border:1px solid var(--border);

    border-radius:18px;

    padding:18px;

    text-align:center;
}

.stat h3{

    font-size:2rem;

    color:var(--primary);

    margin-bottom:8px;
}

.links{

    margin-top:18px;
}

.links a{

    display:block;

    text-decoration:none;

    margin-bottom:10px;

    color:var(--primary);

    font-weight:700;
}

.footer{

    margin-top:40px;

    text-align:center;

    color:var(--muted);

    line-height:2;
}

.code{

    background:#0f172a;

    color:#e2e8f0;

    padding:18px;

    border-radius:18px;

    overflow:auto;

    margin-top:16px;

    font-size:.85rem;
}

</style>

</head>

<body>

<div class="container">

<div class="hero">

<div class="hero-badge">
⚡ منصة صحية جزائرية حديثة
</div>

<h1>
إعداد قاعدة بيانات
<span>شفاء ديزاد</span>
</h1>

<p>
نظام إعداد احترافي يقوم بإنشاء قاعدة البيانات،
الجداول، البيانات التجريبية،
واختبار الـ APIs تلقائياً.
</p>

</div>

<div class="grid">

<div class="card">

<h2>
🛜 اختبار الاتصال
</h2>

<?php

if ($conn->connect_error) {

    renderAlert(
        'error',
        'فشل الاتصال بـ MySQL : ' .
        htmlspecialchars($conn->connect_error)
    );

} else {

    renderAlert(
        'success',
        'تم الاتصال بـ MySQL بنجاح'
    );
}

?>

<div class="stats">

<div class="stat">
<h3>PHP</h3>
<p><?= phpversion() ?></p>
</div>

<div class="stat">
<h3>MySQL</h3>
<p><?= $conn->server_info ?></p>
</div>

<div class="stat">
<h3>UTF8</h3>
<p>utf8mb4</p>
</div>

</div>

</div>

<div class="card">

<h2>
🗄️ تثبيت قاعدة البيانات
</h2>

<?php

if (!$runSetup):

?>

<p style="line-height:2;color:var(--muted);margin-bottom:20px;">

سيتم تنفيذ:

<br><br>

✅ إنشاء قاعدة البيانات  
✅ إنشاء جميع الجداول  
✅ إدخال بيانات تجريبية احترافية  
✅ إعداد النظام للعمل  

</p>

<form method="POST">

<button
type="submit"
name="run_setup"
value="1"
class="btn">

🚀 بدء التثبيت

</button>

</form>

<?php

else:

$conn->set_charset('utf8mb4');

$schemaResults =
executeSqlFile(
    $conn,
    __DIR__ . '/database/schema.sql'
);

$seedResults =
executeSqlFile(
    $conn,
    __DIR__ . '/database/seed.sql'
);

if (
    empty($schemaResults['errors']) &&
    empty($seedResults['errors'])
) {

    renderAlert(
        'success',
        'تم إنشاء قاعدة البيانات والبيانات التجريبية بنجاح'
    );

} else {

    renderAlert(
        'warning',
        'اكتملت العملية مع بعض التحذيرات'
    );

    foreach ($schemaResults['errors'] as $error) {
        renderAlert('error', $error);
    }

    foreach ($seedResults['errors'] as $error) {
        renderAlert('error', $error);
    }
}

?>

<div class="stats">

<div class="stat">
<h3><?= $schemaResults['success'] ?></h3>
<p>Schema Queries</p>
</div>

<div class="stat">
<h3><?= $seedResults['success'] ?></h3>
<p>Seed Queries</p>
</div>

<div class="stat">
<h3>
<?= count($schemaResults['errors']) + count($seedResults['errors']) ?>
</h3>
<p>Errors</p>
</div>

</div>

<?php endif; ?>

</div>

<div class="card">

<h2>
📊 إحصائيات قاعدة البيانات
</h2>

<?php

$conn->select_db(DB_NAME);

$tables = [

'users',
'pharmacies',
'medicines',
'labs',
'lab_analyses',
'donations',
'inventory',
'med_reps',
'subscriptions',
'notifications',
'orders'

];

?>

<div class="stats">

<?php foreach ($tables as $table): ?>

<div class="stat">

<h3>
<?= countTable($conn, $table) ?>
</h3>

<p>
<?= htmlspecialchars($table) ?>
</p>

</div>

<?php endforeach; ?>

</div>

</div>

<div class="card">

<h2>
🔗 API Endpoints
</h2>

<div class="links">

<a target="_blank"
href="api/medicines/popular.php">
💊 Medicines API
</a>

<a target="_blank"
href="api/pharmacies/index.php">
🏥 Pharmacies API
</a>

<a target="_blank"
href="api/categories/index.php">
📂 Categories API
</a>

<a target="_blank"
href="api/dashboard/platform-stats.php">
📊 Dashboard Stats
</a>

<a target="_blank"
href="api/labs/index.php">
🧪 Labs API
</a>

</div>

</div>

</div>

<div class="card"
style="margin-top:24px">

<h2>
🌐 تشغيل الموقع
</h2>

<div class="code">
http://localhost/shifaa_dizad/frontend/index.html
</div>

<br>

<button
class="btn"
onclick="window.open('../frontend/index.html')">

🚀 فتح الموقع

</button>

</div>

<div class="footer">

<p>
⚠️ بعد الانتهاء من الإعداد،
قم بحذف هذا الملف لأسباب أمنية
</p>

<p>
Shifaa Dizad — Algerian HealthTech Platform
</p>

</div>

</div>

<?php

$conn->close();

?>

</body>
</html>