<?php
namespace JSONplus;
require_once('JSONplus.php');

class JSON extends \JSONplus {
  public function is(){
    return 'JSON';
  }
  public function get_extension($multiple=FALSE){
    $ext = array('json');
    if($multiple === FALSE){ return reset($ext); }
    else{ return $ext; }
  }
}
?>
