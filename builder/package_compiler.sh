#!/bin/bash
if [ $# -lt 3 ];
then
	echo "usage: $0 <src_package> <target_package> <type:object|set|queue|index>"
	exit 1;
fi

PACKAGE_SOURCE=$1
PACKAGE_TARGET=$2
TYPE=$3
SEED=osic_$$

#step 0. validate package_file path
if [ `echo $PACKAGE_SOURCE | cut -c 1,1` != "/" ] || [ `echo $PACKAGE_TARGET | cut -c 1,1` != "/" ];
then
	echo "PLEASE USE ABSOLUTE PATH FOR BOTH INPUT&OUTPUT"
	exit 1
fi

#step 1. setup env
mkdir -p /tmp/$SEED/src
cp $PACKAGE_SOURCE /tmp/$SEED/src/src.tgz
cd /tmp/$SEED/src/
tar zxf ./src.tgz
rm -f ./src.tgz

#step 2. building
cd /home/cloud/services/osi_builder/builder/
./build_${TYPE}.sh /tmp/$SEED/src  /tmp/$SEED/dst

#step 3. packup result files to packages
if [ $? -eq 0 ];
then
	cd /tmp/$SEED/dst
	tar czf $PACKAGE_TARGET ./*
	chmod 777 $PACKAGE_TARGET
fi

#step 4. clean up
rm -rf /tmp/$SEED
