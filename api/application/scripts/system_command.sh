#!/bin/sh
# This shell script is used to execute one of the commands below based on request.

# These commands are adapted from: 
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=1
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=2
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=3
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=4
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=5
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=6
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=7
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=8
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=9
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=10
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=29
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=41
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=42
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=43
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=44
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=47
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=48
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=55
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=56
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=57
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=58
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=65
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=66
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=67
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=68
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=69
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=70
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=73
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=74
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=118
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=122

now=`date +%s`
commandfile='/usr/local/nagios/var/rw/nagios.cmd'

# Written by : Low Zhi Jian (UTAR)
commands=$1

/usr/bin/printf "[%lu] $commands\n" $now > $commandfile
echo "The command run successfully !"