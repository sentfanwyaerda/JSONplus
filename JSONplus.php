<?php
class JSONplus{
	function encode($value, $options=0, $depth=512){
		$str = json_encode($value, $options, $depth);
		//pretty print (human readable and support for GiT-version management)
		return $str;
	}
	function decode($json, $assoc=TRUE, $depth=512, $options=0){
		//proces <datalist:*> before return
		return json_decode($json, $assoc, $depth, $options);
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
	function last_error_msg(){ return json_last_error(); }
}
?>