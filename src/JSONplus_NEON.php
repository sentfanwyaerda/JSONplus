<?php
namespace JSONplus;
require_once('JSONplus.php');

class NEON extends \JSONplus {
  public function is(){
    return 'NEON';
  }
  public function get_extension($multiple=FALSE){
    $ext = array('neon');
    if($multiple === FALSE){ return reset($ext); }
    else{ return $ext; }
  }
}
?>
