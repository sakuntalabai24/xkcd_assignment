<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Autoload PHPMailer & dotenv
require __DIR__ . '/vendor/autoload.php';

// Load environment variables from .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

//  Reusable mail sender function
function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        // SMTP setup
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Mail settings
        $mail->setFrom($_ENV['EMAIL_USER'], 'XKCD Bot');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Sends verification email
function sendVerificationEmail($email, $code) {
    $subject = 'Your Verification Code';
    $body = "<p>Your verification code is: <strong>$code</strong></p>";
    return sendMail($email, $subject, $body);
}

// Generate 6-digit code
function generateVerificationCode() {
    return rand(100000, 999999);
}

// Save verification code
function saveCodeForEmail($email, $code) {
    $dir = __DIR__ . '/temp_codes';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    file_put_contents($dir . '/' . md5($email) . '.txt', $code);
}

// Verify user's code
function verifyCode($email, $userCode) {
    $file = __DIR__ . '/temp_codes/' . md5($email) . '.txt';
    if (!file_exists($file)) return false;
    $savedCode = trim(file_get_contents($file));
    return $userCode === $savedCode;
}

// Register a new email
function registerEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];
    if (!in_array($email, $emails)) {
        $emails[] = $email;
        file_put_contents($file, implode(PHP_EOL, $emails) . PHP_EOL);
    }
}

// Unsubscribe email
function unsubscribeEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;
    $emails = file($file, FILE_IGNORE_NEW_LINES);
    $emails = array_filter($emails, fn($e) => $e !== $email);
    file_put_contents($file, implode(PHP_EOL, $emails) . PHP_EOL);
}

//  Fetch XKCD comic
function fetchAndFormatXKCDData() {
    $json = file_get_contents('https://xkcd.com/info.0.json');
    $data = json_decode($json, true);
    $img = $data['img'];
    $title = $data['title'];
    $alt = $data['alt'];
    return "<h2>{$title}</h2><img src='{$img}' alt='{$alt}' style='max-width:100%;'><p>{$alt}</p>";
}

//  Send latest XKCD to all subscribers
function sendXKCDUpdatesToSubscribers() {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;
    $emails = file($file, FILE_IGNORE_NEW_LINES);
    $body = fetchAndFormatXKCDData();
    foreach ($emails as $email) {
        sendMail($email, 'Your XKCD Comic', $body);
    }
}
