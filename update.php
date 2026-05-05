<?php
header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['zeitstempel']) && isset($data['text'])) {
    $zeitstempel = $data['zeitstempel'];
    $neuerText = $data['text'];
    require_once 'storage_helper.php';
    $datei = getStoragePath();

    if (file_exists($datei)) {
        $eintraege = json_decode(file_get_contents($datei), true);

        if (is_array($eintraege)) {
            $updated = false;
            foreach ($eintraege as $i => $e) {
                if ($e['zeitstempel'] === $zeitstempel) {
                    $eintraege[$i]['text'] = $neuerText;
                    $updated = true;
                    break;
                }
            }

            if ($updated) {
                file_put_contents($datei, json_encode(array_values($eintraege), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                http_response_code(200);
                echo json_encode(["status" => "success"]);
                exit;
            }
        }
    }
}

http_response_code(400);
echo json_encode(["error" => "Fehler beim Speichern"]);
?>