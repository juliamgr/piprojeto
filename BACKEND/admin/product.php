<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

require_admin();

require __DIR__ . '/../app/admin-layout.php';
require __DIR__ . '/../app/inventory.php';


$id = max(0, (int)($_GET['id'] ?? 0));

$submitted = null;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        csrf_check();

        $action = $_POST['action'] ?? '';

        if ($action === 'save') {

            $imagePath = $_POST['image'] ?? '';

            if (
                isset($_FILES['inputImagem']) &&
                $_FILES['inputImagem']['error'] !== UPLOAD_ERR_NO_FILE
            ) {

                if ($_FILES['inputImagem']['error'] !== UPLOAD_ERR_OK) {
                    throw new RuntimeException(
                        'Erro ao enviar a imagem do produto.'
                    );
                }

                $fileTmp  = $_FILES['inputImagem']['tmp_name'];
                $fileSize = $_FILES['inputImagem']['size'];

                $fileType = mime_content_type($fileTmp);

                if ($fileSize > 10 * 1024 * 1024) {
                    throw new RuntimeException(
                        'A imagem deve ter no máximo 10 MB.'
                    );
                }

                
                $allowedTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                if (!isset($allowedTypes[$fileType])) {
                    throw new RuntimeException(
                        'Tipo de imagem inválido. Use JPG, PNG ou WEBP.'
                    );
                }

                $extension = $allowedTypes[$fileType];

            
                
                $uploadDir = dirname(__DIR__, 2)
                . DIRECTORY_SEPARATOR
                . 'img'
                . DIRECTORY_SEPARATOR
                . 'produtos'
                . DIRECTORY_SEPARATOR;

                if (!is_dir($uploadDir)) {

                    if (!mkdir($uploadDir, 0755, true)) {
                        throw new RuntimeException(
                            'Não foi possível criar a pasta de imagens.'
                        );
                    }
                }

                $photoName =
                    bin2hex(random_bytes(16))
                    . '.'
                    . $extension;

                $destino = $uploadDir . $photoName;

                if (!move_uploaded_file($fileTmp, $destino)) {
                    throw new RuntimeException(
                        'Não foi possível salvar a imagem.'
                    );
                }

    
                $imagePath = 'img/produtos/' . $photoName;
            }

            /*
             * Passa a imagem para o save_product().
             */
            $_POST['image'] = $imagePath;

            $id = save_product($_POST, $id);

            flash('Produto salvo com sucesso!');

            redirect('product.php?id=' . $id);

        }

        elseif ($action === 'move') {

            move_stock($id, $_POST);

            flash('Movimentação registrada e estoque atualizado.');

            redirect('product.php?id=' . $id);
        }

    } catch (Throwable $err) {

        $error_message = $err->getMessage();

        $submitted = $_POST;
    }
}


if ($id > 0) {

    $product = query(
        'SELECT * FROM products WHERE id = ?',
        [$id]
    )->fetch();

    if (!$product) {

        http_response_code(404);

        exit('Produto não encontrado.');

    }

    $p = $product;

} else {

    $p = [

        'id' => 0,

        'name' => '',

        'category' => 'Legumes',

        'producer' => '',

        'location' => 'Porto Alegre',

        'unit' => 'unidade',

        'price_cents' => 0,

        'stock' => 0,

        'min_stock' => 5,

        'image' => 'img/produto-placeholder.svg',

        'badge' => '',

        'batch_code' => '',

        'expires_on' => '',

        'active' => 1

    ];

}


if ($submitted !== null) {

    $p['name'] = $submitted['name'] ?? $p['name'];

    $p['category'] = $submitted['category'] ?? $p['category'];

    $p['producer'] = $submitted['producer'] ?? $p['producer'];

    $p['location'] = $submitted['location'] ?? $p['location'];

    $p['unit'] = $submitted['unit'] ?? $p['unit'];

    $p['price_cents'] = isset($submitted['price'])
        ? (int)round((float)str_replace(',', '.', $submitted['price']) * 100)
        : $p['price_cents'];

    $p['min_stock'] = $submitted['min_stock'] ?? $p['min_stock'];

    $p['image'] = $submitted['image'] ?? $p['image'];

    $p['badge'] = $submitted['badge'] ?? $p['badge'];

    $p['batch_code'] = $submitted['batch_code'] ?? $p['batch_code'];

    $p['expires_on'] = $submitted['expires_on'] ?? $p['expires_on'];

    $p['active'] = isset($submitted['active']) ? 1 : 0;

}

$price = number_format(
    ((int)$p['price_cents']) / 100,
    2,
    ',',
    ''
);


page_start(
    $id > 0 ? '' : 'Novo produto',
    'inventory'
);

?>

<div class="section-heading">

    <div>

        <h1>
            <?= $id > 0 ? 'Editar produto' : ' ' ?>
        </h1>


    </div>

    <a
        class="button secondary"
        href="inventory.php"
    >
        ← Voltar ao estoque
    </a>

</div>


<?php if ($error_message): ?>

    <div
        class="panel"
        style="
            border:1px solid #d96c6c;
            margin-bottom:20px;
        "
    >

        <strong>Não foi possível salvar.</strong>

        <p style="margin:8px 0 0;">
            <?= e($error_message) ?>
        </p>

    </div>

<?php endif; ?>


<form
    method="post"
    enctype="multipart/form-data"
    action="product.php<?= $id > 0 ? '?id=' . $id : '' ?>"
>

    <?= csrf_field() ?>

    <input
        type="hidden"
        name="action"
        value="save"
    >

    <input
        type="hidden"
        name="image"
        value="<?= e($p['image']) ?>"
    >


    <section class="panel">

        <div class="section-heading">

            <div>

                <h2>Informações do produto</h2>

                <p class="muted">
                    Esses dados aparecem no catálogo da AgroLink.
                </p>

            </div>

        </div>


        <div class="form-grid">


            <label>

                Nome do produto

                <input
                    type="text"
                    name="name"
                    value="<?= e($p['name']) ?>"
                    maxlength="120"
                    required
                >

            </label>

            <label>

                Categoria

                <select
                    name="category"
                    required
                >

                    <?php foreach (
                        ['Cestas', 'Folhas', 'Frutas', 'Legumes', 'Mel', 'Temperos', 'Ovos','Laticínio', 'Panificados', 'Tubérculos' ]
                        as $category
                    ): ?>

                        <option
                            value="<?= e($category) ?>"
                            <?= $p['category'] === $category ? 'selected' : '' ?>
                        >

                            <?= e($category) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </label>

            <label>

                Produtor

                <input
                    type="text"
                    name="producer"
                    value="<?= e($p['producer']) ?>"
                    maxlength="120"
                    required
                >

            </label>


            <label>

                Localização

                <input
                    type="text"
                    name="location"
                    value="<?= e($p['location']) ?>"
                    maxlength="100"
                    required
                >

            </label>

            <label>

                Unidade

                <input
                    type="text"
                    name="unit"
                    value="<?= e($p['unit']) ?>"
                    maxlength="40"
                    required
                >

            </label>


            <label>

                Preço

                <input
                    type="text"
                    name="price"
                    value="<?= e($price) ?>"
                    placeholder="Ex.: 12,90"
                    required
                >

            </label>


            <label>

                Estoque mínimo

                <input
                    type="number"
                    name="min_stock"
                    value="<?= (int)$p['min_stock'] ?>"
                    min="0"
                    max="1000000"
                    required
                >

            </label>



            <label>

Imagem do produto

<input
    type="file"
    name="inputImagem"
    id="inputImagem"
    accept="image/jpeg,image/png,image/webp"
>

<small class="muted">
    JPG, PNG ou WEBP — máximo de 10 MB.
</small>

<?php if (!empty($p['image'])): ?>

    <div style="
        margin-top:12px;
        display:flex;
        align-items:center;
        gap:12px;
    ">

        <img
            src="../../<?= e($p['image']) ?>"
            alt="Imagem atual do produto"
            style="
                width:90px;
                height:90px;
                object-fit:cover;
                border-radius:10px;
                border:1px solid #d9e2d8;
            "
        >

        <span class="muted">
            Imagem atual
        </span>

    </div>

<?php endif; ?>

</label>

            <label>

                Selo

                <input
                    type="text"
                    name="badge"
                    value="<?= e($p['badge']) ?>"
                    placeholder="Orgânico"
                    maxlength="40"
                >

            </label>


            <label>

                Código do lote

                <input
                    type="text"
                    name="batch_code"
                    value="<?= e($p['batch_code']) ?>"
                    maxlength="60"
                >

            </label>


            <label>

                Data de validade

                <input
                    type="date"
                    name="expires_on"
                    value="<?= e($p['expires_on'] ?? '') ?>"
                >

            </label>


        </div>


        <label
            style="
                display:flex;
                align-items:center;
                gap:10px;
                margin-top:20px;
                margin-left: -25px;
            "
        >

            <input
                type="checkbox"
                name="active"
                value="1"
                <?= !empty($p['active']) ? 'checked' : '' ?>
            > Produto ativo no catálogo

        </label>


        <div
            style="
                display:flex;
                gap:10px;
                margin-top:25px;
            "
        >

            <button
                type="submit"
                class="button button-primary"
            >
                Salvar produto
            </button>

            <a
                href="inventory.php"
                class="button secondary"
            >
                Cancelar
            </a>

        </div>

    </section>

</form>


<?php if ($id > 0): ?>


    <section
        class="panel"
        style="margin-top:20px;"
    >

        <div class="section-heading">

            <div>

                <h2>Movimentar estoque</h2>

                <p class="muted">
                    Estoque atual:
                    <strong>
                        <?= (int)$p['stock'] ?>
                    </strong>
                </p>

            </div>

        </div>


        <form
            method="post"
            action="product.php?id=<?= $id ?>"
        >

            <?= csrf_field() ?>

            <input
                type="hidden"
                name="action"
                value="move"
            >


            <div class="form-grid">


                <label>

                    Tipo

                    <select
                        name="type"
                        required
                    >

                        <?php foreach (
                            movement_labels()
                            as $type => $label
                        ): ?>

                            <?php
                            if (
                                in_array(
                                    $type,
                                    [
                                        'reserva',
                                        'cancelamento'
                                    ],
                                    true
                                )
                            ) {
                                continue;
                            }
                            ?>

                            <option value="<?= e($type) ?>">

                                <?= e($label) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </label>


                <label>

                    Quantidade

                    <input
                        type="number"
                        name="quantity"
                        min="1"
                        max="1000000"
                        required
                    >

                </label>


                <label>

                    Motivo

                    <input
                        type="text"
                        name="reason"
                        maxlength="220"
                        required
                    >

                </label>


            </div>


            <button
                type="submit"
                class="button button-primary"
                style="margin-top:20px;"
            >

                Registrar movimentação

            </button>


        </form>

    </section>


<?php endif; ?>


<?php

page_end();

?>