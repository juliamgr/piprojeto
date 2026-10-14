<?php
declare(strict_types=1);

// Customer identity always comes from the authenticated session.
function current_customer(): ?array {
    if (empty($_SESSION['user_id']) || time() - (int)($_SESSION['user_last_active'] ?? 0) > 1800) {
        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_last_active']);
        return null;
    }
    $user = query('SELECT id,name,email,phone,cpf,birth_date,member_type,city,address,neighborhood,zip_code,production,notes FROM users WHERE id = ?', [(int)$_SESSION['user_id']])->fetch();
    if (!$user) {
        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_last_active']);
        return null;
    }
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_last_active'] = time();
    return $user;
}

function update_customer(array $data): void {
    $user = current_customer();
    if (!$user) throw new DomainException('Sua sessão expirou. Entre novamente para salvar.');
    $name=field($data,'name',3,90);
    $email=strtolower(field($data,'email',3,160));
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new DomainException('Informe um e-mail válido.');
    $phone=field($data,'phone',10,30);
    if (!preg_match('/^[0-9+() .-]+$/',$phone) || !preg_match('/^\d{10,13}$/',preg_replace('/\D/','',$phone))) throw new DomainException('Informe um telefone válido com DDD.');
    $type=field($data,'member_type',1,60);
    if (!in_array($type,['Produtor(a) rural','Agricultor(a) familiar','Consumidor(a)','Organização / entidade','Outro'],true)) throw new DomainException('Selecione um tipo de filiação válido.');
    $city=field($data,'city',2,100); $address=field($data,'address',3,220);
    $optional=[];
    foreach (['cpf'=>14,'birth_date'=>10,'neighborhood'=>100,'zip_code'=>9,'production'=>4000,'notes'=>4000] as $key=>$max) $optional[$key]=field($data+[$key=>''],$key,0,$max);
    $cpf=preg_replace('/[. -]/','',$optional['cpf']);
    if ($cpf!=='') {
        if (!preg_match('/^\d{11}$/',$cpf) || preg_match('/^(\d)\1{10}$/',$cpf)) throw new DomainException('CPF inválido.');
        for ($i=9;$i<11;$i++) { $sum=0; for($j=0;$j<$i;$j++) $sum+=(int)$cpf[$j]*($i+1-$j); $digit=($sum*10)%11; if ($digit===10) $digit=0; if ($digit!==(int)$cpf[$i]) throw new DomainException('CPF inválido.'); }
    }
    $birth=$optional['birth_date'];
    if ($birth!=='') { $date=DateTimeImmutable::createFromFormat('!Y-m-d',$birth); if (!$date || $date->format('Y-m-d')!==$birth || $birth>date('Y-m-d') || $birth<'1900-01-01') throw new DomainException('Data de nascimento inválida.'); }
    $zip=str_replace('-','',$optional['zip_code']);
    if ($zip!=='' && !preg_match('/^\d{8}$/',$zip)) throw new DomainException('CEP inválido.');

    // Changing the login address requires the current password.
    if ($email !== $user['email']) {
        $password = $data['current_password'] ?? '';
        $hash = query('SELECT password_hash FROM users WHERE id = ?', [(int)$user['id']])->fetchColumn();
        if (!is_string($password) || strlen($password) > 72 || !password_verify($password, (string)$hash)) {
            throw new DomainException('Informe sua senha atual para alterar o e-mail.');
        }
    }
    try {
        $values = [$name,$email,$phone,$cpf ?: null,$birth ?: null,$type,$city,$address,$optional['neighborhood'],$zip,$optional['production'],$optional['notes']];
        $values[] = (int)$user['id'];
        query('UPDATE users SET name=?,email=?,phone=?,cpf=?,birth_date=?,member_type=?,city=?,address=?,neighborhood=?,zip_code=?,production=?,notes=? WHERE id=?', $values);
    } catch (PDOException $err) {
        if ((int)($err->errorInfo[1] ?? 0) === 1062) throw new DomainException('Este e-mail já está sendo usado por outra conta.');
        throw $err;
    }
    $_SESSION['user_name'] = $name;
}
