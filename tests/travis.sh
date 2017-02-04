#!/bin/bash

PHP_BINARY="php"

while getopts "p:" OPTION 2> /dev/null; do
	case ${OPTION} in
		p)
			PHP_BINARY="$OPTARG"
			;;
	esac
done

./tests/lint.sh -p "$PHP_BINARY"

if [ $? -ne 0 ]; then
	echo Lint scan failed!
	exit 1
fi

cd tests/plugins
"$PHP_BINARY" ./PocketMine-DevTools/src/DevTools/ConsoleScript.php --make ./PocketMine-DevTools --relative ./PocketMine-DevTools --out ../../DevTools.phar
cd ../..

"$PHP_BINARY" DevTools.phar --make src,vendor --relative ./ --entry src/pocketmine/PocketMine.php --out PocketMine-MP.phar
if [ -f PocketMine-MP.phar ]; then
    echo Server phar created successfully.
else
    echo Server phar was not created!
    exit 1
fi


mkdir plugins
mv DevTools.phar plugins
cp -r tests/plugins/PocketMine-TesterPlugin ./plugins

"$PHP_BINARY" src/pocketmine/PocketMine.php --no-wizard --disable-ansi --disable-readline --debug.level=2

result=$(grep 'TesterPlugin' server.log | grep 'Finished' | grep -v 'PASS')
if [ "$result" != "" ]; then
    echo "$result"
    echo Some tests did not complete successfully, changing build status to failed
    exit 1
else
    echo All tests passed
fi
