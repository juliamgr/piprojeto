<?php
// Teste de integração: somente banco vazio, separado e chamado agrolink_test.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../app/bootstrap.php';
require __DIR__.'/../app/orders.php';
require __DIR__.'/../app/inventory.php';
require __DIR__.'/../app/catalog.php';
function check(bool $ok,string $message): void { if(!$ok) throw new RuntimeException($message); echo "OK: $message\n"; }
function rejects(callable $fn,string $message): void {
    try { $fn(); } catch (DomainException $err) { if(db()->inTransaction()) db()->rollBack(); echo "OK: $message\n"; return; }
    throw new RuntimeException('Não rejeitou: '.$message);
}
try {
    if (query('SELECT DATABASE()')->fetchColumn()!=='agrolink_test') throw new RuntimeException('Configure AGROLINK_DSN para um banco separado chamado agrolink_test.');
    if (query('SHOW TABLES')->fetch()) throw new RuntimeException('O banco de teste precisa estar vazio. Use outro banco vazio chamado agrolink_test.');
    $schema=file_get_contents(__DIR__.'/../database/schema.sql');
    $schema=preg_replace('/^--.*$/m','',$schema);
    foreach(explode(';',$schema) as $statement) {
        $statement=trim($statement);
        if (!$statement || preg_match('/^(CREATE DATABASE|USE )/i',$statement)) continue;
        db()->exec($statement);
    }
    query('INSERT INTO admins(name,email,password_hash) VALUES(?,?,?)',['Teste','test@example.com',password_hash('senha-de-teste-123',PASSWORD_DEFAULT)]);
    $_SESSION=['admin_id'=>(int)db()->lastInsertId()]; session_id('agrolink-test-'.bin2hex(random_bytes(8)));
    query("INSERT INTO products(name,category,producer,location,unit,price_cents,stock) VALUES('Alface','Folhas','Produtor','Porto Alegre','unidade',450,3)");
    $id=(int)db()->lastInsertId();
    $payload=['requestId'=>str_repeat('a',40),'customer'=>['name'=>'Cliente Teste','email'=>'cliente@example.com','phone'=>'51999999999','region'=>'Porto Alegre','address'=>'Rua Exemplo, 123'],'paymentMethod'=>'pix','items'=>[['productId'=>$id,'quantity'=>2]],'total'=>0.01];
    $receipt=create_order($payload); $orderId=(int)query('SELECT id FROM orders WHERE code=?',[$receipt['code']])->fetchColumn();
    check($receipt['total']===18.9,'total e frete calculados no servidor, ignorando total do cliente');
    check((int)query('SELECT stock FROM products WHERE id=?',[$id])->fetchColumn()===1,'baixa de estoque na reserva');
    check(create_order($payload)===$receipt,'reenvio idempotente retorna o mesmo pedido');
    check((int)query('SELECT COUNT(*) FROM orders')->fetchColumn()===1,'reenvio não duplica pedido');
    $changed=$payload;$changed['items'][0]['quantity']=1;
    rejects(fn()=>create_order($changed),'mesma chave com conteúdo diferente rejeitada');
    $_SESSION['last_order']=0;$over=$payload;$over['requestId']=str_repeat('b',40);
    rejects(fn()=>create_order($over),'estoque insuficiente rejeitado');
    check((int)query('SELECT stock FROM products WHERE id=?',[$id])->fetchColumn()===1,'falha mantém estoque');
    $invalid=$over;$invalid['items'][0]['quantity']=-2;rejects(fn()=>create_order($invalid),'quantidade negativa rejeitada');
    $invalid=$over;$invalid['items'][]=$invalid['items'][0];rejects(fn()=>create_order($invalid),'itens duplicados rejeitados');
    update_order($orderId,'confirmado','Teste');
    rejects(fn()=>update_order($orderId,'concluido','Teste'),'salto de status rejeitado');
    update_order($orderId,'cancelado','Teste');
    check((int)query('SELECT stock FROM products WHERE id=?',[$id])->fetchColumn()===3,'cancelamento devolve o estoque');
    rejects(fn()=>update_order($orderId,'cancelado','Teste'),'segundo cancelamento rejeitado');
    check((int)query('SELECT stock FROM products WHERE id=?',[$id])->fetchColumn()===3,'segundo cancelamento não duplica saldo');
    move_stock($id,['type'=>'doacao','quantity'=>1,'reason'=>'Associação de teste']);
    check((int)query('SELECT stock FROM products WHERE id=?',[$id])->fetchColumn()===2,'doação reduz saldo disponível');
    rejects(fn()=>move_stock($id,['type'=>'perda','quantity'=>3,'reason'=>'Teste']),'saída maior que saldo rejeitada');
    query('UPDATE products SET expires_on=DATE_SUB(CURRENT_DATE,INTERVAL 1 DAY) WHERE id=?',[$id]);
    check(count(public_products())===0,'produto vencido não aparece no catálogo');
    rejects(fn()=>create_order($over),'produto vencido não pode ser pedido');
    rejects(fn()=>move_stock($id,['type'=>'doacao','quantity'=>1,'reason'=>'Teste']),'doação de vencido rejeitada');
    move_stock($id,['type'=>'perda','quantity'=>2,'reason'=>'Vencimento de teste']);
    check((int)query('SELECT stock FROM products WHERE id=?',[$id])->fetchColumn()===0,'perda zera o saldo restante');
    echo "Todos os testes passaram. O banco de teste foi mantido para inspeção.\n";
} catch (Throwable $err) {
    try { if(db()->inTransaction()) db()->rollBack(); } catch(Throwable $ignored) {}
    fwrite(STDERR,$err->getMessage()."\n"); exit(1);
}
