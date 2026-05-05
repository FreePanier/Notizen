<?php
header('Content-Type: application/json');

// Daten vom JavaScript empfangen
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['zeitstempel'])) {
    $zeitstempel = $data['zeitstempel'];
    require_once 'storage_helper.php';
    $datei = getStoragePath();

    if (file_exists($datei)) {
        $eintraege = json_decode(file_get_contents($datei), true);

        if (is_array($eintraege)) {
            $alterZustandCount = count($eintraege);
            
            // Filtere den Eintrag heraus, der gelöscht werden soll
            $neueEintraege = array_filter($eintraege, function($e) use ($zeitstempel) {
                return $e['zeitstempel'] !== $zeitstempel;
            });

            // Prüfen, ob wirklich etwas gelöscht wurde
            if (count($neueEintraege) < $alterZustandCount) {
                // LOCK_EX hinzugefügt, um Server-Abstürze beim gleichzeitigen Lesen/Schreiben zu verhindern
                $jsonString = json_encode(array_values($neueEintraege), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                
                if (file_put_contents($datei, $jsonString, LOCK_EX)) {
                    http_response_code(200);
                    echo json_encode(["status" => "success"]);
                    exit;
                }
            }
        }
    }
}

// Falls etwas schiefging
http_response_code(400);
echo json_encode(["error" => "Eintrag konnte nicht gelöscht werden."]);
exit;
?>