<?php
namespace JSONplus;
require_once(dirname(__FILE__).'/JSONplus.php');
class Schema extends \JSONplus {
  function load($schemajson){
    $this->_ = $schemajson;
  }
  function validate($json){

  }

  static function find_schema_for($file, $base=NULL, $autoload=FALSE, $giveanyway=FALSE){
    if(file_exists($base.$file)){
      $pkf = preg_replace('#\.json$#', '.schema', (preg_match('#[-]#', $file) ? substr($file, (-1*(strlen($file)-strrpos($file, '-'))+1) ) : $file) );
      if(file_exists($base.$pkf)){
        if(isset($this) && $autoload === TRUE){ $this->open($pkf); }
        return $pkf;
      }
      else{
        return ($giveanyway === TRUE ? $pkf : FALSE);
      }
    }
    else{ return FALSE; }
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
