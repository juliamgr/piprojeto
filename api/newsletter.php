<?php
require __DIR__ . '/bootstrap.php';
require_method('POST');
$input = request_json();

if (!empty($input['company'])) respond(['success' => true, 'message' => 'Cadastro realizado.']);
$email = filter_var(clean_text($input['email'] ?? '', 160), FILTER_VALIDATE_EMAIL);
if (!$email) respond(['success' => false, 'message' => 'Informe um e-mail válido.'], 422);

$created = update_json('newsletter.json', function (&$subscribers) use ($email) {
    foreach ($subscribers as $subscriber) {
        if (strcasecmp($subscriber['email'] ?? '', $email) === 0) return false;
    }
    $subscribers[] = ['email' => strtolower($email), 'createdAt' => gmdate('c')];
    return true;
});

respond(['success' => true, 'message' => $created ? 'Cadastro realizado com sucesso.' : 'Este e-mail já está cadastrado.']);
