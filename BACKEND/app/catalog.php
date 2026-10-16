<?php
declare(strict_types=1);
function public_products(): array {
    $rows = query('SELECT * FROM products WHERE active=1 AND stock>0 AND (expires_on IS NULL OR expires_on>=CURRENT_DATE) ORDER BY name,id')->fetchAll();
    $legacy = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/FRONT-AGROLINK/') !== false;
    $prefix = $legacy ? '../' : '';
    return array_map(function ($p) use ($prefix) {
        $image = (string)($p['image'] ?? '');
        if (!preg_match('~^img/[a-zA-Z0-9_/-]+\.(jpg|jpeg|png|webp|gif|svg)$~D',$image)) $image = 'BACKEND/img/produto-placeholder.svg';
        return [
            'id'=>(int)$p['id'], 'name'=>$p['name'], 'category'=>$p['category'],
            'producer'=>$p['producer'], 'location'=>$p['location'], 'unit'=>$p['unit'],
            'price'=>(int)$p['price_cents']/100, 'stock'=>(int)$p['stock'],
            'image'=>$prefix.$image, 'imagePosition'=>'center', 'badge'=>$p['badge'],
        ];
    }, $rows);
}
