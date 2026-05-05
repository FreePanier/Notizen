<?php
header('Content-Type: application/json');

require_once 'storage_helper.php';
$file = getStoragePath();

if (!empty($file) && file_exists($file)) {
    echo file_get_contents($file);
} else {
    echo json_encode([]);
}
?>
