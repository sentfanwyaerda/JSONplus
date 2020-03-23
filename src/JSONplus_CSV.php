<?php
namespace JSONplus;
require_once(__DIR__.'/JSONplus.php');

class CSV extends \JSONplus {
  public function is(){
    return 'CSV';
  }
  public function get_extension($multiple=FALSE){
    $ext = array('csv');
    if($multiple === FALSE){ return reset($ext); }
    else{ return $ext; }
  }
  private function __settings($a=array()){
    if(!isset($a['delimiter'])){ $a['delimiter'] = ","; }
    if(!isset($a['enclosure'])){ $a['enclosure'] = '"'; }
    if(!isset($a['escape'])){ $a['escape'] = "\\"; }
    return $a;
  }
  public function import($str=NULL, $setting=array()){
    $setting = $this->__settings($setting);
    $json = array();
    $firstline = strtok($str, "\n");
    $head = str_getcsv($firstline, $setting['delimiter'], $setting['enclosure'], $setting['escape']);
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
    $db = str_getcsv($str, $setting['delimiter'], $setting['enclosure'], $setting['escape']);
    foreach($db as $k=>$v){
      if(!($b == 0 || $k == count($db)-1)){
        if(preg_match('#^\&\@([0-9]+)\;$#', $v, $z)){ $v = $buffer[1][(int) $z[1]]; }
        $v = str_replace('&@quot;', '"', $v);
        $json[$b-1][$head[$a]] = $v;
      }
      $a++; if($a>=$c){ $a = 0; $b++; }
      //if($b >= 2){ return $json; }
    }
    $json = self::string_to_type_fix($json);
    $this->_ = $json;
    return $json;
  }
  public function import_file($file=FALSE, $setting=array()){
    $this->uri = $file;
    return $this->import(file_get_contents($file), $setting);
  }
}
?>
