<?php
require __DIR__ . '/bootstrap.php';
require_method('GET');
respond(['success' => true, 'products' => read_json('products.json')]);
