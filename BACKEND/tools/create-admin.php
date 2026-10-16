<?php
// Executar no terminal. Nunca expõe uma tela pública de criação de administradores.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../app/bootstrap.php';
try {
    echo "Nome: "; $name=trim(fgets(STDIN));
    echo "E-mail: "; $email=trim(fgets(STDIN));
    echo "Senha (mínimo 12 caracteres; aparece neste terminal local): "; $password=rtrim(fgets(STDIN),"\r\n");
    if (strlen($name)<2 || strlen($name)>90 || strlen($email)>160 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($password)<12 || strlen($password)>72) throw new DomainException('Dados inválidos. Use senha de 12 a 72 caracteres.');
    query('INSERT INTO admins(name,email,password_hash) VALUES(?,?,?)',[$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
    echo "Administrador criado. Acesse /agrolink/admin/.\n";
} catch (Throwable $err) { fwrite(STDERR,"Não foi possível criar: confira o banco, os campos e se o e-mail já existe.\n"); exit(1); }
