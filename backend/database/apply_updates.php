<?php
require_once __DIR__ . '/../config/database.php';
$db = getDB();
$sql = file_get_contents(__DIR__ . '/add_medical_services.sql');
if ($db->multi_query($sql)) {
    do {
        if ($result = $db->store_result()) { $result->free(); }
    } while ($db->next_result());
    echo 'Database updated successfully\n';
} else {
    echo 'Error: ' . $db->error . '\n';
}
$db->close();
?>
