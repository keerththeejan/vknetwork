<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/includes/init.php';
require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed'], JSON_THROW_ON_ERROR);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '[]', true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON'], JSON_THROW_ON_ERROR);
    exit;
}

$to = trim((string) ($data['to'] ?? ''));
if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Valid recipient email required'], JSON_THROW_ON_ERROR);
    exit;
}

$pdo = db();
if (!vk_settings_table_ready($pdo)) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'Settings table missing'], JSON_THROW_ON_ERROR);
    exit;
}

$host = trim((string) vk_settings_get($pdo, 'smtp_host', ''));
$port = (int) vk_settings_get($pdo, 'smtp_port', '587');
$user = (string) vk_settings_get($pdo, 'smtp_username', '');
$pass = (string) vk_settings_get($pdo, 'smtp_password', '');
$from = trim((string) vk_settings_get($pdo, 'email_from', ''));

if ($host === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Configure SMTP host in Email settings first'], JSON_THROW_ON_ERROR);
    exit;
}
if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Configure a valid From email'], JSON_THROW_ON_ERROR);
    exit;
}

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_file($autoload)) {
    http_response_code(503);
    echo json_encode([
        'ok' => false,
        'error' => 'PHPMailer not installed. Run: composer install in the project root.',
    ], JSON_THROW_ON_ERROR);
    exit;
}

require_once $autoload;

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $host;
    $mail->Port = $port > 0 ? $port : 587;
    $mail->SMTPAuth = $user !== '';
    if ($mail->SMTPAuth) {
        $mail->Username = $user;
        $mail->Password = $pass;
    }
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    if ($port === 465) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    }
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($from, vk_settings_get($pdo, 'site_name', 'VK Network') ?? 'VK Network');
    $mail->addAddress($to);
    $mail->Subject = 'VK Network — test email';
    $mail->Body = "This is a test message from your VK admin panel.\r\n\r\nSent at " . date('c');

    $mail->send();
    echo json_encode(['ok' => true, 'message' => 'Test email sent'], JSON_THROW_ON_ERROR);
} catch (MailException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $mail->ErrorInfo ?: $e->getMessage()], JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_THROW_ON_ERROR);
}
