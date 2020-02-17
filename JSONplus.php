<?php
if(!defined("JSONplus_DATALIST_ROOT")){define("JSONplus_DATALIST_ROOT", dirname(__FILE__).'/');}
if(!defined("JSONplus_FILE_ARGUMENT")){define("JSONplus_FILE_ARGUMENT", 'file');}
if(!defined("JSONplus_POST_ARGUMENT")){define("JSONplus_POST_ARGUMENT", 'json');}
class JSONplus{
	function __construct(){}
	function __toString(){ return '[]'; }
	static function get_datalist($datalist){
		return JSONplus::decode(JSONplus::open_datalist($datalist, '[]'), TRUE);
	}
	static function open_datalist($datalist, $errortype=FALSE){
		if(!file_exists(JSONplus_DATALIST_ROOT.(substr(JSONplus_DATALIST_ROOT, -1) == '/' ? NULL : '/').$datalist.'.json')){ return $errortype; }
		$str = file_get_contents(JSONplus_DATALIST_ROOT.(substr(JSONplus_DATALIST_ROOT, -1) == '/' ? NULL : '/').$datalist.'.json');
		return $str;
	}
	static function include_all_datalist($json){
		preg_match_all("#(\"[^\"]+\"\s*:\s*)?<datalist:([^>]+)>#i", $json, $buffer);
		foreach($buffer[0] as $i=>$match){
			if(strlen($buffer[1][$i]) >= 1){
				$json = str_replace($buffer[0][$i], $buffer[1][$i].JSONplus::open_datalist($buffer[2][$i], '[]'), $json);
			} else {
				$json = str_replace($buffer[0][$i], preg_replace("#^\s*[\{\[](.*)[\}\]]\s*$#i", "\\1", JSONplus::open_datalist($buffer[2][$i], NULL)), $json);
			}
		}
		return $json;
	}
	static function encode($value, $options=0, $depth=512){
		$str = json_encode($value, $options, $depth);
		//pretty print (human readable and support for GiT-version management)
		$str = JSONplus::prettyPrint($str);
		/*fix*/ $str = JSONplus::printfixes($str);
		return $str;
	}
	static function decode($json, $assoc=FALSE, $depth=512, $options=0){
		//proces <datalist:*> before return
		$json = JSONplus::include_all_datalist($json);
		if(isset($options) && !($options===0) ) return json_decode($json, $assoc, $depth, $options);
		if(isset($depth) && !($depth===512) ) return json_decode($json, $assoc, $depth);
		if(isset($assoc) && !($assoc===FALSE) ) return json_decode($json, $assoc);
		return json_decode($json);
	}
	static function last_error(){
		return json_last_error();
	}
	static function test_string($json){
		$str = NULL;
		if(!is_array($json)){ $json = array($json); }
		foreach ($json as $string) {
			$str .= 'Decoding: ' . $string;
			//json_decode($string);
			JSONplus::decode($string);

			//switch (json_last_error()) {
			switch (JSONplus::last_error()) {
				case JSON_ERROR_NONE:
					$str .= ' - No errors';
				break;
				case JSON_ERROR_DEPTH:
					$str .= ' - Maximum stack depth exceeded';
				break;
				case JSON_ERROR_STATE_MISMATCH:
					$str .= ' - Underflow or the modes mismatch';
				break;
				case JSON_ERROR_CTRL_CHAR:
					$str .= ' - Unexpected control character found';
				break;
				case JSON_ERROR_SYNTAX:
					$str .= ' - Syntax error, malformed JSON';
				break;
				case JSON_ERROR_UTF8:
					$str .= ' - Malformed UTF-8 characters, possibly incorrectly encoded';
				break;
				default:
					$str .= ' - Unknown error';
				break;
			}

			$str .=  PHP_EOL;
		}
		return $str;
	}
	static function last_error_msg(){ return json_last_error_msg(); }
	static function printfixes($str){
		$str = str_replace('\/', '/', $str);
		return $str;
	}
	static function prettyPrint( $json ){
		$result = '';
		$level = 0;
		$in_quotes = false;
		$in_escape = false;
		$ends_line_level = NULL;
		$json_length = strlen( $json );

		for( $i = 0; $i < $json_length; $i++ ) {
			$char = $json[$i];
			$new_line_level = NULL;
			$post = "";
			if( $ends_line_level !== NULL ) {
				$new_line_level = $ends_line_level;
				$ends_line_level = NULL;
			}
			if ( $in_escape ) {
				$in_escape = false;
			} else if( $char === '"' ) {
				$in_quotes = !$in_quotes;
			} else if( ! $in_quotes ) {
				switch( $char ) {
					case '}': case ']':
						$level--;
						$ends_line_level = NULL;
						$new_line_level = $level;
						break;

					case '{': case '[':
						$level++;
					case ',':
						$ends_line_level = $level;
						break;

					case ':':
						$post = " ";
						break;

					case " ": case "\t": case "\n": case "\r":
						$char = "";
						$ends_line_level = $new_line_level;
						$new_line_level = NULL;
						break;
				}
			} else if ( $char === '\\' ) {
				$in_escape = true;
			}
			if( $new_line_level !== NULL ) {
				$result .= "\n".str_repeat( "\t", $new_line_level );
			}
			$result .= $char.$post;
		}

		return $result;
	}
	static function import_csv_file($src, $delimiter=",", $enclosure='"', $escape="\\"){
		return JSONplus::import_csv(file_get_contents($src), $delimiter, $enclosure, $escape);
	}
	static function import_csv($str=NULL, $delimiter=",", $enclosure='"', $escape="\\"){
		$json = array();
		$firstline = strtok($str, "\n");
		$head = str_getcsv($firstline, $delimiter, $enclosure, $escape);
		$c = count($head);
		$a = $b = 0;
		//return $head;
		$original = $str;
		$str = str_replace('""', '&@quot;', $str);
		preg_match_all('#["]([^"]+)["]#', $str, $buffer);
		foreach($buffer[1] as $i=>$j){
			$str = str_replace('"'.$j.'"', '&@'.$i.';', $str);
		}
		$str = str_replace("\n", ",", $str);
		$db = str_getcsv($str, $delimiter, $enclosure, $escape);
		foreach($db as $k=>$v){
			if(!($b == 0 || $k == count($db)-1)){
				if(preg_match('#^\&\@([0-9]+)\;$#', $v, $z)){ $v = $buffer[1][(int) $z[1]]; }
				$v = str_replace('&@quot;', '"', $v);
				$json[$b-1][$head[$a]] = $v;
			}
			$a++; if($a>=$c){ $a = 0; $b++; }
			//if($b >= 2){ return $json; }
		}
		return $json;
	}
	static function is_table($json=array(), $column=array(), $primarykey_depth=-1, $multiple=TRUE, $keys=array(), $autoadd=TRUE){
		return JSONplus::magical_is_table($json, $column, $primarykey_depth, $multiple, $keys, $autoadd);
	}
	static function magical_is_table($json, &$column, $primarykey_depth=-1, $multiple=TRUE, $keys=array(), $autoadd=TRUE){
		/*fix*/ if($primarykey_depth === -1 && is_array($column) && count($column) > 0){ foreach($column as $i=>$k){ if(is_int($i) && $i < 0){ $primarykey_depth[$i] = $k; } } }
		/*fix*/ if(is_array($primarykey_depth)){ $keys = $primarykey_depth; $primarykey_depth = count($primarykey_depth); }
		/*fix*/ $primarykey_depth = (int) $primarykey_depth;
		$bool = TRUE;
		$clean_c = (count($column) == 0 ? TRUE : FALSE);
		if($primarykey_depth > 0){
			if(isset($keys[-1*$primarykey_depth])){ $column[-1*$primarykey_depth] = $keys[-1*$primarykey_depth]; }
			elseif(isset($keys[$primarykey_depth])){ $column[-1*$primarykey_depth] = $keys[$primarykey_depth]; }
			foreach($json as $i=>$row){
				$nb = JSONplus::magical_is_table($row, $column, ($primarykey_depth-1), $multiple, $keys, $autoadd);
				$bool = ($bool && $nb);
			}
			return $bool;
		}
		else{
			if(!($multiple === TRUE)){
				if(is_array($json)){
					foreach($json as $x=>$cell){
						if(($autoadd === TRUE && !in_array($x, $column)) || ($clean_c === TRUE && $i === 0)){ $column[max(array_keys($culumn))+1] = $x; }
						if(!in_array($x, $column)){ $bool = /*cell out of bound*/ FALSE; }
					}
				}
				else{
					return $bool;
				}
			}
			else{
				$c = 0;
				if(!is_array($json)){ return FALSE; }
				foreach($json as $i=>$row){
					if(!(is_int($i) && $i == $c)){
						$bool = /*no incremental rows*/ FALSE;
					}
					else{
						foreach($row as $x=>$cell){
							if(($autoadd === TRUE && !in_array($x, $column)) || ($clean_c === TRUE && $i === 0)){ $column[max(array_keys($column))+1] = $x; }
							if(!in_array($x, $column)){ $bool = /*cell out of bound*/ FALSE; }
						}
					}
					$c++;
				}
			}
			return $bool;
		}
	}
	static function get_columns($json=array(), $column=array(), $primarykey_depth=-1, $multiple=TRUE, $keys=array(), $autoadd=TRUE){
		if(!JSONplus::magical_is_table($json, $column, $primarykey_depth, $multiple, $keys, $autoadd)){ return array(); }
		return $column;
	}
	static function flatten_table($json=array(), $column=array(), $primarykey_depth=-1, $multiple=TRUE, $keys=array(), $autoadd=TRUE, $prefix=array(), $autoempty=FALSE){
		if(JSONplus::magical_is_table($json, $column, $primarykey_depth, $multiple, $keys, $autoadd)){
			/*fix*/ if($primarykey_depth === -1 && is_array($column) && count($column) > 0){ foreach($column as $i=>$k){ if(is_int($i) && $i < 0){ $primarykey_depth[$i] = $k; } } }
			/*fix*/ if(is_array($primarykey_depth)){ $keys = $primarykey_depth; $primarykey_depth = count($primarykey_depth); }
			/*fix*/ $primarykey_depth = (int) $primarykey_depth;
			$table = array();
			if($primarykey_depth > 0){
				foreach($json as $i=>$set){
					return JSONplus::flatten_table($set, $column, ($primarykey_depth-1), $multiple, $keys, $autoadd, array_merge($prefix, (isset($column[-1*$primarykey_depth]) ? array($column[-1*$primarykey_depth] => $i) : array() )), $autoempty);
				}
			}
			else{
				$c = 0;
				foreach($json as $i=>$row){
					if(is_int($i) && $i == $c){
							$current = array();
							foreach($column as $x=>$y){
								if(!($autoempty === FALSE) || isset($prefix[$y]) || isset($row[$y])){
									$current[$y] = (isset($prefix[$y]) ? $prefix[y] : (isset($row[$y]) ? $row[$y] : NULL) );
								}
							}
							$table[] = $current;
					}
					$c++;
				}
			}
			return $table;
		}
		else FALSE;
	}
	static function export_csv_file($file=FALSE, $json=array(), $column=array(), $primarykey_depth=-1, $multiple=TRUE, $keys=array(), $autoadd=TRUE, $prefix=array(), $autoempty=FALSE){
		if(JSONplus::magical_is_table($json, $column, $primarykey_depth, $multiple, $keys, $autoadd)){
			$csv = NULL;
			$flat = JSONplus::flatten_table($json, $column, $primarykey_depth, $multiple, $keys, $autoadd, $prefix, $autoempty);
			if(is_bool($file) || $file === NULL){ $fp = tmpfile(); }
			else{ $fp = fopen($file, 'w+'); }
			fputcsv($fp, $column);
			foreach ($flat as $fields) {
				$row = array();
				foreach($column as $i=>$c){
					$row[$i] = (isset($fields[$c]) ? $fields[$c] : NULL);
				}
				fputcsv($fp, $row);
				//*debug*/ fwrite($fp, JSONplus::encode($row)."\r\n");
			}
			fseek($fp, 0);
			while (!feof($fp)) {
		    $csv .= fread($fp, 1024);
			}
			fclose($fp);
			return $csv;
		}
		else { return FALSE; }
	}
	static function export_csv($json=array(), $column=array()){
		return JSONplus::export_csv_file(FALSE, $json, $column);
	}
	static function worker($mode='json'){
    $argv_list = array();
    if($_SERVER['argc'] > 0 && is_array($_SERVER['argv'])){ //case: php -f worker.php a=1 >> $_GET['a'] = 1
      foreach($_SERVER['argv'] as $i=>$par){
        if(preg_match('#^([^=]+)[=](.*)$#', $par, $buffer)){ $_GET[$buffer[1]] = $buffer[2]; }
        else{ $argv_list[$i] = $par; }
      }
    }

    if(isset($_GET[JSONplus_FILE_ARGUMENT]) && file_exists($_GET[JSONplus_FILE_ARGUMENT])){ //case: http://../worker.php?file=set.json
			$raw = file_get_contents($_GET[JSONplus_FILE_ARGUMENT]);
    }
    elseif(isset($argv_list[1]) && file_exists($argv_list[1])){ //case: php -f worker.php set.json
			$raw = file_get_contents($argv_list[1]);
    }

    if(!isset($raw)){ //case: cat set.json | php -f worker.php
      if(defined('STDIN') && php_sapi_name()==="cli"){
        $input = NULL;
        $fh = fopen('php://stdin', 'r');
        $read  = array($fh);
        $write = NULL;
        $except = NULL;
        if ( stream_select( $read, $write, $except, 0 ) === 1 ) {
            while ($line = fgets( $fh )) {
                    $input .= $line;
            }
        }
        fclose($fh);
				$raw = $input;
      }
      elseif(isset($_POST) && is_array($_POST) && isset($_POST[JSONplus_POST_ARGUMENT]) && is_string($_POST[JSONplus_POST_ARGUMENT])){ //case: http://../worker.php < $_POST['json']
				$raw = $_POST[JSONplus_POST_ARGUMENT];
      }
      else{
        //ERROR Message for worker
        //exit;
        return FALSE;
      }
    }
		switch(strtolower($mode)){
			case 'raw': return $raw; break;
			case 'json': default: return JSONplus::decode($raw, TRUE);
		}
  }
}
?>
