<?php
/**
 * Created by PhpStorm.
 * User: vallefor
 * Date: 12.08.14
 * Time: 21:16
 */
require_once $_SERVER["DOCUMENT_ROOT"]."/config.php";
//require_once $_SERVER["DOCUMENT_ROOT"]."/db_connect.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/hc_curl.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/class/autoElectric.php";
$ae=new autoElectric();
$db=new db();

$login=ME_LOGIN;
$password=ME_PASS;

/**
 * API для записи данных с Малины и получение/отправления данных виртуальным устройством HC2
 */

switch($_GET["c"])
{
	case "getDebt":
		$ae->lkLogin($login,$password);
		$debt=$ae->getDebtByCurValues();
		die(json_encode(array("debt"=>$debt?$debt:'Ошибка')));
	break;
	case "sendValues":
		$ae->lkLogin($login,$password);
		$ret=$ae->sendCurValuesAndStartNextMonth();
		if($ret)
			die(json_encode(array("status"=>"Готово!")));
		else
			die(json_encode(array("status"=>"Ошибка")));
	break;
	case "get":
		die(json_encode($ae->getLastValues()));
	break;
	default:
		$ae->pushValue($_GET["value"]);
	break;
}
?>