<?php
namespace JSONplus;
require_once('JSONplus.php');

class Schema extends \JSONplus {
  public function is(){
    return 'json-schema';
  }
  public function get_extension($multiple=FALSE){
    $ext = array('schema','json');
    if($multiple === FALSE){ return reset($ext); }
    else{ return $ext; }
  }
}
?>
