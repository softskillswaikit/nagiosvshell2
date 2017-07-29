#!/bin/sh
# This shell script is used to submit the SCHEDULE_HOST_DOWNTIME or SCHEDULE_HOST_SVC_DOWNTIME command to Nagios.

# These command are adapted from: 
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=118
# and 
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=122
now=`date +%s`
commandfile='/usr/local/nagios/var/rw/nagios.cmd'

# Written by : Low Zhi Jian (UTAR)
host_name=$1
start_time=$2
end_time=$3
fixed=$4
trigger_id=$5
duration=$6
author=$7
comments=$8
types=$9

if [ $types = "host" ]
then
	/bin/printf "[%lu] SCHEDULE_HOST_DOWNTIME;$host_name;$start_time;$end_time;$fixed;$trigger_id;$duration;$author;$comments\n" $now > $commandfile
	echo "The SCHEDULE_HOST_DOWNTIME command run successfully !"
else
	/bin/printf "[%lu] SCHEDULE_HOST_SVC_DOWNTIME;$host_name;$start_time;$end_time;$fixed;$trigger_id;$duration;$author;$comments\n" $now > $commandfile
	echo "The SCHEDULE_HOST_SVC_DOWNTIME command run successfully !"
fi