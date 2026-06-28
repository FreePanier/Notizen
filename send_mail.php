<?php
header('Content-Type: application/json');

// Autoloader für dotenv (falls genutzt)
require_once __DIR__ . '/vendor/autoload.php';

// .env laden
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// PHPMailer laden
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['zeitstempel']) && isset($data['text']) && isset($data['email'])) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.strato.de';
        $mail->SMTPAuth   = true;

        // ✅ Werte kommen aus .env
        $mail->Username   = $_ENV['SMTP_USER'];
        $mail->Password   = $_ENV['SMTP_PASS'];

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($_ENV['SMTP_FROM'], 'Burkhard - Notizen');
        $mail->addAddress($data['email']);

        $mail->Subject = 'Notiz vom ' . $data['zeitstempel'];
        $mail->Body    = "Gesendete Notiz:\n\n---\n" . $data['text'] . "\n---";

        $mail->send();
        echo json_encode(["status" => "success"]);
        exit;

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $mail->ErrorInfo]);
        exit;
    }
}

http_response_code(400);
echo json_encode(["error" => "Daten unvollständig"]);