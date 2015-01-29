<?php
require 'pack_base.php';

$originDom = new DOMDocument();
$originDom->load($argv[1]);
$originDom->preserveWhiteSpace = false;
$originDom->formatOutput = true;
$array = getManifest($originDom->documentElement);

mergeManifest($array,$originDom);

function mergeManifest($arrayFrom,$dom){
	echo "Clear MobagePay ...\n";
	foreach(array_keys($arrayFrom) as $key){
		$dom = clearMobagePay($key,$arrayFrom,$dom);
	}
  	$dom->save('PrePackAndroidManifest.xml');
}

function clearMobagePay($element,$arrayFrom,$dom){
	if($element=='activity'||$element=='receiver'||$element=='service'||$element=='uses-permission'||$element=='meta-data'){
		if(count($arrayFrom[$element])>0){
			echo "--------------remove $element nodes----------------------\n";
			foreach($arrayFrom[$element] as $node){
				$array = false;
				foreach ($node->attributes as $attr) {
					$nodeName = trim($attr->nodeName);
					$nodeValue = trim($attr->nodeValue);
					if($nodeName=='android:name'&&($nodeValue=='com.unionpay.uppay.PayActivityEx'
					||$nodeValue=='com.unionpay.uppay.PayActivity'
					||$nodeValue=='com.alipay.android.app.sdk.WapPayActivity'
					||$nodeValue=='com.alipay.android.mini.window.sdk.MiniPayActivity'
					||$nodeValue=='com.alipay.android.mini.window.sdk.MiniWebActivity'
					||$nodeValue=='com.alipay.android.app.MspService'
					||$nodeValue=='com.denachina.mobage.MobagePayActivity'
					)){
						echo "remove node ".$nodeValue."\n";
						$node->parentNode->removeChild($node);
					}
  		  		}
			}
			echo '------------------------------------------------------'."\n";
		}
	}
	return $dom;
}
?>
