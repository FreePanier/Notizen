<?php
header('Content-Type: application/json');

$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

if (!$data || !isset($data['entries'])) {
    http_response_code(400);
    echo json_encode(["error" => "Keine Daten empfangen"]);
    exit;
}

$newEntries = $data['entries'];
$mail = isset($data['mail']) ? $data['mail'] : null;
$storageType = isset($data['storage']) ? $data['storage'] : null;

require_once 'storage_helper.php';
$file = getStoragePath($mail, $storageType);

// Bestehende Daten laden
$currentData = [];
if (file_exists($file)) {
    $currentData = json_decode(file_get_contents($file), true) ?: [];
}

// Neue Einträge hinzufügen
foreach ($newEntries as $entry) {
    $currentData[] = $entry;
}

// Datei speichern
if (!empty($file)) {
    if (file_put_contents($file, json_encode($currentData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
        echo json_encode(["status" => "success"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Schreibfehler auf Server"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Kein Server-Speicherpfad konfiguriert (Lokaler Modus?)"]);
}
?>