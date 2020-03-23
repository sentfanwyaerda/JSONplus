<?php
namespace JSONplus\Schema;
require_once(dirname(__FILE__).'/JSONplus.php');
require_once(dirname(__FILE__).'/JSONplus_schema.php');
class Rule {
  var $schema;
  var $log = array();
  function __construct(&$schema){
    $this->schema = $schema;
  }

  function get_log($print=FALSE){
    /*fix*/ if(is_bool($print)){ $print = ($print ? 'true' : 'false'); }
    switch($print){
      case 'true':
        $str = NULL;
        foreach($this->log as $i=>$o){
          $str .= '- '.json_encode($o)."\n";
        }
        break;
      case 'array': case 'false': default: return $this->log;
    }
  }
  function clear(){ $this->log = array(); }
  function notify($rule, $result=FALSE, $path=NULL, $debug=\JSONplus::UNSUPPORTED){
    $i = count($this->log);
    if($path !== NULL){ $this->log[$i]['path'] = $path; }
    $this->log[$i]['rule'] = $rule;
    $this->log[$i]['result'] = $result;
    if($debug !== \JSONplus::UNSUPPORTED){ $this->log[$i]['debug'] = $debug; }
    return $i;
  }

  static function clean($str){
    $str = str_replace(array('$', '-'), array('dollar_', '_'), $str);
    $str = preg_replace('#[^a-z_]#i', '', $str);
    return strtolower($str);
  }

  function each($el, $definition=array(), $path=NULL){
    /*debug*/ print json_encode($definition).PHP_EOL;
    /*fix*/ if(!\JSONplus::is_JSONplus_or_array($definition) ){ $definition = $this->schema->get_element_definition($path); }
    $bool = \JSONplus::is_JSONplus_or_array($definition);
    /*log*/ $this->notify('> each', $bool, $path);
		/*fix*/ if(\JSONplus::is_JSONplus($definition)){ $definition = $definition->__toArray(); }
    if(!is_array($definition)){ return \JSONplus::MALFORMED_ERROR; }
    foreach($definition as $key=>$val){
      //*debug log*/ $this->notify('% '.$key, NULL, $path);
      $cl = array();
      $cl[] = self::clean('key_'.$key);
      if(is_string($val)){ $cl[] = self::clean('def_'.$key.'_'.$val); }
      $ci = 0;
      foreach($cl as $c){
        if(method_exists($this, $c)){ $bool = ($bool && $this->$c($el, $definition, $path)); $ci++; }
      }
      if($ci == 0){ $this->notify($key, '%UNSUPPORTED', $path); }
    }
    return $bool;
  }

  /***************************************************
   * RULES
   **************************************************/
  function key_dollar_schema($el, $definition=array(), $path=NULL){
    $t = (is_string($el) && preg_match('#^http[s]?://#', $el));
    /*log*/ $this->notify('$schema', $t, $path);
    return $t;
  }
  function def_type_array($el, $definition=array(), $path=NULL){
    $t = is_array($el);
    /*log*/ $this->notify('type=array', $t, $path);
    return $t;
  }
  function def_type_string($el, $definition=array(), $path=NULL){
    $t = is_string($el);
    /*log*/ $this->notify('type=string', $t, $path);
    return $t;
  }
  function def_type_object($el, $definition=array(), $path=NULL){
    $t = (is_array($el) || is_object($el));
    $i = 0;
    if($t){
      foreach($el as $x=>$y){
        $t = ($t && !(is_int($x) && $i===$x));
        $xp = (substr($path, -1) == '/' ? $path : $path.'/').$x;
        //if(is_array($y) || is_object($y)){ $this->each($y, $this->schema->get_element_definition($xp), $xp); }
        $i++;
      }
    }
    //var_dump($el);
    /*log*/ $this->notify('type=object', $t, $path); //, /*debug*/ $el
    return $t;
  }

  /***************************************************
   * TESTS
   **************************************************/
}
?>
