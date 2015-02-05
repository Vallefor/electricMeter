<?php 
class db
{
	var $res;
	var $answer;
	var $p;
	var $type_go;
	var $error;
	var $rows;

	private $connect=false;



	private $namesWas=false;
	private $d;
	function __destruct()
	{
		//@$this->res->free_result();
		if($this->connect)
			$this->d->close();
	}
	function __construct()
	{

		$this->d=new mysqli(DB_HOST,DB_LOGIN,DB_PASSWORD,DB_BASE);
		$this->setNames();
	}
	function connect()
	{
		$this->connect=true;
		$this->d->connect();
	}
	function close()
	{
		if($this->connect)
		{
			$this->connect=false;
			$this->d->close();
		}
	}
	function db()
	{
		/*
		mysql_connect(db::host,db::username,db::password,false,128) or die(mysql_error());
		mysql_select_db(db::database) or die(mysql_error());
		*/
	}
	function setNames()
	{
		$this->d->set_charset("utf8");
		/*global $SET_NAMES;
		if($SET_NAMES=="")
		{
			$this->d->set_charset("utf8");
			//$this->query("SET NAMES 'UTF8'");
			$SET_NAMES="UTF8";
		}*/
	}
	function real_escape_string($str)
	{
		return $this->d->real_escape_string($str);
	}
	function makeSelect($arr)
	{
		if($arr=="*")
			return $arr;
		$str="";
		foreach($arr as $ind=>$val)
		{
			$add="";
			if(is_int($ind))
			{
				if(strpos($val,"`")!==false)
					$add=$val;
				else
					$add="`".$val."`";
			}
			else
			{
				if(strpos($ind,"`")!==false)
					$add=$ind;
				else
					$add="`".$ind."`";
				if(strpos($val,"`")!==false)
					$add.=" AS ".$val;
				else
					$add.=" AS `".$val."`";
			}
			if($str!="")
				$str.=", ".$add;
			else
				$str=$add;
		}
		return $str;
	}
	function makeWhereV2($filter)
	{
		$str="";
		if(count($filter))
		{
			foreach($filter as $val)
			{
				if(is_array($val["val"]))
				{
					$str.=$this->makeWhereV2($val["val"]);
				}
				else
				{
					$str.=" ".$val["type"]." ";
					if($val["open"])
						$str.="(";
					$str.="{$val["field"]} {$val["znak"]} ".((isset($val["val"]) && $val["val"]!='NULL')?("'".$this->real_escape_string($val["val"])."'"):($val["val"]=='NULL'?'NULL':''));
					if($val["close"])
						$str.=")";
				}
			}
		}
		return $str;
	}
	function makeWhere($filter,$cat="",$fliper=array())
	{
		//if(!$this->d) $this->__construct();
		if(is_array($filter) && count($filter)==0) return $cat;
		if(!is_array($filter))
		{
			if($cat=="") $cat.=" WHERE ".$filter;
				else $cat.=" ".$filter;
			return $cat;
		}
		else
		{
			/*$numeric=true;
			foreach($filter as $ind)
				if(!is_numeric($ind))
					$numeric=false;*/
			$fArr=array();
			$globalMode="AND";
			if(is_array($filter[0]) || is_array($filter["FILTER_ARR"][0]))
			{
				if($filter["FILTER_MODE"])
				{
					$globalMode=$filter["FILTER_MODE"];
					$fArr=$filter["FILTER_ARR"];
				}
				else
					$fArr=$filter;
			}
			else
			{
				$fArr[]=$filter;
			}


			$start_cat=$cat;
			$opened=false;
			foreach($fArr as $index=>$filter)
			{
				$tmpCat="";
				$mode='';
				if(isset($filter["FILTER_MODE"]) && is_array($filter)/* $filter["FILTER_MODE"]!=""*/)
				{
					/*echo "[top:$index]";
					print_r($filter);*/
					$mode=$filter["FILTER_MODE"];
					$filter=$filter["FILTER_ARR"];
				}
				elseif(!is_array($filter) || (is_array($filter) && isset($filter[0])) ){
					/*echo "[bottom:$index]";
					print_r($filter);*/
					$filter=array($index=>$filter);
					$mode='';
					//$tmpSummCat.=" ) ";
				}

				if($mode=="") $mode="AND";
				if (count($filter)>0)
				{
					$i=0;
					foreach ($filter as $ind=>$val)
					{
						if(!is_numeric($index) && is_numeric($ind))
							$ind=$index;

						$i++;
						$len=0;
						switch ($ind{0})
						{
							case ">":
								$si=$ind{0};
								$len++;
								if ($ind{1}=="=") { $si.=$ind{1}; $len++; }
							break;
							case "<":
								$si=$ind{0};
								$len++;
								if ($ind{1}=="=") { $si.=$ind{1}; $len++; }
							break;
							case "!":
								if($ind{1}!="~")
								{
									$si=$ind{0};
									$len++;
									if ($ind{1}=="=") { $si.=$ind{1}; $len++; }
								}
								else
								{
									$si=" NOT LIKE";
									$len+=2;
								}
							break;
							case "~":
								$si=" LIKE ";
								$len++;
							break;
							default:
								//$len++;
								$si="=";
							break;
						}
						$ind=substr($ind,$len);
						/*
						$ind=str_replace(">","",$ind);
						$ind=str_replace("<","",$ind);
						$ind=str_replace("=","",$ind);
						$ind=str_replace("!","",$ind);
						$ind=str_replace("~","",$ind);*/
						if(strpos($ind,"`")!==false) $kav="";
							else $kav="`";
		//				echo "<h2>[$kav]</h2>";
		//				ob_flush();
		//				die();
						if($fliper[$kav.$ind.$kav]!="") $item=$fliper[$kav.$ind.$kav];
							else $item=$kav.$ind.$kav;

						if($val==="NULL")
						{
							if ($tmpCat=="")
							{
								if($si=="=") $tmpCat=" $item IS NULL";
								else $tmpCat=" $item IS NOT NULL";
							}
							else
							{
								if($si=="=") $tmpCat.=" AND $item IS NULL";
								else $tmpCat.=" AND $item IS NOT NULL";
							}
						}
						else
						{
							$arrayWas=false;
							if(is_array($val))
							{
								$arrayWas=true;
								if(false/* && $val[0]['FILTER_MODE'] == 'AND' || $val[0]['FILTER_MODE'] == 'OR'*/)
								{
									print_r($val[0]);
									die("go! :(".$val[0]["FILTER_MODE"]);
									//print_r($val);
								}
								else
								{
									//print_r($val);
									$final="";
									$innerJoiner="OR";
									if($val["FILTER_MODE"] && $val["FILTER_ARR"])
									{
										$innerJoiner=$val["FILTER_MODE"];
										$val=$val["FILTER_ARR"];
									}

									foreach($val as $m)
									{
										if($m==="NULL") { $kav=""; $zn="IS"; }
										elseif($m{0}=='`')
											$kav="";
										else { $kav="'"; $zn=$si; }

										if($final=="") $final.=$item." ".$zn." $kav".$this->d->real_escape_string($m)."$kav ";
											else $final.=" $innerJoiner ".$item." ".$zn." $kav".$this->d->real_escape_string($m)."$kav";
										//echo "[".$final."]";
									}
									$val="(".$final.") ";
									//echo "[$val]";
									//$item="(".$item;
								}
							}
							else
							{
								$kav="'";
								if($val{0}=='`')
									$kav="";
								$val="$item $si $kav".$this->d->real_escape_string($val)."$kav";
								//echo "[$val]";
							}
							if($i==1) $mode_g="AND"; else $mode_g=$mode;
							/*if ($cat=="") $cat=" WHERE $item ".$si." ".$val."";
								else $cat.=" $mode_g $item ".$si." ".$val."";*/
							if ($tmpCat=="") $tmpCat="".$val."";
								else $tmpCat.=" ".$mode_g." ".$val."";
						}
					}
					//echo "[".$cat."]";
					/*if($tmpSummCat)
						$tmpSummCat.=" $mode ".$tmpCat."";
					else
						$tmpSummCat.=" ".$tmpCat."";*/
				}


				if(!$tmpSummCat)
					$tmpSummCat.=" ( ".$tmpCat." ) ";
				else
					$tmpSummCat.=" $globalMode ( ".$tmpCat." ) ";
			}
			/*$tmpSummCat.=($opened==true?" -)- ":"");*/

			if($cat=="")
				$cat.=" WHERE (".$tmpSummCat.")";
			else
				$cat.=" ".$globalMode." (".$tmpSummCat.")";

			if($start_cat!="" && !$GLOBALS["NOT_CAT"])
			{
				/*die($start_cat);*/
				$cat=str_replace(") AND",") AND (",$cat).")";
			}
			$cat=strtr($cat,$fliper);
			$cat=str_replace("`xxx_dynamic`.`xxx_dynamic`","`xxx_dynamic`",$cat);

			return $cat;
		}
	}
	function removeSlashes($arr)
	{
		if(get_magic_quotes_gpc())
		{
			foreach($arr as $ind=>$val)
			{
				$arr[$ind]=stripcslashes($val);
			}
		}
		return $arr;
	}
	function safeArr($arr)
	{
		foreach($arr as $ind=>$val)
		{
			$arr[$ind]=$this->d->real_escape_string($val);
		}
		return $arr;
	}
	/*function connect()
	{

		 mysql_connect(db::host,db::username,db::password,false,128) or die(mysql_error());
		 mysql_select_db(db::database) or die(mysql_error());
		 $this->query("SET NAMES 'UTF8'");
	}*/
	function insert($array,$table,$manual=false,$remove=true)
	{
		if($remove && get_magic_quotes_gpc())
		{
			$array=$this->removeSlashes($array);
			//$array=$this->removeSlashes($array);
		}
		$array=$this->safeArr($array);
		/*if (!$manual) $this->begin();
		$this->query("SHOW TABLE STATUS LIKE '".$table."'");
		$this->fetch();
		$ret=$this->answer["Auto_increment"];*/
		foreach($array as $ind=>$val)
		{
			if($val!="NULL")
			{
				if ($names!="") $names.=",`".$ind."`";
					else $names.="(`".$ind."`";
				if ($values!="") $values.=",'".$val."'";
					else $values.="('".$val."'"; 
			}
		}
		$names.=")";
		$values.=")";
		if($_GET["test"]=="test") echo "INSERT INTO `".$table."` ".$names." VALUES ".$values;
		$this->query("INSERT INTO `".$table."` ".$names." VALUES ".$values);
		/*if (!$manual) $this->commit();*/
		$ret=$this->d->insert_id;
		return $ret;
	}
	function update($array,$table,$where,$remove=true)
	{
		if($remove && get_magic_quotes_gpc())
		{
			$array=$this->removeSlashes($array);
			//$array=$this->removeSlashes($array);
		}
		$array=$this->safeArr($array);
		foreach($array as $ind=>$val)
		{
			if ($names!="")
			{
				if($val=="NULL") $names.=",`".$ind."`=NULL";
					else $names.=",`".$ind."`='".$val."'";
			}
			else
			{
				if($val=="NULL") $names.="`".$ind."`=NULL";
					else $names.="`".$ind."`='".$val."'";
			}
				/*
			if ($values!="") $values.=",'".$val."'";
				else $values.="('".$val."'";
				*/ 
		}
		//echo "UPDATE `".$table."` SET ".$names." ".$where;
		$this->query("UPDATE `".$table."` SET ".$names." ".$where);
	}
	function query($query)
	{
		//echo "<b>".$query."</b>";


		global $Q_SHOW, $Q_COUNT, $USER;
		$Q_SHOW[]=$query;
		//echo "<h2>$query</h2>";
		
		//echo $query."<br/>";
		//echo "<h5>".$query."</h5>";
		if (!$this->res=$this->d->query($query))
		{
			if ($this->type_go=="trans")
			{
				echo "[in!]";
				$this->error="<h4>".$query."</h4>".$this->d->error;
				$this->rollback();
				$this->type_go="casual";
				die();
			}
			else 
			{
				if(true || $USER && $USER->IsAdmin())
				{
					echo $query."  ".$this->d->error;
					ob_flush();
				}
				die("DB error");
			}
		}
		//echo "<p>".$query."</p>";
		$Q_COUNT++;
		@$this->rows=$this->d->affected_rows;
	}
	function fetch($res="")
	{
		if ($res=="") $res=$this->res;
		if (@$this->answer=$res->fetch_assoc())
		{
			return true;
		}
		else 
		{
			return false;			
		}
	}
	function begin()
	{
		$this->type_go="trans";
		//$this->d->stat();
		/*$this->query("SELECT @@autocommit;");
		$this->fetch();
		print_r($this->answer);*/
		$this->d->autocommit(false);
		/*$this->query("SELECT @@autocommit;");
		$this->fetch();
		print_r($this->answer);*/
		//mysql_query("BEGIN");
	}
	function commit()
	{
		//mysql_query("COMMIT");
		$this->d->commit();
		$this->type_go="casual";
		$this->d->autocommit(true);
		
	}
	function rollback($silence=false)
	{
		if($this->d->rollback())
		{
			if(!$silence)
				die("Transaction error: ".$this->error);
			$this->error="";
		}
		else
		{
			die("roll back fail!");
		}
	}
}
?>