<?php
require_once(dirname(dirname(__FILE__)).'/JSONplus.php');

/* ls *.json | php -f toolbox/each_json2csv.php base=`pwd` */

$raw = JSONplus::worker('raw');

$list = explode("\n", $raw);
$base = (isset($_GET['base']) ? $_GET['base'] : dirname(__FILE__).'/' );
$multiple = (isset($_GET['multiple']) ? (is_bool($_GET['multiple']) ? $_GET['multiple'] : ($_GET['multiple'] === 'false' ? FALSE : TRUE)) : TRUE);
$save = (isset($_GET['save']) && $_GET['save'] == 'true' ? TRUE : FALSE);
/*fix*/ if(substr($base, -1) != '/'){ $base .= '/'; }

print $base."\n";
print "multiple = "; print_r($multiple); print "\n";
print_r($list);

foreach($list as $i=>$file){
  print $base.$file."\n";
  if(preg_match('#\.json$#', $file) && file_exists($base.$file)){
    $column = array();
    $json = JSONplus::decode(file_get_contents($base.$file), TRUE);
    //print_r($json);
    $pkf = $base.preg_replace('#\.json$#', '.keys', (preg_match('#[-]#', $file) ? substr($file, (-1*(strlen($file)-strrpos($file, '-'))+1) ) : $file) );
    if(file_exists($pkf)){ $keys = JSONplus::decode(file_get_contents($pkf), TRUE); } else { $keys = array(); }
    /*debug*/ print $pkf.' = '; print_r($keys); print "\n";
    $column = $keys;
    $primarykey_depth = (is_array($keys) && count($keys) > 0 ? -1*min(array_keys($keys)) : -1 );
    if($save === TRUE){
      $csv = JSONplus::export_csv_file(preg_replace('#\.json$#', '.csv', $base.$file), $json, $column, $primarykey_depth, $multiple, $keys);
    }
    else{
      $csv = JSONplus::export_csv($json, $column, $primarykey_depth, $multiple, $keys);
    }
    print strlen($csv).' '.md5($csv)."\n";
    //print $csv."\n";
  }
}
?>
