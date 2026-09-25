<?php
require __DIR__ . '/../BACKEND/app/bootstrap.php';
require_method('GET');
respond(['success' => true, 'products' => read_json('products.json')]);

try {
    require __DIR__ . '/../BACKEND/app/bootstrap.php';

    $rows = query("
        SELECT 
            id,
            name,
            category,
            producer,
            location,
            unit,
            price_cents,
            stock,
            image,
            badge
        FROM products
        WHERE active = 1
          AND stock > 0
          AND (expires_on IS NULL OR expires_on >= CURRENT_DATE)
        ORDER BY name
    ")->fetchAll();

    $products = array_map(static function (array $row): array {

        $image = (string) ($row['image'] ?: 'img/produto-placeholder.svg');

        if (
            !preg_match('~^img/[a-zA-Z0-9_./-]+$~', $image) ||
            str_contains($image, '..')
        ) {
            $image = 'img/produto-placeholder.svg';
        }

        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'category' => $row['category'],
            'producer' => $row['producer'],
            'location' => $row['location'],
            'unit' => $row['unit'],
            'price' => (int) $row['price_cents'] / 100,
            'stock' => (int) $row['stock'],
            'image' => $image,
            'badge' => $row['badge'],
        ];

    }, $rows);

    echo json_encode(
        ['products' => $products],
        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    );

} catch (Throwable $error) {

    error_log('Agrolink products API: ' . $error->getMessage());

    http_response_code(500);

    echo json_encode([
        'message' => 'Não foi possível carregar os produtos agora.'
    ], JSON_UNESCAPED_UNICODE);
}

