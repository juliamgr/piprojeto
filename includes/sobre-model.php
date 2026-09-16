<?php
declare(strict_types=1);
function sobre_e($value): string { return htmlspecialchars((string)$value,ENT_QUOTES | ENT_SUBSTITUTE,'UTF-8'); }
function sobre_param(string $name,int $max=150): string {
    $value=$_GET[$name] ?? '';
    if (!is_string($value)) return '';
    preg_match('/^.{0,'.$max.'}/us',trim($value),$match);
    return $match[0] ?? '';
}
function sobre_categories(): array {
    return json_decode(file_get_contents(__DIR__.'/categorias.json'),true,512,JSON_THROW_ON_ERROR);
}
function sobre_filter_sql(array $filters,array $categories): array {
    $parts=[]; $params=[];
    if ($filters['busca']!=='') {
        $term='%'.strtr($filters['busca'],['!'=>'!!','%'=>'!%','_'=>'!_']).'%';
        $parts[]="(p.nome LIKE ? ESCAPE '!' OR p.municipio LIKE ? ESCAPE '!' OR p.processamento LIKE ? ESCAPE '!' OR p.registro_peaf LIKE ? ESCAPE '!')";
        array_push($params,$term,$term,$term,$term);
    }
    foreach(['municipio'=>'municipio','producao'=>'classificacao'] as $key=>$column) {
        if ($filters[$key]!=='') { $parts[]='p.'.$column.'=?'; $params[]=$filters[$key]; }
    }
    if (isset($categories[$filters['categoria']])) {
        $or=[];
        foreach($categories[$filters['categoria']]['terms'] as $term) {
            // MEL inclui melado/melancia; evite atribuir esses termos à apicultura.
            if ($filters['categoria']==='mel' && $term==='MEL') {
                $or[]="p.processamento REGEXP '(^|[^[:alpha:]])MEL([^[:alpha:]]|$)'";
            } else {
                $or[]='p.processamento LIKE ?'; $params[]='%'.$term.'%';
            }
        }
        $parts[]='('.implode(' OR ',$or).')';
    } elseif($filters['categoria']==='outros') {
        $or=[];
        foreach($categories as $key=>$category) foreach($category['terms'] as $term) {
            if ($key==='mel' && $term==='MEL') $or[]="p.processamento REGEXP '(^|[^[:alpha:]])MEL([^[:alpha:]]|$)'";
            else { $or[]='p.processamento LIKE ?'; $params[]='%'.$term.'%'; }
        }
        $parts[]='NOT ('.implode(' OR ',$or).')';
    }
    return [$parts?' WHERE '.implode(' AND ',$parts):'', $params];
}
function sobre_load(array $filters,int $requestedPage,array $categories): array {
    // Reutiliza apenas a conexão de produtores; não inicia login nem lê pedidos.
    $backend=dirname(__DIR__,2).'/BACKEND/app/produtores.php';
    if (!is_file($backend)) throw new RuntimeException('Instale o módulo Produtores RS no BACKEND.');
    require_once $backend;
    [$where,$params]=sobre_filter_sql($filters,$categories);
    $count=(int)produtores_query('SELECT COUNT(*) FROM produtores p'.$where,$params)->fetchColumn();
    $pages=max(1,(int)ceil($count/12)); $page=min($pages,max(1,$requestedPage));$offset=($page-1)*12;
    $rows=produtores_query('SELECT p.id,p.nome,p.municipio,p.processamento,p.registro_peaf,p.licenciamento,p.classificacao,p.data_inclusao,p.ultima_atualizacao FROM produtores p'.$where.' ORDER BY p.nome,p.municipio,p.id LIMIT 12 OFFSET '.$offset,$params)->fetchAll();
    $stats=produtores_query("SELECT COUNT(*) AS total,COUNT(DISTINCT municipio) AS municipios,COALESCE(SUM(classificacao='ORGÂNICO CERTIFICADO'),0) AS organicos FROM produtores")->fetch();
    $cities=produtores_query('SELECT DISTINCT municipio FROM produtores ORDER BY municipio')->fetchAll(PDO::FETCH_COLUMN);
    $types=produtores_query('SELECT DISTINCT classificacao FROM produtores ORDER BY classificacao')->fetchAll(PDO::FETCH_COLUMN);
    return compact('count','pages','page','rows','stats','cities','types');
}
function sobre_link(array $filters,int $page): string {
    return 'index3.php?'.http_build_query(array_merge(array_filter($filters,fn($v)=>$v!==''),['pagina'=>$page])).'#produtores';
}
function sobre_date(?string $date): string { return $date?date('d/m/Y',strtotime($date)):'Não informada'; }
