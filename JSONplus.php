<?php
if(!defined("JSONplus_DATALIST_ROOT")){define("JSONplus_DATALIST_ROOT", dirname(__FILE__).'/');}
class JSONplus{
	function get_datalist($datalist){
		return JSONplus::decode(JSONplus::open_datalist($datalist), TRUE);
	}
	function open_datalist($datalist){
		if(!file_exists(JSONplus_DATALIST_ROOT.$datalist.'.json')){ return FALSE; }
		$str = file_get_contents(JSONplus_DATALIST_ROOT.(substr(JSONplus_DATALIST_ROOT, -1) == '/' ? NULL : '/').$datalist.'.json');
		return $str;
	}
	function include_all_datalist($json){
		preg_match_all("#(\"[^\"]+\"\s*:\s*)?<datalist:([^>]+)>#i", $json, $buffer);
		foreach($buffer[0] as $i=>$match){
			if(strlen($buffer[1][$i]) >= 1){
				$json = str_replace($buffer[0][$i], $buffer[1][$i].JSONplus::open_datalist($buffer[2][$i]), $json);
			} else {
				$json = str_replace($buffer[0][$i], preg_replace("#^\s*[\{\[](.*)[\}\]]\s*$#i", "\\1", JSONplus::open_datalist($buffer[2][$i])), $json);
			}
		}
		return $json;
	}
	function encode($value, $options=0, $depth=512){
		$str = json_encode($value, $options, $depth);
		//pretty print (human readable and support for GiT-version management)
		$str = JSONplus::prettyPrint($str);
		return $str;
	}
	function decode($json, $assoc=FALSE, $depth=512, $options=0){
		//proces <datalist:*> before return
		$json = JSONplus::include_all_datalist($json);
		if(isset($options) && !($options===0) ) return json_decode($json, $assoc, $depth, $options);
		if(isset($depth) && !($depth===512) ) return json_decode($json, $assoc, $depth);
		if(isset($assoc) && !($assoc===FALSE) ) return json_decode($json, $assoc);
		return json_decode($json);
	}
	function last_error(){
		return json_last_error();
	}
	function test_string($json){
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
	function last_error_msg(){ return json_last_error_msg(); }
	
	function prettyPrint( $json ){
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
}
?>