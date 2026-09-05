<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Metodă invalidă.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$email = filter_var(trim($input['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$name  = sanitize($input['name'] ?? '');

if (!$email) {
    jsonResponse(['success' => false, 'message' => 'Adresa de email nu este validă.'], 400);
}

// Verifică dacă există deja
$existing = Database::fetchOne('SELECT id, is_active, unsubscribed_at FROM newsletter WHERE email=?', [$email]);

if ($existing) {
    if ($existing['is_active']) {
        jsonResponse(['success' => false, 'message' => 'Ești deja abonat la newsletter.']);
    } else {
        // Reabonare
        Database::update('newsletter', ['is_active' => 1, 'unsubscribed_at' => null], 'id=?', [$existing['id']]);
        jsonResponse(['success' => true, 'message' => 'Ai fost reînscris cu succes!']);
    }
}

$token = bin2hex(random_bytes(32));
Database::insert('newsletter', [
    'email'         => $email,
    'name'          => $name ?: null,
    'confirm_token' => $token,
    'ip'            => $_SERVER['REMOTE_ADDR'] ?? null,
]);

// Trimite email de confirmare (dacă mail() este configurat)
$domain  = SITE_DOMAIN;
$confirmUrl = "$domain/newsletter/confirm/?token=$token";
$subject    = 'Confirmă abonarea la GhidRomânesc';
$body       = "Bună,\n\nAi solicitat abonarea la newsletter-ul GhidRomânesc.\n\nConfirmă abonarea accesând: $confirmUrl\n\nDacă nu ai solicitat abonarea, ignoră acest email.\n\nEchipa GhidRomânesc";
$headers    = "From: GhidRomânesc <noreply@ghidromanesc.ro>\r\nContent-Type: text/plain; charset=UTF-8";
@mail($email, $subject, $body, $headers);

jsonResponse(['success' => true, 'message' => 'Verifică emailul pentru a confirma abonarea.']);
