<?php
function produtores_db(): PDO {
    static $pdo;
    if ($pdo instanceof PDO) return $pdo;
    $base=require __DIR__.'/config.php';
    $settings=require __DIR__.'/produtores-config.php';
    $dsn=$settings['dsn'];
    if (!$dsn) {
        if (empty($settings['database']) || !preg_match('/^[a-zA-Z0-9_]+$/D', $settings['database'])) {
            throw new RuntimeException('Nome de banco inválido ou não definido.');
        }
        $dsn = preg_replace('/dbname=[^;]*/i', 'dbname=' . $settings['database'], $base['dsn'], -1, $count);
        if (!$count) {
            $dsn = rtrim($dsn, ';') . ';dbname=' . $settings['database'];
        }
    }
    $pdo=new PDO($dsn,$settings['user'] ?? $base['user'],$settings['password'] ?? $base['password'],[
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES=>false,
    ]);
    $pdo->exec("SET time_zone = '-03:00'");
    return $pdo;
}
function produtores_query(string $sql,array $params=[]): PDOStatement {
    $stmt=produtores_db()->prepare($sql); $stmt->execute($params); return $stmt;
}
function produtores_param(string $key,int $length=150): string {
    $value=$_GET[$key] ?? '';
    if (!is_string($value)) return '';
    preg_match('/^.{0,'.$length.'}/us',trim($value),$match);
    return $match[0] ?? '';
}
function produtores_filters(array $filters): array {
    $where=[]; $params=[];
    if ($filters['q']!=='') {
        // Escape explícito: %, _ e ! são tratados como texto digitado.
        $term='%'.strtr($filters['q'],['!'=>'!!','%'=>'!%','_'=>'!_']).'%';
        $where[]="(p.nome LIKE ? ESCAPE '!' OR p.registro_peaf LIKE ? ESCAPE '!' OR p.processamento LIKE ? ESCAPE '!' OR p.municipio LIKE ? ESCAPE '!')";
        array_push($params,$term,$term,$term,$term);
    }
    foreach(['municipio','classificacao','licenciamento'] as $key) {
        if ($filters[$key]!=='') { $where[]='p.'.$key.'=?'; $params[]=$filters[$key]; }
    }
    if ($filters['alertas']==='1') $where[]='p.alerta_fonte IS NOT NULL';
    return [$where?' WHERE '.implode(' AND ',$where):'', $params];
}
function produtores_date(?string $date): string {
    if (!$date) return 'Não informada';
    return date('d/m/Y',strtotime($date));
}
function produtores_class(string $value): string {
    return ['ORGÂNICO CERTIFICADO'=>'organico','ORGÂNICO NÃO CERTIFICADO'=>'nao-certificado','EM TRANSIÇÃO AGROECOLÓGICA'=>'transicao','EM CONVERSÃO ORGÂNICA'=>'transicao','PARALELA'=>'paralela'][$value] ?? 'convencional';
}
function produtores_unavailable(Throwable $error): void {
    error_log((string)$error); http_response_code(503);
    page_start('Produtores do RS','produtores');
    echo '<section class="panel"><h2>A base de produtores ainda não está disponível.</h2><p>Importe <strong>database/produtores-rs.sql</strong> no phpMyAdmin e confira <strong>app/produtores-config.php</strong>.</p><p>O módulo utiliza o banco separado <strong>agrolink_produtores</strong>.</p><a class="button secondary" href="index.php">Voltar à visão geral</a></section>';
    page_end(); exit;
}
