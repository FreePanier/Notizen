<?php
header('Content-Type: application/json');
require_once 'storage_helper.php';

$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(["error" => "Email und Passwort erforderlich"]);
    exit;
}

$email = $data['email'];
$password = $data['password'];
$storageType = isset($data['storage']) ? $data['storage'] : 'server';

// Bestimme das Verzeichnis für diesen User
$userDir = getStorageDir($email, $storageType);

if (empty($userDir)) {
    // Lokaler Modus erfordert kein Server-Login mehr
    echo json_encode([
        "status" => "success",
        "message" => "Lokaler Modus bestätigt (kein Server-Verzeichnis)",
        "mail" => $email,
        "storage" => 'local'
    ]);
    exit;
}

$secretFile = $userDir . '/secret.txt';

if (!file_exists($secretFile)) {
    // Erster Login: Passwort setzen
    $hash = password_hash($password, PASSWORD_DEFAULT);
    file_put_contents($secretFile, $hash);
    echo json_encode([
        "status" => "success",
        "message" => "Erster Login: Passwort wurde gespeichert.",
        "mail" => $email,
        "storage" => $storageType
    ]);
    exit;
}

$hash = trim(file_get_contents($secretFile));

if (password_verify($password, $hash)) {
    echo json_encode([
        "status" => "success",
        "mail" => $email,
        "storage" => $storageType
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Login Fehler",
        "mail" => $email,
        "storage" => $storageType
    ]);
}
?>
