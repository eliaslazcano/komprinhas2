<?php
/**
 * API basica para operacoes de CRUD.
 * GET: Fornece todos os dados da tabela em array conforme o nome informado no parametro 'table'.
 *      Se informar o parametro 'id' ele filtrará e retornará diretamente o registro encontrado ao invés de array.
 * POST: Insere um novo registro, o parametro 'table' indica o nome da tabela, o parametro 'data' é um objeto com chave = nome da coluna, valor = valor da coluna.
 * PUT: Atualiza um registro, igual ao comportamento do POST mas precisa informar o ID.
 * DELETE: Remove um registro conforme o parametro 'id' e 'table' informado, se a tabela possuir a coluna 'deletado_em' ela será alimentada com a datahora atual.
 */

use Eliaslazcano\Helpers\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos();

$db = new DbApp();

if (HttpHelper::isGet()) {
  $table = HttpHelper::validarParametro('table');
  $id    = (int)HttpHelper::obterParametro('id');

  $tables = $db->tabelas();
  if (!in_array($table, $tables)) HttpHelper::erroJson(400, "A tabela `$table` não existe no banco");
  $colunas_info = $db->query("DESCRIBE $table");
  $colunas_numericas = array_column(array_filter($colunas_info, function ($i) {
    return strpos($i['Type'], 'int(') !== false || strpos($i['Type'], 'decimal(') !== false;
  }), 'Field');

  if ($id) {
    $sql = "SELECT * FROM $table WHERE id = :id";
    $resultado = $db->queryPrimeiraLinha($sql, [':id' => $id], $colunas_numericas);
  } else {
    $sql = "SELECT * FROM $table";
    $resultado = $db->query($sql, [], $colunas_numericas);
  }
  HttpHelper::emitirJson($resultado);
}

if (HttpHelper::isPost()) {
  $table = HttpHelper::validarParametro('table');
  $data =  HttpHelper::validarParametro('data');
  if (!empty($data['id'])) HttpHelper::erroJson(400, 'Novos registros não podem definir o ID');

  $tables = $db->tabelas();
  if (!in_array($table, $tables)) HttpHelper::erroJson(400, "A tabela `$table` não existe no banco");

  $colunas = array_keys($data);
  $colunasStr = implode(', ', $colunas);
  $params = [];
  foreach ($colunas as $coluna) $params[":$coluna"] = $data[$coluna];
  $paramsStr = implode(', ', array_keys($params));
  $sql = "INSERT INTO $table ($colunasStr) VALUES ($paramsStr)";
  $id = (int)$db->insert($sql, $params);
  HttpHelper::emitirJson(['id' => $id]);
}

if (HttpHelper::isPut()) {
  $table = HttpHelper::validarParametro('table');
  $data =  HttpHelper::validarParametro('data');

  if (empty($data['id'])) HttpHelper::erroJson(400, 'ID não informado');
  $id = $data['id'];
  unset($data['id']);

  $tables = $db->tabelas();
  if (!in_array($table, $tables)) HttpHelper::erroJson(400, "A tabela `$table` não existe no banco");

  $colunas = array_keys($data);
  $params = [];
  foreach ($colunas as $coluna) $params[":$coluna"] = $data[$coluna];
  $params[':id'] = $id;
  $colunas = array_map(function ($i) { return "$i = :$i"; }, $colunas);
  $colunas = implode(', ', $colunas);

  $sql = "UPDATE $table SET $colunas WHERE id = :id";
  $i = $db->update($sql, $params);
  HttpHelper::emitirJson(['sucesso' => $i > 0, 'sql' => $sql]);
}

if (HttpHelper::isDelete()) {
  $id = (int)HttpHelper::validarParametro('id');
  $table = HttpHelper::validarParametro('table');

  $tables = $db->tabelas();
  if (!in_array($table, $tables)) HttpHelper::erroJson(400, "A tabela `$table` não existe no banco");

  $colunas_info = $db->query("DESCRIBE $table");
  $coluna_deletado = array_filter($colunas_info, function ($i) {
    return $i['Field'] === 'deletado_em';
  });
  $coluna_deletado = count($coluna_deletado) > 0 ? (array_values($coluna_deletado))[0]['Field'] : null;

  if ($coluna_deletado) {
    $sql = "UPDATE $table SET $coluna_deletado = CURRENT_TIMESTAMP WHERE id = :id";
  } else {
    $sql = "DELETE FROM $table WHERE id = :id";
  }
  $i = $db->update($sql, [':id' => $id]);
  HttpHelper::emitirJson(['sucesso' => $i > 0]);
}
