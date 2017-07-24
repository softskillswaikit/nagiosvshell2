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
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=11
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=12
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=13
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=14
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=15
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=16
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=29
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=30
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=33
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=34
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=35
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=36
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=39
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=40
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=41
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=42
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=43
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=44
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=45
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=46
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=47
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=48
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=53
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=54
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=55
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=56
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=57
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=58
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=59
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=60
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=61
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=62
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=65
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=66
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=67
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=68
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=69
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=70
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=71
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=72
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=73
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=74
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=75
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=76
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=77
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=118
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=119
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=122
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=127
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=134
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=135
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=150

now=`date +%s`
commandfile='/usr/local/nagios/var/rw/nagios.cmd'

# Written by : Low Zhi Jian (UTAR)
commands=$1

/usr/bin/printf "[%lu] $commands\n" $now > $commandfile
echo "The command run successfully !"