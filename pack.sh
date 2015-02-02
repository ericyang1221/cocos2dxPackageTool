#!/bin/bash
#P or P for portrait, l or L for landscape.
#./pack.sh cocosSample2.0 Mobage_CN_Native_SDK_1.3.9.4.2_Android P TF2
home=`pwd`
gameDir="${1}"
allianceDir="${2}/Android"
gameName="${4}"
metaDataConf="$allianceDir/${gameName}params/metaData.conf"
gameInfo="$allianceDir/${gameName}params/gameInfo.conf"
channelInfo="$allianceDir/${gameName}params/channelInfo.conf"
customManifestInfo="$allianceDir/${gameName}params/customManifestInfo.conf"
rmlibsInfo="$allianceDir/${gameName}params/rmlibs.conf"
orientation="${3}"
customFileDir="$allianceDir/${gameName}params/customFiles/"

if [ ! -d "$gameDir" ]; 
then
echo "$gameDir not found!"
exit 1
fi

if [ ! -d "$allianceDir" ]; 
then
echo "$allianceDir not found!"
exit 1
else
	mobageNativeLib=$allianceDir/MobageNativeSDKLib
	mobageNativeSample=$allianceDir/MobageNativeSDKSampleActivity
	if [ ! -d "$mobageNativeLib" ];
	then
	echo "$mobageNativeLib not found!"
	exit 1
	fi
	if [ ! -d "$mobageNativeSample" ];
	then
	echo "$mobageNativeSample not found!"
	exit 1
	fi
fi

destBasePath="release/${gameDir}_$allianceDir"
echo "rm -rf $destBasePath"
rm -rf $destBasePath
destGamePath="$destBasePath/$gameDir"
mkdir -p $destGamePath
echo "cp -R $gameDir/ $destGamePath/"
cp -R $gameDir/ $destGamePath/

destLibDir="$destBasePath/MobageNativeSDKLib"
mkdir -p $destLibDir
echo "cp -R $mobageNativeLib/ $destLibDir/"
cp -R $mobageNativeLib/ $destLibDir/

rsync -vzrtopgu -progress $mobageNativeLib/assets/ $destGamePath/assets/
#rsync -vzrtopgu -progress $mobageNativeLib/libs/armeabi/ $destGamePath/libs/armeabi/
#rsync -vzrtopgu -progress $mobageNativeSample/libs/ $destGamePath/libs/
rsync -vzrtopgu -progress $customFileDir $destGamePath/

cp -R $mobageNativeLib/res/xml/ $destGamePath/res/xml/

sed -i '' "s/^\(android\.library\.reference\.1=\).*/\1\.\.\/\.\.\/\.\.\/\.\.\/java/" $destGamePath/project.properties
#tmpDestLibDir=` echo $destLibDir | sed 's#\/#\\\/#g'`
sed -i '' "s/^\(android\.library\.reference\.2=\).*/\1\.\.\/MobageNativeSDKLib/" $destGamePath/project.properties

php pre_pack.php $destGamePath/AndroidManifest.xml
mv PrePackAndroidManifest.xml $destGamePath/AndroidManifest.xml
php pack.php $mobageNativeSample/AndroidManifest.xml $destGamePath/AndroidManifest.xml $orientation
mv ResultAndroidManifest.xml $destGamePath/AndroidManifest.xml
php after_pack.php $destGamePath/AndroidManifest.xml $metaDataConf
mv AfterPackAndroidManifest.xml $destGamePath/AndroidManifest.xml

php process_market.php $destGamePath/res/xml/mobage_market.xml $gameInfo
mv processed_mobage_market.xml $destGamePath/res/xml/mobage_market.xml
php process_alliance.php $destGamePath/res/xml/mobage_alliance.xml $channelInfo
mv processed_mobage_alliance.xml $destGamePath/res/xml/mobage_alliance.xml

for line in `cat $customManifestInfo` 
do
	sed -i '' "s/${line%=*}/${line#*=}/g" $destGamePath/AndroidManifest.xml
	sed -i '' "s/${line%=*}/${line#*=}/g" packagename
done

fromPackageName=`sed -n 1p packagename`
toPackageName=`sed -n 2p packagename`
sed -i '' "s/$fromPackageName/$toPackageName/g" $destGamePath/AndroidManifest.xml
if [ ! -z "$fromPackageName" ] && [ ! -z "$toPackageName" ]
then
	echo "change R..."
	cd $destGamePath/src
	filelist=`find . -type f`
	for filename in $filelist
	do
		if [ "${filename##*.}" == "java" ]
		then
			echo "sed -i '' \"s/$fromPackageName/$toPackageName/g\" $filename"
			sed -i '' "s/$fromPackageName/$toPackageName/g" "$filename"
		fi
	done
	cd -
fi

for line in `cat $rmlibsInfo` 
do
	echo "rm -rf $destGamePath/libs/$line*"
	rm -rf $destGamePath/libs/$line*
done

rm -rf $destGamePath/gen/*
cd $destLibDir
android update project --path .
cd -
cd $destGamePath
android update project --path .
echo "ant clean"
ant clean
echo "ant debug"
ant debug

echo 'RELEASE SUCCESSFUL'
echo 'Release at : '$home"/"$destBasePath