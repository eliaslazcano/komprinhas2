<?php
/**
 * ENTRY POINT: Este arquivo é executado antes de qualquer outro script, centralizando os comandos pré-API.
 * @author Elias Lazcano Castro Neto
 * @copyright Elias Lazcano Castro Neto
 * @version 2023-01-23
 */

use Eliaslazcano\Helpers\HttpHelper;

require_once __DIR__ . '/vendor/autoload.php';

date_default_timezone_set('America/Campo_Grande');
HttpHelper::setAllowOrigin('*');
HttpHelper::useRouter(realpath(__DIR__.'/public'), realpath(__DIR__.'/errors.log'));
