<?php
function create_order(array $data): array {
    global $config;
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if ($userId < 1 || time() - (int)($_SESSION['user_last_active'] ?? 0) > 1800) {
        throw new DomainException('Entre na sua conta para finalizar o pedido.');
    }
    $account = query('SELECT id, name, email FROM users WHERE id = ?', [$userId])->fetch();
    if (!$account) throw new DomainException('Conta não encontrada. Entre novamente.');
    $_SESSION['user_last_active'] = time();
    $customer = $data['customer'] ?? null;
    if (!is_array($customer)) throw new DomainException('Informe os dados de entrega.');
    // Identidade da conta vem do servidor; entrega usa os dados do formulário.
    $name=$account['name']; $email=$account['email'];
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new DomainException('E-mail inválido.');
    $phone=field($customer,'phone',10,30);
    if (!preg_match('/^\d{10,13}$/',preg_replace('/\D/','',$phone))) throw new DomainException('Informe o telefone com DDD.');
    $region=field($customer,'region',2,60); $address=field($customer,'address',8,220);
    if (!in_array($region,$config['regions'],true)) throw new DomainException('Região de entrega não atendida.');
    $payment=field($data,'paymentMethod',3,15);
    if (!in_array($payment,['pix','delivery'],true)) throw new DomainException('Forma de pagamento inválida.');
    $key=field($data,'requestId',32,64);
    if (!preg_match('/^[a-f0-9-]{32,64}$/D',$key)) throw new DomainException('Identificador de pedido inválido. Recarregue a página.');
    $items=$data['items'] ?? null;
    if (!is_array($items) || count($items)<1 || count($items)>50) throw new DomainException('A sacola deve conter de 1 a 50 produtos.');
    $quantities=[];
    foreach ($items as $item) {
        if (!is_array($item)) throw new DomainException('Item inválido.');
        $id=integer($item['productId'] ?? null,1,2147483647);
        if (isset($quantities[$id])) throw new DomainException('Produto repetido na solicitação.');
        $quantities[$id]=integer($item['quantity'] ?? null,1,999);
    }
    ksort($quantities);
    $fingerprint=hash('sha256',json_encode([$userId,$name,$email,$phone,$region,$address,$payment,$quantities]));
    $requestKey=hash('sha256','user:'.$userId.':'.$key);
    db()->beginTransaction();
    try {
    // Serializa pedidos da mesma conta, inclusive em navegadores diferentes.
    if (!query('SELECT id FROM users WHERE id=? FOR UPDATE', [$userId])->fetch()) {
        throw new DomainException('Conta não encontrada.');
    }
    $existing=query('SELECT code,total_cents,payload_hash,status FROM orders WHERE request_key=? AND user_id=?',[$requestKey,$userId])->fetch();
    if ($existing) {
        if (!hash_equals($existing['payload_hash'],$fingerprint)) throw new DomainException('A sacola mudou. Inicie uma nova solicitação.');
        db()->commit(); return ['code'=>$existing['code'],'total'=>(int)$existing['total_cents']/100,'status'=>$existing['status']];
    }
    // Limite por sessão. A sessão PHP serializa envios simultâneos do mesmo navegador.
    if (time()-($_SESSION['last_order'] ?? 0)<10) throw new DomainException('Aguarde alguns segundos antes de enviar outro pedido.');
    $locked=[]; $subtotal=0;
    foreach ($quantities as $id=>$qty) {
        // A mesma ordem de bloqueio em pedidos e cancelamentos evita inversões de locks.
        $p=query('SELECT *, (expires_on IS NULL OR expires_on>=CURRENT_DATE) AS valid_date FROM products WHERE id=? FOR UPDATE',[$id])->fetch();
        if (!$p || !$p['active'] || !$p['valid_date']) throw new DomainException('Um produto ficou indisponível. Atualize a feira.');
        if ($qty>(int)$p['stock']) throw new DomainException('Estoque insuficiente para '.$p['name'].'. Atualize a sacola.');
        $subtotal+=(int)$p['price_cents']*$qty; $locked[$id]=$p;
    }
    if ($subtotal>100000000) throw new DomainException('Pedido acima do limite permitido.');
    $shipping=$subtotal >= $config['free_shipping_from_cents'] ? 0 : $config['shipping_cents'];
    $code='AG-'.strtoupper(bin2hex(random_bytes(8)));
    query('INSERT INTO orders(user_id,code,request_key,payload_hash,customer_name,customer_email,customer_phone,region,address,payment_method,subtotal_cents,shipping_cents,total_cents) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)',[$userId,$code,$requestKey,$fingerprint,$name,$email,$phone,$region,$address,$payment,$subtotal,$shipping,$subtotal+$shipping]);
    $orderId=(int)db()->lastInsertId();
    foreach ($quantities as $id=>$qty) {
        $p=$locked[$id]; $balance=(int)$p['stock']-$qty;
        query('INSERT INTO order_items(order_id,product_id,product_name,unit,quantity,unit_price_cents) VALUES(?,?,?,?,?,?)',[$orderId,$id,$p['name'],$p['unit'],$qty,$p['price_cents']]);
        query('UPDATE products SET stock=? WHERE id=?',[$balance,$id]);
        query("INSERT INTO stock_movements(product_id,order_id,type,quantity,balance_after,reason) VALUES(?,?,'reserva',?,?,?)",[$id,$orderId,-$qty,$balance,'Reserva do pedido '.$code]);
    }
    query("INSERT INTO order_history(order_id,status,note) VALUES(?,'recebido','Solicitação recebida pelo site')",[$orderId]);
    db()->commit(); $_SESSION['last_order']=time();
    return ['code'=>$code,'total'=>($subtotal+$shipping)/100,'status'=>'recebido'];
    } catch (Throwable $error) {
        if (db()->inTransaction()) db()->rollBack();
        throw $error;
    }
}
function update_order(int $id,string $next,string $note): void {
    db()->beginTransaction();
    $order=query('SELECT * FROM orders WHERE id=? FOR UPDATE',[$id])->fetch();
    if (!$order) throw new DomainException('Pedido não encontrado.');
    if (!in_array($next,next_statuses($order['status']),true)) throw new DomainException('Esse pedido já mudou ou a transição não é permitida. Atualize a página.');
    if ($next==='cancelado') {
        $items=query('SELECT * FROM order_items WHERE order_id=? ORDER BY product_id',[$id])->fetchAll();
        foreach ($items as $item) {
            $p=query('SELECT stock FROM products WHERE id=? FOR UPDATE',[$item['product_id']])->fetch();
            $balance=(int)$p['stock']+(int)$item['quantity'];
            if ($balance>1000000) throw new DomainException('Saldo acima do limite. Revise o estoque antes de cancelar.');
            query('UPDATE products SET stock=? WHERE id=?',[$balance,$item['product_id']]);
            query("INSERT INTO stock_movements(product_id,order_id,admin_id,type,quantity,balance_after,reason) VALUES(?,?,?,'cancelamento',?,?,?)",[$item['product_id'],$id,$_SESSION['admin_id'],$item['quantity'],$balance,'Cancelamento do pedido '.$order['code']]);
        }
    }
    query('UPDATE orders SET status=? WHERE id=?',[$next,$id]);
    query('INSERT INTO order_history(order_id,admin_id,status,note) VALUES(?,?,?,?)',[$id,$_SESSION['admin_id'],$next,$note ?: 'Status atualizado']);
    db()->commit();
}
