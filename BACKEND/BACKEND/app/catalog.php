<?php
function public_products(): array {
    $rows = query('SELECT * FROM products WHERE active=1 AND (expires_on IS NULL OR expires_on>=CURRENT_DATE) ORDER BY id')->fetchAll();
    return array_map(function ($p) { return [
        'id'=>(int)$p['id'], 'name'=>$p['name'], 'category'=>$p['category'],
        'producer'=>$p['producer'], 'location'=>$p['location'], 'unit'=>$p['unit'],
        'price'=>(int)$p['price_cents']/100, 'stock'=>(int)$p['stock'],
        'image'=>$p['image'], 'imagePosition'=>'center', 'badge'=>$p['badge'],
    ]; }, $rows);
}
