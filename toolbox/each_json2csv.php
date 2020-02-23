<?php
require_once(dirname(dirname(__FILE__)).'/JSONplus.php');
require_once(dirname(dirname(__FILE__)).'/JSONplus_schema.php');

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
    $pkf = \JSONplus\Schema::find_schema_for($file, $base);
    if(file_exists($base.$pkf)){ $schema = new \JSONplus\Schema($base.$pkf); $keys = $schema->get_primairykey(TRUE); } else { $keys = array(); }
    /*debug*/ print $pkf.' = '; print_r($keys); print "\n";
    $column = $keys;
    $primarykey_depth = \JSONplus\Schema::primairykey_depth($keys);
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
