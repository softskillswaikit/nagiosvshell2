#!/bin/sh
# This shell script is used to submit the ADD_HOST_COMMENT or ADD_SVC_COMMENT command to Nagios.

# These command are adapted from: 
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=1
# and 
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=2
now=`date +%s`
commandfile='/usr/local/nagios/var/rw/nagios.cmd'

# Written by : Low Zhi Jian (UTAR)
host_name=$1
service_description=$2
persistent=$3
author=$4
comments=$5
types=$6

if [ $types = "host" ]
then
	/bin/printf "[%lu] ADD_HOST_COMMENT;$host_name;$persistent;$author;$comments\n" $now > $commandfile
	echo "The ADD_HOST_COMMENT command run successfully !"
else
	/bin/printf "[%lu] ADD_SVC_COMMENT;$host_name;$service_description;$persistent;$author;$comments\n" $now > $commandfile
	echo "The ADD_SVC_COMMENT command run successfully !"
fi


