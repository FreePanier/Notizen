<?php
if (isset($_GET['delete'])) {
    setcookie('Notiz', '', time() - 3600, '/');
    header('Location: cookie.php');
    exit;
}

echo "<h1>Gespeicherte Cookies</h1>";
echo '<p><a href="index.html" style="padding:10px; background:#6c757d; color:white; text-decoration:none; border-radius:5px;">Zurück zum Notizbuch</a></p>';

if (empty($_COOKIE)) {
    echo "<p>Keine Cookies gefunden.</p>";
} else {
    echo '<form method="get">
            <button type="submit" name="delete" value="1" style="padding:10px; background:#dc3545; color:white; border:none; border-radius:5px; cursor:pointer;">
                Cookie "Notiz" löschen
            </button>
          </form><br>';

    echo "<pre>";
    print_r($_COOKIE);
    echo "</pre>";

    foreach ($_COOKIE as $name => $value) {
        echo "<h3>Details für: " . htmlspecialchars($name) . "</h3>";
        // Versuche JSON zu dekodieren, falls es ein JSON-String ist
        $decoded = json_decode($value, true);
        if ($decoded !== null) {
            echo "<pre>Inhalt (JSON): ";
            print_r($decoded);
            echo "</pre>";
        } else {
            echo "<p>Inhalt (Text): " . htmlspecialchars($value) . "</p>";
        }
    }
}
?>
