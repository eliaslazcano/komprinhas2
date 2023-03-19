<?php

use Eliaslazcano\Helpers\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarGet();
$datainicio = HttpHelper::validarParametro('datainicio');
$datafim = HttpHelper::validarParametro('datafim');

$db = new DbApp();
$sql = <<<TXT
SELECT c.id, c.mercado, SUM(ci.preco * ci.quantidade) AS total, c.criado_em
FROM carrinhos c
    INNER JOIN mercados m ON c.mercado = m.id
    LEFT JOIN carrinho_itens ci ON c.id = ci.carrinho
WHERE c.criado_em BETWEEN :datainicio AND :datafim
GROUP BY c.id
TXT;
$carrinhos = $db->query($sql, [':datainicio' => "$datainicio 00:00:00", ':datafim' => "$datafim 23:59:59"], ['id','total']);

HttpHelper::emitirJson(['carrinhos' => $carrinhos]);
