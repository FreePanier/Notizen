<?php
header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['source_zeitstempel']) && isset($data['target_zeitstempel'])) {
    $sourceTs = $data['source_zeitstempel'];
    $targetTs = $data['target_zeitstempel'];
    require_once 'storage_helper.php';
    $datei = getStoragePath();

    if (file_exists($datei)) {
        $eintraege = json_decode(file_get_contents($datei), true);

        if (is_array($eintraege)) {
            $sourceText = '';
            $sourceIndex = -1;
            $targetIndex = -1;

            // Finde die Indizes beider Einträge
            foreach ($eintraege as $i => $e) {
                if ($e['zeitstempel'] === $sourceTs) {
                    $sourceText = $e['text'];
                    $sourceIndex = $i;
                }
                if ($e['zeitstempel'] === $targetTs) {
                    $targetIndex = $i;
                }
            }

            // Wenn beide gefunden wurden, zusammenfügen und Quelle löschen
            if ($sourceIndex !== -1 && $targetIndex !== -1) {
                // Text mit Zeilenumbruch anhängen
                $eintraege[$targetIndex]['text'] .= "\n\n" . $sourceText;
                
                // Alten Eintrag löschen
                unset($eintraege[$sourceIndex]);
                
                // Speichern mit LOCK_EX, damit der Server nicht stolpert
                if(file_put_contents($datei, json_encode(array_values($eintraege), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
                    http_response_code(200);
                    echo json_encode(["status" => "success"]);
                    exit;
                }
            }
        }
    }
}

http_response_code(400);
echo json_encode(["error" => "Zusammenfügen fehlgeschlagen. Einträge nicht gefunden oder falsche Parameter."]);
exit;
?>