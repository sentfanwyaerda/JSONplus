<?php
namespace JSONplus;
require_once(dirname(__FILE__).'/JSONplus.php');
class Schema extends \JSONplus {
  function validate($json){

  }
  function get_rules(){
    $rule = new \JSONplus\Schema\Rule($this);
    return $rule;
  }

  static function find_schema_for($file, $base=NULL, $autoload=FALSE, $giveanyway=FALSE){
    if(file_exists($base.$file) || $giveanyway === TRUE){
      $pkf = preg_replace('#\.json$#', '.schema', (preg_match('#[-]#', $file) ? substr($file, (-1*(strlen($file)-strrpos($file, '-'))+1) ) : $file) );
      if(file_exists($base.$pkf)){
        if(isset($this) && $autoload === TRUE){ $this->open($pkf); }
        return $pkf;
      }
      else{
        return ($giveanyway !== FALSE ? $pkf : FALSE);
      }
    }
    else{ return FALSE; }
  }
  static function static_element_definition($key=NULL, $schema=FALSE, $subset=FALSE){
    /*fix*/ if($subset === FALSE){ $subset = array('properties','definitions'); }
    if($schema === FALSE){ return \JSONplus::MALFORMED_ERROR; }
    /*fix*/ if(in_array($key, array(NULL, '/') )){ return \JSONplus::pointer('/', $schema); }
    /*fix*/ if(substr($key, 0, 1) == '/'){ $key = substr($key, 1); }
    foreach($subset as $group){
      $p = \JSONplus::pointer('/'.$group.'/'.$key, $schema);
      if($p != FALSE){ return $p; }
    }
    return \JSONplus::NOT_FOUND_ERROR;
  }
  function get_element_definition($key=NULL, $schema=FALSE, $subset=FALSE){
    if($schema === FALSE){
      if(!isset($this)){ return \JSONplus::MALFORMED_ERROR; }
      $schema = $this->_;
    }
    return \JSONplus\Schema::static_element_definition($key, $schema, $subset);
  }
  static function element_validate($el, $definition=array()){
    $bool = TRUE;
    if(isset($definition['$ref'])){
      /*fix*/ if(!isset($this)){ return \JSONplus::NOT_FOUND_ERROR; }
      $definition = $this->pointer($definition['$ref']);
      /*fix*/ if($definition === \JSONplus::NOT_FOUND_ERROR){ return \JSONplus::NOT_FOUND_ERROR; }
    }
    /*fix*/ if(!isset($definition['type'])){ return \JSONplus::MALFORMED_ERROR; }
    if(is_array($definition['type'])){
      /*reset*/ $bool = FALSE;
      foreach($definition['type'] as $k=>$type){
        switch($k){
          case 'anyOf': case 'allOf': case 'oneOf': case 'not':
            //...
            return \JSONplus::UNSUPPORTED;
          break;
          default:
            if(!is_int($k)){ return \JSONplus::MALFORMED_ERROR; }
            $bool = ($bool || \JSONplus\schema::element_validate($el, array_merge($definition, array('type' => $type)) ));
        }
      }
    }
    else{
      switch(strtolower($definition['type'])){
        case 'array': return \JSONplus\schema::element_validate_array($el, $definition); break;
        case 'object': return \JSONplus\schema::element_validate_object($el, $definition); break;
        case 'string': return \JSONplus\schema::element_validate_string($el, $definition); break;
        case 'boolean': return \JSONplus\schema::element_validate_boolean($el, $definition); break;
        case 'integer': return \JSONplus\schema::element_validate_integer($el, $definition); break;
        case 'number': return \JSONplus\schema::element_validate_number($el, $definition); break;
        default: return \JSONplus::MALFORMED_ERROR;
      }
    }
    return $bool;
  }
  static function element_validate_array($el, $definition=array()){
    $bool = TRUE;
    if(!isset($definition['type'])){ return \JSONplus::MALFORMED_ERROR; }
    if(strtolower($definition['type']) != 'array'){ return \JSONplus::MALFORMED_ERROR; }
    if(!is_array($el)){ return \JSONplus::INCORRECT; }
    if(isset($definition['items'])){
      //...
    }
    return $bool;
  }
  static function element_validate_object($el, $definition=array()){
    $bool = TRUE;
    if(!is_array($el)){ return \JSONplus::INCORRECT; }
    if(isset($definition['required'])){
      //...
    }
    if(isset($definition['oneOf'])){
      //...
    }
    if(isset($definition['properties'])){
      //...
    }
    if(isset($definition['additionalProperties'])){
      //...
    }
    return $bool;
  }
  static function element_validate_string($el, $definition=array()){
    $bool = is_string($el);
    if(isset($definition['pattern'])){
      if(is_array($definition['pattern'])){
        $pat = FALSE;
        foreach($definition['pattern'] as $p){
          $pat = ($pat || preg_match('#'.$p.'#', $el));
        }
      }
      else{ $pat = preg_match('#'.$definition['pattern'].'#', $el); }
      $bool = ($bool && $pat);
    }
    if(isset($definition['format'])){
      $form = TRUE;
      switch($definition['format']){
        case 'uri': break;
        case 'uri-reference': break;
        case 'email': break;
        case 'regex': break;
        default: return \JSONplus::MALFORMED_ERROR;
      }
      $bool = ($bool && $form);
    }
    return $bool;
  }
  static function element_validate_integer($el, $definition=array()){
    $bool = is_int($el);
    return $bool;
  }
  static function element_validate_number($el, $definition=array()){
    $bool = is_int($el);
    return $bool;
  }
  static function element_validate_boolean($el, $definition=array()){
    $bool = is_bool($el);
    return $bool;
  }

  /****************************************************
   * \JSONplus\Schema extra features
   ***************************************************/
  function /*(array)*/ get_primairykey($extended=FALSE){
    $pk = array();
    if(isset($this->_['primairykey']) && is_array($this->_['primairykey'])){
      $c = 0;
      foreach($this->_['primairykey'] as $i=>$key){
        $a = ($i == $c ? ((-1*count($this->_['primairykey']))+$i) : $i); $c++;
        $pk[$a] = $key;
      }
      if($extended !== FALSE){ if($vn = $this->get_valuename()){
        $pk[0] = $vn;
      } }
    }
    return $pk;
  }
  static function primairykey_depth($keys=NULL){
    if(!is_array($keys)){
      if(isset($this)){ $keys = $this->get_primairykey(); }
      else{ $keys = array(); }
    }
    return (is_array($keys) && count($keys) > 0 ? -1*min(array_keys($keys)) : -1 );
  }
  function get_valuename(){
    if(isset($this->_['valuename'])){
      return $this->_['valuename'];
    }
    return FALSE;
  }
}
?>
