<?php
if(!defined("JSONplus_DATALIST_ROOT")){define("JSONplus_DATALIST_ROOT", dirname(__FILE__).'/');}
if(!defined("JSONplus_FILE_ARGUMENT")){define("JSONplus_FILE_ARGUMENT", 'file');}
if(!defined("JSONplus_POST_ARGUMENT")){define("JSONplus_POST_ARGUMENT", 'json');}

require_once(__DIR__.'/JSONplus_JSON.php');
require_once(__DIR__.'/JSONplus_Schema.php');

class JSONplus {
	const NOT_FOUND_ERROR = NULL;
	const MALFORMED_ERROR = -4;
	const INCORRECT = -3;
	const UNSUPPORTED = -1;
	const EMPTY = array();

  var $uri = \JSONplus::NOT_FOUND_ERROR;
  var $_ = \JSONplus::EMPTY;
  var $schema = \JSONplus::UNSUPPORTED;

  function __construct($x=NULL, $mode='JSON'){
		switch(gettype($x)){
			case 'string':
				if(preg_match('#^\s*[\[\{]#', $x)){
					$this->_ = \JSONplus::decode($x, TRUE);
				}
				elseif(file_exists($x)){
					$this->open($x);
				}
				else{
					$this->__e('MALFORMED_ERROR');
				}
				break;
			case 'object': $this->_ = $x; break;
			case 'array': $this->load($x); break;
			case 'NULL': default: //do nothing
		}
  }
	private function __e($code, $method=NULL, $params=array()){
		//if(is_string($code)){
		switch(strtoupper($code)){
			case 'NOT_FOUND_ERROR': case \JSONplus::NOT_FOUND_ERROR: $code = \JSONplus::NOT_FOUND_ERROR; break;
			case 'MALFORMED_ERROR': case \JSONplus::MALFORMED_ERROR: $code = \JSONplus::MALFORMED_ERROR; break;
			case 'INCORRECT': case \JSONplus::INCORRECT: $code = \JSONplus::INCORRECT; break;
			case 'EMPTY': case \JSONplus::EMPTY: $code = \JSONplus::EMPTY; break;
			case 'UNSUPPORTED': case \JSONplus::UNSUPPORTED: default: $code = \JSONplus::UNSUPPORTED;
		}
		//implement some error logging
		return $code;
	}
  public function __toString(){
    if(\JSONplus::is_JSONplus($this->_)){
      return $this->_->__toString();
    }
    else {
      return \JSONplus::encode($this->_);
    }
  }
  public function __toArray(){
    if(\JSONplus::is_JSONplus($this->_)){
      return $this->_->__toArray();
    }
    else{ return $this->_; }
  }
  public function get(){
    return $this->__toArray();
  }
  public function get_uri(){
    return $this->uri;
  }
  public function set_uri($file){
    $this->uri = $file;
  }
  public function load($json=array()){
    $this->_ = $json;
  }
  public function open($file=FALSE){
    return $this->import_file($file);
  }
  public function save($file=FALSE){
    return $this->export_file($file);
  }
	static function file($file){
		$j = new self($file);
		return $j;
	}
	static function str($str=NULL){
		$j = new self($str);
		return $j;
	}
  public function import($str=NULL, $setting=array()){
    if(\JSONplus::is_JSONplus($this->_)){
      return $this->_->import($str, $setting);
    }
    else {
			if(preg_match('#^\s*[\[\{]#', $str)){
				$json = \JSONplus::decode($str, TRUE);
				$this->load($json);
				return $json;
			}
			else{
				return $this->__e('MALFORMED_ERROR', __METHOD__, array('file'=>$file));
			}
    }
  }
  public function import_file($file=FALSE, $setting=array()){
    if($file !== FALSE){ $this->uri = $file; }
    if(\JSONplus::is_JSONplus($this->_)){
      return $this->_->import_file($file, $setting);
    }
    else {
			if(!file_exists($file)){
				return $this->__e('NOT_FOUND_ERROR', __METHOD__, array('file'=>$file));
			}
			$raw = file_get_contents($file);
			return $this->import($raw, $setting);
      //return \JSONplus::UNSUPPORTED;
    }
  }
  public function export($setting=array()){
    if(\JSONplus::is_JSONplus($this->_)){
      return $this->_->export($setting);
    }
    else {
			return \JSONplus::encode($this->_);
      //return \JSONplus::UNSUPPORTED;
    }
  }
  public function export_file($file=FALSE, $setting=array()){
    if(\JSONplus::is_JSONplus($this->_)){
      return $this->_->export_file($file, $setting);
    }
    else {
      return $this->__e('UNSUPPORTED');
    }
  }
  /***********************************************************
   * TYPE VALIDATION AND SCHEMA *
   ***********************************************************/
  public function is(){
    return $this->__e('MALFORMED_ERROR');
  }
  public function get_extension($multiple=FALSE){
    return $this->__e('UNSUPPORTED');
  }
  public function get_schema(){
    return $this->schema;
  }
  public function set_schema($schema=FALSE){
    if(\JSONplus::is_JSONplus($schema, 'JSONplus\Schema')){
      $this->schema = $schema;
    }
    elseif(is_string($schema) && file_exists($schema)){
      $this->schema = new \JSONplus\Schema($schema);
    }
    else{
      return $this->__e('MALFORMED_ERROR');
    }
  }
  public function validate(){
    return $this->__e('UNSUPPORTED');
  }

  /***********************************************************
   * TABLE *
   ***********************************************************/
 	static function is_table($json=array(), $column=array(), $primarykey_depth=-1, $multiple=TRUE, $keys=array(), $autoadd=TRUE){
 		return \JSONplus::magical_is_table($json, $column, $primarykey_depth, $multiple, $keys, $autoadd);
 	}
 	static function magical_is_table($json, &$column, $primarykey_depth=-1, $multiple=TRUE, $keys=array(), $autoadd=TRUE){
 		/*fix*/ if($primarykey_depth === -1 && is_array($column) && count($column) > 0){ $primarykey_depth = array(); foreach($column as $i=>$k){ if(is_int($i) && $i < 0){ $primarykey_depth[$i] = $k; } } }
 		/*fix*/ if(is_array($primarykey_depth)){ $keys = $primarykey_depth; $primarykey_depth = count($primarykey_depth); }
 		/*fix*/ $primarykey_depth = (int) $primarykey_depth;
 		$bool = TRUE;
 		$clean_c = (is_array($column) && count($column) == 0 ? TRUE : FALSE);
 //		if($autoadd === TRUE && is_array($keys) && count($keys) > 0){
 //			$j = 0;
 //			foreach($keys as $i=>$z){ if(is_int($i) && $i < 0){ $column[$i] = $z; } else { $column[--$j] = $z; } }
 //		}
 		if($primarykey_depth > 0){
 			if(isset($keys[-1*$primarykey_depth])){ $column[-1*$primarykey_depth] = $keys[-1*$primarykey_depth]; }
 			elseif(isset($keys[$primarykey_depth])){ $column[-1*$primarykey_depth] = $keys[$primarykey_depth]; }
 			//*debug*/ print_r(array('primarykey_depth' => $primarykey_depth, 'column' => $column));
 			foreach($json as $i=>$row){
 				$nb = \JSONplus::magical_is_table($row, $column, ($primarykey_depth - 1), $multiple, $keys, $autoadd);
 				$bool = ($bool && $nb);
 			}
 			return $bool;
 		}
 		else{
 			if(!($multiple === TRUE)){
 				if(is_array($json)){
 					foreach($json as $x=>$cell){
 						if(($autoadd === TRUE && !in_array($x, $column)) || ($clean_c === TRUE && $i === 0 && !in_array($x, $column))){ $column[(count($column) > 0 ? max(array_keys($column))+1 : 0)] = $x; }
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
 							if(($autoadd === TRUE && !in_array($x, $column)) || ($clean_c === TRUE && $i === 0 && !in_array($x, $column))){ $column[(count($column) > 0 ? max(array_keys($column))+1 : 0)] = $x; }
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
 		if(!\JSONplus::magical_is_table($json, $column, $primarykey_depth, $multiple, $keys, $autoadd)){ return array(); }
 		return $column;
 	}
 	static function flatten_cell($cell, $flag=array(), $mode='json'){
 		/*fix*/ if(!is_array($flag)){ $flag = (!is_bool($flag) ? array($flag) : array()); }
 		if(in_array('multiple', $flag) && is_array($cell)){ $str = NULL; foreach($cell as $c){ $str = ($str === NULL ? NULL : $str."\n").\JSONplus::flatten_cell($c, $flag); } $cell = $str; }
 		else{
 			foreach($flag as $el){
 				switch(strtolower($el)){
 					case 'int': $cell = (int) $cell; break;
 					case 'bool': $cell = (bool) $cell; break;
 					case 'string': $cell = (string) $cell; break;
 					case 'lower': $cell = strtolower($cell); break;
 					case 'upper': $cell = strtoupper($cell); break;
 					case 'md5sum': $cell = (preg_match('#^[0-9a-f]{32}$#', $cell) ? $cell : md5($cell)); break;
 					default: /*do nothing*/
 				}
 			}
 		}
 		switch(strtolower($mode)){
 			case 'markdown': $cell = str_replace(array(PHP_EOL, "\n", '|'), array("\\n", "\\n", "\\|"), $cell); break;
 		}
 		return $cell;
 	}
 	static function flatten_table($json=array(), $column=array(), $primarykey_depth=-1, $multiple=TRUE, $keys=array(), $autoadd=TRUE, $prefix=array(), $autoempty=FALSE){
 		if(\JSONplus::magical_is_table($json, $column, $primarykey_depth, $multiple, $keys, $autoadd)){
 			/*fix*/ if($primarykey_depth === -1 && is_array($column) && count($column) > 0){ foreach($column as $i=>$k){ if(is_int($i) && $i < 0){ $primarykey_depth[$i] = $k; } } }
 			/*fix*/ if(is_array($primarykey_depth)){ $keys = $primarykey_depth; $primarykey_depth = count($primarykey_depth); }
 			/*fix*/ $primarykey_depth = (int) $primarykey_depth;
 			///*debug*/ print_r(array('primarykey_depth' => $primarykey_depth, 'prefix' => $prefix));
 			$table = array();
 			if($primarykey_depth > 0){
 				foreach($json as $i=>$set){
 					$table = array_merge($table, \JSONplus::flatten_table($set, $column, ($primarykey_depth-1), $multiple, $keys, $autoadd, array_merge($prefix, (isset($column[-1*$primarykey_depth]) ? array($column[-1*$primarykey_depth] => $i) : array(-1*$primarykey_depth => $i) )), $autoempty) );
 				}
 				return $table;
 			}
 			else{
 				//*debug*/ print_r(array('primarykey_depth' => $primarykey_depth, 'prefix' => $prefix, 'is_array json' => is_array($json)));
 				$c = 0;
 				if(is_array($json)){
 					foreach($json as $i=>$row){
 						if(is_int($i) && $i == $c){
 								$current = array();
 								foreach($column as $x=>$y){
 									if(!($autoempty === FALSE) || isset($prefix[$y]) || isset($row[$y])){
 										$current[$y] = (isset($prefix[$y]) ? $prefix[$y] : (isset($row[$y]) ? $row[$y] : NULL) );
 									}
 								}
 								$table[] = $current;
 						}
 						$c++;
 					}
 				}
 				else{
 					$current = array();
 					foreach($column as $x=>$y){
 						if(!($autoempty === FALSE) || isset($prefix[$y]) || $x == 0){
 							$current[$y] = (isset($prefix[$y]) ? $prefix[$y] : ($x == 0 ? $json : NULL) );
 						}
 					}
 					$table[] = $current;
 				}
 			}
 			//*debug*/ print_r(array('primarykey_depth' => $primarykey_depth, 'table' => $table));
 			//*debug*/ print_r(array('primarykey_depth' => $primarykey_depth)); exit;
 			return $table;
 		}
 		else FALSE;
 	}


  /***********************************************************
   * ID *
   ***********************************************************/
	static function ID_crawl($json=array(), $prefix=NULL, $pattern=FALSE, $schema=FALSE, $allow_multiple=FALSE){
		$set = array();
		/*fix*/ if(strlen($prefix) < 1){ $prefix = '/'; }
		/*fix*/ if(is_bool($pattern)){ $pattern = array('#^[\$]?id$#i'); }
		if(FALSE){
			// $schema tells $prefix should be considered to be an ID by #{basename($prefix)}
		}
		foreach($json as $key=>$child){
			if(is_string($child)){
				foreach($pattern as $q=>$p){
					if(/*considered to be an ID*/ preg_match($p, $key)){
						//if(/*valid ID name*/ preg_match('#^[a-z0-9]$#i', $child)){
							if($allow_multiple === TRUE){ $set[] = array('source'=>$child, 'target'=>$prefix); }
							else { $set[$child] = $prefix; }
						//} //else {}
					}
				}
			}
			elseif(is_array($child)){
				$set = array_merge($set, \JSONplus::ID_crawl($child, (substr($prefix, -1) != '/' ? $prefix.'/' : $prefix).$key, $pattern, $schema, $allow_multiple));
			}
		}
		return $set;
	}
	public function ID_table(){
		return \JSONplus::ID_crawl($this->_, NULL, FALSE, $this->schema);
	}

  /***********************************************************
   * PATH AND POINTERS *
   ***********************************************************/
	public function getByPath($path){
		return $this->__e('EMPTY');
	}
	public function getByID($id){
		return $this->__e('EMPTY');
	}
	static function pointer($path, $json=array()){
		return \JSONplus::EMPTY;
	}

  /***********************************************************
   * STATIC *
   ***********************************************************/
 	static function is_JSONplus($o, $c='JSONplus'){
		if(!preg_match('#JSONplus#', $c)){ return \JSONplus::UNSUPPORTED; }
		if(is_array($c)){
			$bool = FALSE;
			foreach($c as $d){
				$w = \JSONplus::is_JSONplus($o, $d);
				if($w === \JSONplus::UNSUPPORTED){ return \JSONplus::UNSUPPORTED; }
				$bool = ($bool || $w);
			}
			return $bool;
		}
		else{
			return ((is_object($o) && (get_class($o) == $c || is_subclass_of($o, $c) ) ) ? TRUE : FALSE);
		}
 	}
  static function encode($value=\JSONplus::NOT_FOUND_ERROR, $options=0, $depth=512){
		if($value === \JSONplus::NOT_FOUND_ERROR){
			if(!isset($this)){ return \JSONplus::MALFORMED_ERROR; }
			$value = $this->_;
		}
		$str = json_encode($value, $options, $depth);
		//pretty print (human readable and support for GiT-version management)
		$str = \JSONplus::prettyPrint($str);
		/*fix*/ $str = \JSONplus::printfixes($str);
		$str = \JSONplus::unhide_negative_keys($str);
		return $str;
	}
	static function /*json*/ decode($str, $assoc=FALSE, $depth=512, $options=0){
		//proces <datalist:*> before return
		$str = \JSONplus::include_all_datalist($str);
		$str = \JSONplus::hide_negative_keys($str);
		if(isset($options) && !($options===0) ){ $json = json_decode($str, $assoc, $depth, $options); }
		elseif(isset($depth) && !($depth===512) ){ $json = json_decode($str, $assoc, $depth); }
		elseif(isset($assoc) && !($assoc===FALSE) ){ $json = json_decode($str, $assoc); }
		else{ $json = json_decode($str); }
		$json = \JSONplus::fix_negative_keys($json);
		return $json;
	}
	static function hide_negative_keys($str){
		if(preg_match_all('#([\{\,]\s*)([\-]?[0-9]+)(\s*[\:])#', $str, $buffer)){
			foreach($buffer[2] as $i=>$negative){
				$str = str_replace($buffer[0][$i], $buffer[1][$i].'"'.$negative.'"'.$buffer[3][$i], $str);
			}
		}
		return $str;
	}
	static function unhide_negative_keys($str){
		if(preg_match_all('#([\{\,]\s*)[\"]([\-]?[0-9]+)[\"](\s*[\:])#', $str, $buffer)){
			foreach($buffer[2] as $i=>$negative){
				$str = str_replace($buffer[0][$i], $buffer[1][$i].$negative.$buffer[3][$i], $str);
			}
		}
		return $str;
	}
	static function fix_negative_keys($json=array()){
		/*fix*/ if(!is_array($json)){ return $json; }
		foreach($json as $key=>$val){
			if(is_string($key) && preg_match('#^[\-]?[0-9]+$#', $key)){ unset($json[$key]); $json[(int) $key] = $json; }
			if(is_array($val)){ $json[$key] = \JSONplus::fix_negative_keys($val); }
		}
		return $json;
	}
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
  static function string_to_type_fix($v){
    if(is_string($v)){
      /* pattern according to https://www.php.net/manual/en/language.types.integer.php extended with negative and decimals */
      if(preg_match('#^[\-]?([1-9][0-9]*(_[0-9]+)*([\.][0-9]+)?|0|0[xX][0-9a-fA-F]+(_[0-9a-fA-F]+)*|0[0-7]+(_[0-7]+)*|0[bB][01]+(_[01]+)*)$#', $v)){ $v = (int) $v; }
      elseif(preg_match('#^(false|no)$#i', $v)){ $v = FALSE; }
      elseif(preg_match('#^(true|yes)$#i', $v)){ $v = TRUE; }
      elseif(preg_match('#^(NULL|)$#i', $v)){ $v = NULL; }
    }
    elseif(is_array($v)){
      foreach($v as $i=>$q){
        $v[$i] = self::string_to_type_fix($q);
      }
    }
    return $v;
  }

  /***********************************************************
   * DATALIST *
   ***********************************************************/
 	static function get_datalist($datalist){
 		return \JSONplus::decode(\JSONplus::open_datalist($datalist, '[]'), TRUE);
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
 				$json = str_replace($buffer[0][$i], $buffer[1][$i].\JSONplus::open_datalist($buffer[2][$i], '[]'), $json);
 			} else {
 				$json = str_replace($buffer[0][$i], preg_replace("#^\s*[\{\[](.*)[\}\]]\s*$#i", "\\1", \JSONplus::open_datalist($buffer[2][$i], NULL)), $json);
 			}
 		}
 		return $json;
 	}

  /***********************************************************
  * WORKER *
  ***********************************************************/
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
			case 'json': default: return \JSONplus::decode($raw, TRUE);
		}
  }
}

/* DEBUGGING */
function print_r_($raw, $title=NULL){
  print $title."\t= "; print_r($raw); print "\n";
}
?>
