<?php
/**
 * Created by PhpStorm.
 * User: vallefor
 * Date: 14.08.14
 * Time: 0:43
 */

require_once $_SERVER["DOCUMENT_ROOT"]."/hc_curl.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/db_connect.php";

class autoElectric
{
	private $db;
	private $lastViewState='';

	function __construct()
	{
		$this->db=new db();
	}

	/**
	 * cURL врапер для работы с сайтом мосэнергосбыта
	 * @param $url
	 * @param bool $postData
	 * @param array $addHeaders
	 * @return mixed
	 */
	function execCurl($url,$postData=false,$addHeaders=array())
	{
		$headers=array(
			"Referer: https://lkkbyt.mosenergosbyt.ru/common/login.xhtml",
			"Connection: keep-alive",
			"Cache-Control: max-age=0",
			"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
			"Origin: https://lkkbyt.mosenergosbyt.ru",
			"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/34.0.1847.116 Chrome/34.0.1847.116 Safari/537.36",
//			"Content-Type: application/x-www-form-urlencoded",
			"Referer: https://lkkbyt.mosenergosbyt.ru/common/login.xhtml",
			"Accept-Encoding: gzip,deflate,sdch",
			"Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4"
		);
		foreach($addHeaders as $val)
			$headers[]=$val;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_ENCODING ,"utf-8");
		//curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		//curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
		curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
		curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		if($postData)
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		}

		$result=curl_exec($ch);
		$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code


		curl_close($ch);
		//echo $status_code;
		$this->parseViewState($result);
		return $result;
		//echo $status_code.":".$result;
	}

	/**
	 * Шаманство
	 * @param $str
	 */
	function parseViewState($str)
	{
		preg_match_all('#javax\.faces\.ViewState" value="(.*)"#Usi',$str,$matches2);
		if($matches2[1][0])
			$this->lastViewState=$matches2[1][0];
		else
		{
			preg_match_all('#\<update id=\"javax\.faces\.ViewState\"\>\<\!\[CDATA\[(.*)\]\]\>\<\/update\>#Usi',$str,$matches);
			if($matches[1][0])
				$this->lastViewState=$matches[1][0];
		}
	}

	/**
	 * метод логинится в ЛК мосэнергосбыта
	 * @param $login
	 * @param $password
	 */
	function lkLogin($login,$password)
	{
		//name="lb_login:f_login:rnd" value="c06baf015fbcee926f9b9b3fdd7dff5f" />
		$ret=$this->execCurl('https://lkkbyt.mosenergosbyt.ru/common/login.xhtml');
		preg_match_all('#f_login:rnd" value="(.*)"#Usi',$ret,$matches);


		/*print_r($matches2);
		die();*/
		$post=array(
			'lb_login:f_login:rnd'=>$matches[1][0], //md5(mt_rand(0,99999999)),
			'lb_login:f_login:t_login'=>$login,
			'lb_login:f_login:t_pwd'=>$password,
			'lb_login:f_login_SUBMIT'=>1,
			'javax.faces.ViewState'=>$this->lastViewState,
			'lb_login:f_login:_idcl'=>'lb_login:f_login:l_submit',
		);

		$post="lb_login%3Af_login%3Arnd=".urlencode($post['lb_login:f_login:rnd']).
		"&lb_login%3Af_login%3At_login=".urlencode($post['lb_login:f_login:t_login']).
		"&lb_login%3Af_login%3At_pwd=".urlencode($post['lb_login:f_login:t_pwd'])."&lb_login%3Af_login_SUBMIT=1".
		"&javax.faces.ViewState=".urlencode($post['javax.faces.ViewState'])."&lb_login%3Af_login%3A_idcl=lb_login%3Af_login%3Al_submit";
		//print_r($post);

		//sleep(1);

		//$ret=$this->execCurl('http://192.168.1.103/test_post.php',$post);
		$ret=$this->execCurl('https://lkkbyt.mosenergosbyt.ru/common/login.xhtml',$post);

		$ret=$this->execCurl('https://lkkbyt.mosenergosbyt.ru/abonent/index.xhtml');

		//echo $ret;

	}

	/**
	 * Метод передает показатели в мосэнергосбыт
	 * @param $t1
	 * @param $t2
	 * @param $t3
	 * @param bool $accept
	 * @return bool|float
	 */
	function putValues($t1,$t2,$t3,$accept=false)
	{
		$t1=intval($t1);
		$t2=intval($t2);
		$t3=intval($t3);

		$post="javax.faces.partial.ajax=true&javax.faces.source=f_transfer%3Acm_transf&javax.faces.partial.execute=%40all&f_transfer%3Acm_transf=f_transfer%3Acm_transf".
		"&f_transfer%3Avl_t1={$t1}".
		"&f_transfer%3Avl_t2={$t2}".
		"&f_transfer%3Avl_t3={$t3}".
		"&f_transfer_SUBMIT=1".
		"&javax.faces.ViewState=".urlencode($this->lastViewState);


//partial/ajax
		/*
		$post="javax.faces.partial.ajax=true&javax.faces.source=f_wiz%3Aw_wiz&javax.faces.partial.execute=f_wiz%3Aw_wiz&javax.faces.partial.render=f_wiz%3Aw_wiz&f_wiz%3Aw_wiz=f_wiz%3Aw_wiz&f_wiz%3Aw_wiz_wizardRequest=true&f_wiz%3Aw_wiz_stepToGo=pgConfirm".
		"&f_wiz%3Aj_id_7q_2_g={$t1}".
		"&f_wiz%3Aj_id_7q_2_m={$t2}".
		"&f_wiz%3Aj_id_7q_2_s={$t3}".
		"&f_wiz_SUBMIT=1&javax.faces.ViewState=".urlencode($this->lastViewState);
		*/

		//echo $post;
		$ret=$this->execCurl('https://lkkbyt.mosenergosbyt.ru/abonent/index.xhtml',$post, array(
			"Faces-Request: partial/ajax",
			"X-Requested-With: XMLHttpRequest"
		));

		preg_match_all('#Сумма начислений по этим показаниям составляет <b>(.*)</b>#Usi',$ret,$matches);
		$rub=floatval(str_replace(",",".",$matches[1][0]));
		if(!$accept)
		{
			return $rub;
		}
		else
		{

			preg_match_all('#action="(.*)"#Usi',$ret,$matches);

			$url="https://lkkbyt.mosenergosbyt.ru".$matches[1][0];
			$post="javax.faces.partial.ajax=true&javax.faces.source=f_wiz%3Aw_wiz&javax.faces.partial.execute=f_wiz%3Aw_wiz&javax.faces.partial.render=f_wiz%3Aw_wiz&f_wiz%3Aw_wiz=f_wiz%3Aw_wiz&f_wiz%3Aw_wiz_wizardRequest=true&f_wiz%3Aw_wiz_stepToGo=pgFinished&f_wiz_SUBMIT=1".
			"&javax.faces.ViewState=".urlencode($this->lastViewState);

			//echo $url."\n\n";
			//echo $post."\n\n";

			$ret=$this->execCurl($url,$post, array(
				"Faces-Request: partial/ajax",
				"X-Requested-With: XMLHttpRequest"
			));

			if(strpos($ret,"Показания вашего счетчика переданы"))
				return true;
		}

		//XrfEb88+WLMIhFpmRbj+U2XwV+FNdqO1h+UlE4dpGxLR/Qme]]></update>

		//print_r($ret);
	}

	/**
	 * Метод передает показатели в мосэнергосбыт, но не завершает последнее действие, а возвращает только сумму, которую вернул мосэнергосбыт
	 * @return bool|float
	 */
	function getDebtByCurValues()
	{
		$cur=$this->getCurMonthValues();;
		return $this->putValues($cur["t1"],$cur["t2"],$cur["t3"]);
	}

	/**
	 * Метод передает показатели в мосэнергосбыт и стартует следующий месяц
	 * @return bool
	 */
	function sendCurValuesAndStartNextMonth()
	{
		$cur=$this->getCurMonthValues();;
		$ret=$this->putValues($cur["t1"],$cur["t2"],$cur["t3"],true);
		$ret=true;
		if($ret)
		{
			$this->finishCurMonth();
			$this->createNextMonth();
			return true;
		}
		return false;
	}

	/**
	 * Метод для виртуального устройства в HC2, возвращает все текущие показатели
	 * @return array
	 */
	function getLastValues()
	{
		$this->db->query("SELECT * FROM `electric_meter` ORDER BY `id` DESC LIMIT 0, 2");
		$iReturn=array();
		while($this->db->fetch())
			$iReturn[]=$this->db->answer;

		$start=new DateTime($iReturn[1]["date"]." ".$iReturn[1]["time"]);
		$end=new DateTime($iReturn[0]["date"]." ".$iReturn[0]["time"]);

		$dif=$end->format("U")-$start->format("U");
		$valDif=(floatval($iReturn[0]["value"])-floatval($iReturn[1]["value"]));
		if($dif)
			$now=(3600/$dif)*$valDif;
		else
			$now=0;

		$cmv=$this->getCurMonthValues();
		$tarif=$this->getTarifs();
		$money=round($cmv["cur_t1"]*$tarif["t1"]+$cmv["cur_t2"]*$tarif["t2"]+$cmv["cur_t3"]*$tarif["t3"],2);

		$iReturn=array(
			"summ"=>round(floatval($iReturn[0]["value"]),2),
			"current"=>round($now*1000,1),
			"diff"=>$valDif,
			"t1"=>round($cmv["cur_t1"],2)." / ".round($cmv["t1"],2),
			"t2"=>round($cmv["cur_t2"],2)." / ".round($cmv["t2"],2),
			"t3"=>round($cmv["cur_t3"],2)." / ".round($cmv["t3"],2),
			"money"=>$money,
			"last_updated"=>$end->format("Y-m-d H:i:s")
		);
		return $iReturn;
	}

	/**
	 * Метод возвращает текущую стоимость электричества (по тарифам)
	 * @return mixed
	 */
	function getTarifs()
	{
		$this->db->query("SELECT * FROM `electric_tarifs` ORDER BY `id` DESC LIMIT 0,1");
		$this->db->fetch();
		return $this->db->answer;
	}

	/**
	 * Метод принимает значение о текущих показателях расхода электричества (сюда попадают данные из Z-way который их берет из NorthQ)
	 * @param $value
	 */
	function pushValue($value)
	{
		$dt=new DateTime();
		$this->db->insert(array("date"=>$dt->format("Y-m-d"),"time"=>$dt->format("H:i:s"),"date_time"=>$dt->format("Y-m-d H:i:s"),"value"=>floatval($value)),"electric_meter");
		$last=$this->getLastValues();
		$this->addToTarif($last["diff"],$dt->format("Y-m-d H:i:s"));
		execCurl("callAction?deviceID=187&name=pressButton&arg1=8");
	}

	/**
	 * Метод прибовляет расход электричества к тарифу, в зависимости от времени
	 * @param $diff
	 * @param $date
	 */
	function addToTarif($diff,$date)
	{
		$column=$this->getTarifByDate($date);
		$cur=$this->getCurMonthValues();
		$cur[$column]+=$diff;
		$cur["cur_".$column]+=$diff;
		unset($cur["to"]);
		$this->updateMonthValues($cur["id"],$cur);
	}

	/**
	 * Метод для обновления показателей текущего платежного периода
	 * @param $id
	 * @param $arr
	 */
	function updateMonthValues($id,$arr)
	{
		$w=$this->db->makeWhere(array("id"=>$id));
		$this->db->update($arr,"electric_month",$w);
	}

	/**
	 * метод получает состояние текущего платежного периода
	 * @return mixed
	 */
	function getCurMonthValues()
	{
		$w=$this->db->makeWhere(array("to"=>"NULL"));
		$this->db->query("SELECT * FROM `electric_month` $w ORDER BY `id` DESC LIMIT 0,1");
		if(!$this->db->fetch())
		{
			$this->createNextMonth();
			$this->db->query("SELECT * FROM `electric_month` $w ORDER BY `id` DESC LIMIT 0,1");
			$this->db->fetch();
		}
		return $this->db->answer;
	}

	/**
	 * Метод заверает текущий платежный период
	 */
	function finishCurMonth()
	{
		$this->db->query("SELECT * FROM `electric_month` ORDER BY `id` DESC LIMIT 0,1");
		$this->db->fetch();
		$ret=$this->db->answer;
		$this->updateMonthValues($ret["id"],array("to"=>date("H-m-d H:i:s")));
	}

	/**
	 * Метод стартует новый платежный период
	 */
	function createNextMonth()
	{
		$this->db->query("SELECT * FROM `electric_month` ORDER BY `id` DESC LIMIT 0,1");
		$this->db->fetch();
		$this->db->insert(array(
			"from"=>$this->db->answer["to"],
			"t1"=>$this->db->answer["t1"],
			"t2"=>$this->db->answer["t2"],
			"t3"=>$this->db->answer["t3"],
			"cur_t1"=>0,
			"cur_t2"=>0,
			"cur_t3"=>0,
		),"electric_month");
	}

	/**
	 * Метод возвращает ккод тарифа t1, t2 или t3, в зависимости от времени
	 * @param $date
	 * @return int|string
	 */
	function getTarifByDate($date)
	{
		$curDate=new DateTime($date);
		$date=$curDate->format("Y-m-d");

		$tarifs=array(
			"t1"=>array(
				array(
					new DateTime($date." 07:00:00"),
					new DateTime($date." 10:00:00"),
				),
				array(
					new DateTime($date." 17:00:00"),
					new DateTime($date." 21:00:00"),
				),
			),
			"t2"=>array(
				array(
					new DateTime($date." 23:00:00"),
					new DateTime($date." 23:59:59"),
				),
				array(
					new DateTime($date." 00:00:00"),
					new DateTime($date." 07:00:00"),
				),
			),
			"t3"=>array(
				array(
					new DateTime($date." 10:00:00"),
					new DateTime($date." 17:00:00"),
				),
				array(
					new DateTime($date." 21:00:00"),
					new DateTime($date." 23:00:00"),
				),
			)
		);

		foreach($tarifs as $tarif=>$groups)
		{
			foreach($groups as $val)
			{
				if($curDate>=$val[0] && $curDate<=$val[1])
					return $tarif;
			}
		}
	}

}
?>