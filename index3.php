<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
require __DIR__.'/includes/sobre-model.php';
$categories=sobre_categories();
$filters=[];
foreach(['busca','municipio','categoria','producao'] as $key) $filters[$key]=sobre_param($key);
if ($filters['categoria']!=='' && $filters['categoria']!=='outros' && !isset($categories[$filters['categoria']])) $filters['categoria']='';
$available=true;
try { $data=sobre_load($filters,(int)sobre_param('pagina',8),$categories); }
catch (Throwable $error) { error_log('Agrolink Sobre: '.$error->getMessage());$available=false;$data=['stats'=>null,'count'=>0,'rows'=>[],'cities'=>[],'types'=>[],'page'=>1,'pages'=>1]; }
$heroPhoto=is_file(__DIR__.'/img/agricultores1.jpg')?'img/agricultores1.jpg':null;
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sobre nós e produtores do RS — Agrolink</title>
  <meta name="description"
        content="Conheça o Agrolink, nossas parcerias e as agroindústrias do Rio Grande do Sul.">
  <meta name="theme-color" content="#073f2a">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap"
    rel="stylesheet"
  >
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
    crossorigin="anonymous"
  >
  <link rel="stylesheet" href="index3.css">
  <script src="index3.js" defer></script>
</head>
<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
  crossorigin="anonymous"
></script>
<body>
<a class="skip" href="#conteudo">Pular para o conteúdo</a>
<header class="site-header"><div class="shell header-inner">
<img src="img/agrolink-logo.png" width="160" height="57" alt="Agrolink">
<button class="menu-button" type="button" aria-controls="main-nav" aria-expanded="false" hidden>Menu <span aria-hidden="true">
    ☰</span></button>
<nav id="main-nav" aria-label="Navegação principal">
    <a href="index.html">Produtos</a>
    <a href="index2.html">Pontos de retirada</a>
    <a href="index3.php" aria-current="page">Sobre nós</a>
</nav>
<a class="header-cta" href="#produtores">
    Conheça os produtores <span aria-hidden="true">↗</span>
</a>
</div>
 </header>
<main id="conteudo">
<section class="hero shell" aria-labelledby="titulo">
    <div class="hero-copy"><p class="eyebrow">
        <span>
        </span> SOMOS AGROLINK</p>
        <h1 id="titulo">A origem é próxima.<br>
        <em>O cuidado também.</em>
    </h1>
    <p class="hero-text">Acreditamos que a boa alimentação começa nas relações: com a terra, com quem produz e com a comunidade que recebe cada alimento.</p><div class="hero-actions"><a class="button primary" href="#nossa-historia">Conheça nossa história <span aria-hidden="true">↓</span></a><a class="text-link" href="#produtores">Explore a produção gaúcha ↗</a></div><div class="hero-footnote"><span class="seed-mark" aria-hidden="true">↗</span><p>Do Rio Grande do Sul.<br><strong>Para uma mesa com mais sentido.</strong></p></div></div>
<div class="hero-visual <?= $heroPhoto?'has-photo':'' ?>">
<?php if($heroPhoto): ?><img class="hero-photo" src="<?= sobre_e($heroPhoto) ?>" alt="Agricultores apresentando sua produção na feira" width="620" height="650" fetchpriority="high"><?php else: ?><div class="field-lines" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div><div class="visual-message"><span class="eyebrow">NOSSA ESSÊNCIA</span><p>Feito de terra.<br>Feito de gente.<br><em>Feito de encontros.</em></p></div><?php endif; ?>
<div class="visual-caption"><span class="small-leaf" aria-hidden="true"></span><div><strong>Quem cultiva faz parte da história.</strong><span>Agricultura familiar · Rio Grande do Sul</span></div></div><span class="visual-stamp">ORIGEM LOCAL<br><b>AGROLINK</b><br>CONEXÕES REAIS</span></div></section>
<section class="story shell" id="nossa-historia" aria-labelledby="story-title"><div><p class="eyebrow">01 / NOSSA HISTÓRIA</p><h2 id="story-title">Aproximar o campo.<br><em>Transformar o cotidiano.</em></h2></div><div class="story-body"><p>O Agrolink nasce de uma ideia simples: tornar os alimentos frescos mais acessíveis e valorizar as pessoas que os produzem. Nosso projeto conecta tecnologia, agricultura familiar e consumo consciente para encurtar a distância entre a colheita e a mesa.</p><p>Queremos fortalecer a economia local, ampliar a visibilidade dos pequenos produtores e contribuir para reduzir o desperdício. Cada conexão é uma oportunidade de fazer o alimento circular melhor — e de reconhecer o trabalho que existe por trás dele.</p><a class="text-link" href="index2.html">Conheça os pontos de retirada <span aria-hidden="true">↗</span></a></div></section>
<section class="values shell" aria-label="Nossos compromissos"><article><span class="value-number">01</span><h3>Valorizar quem produz</h3><p>Dar espaço às histórias, aos saberes e à diversidade da agricultura familiar gaúcha.</p></article><article><span class="value-number">02</span><h3>Fazer o alimento chegar</h3><p>Aproximar a produção local das pessoas que buscam uma alimentação mais natural e acessível.</p></article><article><span class="value-number">03</span><h3>Cuidar de cada etapa</h3><p>Incentivar escolhas conscientes e uma distribuição que ajude a evitar perdas de alimentos.</p></article></section>
<section class="partners" id="parcerias" aria-labelledby="partners-title"><div class="shell"><div class="section-heading"><div><p class="eyebrow">02 / PARCERIAS</p><h2 id="partners-title">Uma ideia cresce melhor<br><em>quando é compartilhada.</em></h2></div><p>Em parceria com a Secretaria de Desenvolvimento Rural do RS, a FETAG-RS e a CPOrg-RS, o Agrolink reúne esforços em torno de um propósito: aproximar a agricultura familiar de uma alimentação mais consciente.</p></div><div class="partner-grid">
<article class="partner-card"><div class="partner-logo"><img src="img/secretaria-rs.svg" alt="Secretaria de Desenvolvimento Rural do Rio Grande do Sul" width="250" height="110" loading="lazy"></div><div><span class="eyebrow">DESENVOLVIMENTO RURAL</span><h3>Secretaria de Desenvolvimento Rural do RS</h3><p>Uma parceria voltada à valorização do campo e à aproximação entre a produção gaúcha e as comunidades.</p></div></article>
<article class="partner-card"><div class="partner-logo"><img src="img/FETAG-RS.svg" alt="FETAG-RS" width="250" height="110" loading="lazy"></div><div><span class="eyebrow">AGRICULTURA FAMILIAR</span><h3>FETAG-RS</h3><p>O compromisso compartilhado de valorizar as famílias produtoras e dar visibilidade ao trabalho que alimenta o nosso estado.</p></div></article>
<article class="partner-card"><div class="partner-logo"><img src="img/CPORG-RS.avif" alt="CPOrg-RS — Comissão da Produção Orgânica do Rio Grande do Sul" width="732" height="254" loading="lazy"></div><div><span class="eyebrow">PRODUÇÃO ORGÂNICA</span><h3>CPOrg-RS</h3><p>A produção orgânica e o cuidado com a terra fazem parte do diálogo que queremos levar a mais pessoas.</p></div></article>
</div></div></section>
<section class="directory shell" id="produtores" aria-labelledby="directory-title"><div class="section-heading"><div><p class="eyebrow">03 / CONHEÇA A PRODUÇÃO GAÚCHA</p><h2 id="directory-title">Tem uma história<br><em>em cada origem.</em></h2></div><p>Explore as agroindústrias do Rio Grande do Sul e descubra a diversidade de alimentos produzidos por aqui.</p></div>
<?php if($available): ?><div class="directory-stats"><div><strong><?= number_format((int)$data['stats']['total'],0,',','.') ?></strong><span>agroindústrias na base</span></div><div><strong><?= (int)$data['stats']['municipios'] ?></strong><span>municípios representados</span></div><div><strong><?= (int)$data['stats']['organicos'] ?></strong><span>registros como orgânico certificado*</span></div><p>* Classificação informada na planilha de referência do PEAF.</p></div><?php endif; ?>
<form class="search-panel" method="get" action="index3.php#produtores" aria-label="Pesquisar agroindústrias"><div class="search-top"><label for="busca">O que você está procurando?</label><div class="search-bar"><span aria-hidden="true">⌕</span><input id="busca" type="search" name="busca" maxlength="150" value="<?= sobre_e($filters['busca']) ?>" placeholder="Nome, alimento, município ou registro PEAF"><button class="button primary" type="submit">Pesquisar <span aria-hidden="true">→</span></button></div></div><div class="filter-grid">
<label>Categoria de alimentos<select name="categoria"><option value="">Todas as categorias</option><?php foreach($categories as $key=>$category): ?><option value="<?= sobre_e($key) ?>" <?= $filters['categoria']===$key?'selected':'' ?>><?= sobre_e($category['label']) ?></option><?php endforeach; ?><option value="outros" <?= $filters['categoria']==='outros'?'selected':'' ?>>Outros processamentos</option></select></label>
<label>Município<select name="municipio"><option value="">Todos os municípios</option><?php if($filters['municipio']!=='' && !in_array($filters['municipio'],$data['cities'],true)): ?><option selected value="<?= sobre_e($filters['municipio']) ?>"><?= sobre_e($filters['municipio']) ?></option><?php endif; ?><?php foreach($data['cities'] as $city): ?><option value="<?= sobre_e($city) ?>" <?= $filters['municipio']===$city?'selected':'' ?>><?= sobre_e($city) ?></option><?php endforeach; ?></select></label>
<label>Tipo de produção<select name="producao"><option value="">Todos os tipos</option><?php if($filters['producao']!=='' && !in_array($filters['producao'],$data['types'],true)): ?><option selected value="<?= sobre_e($filters['producao']) ?>"><?= sobre_e($filters['producao']) ?></option><?php endif; ?><?php foreach($data['types'] as $type): ?><option value="<?= sobre_e($type) ?>" <?= $filters['producao']===$type?'selected':'' ?>><?= sobre_e($type) ?></option><?php endforeach; ?></select></label></div><div class="filter-bottom"><span>Escolha os filtros e clique em Pesquisar.</span><a href="index3.php#produtores">Limpar filtros</a></div></form>
<?php if(!$available): ?><div class="empty-state" role="status"><h3>A consulta aos produtores está temporariamente indisponível.</h3><p>Tente novamente em alguns instantes. Você pode continuar conhecendo o Agrolink nesta página.</p><a class="button outline" href="index3.php#produtores">Tentar novamente</a></div>
<?php else: ?><div class="results-heading"><p><strong><?= number_format($data['count'],0,',','.') ?></strong> agroindústria<?= $data['count']===1?'':'s' ?> encontrada<?= $data['count']===1?'':'s' ?></p><span>Ordem alfabética · página <?= $data['page'] ?> de <?= $data['pages'] ?></span></div>
<?php if(!$data['rows']): ?><div class="empty-state"><h3>Não encontramos um resultado por aqui.</h3><p>Tente outro alimento ou amplie os filtros da sua pesquisa.</p><a class="button outline" href="index3.php#produtores">Ver todos os produtores</a></div><?php endif; ?>
<div class="producer-grid"><?php foreach($data['rows'] as $p): ?><article class="producer-card"><div class="producer-top"><span class="producer-pin" aria-hidden="true">↗</span><span><?= sobre_e($p['municipio']) ?> · RS</span></div><h3><?= sobre_e($p['nome']) ?></h3><span class="production-tag <?= $p['classificacao']==='ORGÂNICO CERTIFICADO'?'organic':'' ?>"><?= sobre_e($p['classificacao']) ?></span><p class="producer-label">PROCESSAMENTO</p><p class="producer-foods"><?= sobre_e($p['processamento']) ?></p><details class="producer-details"><summary>Informações do cadastro <span aria-hidden="true">+</span></summary><dl><div><dt>Registro PEAF</dt><dd><?= sobre_e($p['registro_peaf']) ?></dd></div><div><dt>Licenciamento informado</dt><dd><?= sobre_e($p['licenciamento']) ?></dd></div><div><dt>Inclusão na fonte</dt><dd><?= sobre_e(sobre_date($p['data_inclusao'])) ?></dd></div><div><dt>Última atualização na fonte</dt><dd><?= sobre_e(sobre_date($p['ultima_atualizacao'])) ?></dd></div></dl><p>A fonte não informa telefone ou endereço completo. Datas e licenciamento reproduzem o cadastro de referência.</p></details></article><?php endforeach; ?></div>
<?php if($data['pages']>1): ?><nav class="pagination" aria-label="Páginas de produtores"><?php if($data['page']>1): ?><a class="button outline" href="<?= sobre_e(sobre_link($filters,$data['page']-1)) ?>">← Anterior</a><?php endif; ?><span>Página <?= $data['page'] ?> de <?= $data['pages'] ?></span><?php if($data['page']<$data['pages']): ?><a class="button outline" href="<?= sobre_e(sobre_link($filters,$data['page']+1)) ?>">Próxima →</a><?php endif; ?></nav><?php endif; ?>
<?php endif; ?>
<p class="directory-note">Fonte: planilha de agroindústrias inclusas no PEAF, referência agosto de 2026. A presença nesta lista não indica vínculo comercial com o Agrolink. As categorias de alimentos são agrupamentos para facilitar a pesquisa; um cadastro pode aparecer em mais de uma. A situação atual de licenças e certificações deve ser confirmada com o estabelecimento.</p>
</section>
<section class="closing"><div class="shell closing-inner"><div><p class="eyebrow">O PRÓXIMO PASSO É PERTO</p><h2>Conheça a origem.<br><em>Faça parte dessa conexão.</em></h2></div><a class="button lime" href="index.html">Visite a nossa feira <span aria-hidden="true">↗</span></a></div></section>
</main>
<footer class="site-footer">
<div class="shell footer-top">
    <div>
   <img src="img/LOGO-AGROLINK-DARK.svg" width="170" height="60" alt="Agrolink">
        </a><p>Alimentos frescos, origem próxima<br>e relações que fazem a diferença.</p>
    </div><div>
        <h3>Explore</h3><a href="index.html">Produtos</a>
        <a href="index2.html">Pontos de retirada</a>
        <a href="#nossa-historia">Nossa história</a></div><div><h3>Conexões</h3><a href="#parcerias">Nossas parcerias</a><a href="#produtores">Produtores do RS</a><span>Feito no Rio Grande do Sul.</span></div></div><div class="shell footer-bottom"><span>© <?= date('Y') ?> Agrolink.</span><span>Da terra para a mesa, com propósito.</span><a href="#conteudo">Voltar ao topo ↑</a></div></footer>
</body></html>
