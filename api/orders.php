<?php
require __DIR__ . '/bootstrap.php';
require_method('POST');
$input = request_json();

$customer = is_array($input['customer'] ?? null) ? $input['customer'] : [];
$name = clean_text($customer['name'] ?? '', 90);
$email = filter_var(clean_text($customer['email'] ?? '', 160), FILTER_VALIDATE_EMAIL);
$phone = preg_replace('/\D+/', '', clean_text($customer['phone'] ?? '', 30));
$region = clean_text($customer['region'] ?? '', 60);
$address = clean_text($customer['address'] ?? '', 220);
$payment = clean_text($input['paymentMethod'] ?? '', 20);
$allowedRegions = ['Porto Alegre', 'Canoas', 'Viamão', 'Guaíba'];

if (mb_strlen($name) < 2 || !$email || strlen($phone) < 10 || mb_strlen($address) < 8 || !in_array($region, $allowedRegions, true) || !in_array($payment, ['pix', 'delivery'], true)) {
    respond(['success' => false, 'message' => 'Confira os dados de entrega.'], 422);
}

$requestedItems = is_array($input['items'] ?? null) ? $input['items'] : [];
if (!$requestedItems) respond(['success' => false, 'message' => 'A sacola está vazia.'], 422);

$products = read_json('products.json');
$productMap = [];
foreach ($products as $product) $productMap[(int)$product['id']] = $product;
$items = [];
$subtotal = 0.0;

foreach ($requestedItems as $requested) {
    $id = (int)($requested['productId'] ?? 0);
    $quantity = (int)($requested['quantity'] ?? 0);
    if (!isset($productMap[$id]) || $quantity < 1 || $quantity > (int)$productMap[$id]['stock']) {
        respond(['success' => false, 'message' => 'Um item está indisponível ou excede o estoque.'], 409);
    }
    $product = $productMap[$id];
    $lineTotal = round((float)$product['price'] * $quantity, 2);
    $subtotal += $lineTotal;
    $items[] = ['productId' => $id, 'name' => $product['name'], 'unitPrice' => (float)$product['price'], 'quantity' => $quantity, 'lineTotal' => $lineTotal];
}

$subtotal = round($subtotal, 2);
$shipping = $subtotal >= 120 ? 0.0 : 9.9;
$total = round($subtotal + $shipping, 2);
$code = 'AGR-' . gmdate('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
$order = [
    'code' => $code,
    'createdAt' => gmdate('c'),
    'status' => 'received',
    'customer' => ['name' => $name, 'email' => strtolower((string)$email), 'phone' => $phone, 'region' => $region, 'address' => $address],
    'paymentMethod' => $payment,
    'items' => $items,
    'subtotal' => $subtotal,
    'shipping' => $shipping,
    'total' => $total
];

update_json('orders.json', function (&$orders) use ($order) { $orders[] = $order; });
respond(['success' => true, 'order' => ['code' => $code, 'total' => $total, 'status' => 'received']], 201);
