<?php
require_once __DIR__ . '/includes/config.php';
try {
    $db = getDB();
    print_r($db->query('DESCRIBE questions')->fetchAll(PDO::FETCH_ASSOC));
    print_r($db->query('DESCRIBE tests')->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
