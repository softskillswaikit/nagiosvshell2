#!/bin/sh
# This shell script is used to execute one of the commands below based on request.

# These command are adapted from: 
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=5
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=6
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=29

now=`date +%s`
commandfile='/usr/local/nagios/var/rw/nagios.cmd'

# Written by : Low Zhi Jian (UTAR)
host_name=$1
service_description=$2
checktime=$3
types=$4

if [ $types = "enable_svc_check" ]
then
	/bin/printf "[%lu] ENABLE_SVC_CHECK;$host_name;$service_description\n" $now > $commandfile
	echo "The ENABLE_SVC_CHECK command run successfully !"
elif [ $types = 'disable_svc_check' ]
then
	/bin/printf "[%lu] DISABLE_SVC_CHECK;$host_name;$service_description\n" $now > $commandfile
	echo "The DISABLE_SVC_CHECK command run successfully !"
else
	/bin/printf "[%lu] SCHEDULE_SVC_CHECK;$host_name;$service_description;$checktime\n" $now > $commandfile
	echo "The SCHEDULE_SVC_CHECK command run successfully !"
fi