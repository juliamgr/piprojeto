<?php
require __DIR__.'/../app/bootstrap.php';
require_admin();
require __DIR__.'/../app/admin-layout.php';
require __DIR__.'/../app/produtores.php';
$filters=[];
foreach (['q','municipio','classificacao','licenciamento','alertas'] as $key) $filters[$key]=produtores_param($key);
try {
    [$where,$params]=produtores_filters($filters);
    $count=(int)produtores_query('SELECT COUNT(*) FROM produtores p'.$where,$params)->fetchColumn();
    $pages=max(1,(int)ceil($count/24));
    $page=min($pages,max(1,(int)produtores_param('pagina',8))); $offset=($page-1)*24;
    $rows=produtores_query('SELECT p.* FROM produtores p'.$where.' ORDER BY p.nome,p.municipio,p.id LIMIT 24 OFFSET '.$offset,$params)->fetchAll();
    $stats=produtores_query("SELECT COUNT(*) AS total,COUNT(DISTINCT municipio) AS municipios,COALESCE(SUM(classificacao='ORGÂNICO CERTIFICADO'),0) AS organicos,COALESCE(SUM(alerta_fonte IS NOT NULL),0) AS alertas FROM produtores")->fetch();
    $options=[];
    foreach (['municipio','classificacao','licenciamento'] as $col) $options[$col]=produtores_query('SELECT DISTINCT '.$col.' FROM produtores ORDER BY '.$col)->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $err) { produtores_unavailable($err); }
page_start('Produtores do RS','produtores'); ?>
<section class="pr-intro">
    <div>
    <p class="eyebrow">AGROINDÚSTRIAS FAMILIARES / PEAF</p><h2>Consulte nossos Produtores.</h2>
</div>
</section>
<section class="stats pr-stats" aria-label="Resumo de toda a base"><article><span>Agroindústrias</span><strong><?= number_format((int)$stats['total'],0,',','.') ?></strong><small>Cadastros na planilha</small></article><article><span>Municípios</span><strong><?= (int)$stats['municipios'] ?></strong><small>Localidades informadas</small></article><article><span>Orgânico certificado</span><strong><?= (int)$stats['organicos'] ?></strong><small>Classificação declarada na fonte</small></article><article><span>Registros a conferir</span><strong><?= (int)$stats['alertas'] ?></strong><small>Erros ou datas atípicas na origem</small></article></section>
<form class="panel pr-filters" method="get" action="produtores.php"><label class="pr-search">Busca<input type="search" name="q" maxlength="150" value="<?= e($filters['q']) ?>" placeholder="Nome, alimento, município ou registro PEAF"></label>
<?php foreach(['municipio'=>'Município','classificacao'=>'Tipo de produção','licenciamento'=>'Licenciamento sanitário'] as $key=>$label): ?><label><?= e($label) ?><select name="<?= e($key) ?>"><option value="">Todos</option><?php foreach($options[$key] as $value): ?><option value="<?= e($value) ?>" <?= $filters[$key]===$value?'selected':'' ?>><?= e($value) ?></option><?php endforeach; ?></select></label><?php endforeach; ?>
<div class="pr-filter-actions"><label class="checkbox"><input type="checkbox" name="alertas" value="1" <?= $filters['alertas']==='1'?'checked':'' ?>> Apenas registros a conferir</label><div><button class="button" type="submit">Buscar produtores</button> <a class="button secondary" href="produtores.php">Limpar filtros</a></div></div></form>
<div class="section-heading"><h2><?= number_format($count,0,',','.') ?> resultado<?= $count===1?'':'s' ?></h2><span class="muted small">Em ordem alfabética · página <?= $page ?> de <?= $pages ?></span></div>
<?php if (!$rows): ?><section class="panel empty"><h2>Nenhum produtor encontrado.</h2><p>Tente outro nome, alimento ou município.</p><a class="button secondary" href="produtores.php">Ver todos</a></section><?php endif; ?>
<div class="pr-grid"><?php foreach($rows as $p): ?><article class="pr-card"><div class="pr-card-top"><span class="pr-location"><?= e($p['municipio']) ?> · RS</span><?php if($p['alerta_fonte']): ?><span class="pr-attention" title="A fonte contém informação a conferir">Conferir fonte</span><?php endif; ?></div><h2><a href="produtor.php?id=<?= (int)$p['id'] ?>"><?= e($p['nome']) ?></a></h2><span class="pr-badge <?= e(produtores_class($p['classificacao'])) ?>"><?= e($p['classificacao']) ?></span><p class="pr-processing"><?= e($p['processamento']) ?></p><dl class="pr-meta"><div><dt>Registro PEAF</dt><dd><?= e($p['registro_peaf']) ?></dd></div><div><dt>Licenciamento</dt><dd><?= e($p['licenciamento']) ?></dd></div></dl><a class="pr-open" href="produtor.php?id=<?= (int)$p['id'] ?>">Ver ficha completa <span aria-hidden="true">→</span></a></article><?php endforeach; ?></div>
<?php if ($count): ?><nav class="pagination pr-pagination" aria-label="Páginas de produtores"><?php if($page>1): ?><a class="button secondary" href="?<?= e(http_build_query(array_merge($filters,['pagina'=>$page-1]))) ?>">← Anterior</a><?php endif; ?><span><?= $page ?> / <?= $pages ?></span><?php if($page<$pages): ?><a class="button secondary" href="?<?= e(http_build_query(array_merge($filters,['pagina'=>$page+1]))) ?>">Próxima →</a><?php endif; ?></nav><?php endif; ?>
<p class="pr-source-note">Fonte: agroindústrias inclusas no PEAF, planilha enviada com referência agosto de 2026. Esta lista não indica parceria com o Agrolink nem verifica a situação atual das licenças. Os totais acima representam toda a base.</p>
<?php page_end(); ?>
