<?php
function save_product(array $data,int $id): int {
    $name=field($data,'name',2,120); $category=field($data,'category',2,30);
    if (!in_array($category,['Cestas','Folhas','Frutas','Legumes'],true)) throw new DomainException('Categoria inválida.');
    $producer=field($data,'producer',2,120); $location=field($data,'location',2,100); $unit=field($data,'unit',1,40);
    $price=str_replace(',','.',field($data,'price',1,12));
    if (!preg_match('/^\d{1,5}(\.\d{1,2})?$/D',$price)) throw new DomainException('Preço inválido. Exemplo: 12,90.');
    $parts=explode('.',$price); $cents=(int)$parts[0]*100+(int)str_pad($parts[1] ?? '',2,'0');
    $min=integer($data['min_stock'] ?? null,0,1000000);
    $image=field($data,'image',0,240) ?: 'img/produto-placeholder.svg';
    // Somente imagens locais; não aceita URL, HTML ou caminho relativo com ..
    if (!preg_match('~^img/[a-zA-Z0-9_/-]+\.(jpg|jpeg|png|webp|gif|svg)$~D',$image)) throw new DomainException('Use um caminho local como img/alface.jpg, sem espaços ou acentos.');
    $badge=field($data,'badge',0,40); $batch=field($data,'batch_code',0,60);
    $expires=field($data,'expires_on',0,10); $active=isset($data['active'])?1:0;
    if ($expires!=='') {
        $date=DateTimeImmutable::createFromFormat('!Y-m-d',$expires);
        if (!$date || $date->format('Y-m-d')!==$expires || $expires<'1000-01-01') throw new DomainException('Data de validade inválida.');
    }
    db()->beginTransaction();
    if ($id) {
        $old=query('SELECT * FROM products WHERE id=? FOR UPDATE',[$id])->fetch();
        if (!$old) throw new DomainException('Produto não encontrado.');
        // Evita trocar o lote de um produto que ainda está reservado em pedido aberto.
        if ($old['batch_code']!==$batch || ($old['expires_on'] ?? '')!==$expires) {
            $open=query("SELECT COUNT(*) FROM order_items i JOIN orders o ON o.id=i.order_id WHERE i.product_id=? AND o.status NOT IN ('cancelado','concluido')",[$id])->fetchColumn();
            if ($open) throw new DomainException('Há pedidos abertos deste lote. Cadastre outro produto para um novo lote.');
        }
        query('UPDATE products SET name=?,category=?,producer=?,location=?,unit=?,price_cents=?,min_stock=?,image=?,badge=?,batch_code=?,expires_on=?,active=? WHERE id=?',[$name,$category,$producer,$location,$unit,$cents,$min,$image,$badge,$batch,$expires ?: null,$active,$id]);
    } else {
        query('INSERT INTO products(name,category,producer,location,unit,price_cents,min_stock,image,badge,batch_code,expires_on,active) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)',[$name,$category,$producer,$location,$unit,$cents,$min,$image,$badge,$batch,$expires ?: null,$active]);
        $id=(int)db()->lastInsertId();
    }
    db()->commit(); return $id;
}
function move_stock(int $id,array $data): void {
    $type=field($data,'type',3,20); $qty=integer($data['quantity'] ?? null,1,1000000); $reason=field($data,'reason',3,220);
    if (!in_array($type,['entrada','ajuste_entrada','ajuste_saida','doacao','perda'],true)) throw new DomainException('Movimentação inválida.');
    $delta=in_array($type,['entrada','ajuste_entrada'],true)?$qty:-$qty;
    db()->beginTransaction();
    $p=query('SELECT *, (expires_on<CURRENT_DATE) AS expired FROM products WHERE id=? FOR UPDATE',[$id])->fetch();
    if (!$p) throw new DomainException('Produto não encontrado.');
    if ($type==='doacao' && $p['expired']) throw new DomainException('Não é possível registrar doação de alimento vencido. Registre a perda.');
    $balance=(int)$p['stock']+$delta;
    if ($balance<0 || $balance>1000000) throw new DomainException('A movimentação excede o saldo disponível ou o limite do estoque.');
    query('UPDATE products SET stock=? WHERE id=?',[$balance,$id]);
    query('INSERT INTO stock_movements(product_id,admin_id,type,quantity,balance_after,reason) VALUES(?,?,?,?,?,?)',[$id,$_SESSION['admin_id'],$type,$delta,$balance,$reason]);
    db()->commit();
}
function movement_labels(): array { return ['entrada'=>'Entrada','ajuste_entrada'=>'Correção (+)','ajuste_saida'=>'Correção (−)','reserva'=>'Reserva de pedido','cancelamento'=>'Devolução por cancelamento','doacao'=>'Doação','perda'=>'Perda / descarte']; }
