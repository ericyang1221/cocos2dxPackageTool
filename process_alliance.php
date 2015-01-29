<?php
require 'pack_base.php';

$channelInfoArray=loadChannelInfo($argv[2]);
if(!$channelInfoArray){
	echo "no channel info.\n";
	return;
}
echo "loadChannelInfo...\n";
print_r($channelInfoArray);

$originDom = new DOMDocument();
$originDom->load($argv[1]);
$originDom->preserveWhiteSpace = false;
$originDom->formatOutput = true;
$array = getXML($originDom->documentElement);

mergeManifest($array,$originDom);

function mergeManifest($arrayFrom,$dom){
	echo "Check channel info ...\n";
	foreach(array_keys($arrayFrom) as $key){
		$dom = checkChannelInfo($key,$arrayFrom,$dom);
	}
  	$dom->save('processed_mobage_alliance.xml');
}

function loadChannelInfo($channelInfoPath){
	$fp_in = fopen($channelInfoPath, "r");
	if(!$fp_in){
		echo "channelInfoPath.conf not found!\n";
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

function checkChannelInfo($element,$arrayFrom,$dom){
	global $channelInfoArray;
	if($element!='alliance'){
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
					$value = str_replace("\n","",$channelInfoArray[$nodeName]);
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
