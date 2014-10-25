#!/bin/bash
SETUPED=0

function fatal
{
	echo "FATAL: $1" >&2
	exit 1
}

function check
{
    if [ `echo $DEF_SOURCE_FOLDER | cut -c 1,1` != "/" ] || [ `echo $ROUTER_SOURCE_FOLDER | cut -c 1,1` != "/" ] || [ `echo $TARGET_FOLDER | cut -c 1,1` != "/" ]; then
        fatal "USE ABSOLUTE PATH FOR FOLDERS"
    fi

    if [ ! -d "$DEF_SOURCE_FOLDER" ]; then
	    fatal "def folder doesn't exist"
    fi

    if [ ! -d "$ROUTER_SOURCE_FOLDER" ]; then
    	fatal "router folder doesn't exist"
    fi

    if [ -d "$TARGET_FOLDER" ];then
    	fatal "target folder[$TARGET_FOLDER] already exists"
    fi
}

function setup
{
    mkdir -p $TARGET_FOLDER > /dev/null 2>&1
    if [ $? -ne 0 ]; then
    	fatal "failed to create [target] folder in current folder"
    fi
    SETUPED=1
}

if [ $# -lt 2 ]; then
	fatal "usage: $0 <source_folder> <target_folder>"
fi

#verify source & target folder
DEF_SOURCE_FOLDER=$1"/def"
ROUTER_SOURCE_FOLDER=$1"/router"
TARGET_FOLDER=$2
SCRIPT_FOLDER=$(cd "$(dirname "$0")"; pwd)

check
setup

#go through object file one by one
cd $DEF_SOURCE_FOLDER
for file in $(find ./ -name "*.xml")
do
	file=$(echo "$file" | sed 's/\.xml$//g' | sed 's/^\.\///g')
	mod_name=$(basename "$file")
	mod_path=$(dirname "$file")
	if [ "$mod_path" == "." ]; then
		mod_path=""
	fi
	standard_mod_path=$(echo "$mod_path" | sed 's/\//\./g')
	standard_mod_path=${standard_mod_path}.${mod_name}
    mkdir -p $TARGET_FOLDER/$mod_path >/dev/null 2>&1

	if [ ! -f "../router/${mod_path}/${mod_name}.current.xml" ]; then
		fatal "missing current router file"
	fi

	has_obsolete_router=0
	if [ -f "../router/${mod_path}/${mod_name}.obsolete.xml" ]; then
		has_obsolete_router=1
	fi

#frontend compiling
	php $SCRIPT_FOLDER/../frontend/queue_parser.php "./${mod_path}/${mod_name}.xml" "${standard_mod_path}" > $TARGET_FOLDER/tmp.def
	php $SCRIPT_FOLDER/../frontend/router_parser.php "../router/${mod_path}/${mod_name}.current.xml" "${standard_mod_path}" > $TARGET_FOLDER/tmp.router.current
	if [ $has_obsolete_router -eq 1 ]; then
	    php $SCRIPT_FOLDER/../frontend/router_parser.php "../router/${mod_path}/${mod_name}.obsolete.xml" "${standard_mod_path}" > $TARGET_FOLDER/tmp.router.obsolete
	fi

#validate result
	result=$(php ${SCRIPT_FOLDER}/../tools/validator.php queue ${TARGET_FOLDER}/tmp.def)
	if [ "$result" != "YES" ]; then
		fatal "bad syntax in def xml of ${standard_mod_path}"
	fi
	result=$(php ${SCRIPT_FOLDER}/../tools/validator.php router ${TARGET_FOLDER}/tmp.router.current)
	if [ "$result" != "YES" ]; then
		fatal "bad syntax in current router xml of ${standard_mod_path}"
	fi
	if [ $has_obsolete_router -eq 1 ]; then
	    result=$(php ${SCRIPT_FOLDER}/../tools/validator.php router ${TARGET_FOLDER}/tmp.router.obsolete)
		if [ "$result" != "YES" ]; then
			fatal "bad syntax in obsolete router xml of ${standard_mod_path}"
		fi
	fi

#php backend compiling
	obsolete_router_object=""
	if [ $has_obsolete_router -eq 1 ]; then
		obsolete_router_object="${TARGET_FOLDER}/tmp.router.obsolete"
	fi

	php $SCRIPT_FOLDER/../backend/queue_renderer.php "${SCRIPT_FOLDER}/../backend/queue/queue.html" "${TARGET_FOLDER}/tmp.def" "${TARGET_FOLDER}/tmp.router.current" "${obsolete_router_object}" > ${TARGET_FOLDER}/${mod_path}/${mod_name}.queue.php
	php $SCRIPT_FOLDER/../backend/queue_renderer.php "${SCRIPT_FOLDER}/../backend/queue/router.html" "${TARGET_FOLDER}/tmp.def" "${TARGET_FOLDER}/tmp.router.current" "${obsolete_router_object}" > ${TARGET_FOLDER}/${mod_path}/${mod_name}.router.php

	for component in $(php $SCRIPT_FOLDER/../tools/component_inspector.php "${TARGET_FOLDER}/tmp.router.current")
	do
		media=$(php $SCRIPT_FOLDER/../tools/media_inspector.php $component "${TARGET_FOLDER}/tmp.router.current")
		if [ "$media" == "" ]; then
			fatal "unknown media in current $component of $standard_mod_path"
		fi
		php $SCRIPT_FOLDER/../backend/queue_renderer.php "${SCRIPT_FOLDER}/../backend/queue/${component}_${media}.html" "${TARGET_FOLDER}/tmp.def" "${TARGET_FOLDER}/tmp.router.current" "" > ${TARGET_FOLDER}/${mod_path}/${mod_name}.${component}.current.php
	done

	if [ $has_obsolete_router -eq 1 ]; then
		for component in $(php $SCRIPT_FOLDER/../tools/component_inspector.php "${TARGET_FOLDER}/tmp.router.obsolete")
		do
			media=$(php $SCRIPT_FOLDER/../tools/media_inspector.php $component "${TARGET_FOLDER}/tmp.router.obsolete")
			if [ "$media" == "" ]; then
				fatal "unknown media in obsolete $component of $standard_mod_path"
			fi
			php $SCRIPT_FOLDER/../backend/queue_renderer.php "${SCRIPT_FOLDER}/../backend/queue/${component}_${media}.html" "${TARGET_FOLDER}/tmp.def" "" "${TARGET_FOLDER}/tmp.router.obsolete" > ${TARGET_FOLDER}/${mod_path}/${mod_name}.${component}.obsolete.php
		done
	fi

    rm -f ${TARGET_FOLDER}/tmp.def
    rm -f ${TARGET_FOLDER}/tmp.router.current
    rm -f ${TARGET_FOLDER}/tmp.router.obsolete
done
cp ${SCRIPT_FOLDER}/../libs/queue_loader.php $TARGET_FOLDER
exit 0
