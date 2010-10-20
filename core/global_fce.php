<?php
function __autoload($class_name) {
    if(!class_exists($class_name, false)){
    	require_once ($class_name.".php");
    }
	//if(!requireFile("core/class/".$class_name.".php")) { 
}

function requireFile($file) {
	if(@require_once(ROOT_PATH.$file)){
		return true;
	}
	return false;
}

function getUrl($string="") {
	$string = (get_magic_quotes_gpc() ? $string : addslashes($string));
	if(@is_file(ROOT_PATH.$string)) {
		return HTTP_PATH.$string;
	}
	return ($string == "") ? HTTP_PATH.$string : HTTP_PATH.$string."/";
}

function getImgUrl($img_file = "") {
	$string = (get_magic_quotes_gpc() ? $img_file : addslashes($img_file));
	if(is_file(ROOT_PATH."pages/tpl/img/".$string)) {
		return HTTP_PATH."pages/tpl/img/".$string;
	}
	return ($string == "") ? HTTP_PATH."pages/tpl/img" : "";
}

/**
 * Returns the located string if exists, otherwise returns that value in brackets
 * @param $string - string to look for
 * @param $template - name of template which is $string set for in lng/Lng.php;if not set, the string will be searched in core location 
 */
function getString($string,$template=null) {
	$class = ucfirst(cCfg::$lng);
	$file = "lng/".$class.".php";
	
	if(!is_file(ROOT_PATH.$file)){
		return '[['.$string.']]';
	}
	require_once(ROOT_PATH.$file);
	$oLng = new $class;
	
	/// template wasn't set => result will be searched in core var ///
	if(is_null($template) || $template == 'core'){
		$location = $oLng->core;
	}else{	
		/// array location(template) doesn't exist ///
		if(!isset($oLng->pages[$template]) || !is_array($oLng->pages[$template])){
			return $string;
		}
		$location = $oLng->pages[$template];
	}
	/// looking for string in array location ///
	if(isset($location[$string]) && strlen($location[$string])) {
		return $location[$string];
	}
	/// string wasn't found ///
	return $string;
}

/**
 * parses corect form of url from string $title; removes or convert all 'bad' symbols
 * @param string $title
 * @return string
 */
function parseCorectUrl($title) {
    static $convertTable = array (
        'á' => 'a', 'Á' => 'A', 'ä' => 'a', 'Ä' => 'A', 'č' => 'c',
        'Č' => 'C', 'ď' => 'd', 'Ď' => 'D', 'é' => 'e', 'É' => 'E',
        'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'í' => 'i',
        'Í' => 'I', 'ï' => 'i', 'Ï' => 'I', 'ľ' => 'l', 'Ľ' => 'L',
        'ĺ' => 'l', 'Ĺ' => 'L', 'ň' => 'n', 'Ň' => 'N', 'ń' => 'n',
        'Ń' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ö' => 'o', 'Ö' => 'O',
        'ř' => 'r', 'Ř' => 'R', 'ŕ' => 'r', 'Ŕ' => 'R', 'š' => 's',
        'Š' => 'S', 'ś' => 's', 'Ś' => 'S', 'ť' => 't', 'Ť' => 'T',
        'ú' => 'u', 'Ú' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ü' => 'u',
        'Ü' => 'U', 'ý' => 'y', 'Ý' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y',
        'ž' => 'z', 'Ž' => 'Z', 'ź' => 'z', 'Ź' => 'Z',
    );
    $title = strtolower(strtr($title, $convertTable));
    $title = preg_replace('/[^a-zA-Z0-9]+/u', '-', $title);
    $title = str_replace('--', '-', $title);
    $title = trim($title, '-');
    return $title;
}

/**
 * covert timestamp from DB to format to print out
 * @param string $date - format of timestamp in DB (2010-07-06 18:11:48) 
 * @param string $sDparts[optional] - pattern how to convert the date; <br>
 * 			default depends on LNG and fce getDateToPrint()
 * @return string - formated value by fce date()
 */
function getDateToPrintFromDb($date,$sDparts = "") {
	$date = getMktimeCustom($date);  
  	return getDateToPrint($date,$sDparts);
}

/**
 * convert time to correct format for DB ('Y-m-d H:i:s') 
 * @param timestamp $time[optional][default mktime()]
 * @return string - formated value by fce date()
 */
function getDateToDb($time=false) {
	if(!$time)
		$time = mktime();
  	return date('Y-m-d H:i:s', $time);
}

/**
 * make a timestamp by fce mktime() from 
 * @param string $_timestamp[optional] - default 'null'
 * @return timestamp
 */
function getMktimeCustom($_timestamp = null) { 
	if($_timestamp){ 
    	$_split_datehour = explode(' ',$_timestamp); 
        $_split_data = explode("-", $_split_datehour[0]); 
        $_split_hour = explode(":", $_split_datehour[1]); 
		
        return mktime($_split_hour[0], $_split_hour[1], $_split_hour[2], $_split_data[1], $_split_data[2], $_split_data[0]); 
    }
    return mktime(); 
} 

/**
 * function transform string according to sFormat (e.g.: "n.j.Y")
 * @param $sDate - string of date (1.8.2010)
 * @param $sFormat - format for convert (n.j.Y)
 * @return timestamp
 */
function getStringToTime($sDate,$sFormat) {
	$aDate = preg_split('/(\.|-|,|\s|:)/',$sDate);
	$aFormat = preg_split('/(\.|-|,|\s|:)/',$sFormat);
	//var_dump($aDate);
	//echo("<br>");
	//var_dump($aFormat);
	//echo("<br>");
	$dateFormats = array(
		'H' => array('G','H','g','h'),					//hour
		'i' => array('i'),								//minute
		's' => array('s'),								//seconds
		'n' => array('f','m','M','n','t'),				//month
		'j' => array('d','D','j','l','N','S','w','z'), 	//day 
		'Y' => array('L','o','y','Y'),					//year
	);
	$aMk = array("H"=>0,"i"=>0,"s"=>0,'n'=>0,'j'=>0,'Y'=>0);
	foreach($aFormat as $k=>$format){
		if(($key = arraySearch($format,$dateFormats)) !== false){
			$aMk[$key] = $aDate[$k];
		}
	}
	return mktime($aMk['H'], $aMk['i'], $aMk['s'], $aMk['n'], $aMk['j'], $aMk['Y']);
}
/**
 * convert a timestamp to format to print out
 * @param timestamp $date
 * @param string $sDparts[optional] - pattern how to convert the date
 * @return string - formated value by fce date()
 */
function getDateToPrint($date,$sDparts = "") {	
	if($sDparts == "" && cCfg::$lng == "cs"){
		$sDparts = "d.m.Y H:i:s";
	}else if($sDparts == "") {
		$sDparts = "m-d-Y H:i:s";
	}
  	return date($sDparts, $date);
}

function arraySearch($needle,$haystack,$arraykey=false)
{
  if(!is_array($haystack))
    return false;
    
  foreach($haystack as $key=>$value) {
      $current_key=$key;
  
      if($arraykey){
          if($needle == $value[$arraykey]){
            return $key;
          }
  
          if(arraySearch($needle,$value[$arraykey]) == true) {
            return $current_key;
          }            
      }else{            
          if($needle == $value){
            return $value;
          }
          
          if(arraySearch($needle,$value) == true) {
              return $current_key;
          }            
      }
  }
  return false;
}

/**
 * Add quotes to HTML characters
 *
 * Returns $var with HTML characters (like "<", ">", etc.) properly quoted.
 * This function is very similar to {@link p()}
 *
 * @param string $var the string potentially containing HTML characters
 * @param boolean $strip to decide if we want to strip slashes or no. Default to false.
 *                true should be used to print data from forms and false for data from DB.
 * @return string
 */
function format_string($var, $strip=false) {

    if ($var === '0' or $var === false or $var === 0) {
        return '0';
    }

    if ($strip) {
        return preg_replace("/&amp;(#\d+);/i", "&$1;", htmlspecialchars(stripslashes_safe($var)));
    } else {
        return preg_replace("/&amp;(#\d+);/i", "&$1;", htmlspecialchars($var));
    }
}

/**
 * Moodle replacement for php stripslashes() function,
 * works also for objects and arrays.
 *
 * The standard php stripslashes() removes ALL backslashes
 * even from strings - so  C:\temp becomes C:temp - this isn't good.
 * This function should work as a fairly safe replacement
 * to be called on quoted AND unquoted strings (to be sure)
 *
 * @param mixed something to remove unsafe slashes from
 * @return mixed
 */
function stripslashes_safe($mixed) {
    // there is no need to remove slashes from int, float and bool types
    if (empty($mixed)) {
        //nothing to do...
    } else if (is_string($mixed)) {
        if (ini_get_bool('magic_quotes_sybase')) { //only unescape single quotes TODO
            $mixed = str_replace("''", "'", $mixed);
        } else { //the rest, simple and double quotes and backslashes
            $mixed = str_replace("\\'", "'", $mixed);
            $mixed = str_replace('\\"', '"', $mixed);
            $mixed = str_replace('\\\\', '\\', $mixed);
        }
    } else if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = stripslashes_safe($value);
        }
    } else if (is_object($mixed)) {
        $vars = get_object_vars($mixed);
        foreach ($vars as $key => $value) {
            $mixed->$key = stripslashes_safe($value);
        }
    }

    return $mixed;
}

function microtimeFloat()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
?>