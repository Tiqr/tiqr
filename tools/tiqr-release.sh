#!/bin/bash

if [ -z $1 ] ; then
  echo "Usage $0 versionnumber, e.g. $0 1.0"
  exit
fi

VERSION=$1

svn copy https://svn.surfnet.nl/svn/tiqr/trunk https://svn.surfnet.nl/svn/tiqr/tags/release-$VERSION -m "Tagging the $VERSION release of the 'Tiqr' project."

mkdir tiqr-tmp-src

cd tiqr-tmp-src

svn export https://svn.surfnet.nl/svn/tiqr/trunk/Android tiqr-android-$VERSION
tar czvf tiqr-android-$VERSION.tar.gz tiqr-android-$VERSION
mv tiqr-android-$VERSION.tar.gz .. 

svn export https://svn.surfnet.nl/svn/tiqr/trunk/iPhone tiqr-iphone-$VERSION
tar czvf tiqr-iphone-$VERSION.tar.gz tiqr-iphone-$VERSION
mv tiqr-iphone-$VERSION.tar.gz .. 

svn export https://svn.surfnet.nl/svn/tiqr/trunk/Server/libTiqr tiqr-server-library-$VERSION
tar czvf tiqr-server-library-$VERSION.tar.gz tiqr-server-library-$VERSION
mv tiqr-server-library-$VERSION.tar.gz .. 

svn export https://svn.surfnet.nl/svn/tiqr/trunk/Server/simplesamlphp-demo tiqr-server-simplesamlphp-demo-$VERSION
tar czvf tiqr-server-simplesamlphp-demo-$VERSION.tar.gz tiqr-server-simplesamlphp-demo-$VERSION
mv tiqr-server-simplesamlphp-demo-$VERSION.tar.gz .. 

svn export https://svn.surfnet.nl/svn/tiqr/trunk/Server/simplesamlphp-modules/authTiqr tiqr-server-simplesamlphp-authtiqr-$VERSION
tar czvf tiqr-server-simplesamlphp-authtiqr-$VERSION.tar.gz tiqr-server-simplesamlphp-authtiqr-$VERSION
mv tiqr-server-simplesamlphp-authtiqr-$VERSION.tar.gz .. 

svn export https://svn.surfnet.nl/svn/tiqr/trunk/Server/zfTiqr tiqr-server-zendframework-$VERSION
tar czvf tiqr-server-zendframework-$VERSION.tar.gz tiqr-server-zendframework-$VERSION
mv tiqr-server-zendframework-$VERSION.tar.gz .. 

cd ..
rm -rf tiqr-tmp-src