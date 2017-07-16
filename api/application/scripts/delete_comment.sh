#!/bin/sh
# This shell script is used to submit the DEL_HOST_COMMENT or DEL_SVC_COMMENT command to Nagios.

# These command are adapted from: 
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=3
# and 
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=4
now=`date +%s`
commandfile='/usr/local/nagios/var/rw/nagios.cmd'

# Written by : Low Zhi Jian (UTAR)
comment_id=$1
types=$2

if [ $types = "host" ]
then
	/bin/printf "[%lu] DEL_HOST_COMMENT;$comment_id\n" $now > $commandfile
	echo "The DEL_HOST_COMMENT command run successfully !"
else
	/bin/printf "[%lu] DEL_SVC_COMMENT;$comment_id\n" $now > $commandfile
	echo "The DEL_SVC_COMMENT command run successfully !"
fi


