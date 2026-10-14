<?php
declare(strict_types=1);
function register_user(array $data): int {
    $name=field($data,'name',3,90);
    $email=strtolower(field($data,'email',3,160));
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new DomainException('Informe um e-mail válido.');
    $password=$data['password'] ?? null;
    if (!is_string($password) || strlen($password)<12 || strlen($password)>72 || strpos($password,"\0")!==false) throw new DomainException('A senha deve ter entre 12 e 72 bytes (letras acentuadas podem ocupar mais de um byte).');
    if ($password!==($data['password_confirmation'] ?? null)) throw new DomainException('As senhas não coincidem.');
    if (($data['privacy'] ?? '')!=='1') throw new DomainException('Confirme o consentimento para cadastro e contato.');
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
    try {
        query('INSERT INTO users (name,email,password_hash,phone,cpf,birth_date,member_type,city,address,neighborhood,zip_code,production,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',[$name,$email,password_hash($password,PASSWORD_DEFAULT),$phone,$cpf ?: null,$birth ?: null,$type,$city,$address,$optional['neighborhood'],$zip,$optional['production'],$optional['notes']]);
    } catch (PDOException $err) {
        if (($err->errorInfo[1] ?? 0)===1062) throw new DomainException('Este e-mail já possui cadastro. Entre na sua conta.');
        throw $err;
    }
    return (int)db()->lastInsertId();
}
