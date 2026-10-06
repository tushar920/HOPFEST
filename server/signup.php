<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

$cfg = require __DIR__ . '/config.php';
require __DIR__ . '/signup-lib.php';

function hf_reply(int $code, array $body): void
{
    http_response_code($code);
    echo json_encode($body);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    hf_reply(405, ['ok' => false]);
}

// Spam trap: real people never fill this hidden field.
if (trim((string)($_POST['website'] ?? '')) !== '') {
    hf_reply(200, ['ok' => true]);
}

function hf_in(string $key, int $max): string
{
    $v = trim(preg_replace('/\s+/u', ' ', (string)($_POST[$key] ?? '')) ?? '');
    return mb_substr($v, 0, $max);
}

$name  = hf_in('name', 80);
$phone = hf_in('phone', 20);
$email = hf_in('email', 120);
$city  = hf_in('city', 20);
$kids  = hf_in('kids', 3);
$hoppers = hf_in('hoppers', 5);

$errors = [];
if (mb_strlen($name) < 2) {
    $errors['name'] = 'Please enter your name.';
}
$digits = preg_replace('/\D/', '', $phone);
if (strlen($digits) === 12 && str_starts_with($digits, '91')) { $digits = substr($digits, 2); }
if (strlen($digits) === 11 && $digits[0] === '0') { $digits = substr($digits, 1); }
if (!preg_match('/^[6-9]\d{9}$/', $digits)) {
    $errors['phone'] = 'Please enter a valid 10-digit mobile number.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
}
if (!in_array($city, ['Bangalore', 'Hyderabad', 'Pune', 'Mumbai', 'Delhi', 'Other'], true)) {
    $errors['city'] = 'Please choose your city.';
}
if (!in_array($kids, ['1', '2', '3', '4+'], true)) {
    $errors['kids'] = 'Please pick how many Hoppers are coming.';
}
if (!in_array($hoppers, ['Boys', 'Girls', 'Both'], true)) {
    $errors['hoppers'] = 'Please pick one.';
}
if (empty($_POST['consent'])) {
    $errors['consent'] = 'Please tick this so we can contact you.';
}
if ($errors) {
    hf_reply(422, ['ok' => false, 'errors' => $errors]);
}

$when = (new DateTime('now', new DateTimeZone('Asia/Kolkata')))->format('Y-m-d H:i');
$row = [$when, $name, $digits, $email, $city, $kids, $hoppers];

if (!hf_save($row)) {
    error_log('HOP FEST sign-up could not be saved: check folder permissions.');
    hf_reply(500, ['ok' => false]);
}

$to = trim((string)($cfg['notify_email'] ?? ''));
if ($to !== '' && filter_var($to, FILTER_VALIDATE_EMAIL)) {
    $host = strtolower(explode(':', (string)($_SERVER['HTTP_HOST'] ?? 'localhost'))[0]);
    $host = preg_replace('/^www\./', '', preg_replace('/[^a-z0-9.\-]/', '', $host));
    $body = "New HOP FEST sign-up\n\n";
    foreach (HF_COLUMNS as $i => $label) {
        $body .= $label . ': ' . $row[$i] . "\n";
    }
    $headers = 'From: HOP FEST <no-reply@' . $host . ">\r\nContent-Type: text/plain; charset=UTF-8";
    if ($email !== '') {
        $headers .= "\r\nReply-To: " . str_replace(["\r", "\n"], '', $email);
    }
    @mail($to, 'New HOP FEST sign-up: ' . str_replace(["\r", "\n"], ' ', $name) . ' (' . $city . ')', $body, $headers);
}

hf_reply(200, ['ok' => true]);
