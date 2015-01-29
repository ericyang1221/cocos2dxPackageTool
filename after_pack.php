<?php
require 'pack_base.php';

$metaDataArray=loadMetaData($argv[2]);
if(!$metaDataArray){
	echo "no meta data.\n";
	return;
}
echo "loadMetaData...\n";
print_r($metaDataArray);

$originDom = new DOMDocument();
$originDom->load($argv[1]);
$originDom->preserveWhiteSpace = false;
$originDom->formatOutput = true;
$array = getManifest($originDom->documentElement);

mergeManifest($array,$originDom);

function mergeManifest($arrayFrom,$dom){
	echo "Check meta data ...\n";
	foreach(array_keys($arrayFrom) as $key){
		$dom = checkMetaData($key,$arrayFrom,$dom);
	}
  	$dom->save('AfterPackAndroidManifest.xml');
}

function loadMetaData($metaDataPath){
	$fp_in = fopen($metaDataPath, "r");
	if(!$fp_in){
		echo "metaData.conf not found!\n";
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

function checkMetaData($element,$arrayFrom,$dom){
	global $metaDataArray;
	if($element=='meta-data'){
		if(count($arrayFrom[$element])>0){
			echo "--------------add $element nodes----------------------\n";
			foreach($arrayFrom[$element] as $node){
				$array = false;
				$hasAttr = false;
				$nodeName=false;
				$nodeValue=false;
				foreach ($node->attributes as $attr) {
					$nodeName = trim($attr->nodeName);
					$nodeValue = trim($attr->nodeValue);
					echo "nodeName:".$nodeName."-------nodeValue:".$nodeValue."\n";
					if('android:name' == $nodeName && $metaDataArray[$nodeValue]){
						$hasAttr = true;
						echo "hasAttr\n";
						break;
					}
  		  		}
  		  		if($hasAttr){
  		  			$metaDataValue = str_replace("\n","",$metaDataArray[$nodeValue]);
  		  			echo "set ".$nodeValue."  ".$metaDataValue."\n";
  		  			$node->setAttribute('android:value',$metaDataValue);
  		  			unset($metaDataArray[$nodeValue]);
  		  		}
			}
			echo "metaData left: \n";
			print_r($metaDataArray);
			foreach(array_keys($metaDataArray) as $key){
				$node = $dom->createElement('meta-data');
				$node->setAttribute('android:name',$key);
				$node->setAttribute('android:value',str_replace("\n","",$metaDataArray[$key]));
				addApplicationSubNode($node,$dom);
			}
			echo '------------------------------------------------------'."\n";
		}
	}
	return $dom;
}
?>
