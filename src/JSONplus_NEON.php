<?php
namespace JSONplus;
require_once(__DIR__.'/JSONplus.php');

class NEON extends \JSONplus {
  public function is(){
    return 'NEON';
  }
  public function get_extension($multiple=FALSE){
    $ext = array('neon');
    if($multiple === FALSE){ return reset($ext); }
    else{ return $ext; }
  }

	/***********************************************************
	 * IMPORTING AND EXPORTING NEON
	 ***********************************************************/
	public function import($str=NULL, $setting=array()){ /* Alias of Neon::decode */
		if(!class_exists('\Nette\Neon\Neon')){ return \JSONplus::UNSUPPORTED; }
		return /* (json) */ \Nette\Neon\Neon::decode($str);
	}
	public function export($setting=array()){ /* Alias of Neon::encode */
		//if(class_exists('\Nette\Neon\Neon')){ return /* (string) */ \Nette\Neon\Neon::encode($json, $flags); }
    $json = $this->get();
		return self::neon_prettyRePrint($json);
	}
	static function neon_prettyRePrint( $raw , $depth=0){
		if(is_array($raw)){
			$json = $raw; $str = NULL;
			$i = 0;
			foreach($json as $key=>$el){
				if(is_array($el)){
					if (is_int($key) && $key == $i){
						$str = str_repeat("\t", $depth).'- '.str_replace(array('","','":"', '{"', '"}','["','"]','\\/'), array('", "','": "', '{" ', ' "}','[" ',' "]','/'), json_encode($el))."\n";
					}
					else {
						$str .= str_repeat("\t", $depth).'"'.$key.'": '."\n";
						$str .= self::neon_prettyRePrint($el, $depth+1);
					}
					$str .= "\n";
				}
				else{
					if(is_int($key) && $key == $i){
						$str .= str_repeat("\t", $depth).'- "'.$el.'"'."\n";
					}
					else{
						$str .= str_repeat("\t", $depth).'"'.$key.'": "'.$el.'"'."\n";
					}
				}
				$i++;
			}
			preg_match_all('#[\"]([^\"\,\:\<\>\=\r\n\t]*)[\"]#', $str, $buffer);
			foreach($buffer[1] as $i=>$match){
				$str = str_replace($buffer[0][$i], $buffer[1][$i], $str);
			}
		} /****************************************************/
		else{
			$str = $raw;
			# What if we could omit quotes?
			preg_match_all('#[\"]([^\"\,\:\<\>\=\r\n\t]*)[\"]#', $str, $buffer);
			foreach($buffer[1] as $i=>$match){
				$str = str_replace($buffer[0][$i], $buffer[1][$i], $str);
			}
			# (EARLY) Are bullets more legible?
			preg_match_all('#[\[]([^\[\]]*)[\]]#', $str, $bullets);
			foreach($bullets[0] as $i=>$match){
				preg_match_all('#[\{]([^\{\}]*)[\}]#', $match, $group);
				foreach($group[0] as $j=>$g){ $match = str_replace($g, preg_replace("#\s+#i", ' ', $g), $match); }
				$str = str_replace($bullets[0][$i], preg_replace('#[\-]\s(\])#', '\\1', preg_replace('#(\n[\t]+)#', '\\1- ', $match)), $str);
			}
			# How about braces and commas?
			$str = preg_replace('#[\t]*[\}\]]?[\,\{\}\[\]](\n|$)#', '\\1', $str);
			/*
			$c = 0; $depth = 8; $bset = array();
			while($depth > 0 && preg_match('#(^|[\:\,]\s*)[\{\[]#', $str)){
				foreach(array('\['=>'\]','\{'=>'\}') as $a=>$b){
					preg_match_all('#['.$a.']([^'.$a.$b.']*)['.$b.']#', $str, $brace);
					foreach($brace[2] as $i=>$match){
						//$str = str_replace($brace[0][$i], $brace[1][$i].$brace[2][$i], $str);
						$str = str_replace($brace[0][$i], '&@'.$c.';', $str);
						$r = $brace[1][$i];
						switch($a){
							case '[':
								//$r = ...(r)
								break;
							default: //do nothing
						}
						$bset[$c] = $r;
						$c++;
					}
				}
				$d--;
			}
			foreach($bset as $i=>$d){
				$str = str_replace('&@'.$i.';', $d, $str);
			}
			*/
			/*commas*/ $str = preg_replace('#[\,](\n|$)#', '\\1', $str);
			/*indent fix*/ $str = preg_replace('#(^|\n)\t#', "\\1", $str);
			# Are bullets more legible?
			#...
			# How about comments?
			#...
			# You found NEON syntax!
		}
		/*fix*/ $str = preg_replace('#(^\n|\n$)#', '', $str);
		return $str;
	}
}
?>
