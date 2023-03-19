<?php
/**
 * Esta classe pode instanciar objetos que tem funcionalidade de realizar operações SQL no banco de dados principal da aplicação.
 */

namespace App\Database;

use Eliaslazcano\Helpers\HttpHelper;

class DbApp extends DatabaseController
{
  //Configuração
  protected $host = 'mariadb';
  protected $usuario = 'root';
  protected $senha = '501zinh0';
  protected $base_de_dados = 'komprinhas';
  protected $timezone = '-04:00';

  protected function aoFalhar(string $mensagem, string $dadosLog = null)
  {
    if ($mensagem) HttpHelper::gravarLog($dadosLog ? ("$mensagem|$dadosLog") : $mensagem, realpath(__DIR__.'/..'), 'DbErrors.log');
    HttpHelper::erroJson(507, "Ocorreu uma falha na base de dados: $mensagem");
  }
}
