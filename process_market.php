<?php
require 'pack_base.php';

$gameInfoArray=loadGameInfo($argv[2]);
if(!$gameInfoArray){
	echo "no game info.\n";
	return;
}
echo "loadGameInfo...\n";
print_r($gameInfoArray);

$originDom = new DOMDocument();
$originDom->load($argv[1]);
$originDom->preserveWhiteSpace = false;
$originDom->formatOutput = true;
$array = getXML($originDom->documentElement);

mergeManifest($array,$originDom);

function mergeManifest($arrayFrom,$dom){
	echo "Check game info ...\n";
	foreach(array_keys($arrayFrom) as $key){
		$dom = checkGameInfo($key,$arrayFrom,$dom);
	}
  	$dom->save('processed_mobage_market.xml');
}

function loadGameInfo($gameInfoPath){
	$fp_in = fopen($gameInfoPath, "r");
	if(!$fp_in){
		echo "gameInfo.conf not found!\n";
		return;
	}
	$array=false;
	while (!feof($fp_in)) {
    	$line = fgets($fp_in);
    	list($name, $value) = split("=", $line);
    	$array[$name]=$value;
	}
	return $array;
}

function checkGameInfo($element,$arrayFrom,$dom){
	global $gameInfoArray;
	if($element=='market'){
		if(count($arrayFrom[$element])>0){
			echo "--------------process $element nodes----------------------\n";
			foreach($arrayFrom[$element] as $node){
				$array = false;
				$hasAttr = false;
				$nodeName=false;
				$nodeValue=false;
				foreach ($node->attributes as $attr) {
					$nodeName = trim($attr->nodeName);
					$nodeValue = str_replace("\n","",trim($attr->nodeValue));
					echo "nodeName:".$nodeName."-------nodeValue:".$nodeValue."\n";
					$value = str_replace("\n","",$gameInfoArray[$nodeName]);
					if($value){
						echo "set ".$nodeName."  ".$value."\n";
						$node->setAttribute($nodeName,$value);
					}
  		  		}
			}
			echo '------------------------------------------------------'."\n";
		}
	}
	return $dom;
}
?>
