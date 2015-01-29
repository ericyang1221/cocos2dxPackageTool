<?php
require 'pack_base.php';

$gameDom = new DOMDocument();
$gameDom->load($argv[2]);
$gameDom->preserveWhiteSpace = false;
$gameDom->formatOutput = true;
$arrayGame = getManifest($gameDom->documentElement);

$allianceDom = new DOMDocument();
$allianceDom->load($argv[1]);
$allianceDom->preserveWhiteSpace = false;
$allianceDom->formatOutput = true;
$arrayAlliance = getManifest($allianceDom->documentElement);

$orientation = $argv[3];
if($orientation == 'l' || $orientation == 'L'){
	$orientation='landscape';
}else if($orientation == 'p' || $orientation == 'P'){
	$orientation='portrait';
}else{
	$orientation=false;
}
echo "orientation = ".$orientation."\n";

mergeManifest($arrayAlliance,$arrayGame,$gameDom,$orientation);

function mergeManifest($arrayFrom,$arrayTo,$dom,$orientation){
	echo "mergeManifest ...\n";
	foreach(array_keys($arrayFrom) as $key){
		$dom = merge($key,$arrayFrom,$arrayTo,$dom,$orientation);
	}
	$dom = mergePackageName($arrayFrom,$arrayTo,$dom);
	$dom = mergeApplicationName($arrayFrom,$arrayTo,$dom);
//  	echo $dom->saveXML()."\n";
  	$dom->save('ResultAndroidManifest.xml');
}
?>
