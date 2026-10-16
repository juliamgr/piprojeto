<?php
declare(strict_types=1);
require __DIR__ . '/../BACKEND/app/bootstrap.php';
api_run('POST', function () {
    $data = input_json();
    if (!empty($data['company'])) json_out(['success'=>true,'message'=>'Cadastro realizado.']);
    $email = strtolower(field($data, 'email', 3, 160));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new DomainException('Informe um e-mail válido.');
    query('INSERT INTO newsletter(email) VALUES(?) ON DUPLICATE KEY UPDATE email=VALUES(email)', [$email]);
    json_out(['success'=>true,'message'=>'E-mail cadastrado com sucesso.']);
});
