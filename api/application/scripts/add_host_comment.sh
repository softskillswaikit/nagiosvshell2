#!/bin/sh
# This shell script is used to submit the ADD_HOST_COMMENT command to Nagios.

# These command are adapted from: 
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=1
now = `date +%s`
commandfile = '/usr/local/nagios/var/rw/nagios.cmd'

# Written by : Low Zhi Jian (UTAR)
host_name=$1
persistent=$2
author=$3
comments=$4

ADD_HOST_COMMENT;$host_name;$persistent;$author;$comments $now > $commandfile

echo "The command run successfully !"

