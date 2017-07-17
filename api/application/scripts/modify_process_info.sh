#!/bin/sh
# This shell script is used to execute one of the commands below based on request.

# These command are adapted from: 
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=7
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=8
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=9
# https://old.nagios.org/developerinfo/externalcommands/commandinfo.php?command_id=10
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

now=`date +%s`
commandfile='/usr/local/nagios/var/rw/nagios.cmd'

# Written by : Low Zhi Jian (UTAR)
types=$1

case $types in
	'shutdown_nagios')	
		/bin/printf "[%lu] SHUTDOWN_PROGRAM\n" $now > $commandfile
		echo "The SHUTDOWN_PROGRAM command run successfully !"
		;;
	'restart_nagios')	
		/bin/printf "[%lu] RESTART_PROGRAM\n" $now > $commandfile
		echo "The RESTART_PROGRAM command run successfully !"
		;;
	'enable_notification')	
		/bin/printf "[%lu] ENABLE_NOTIFICATIONS\n" $now > $commandfile
		echo "The ENABLE_NOTIFICATIONS command run successfully !"
		;;
	'disable_notification')	
		/bin/printf "[%lu] DISABLE_NOTIFICATIONS\n" $now > $commandfile
		echo "The DISABLE_NOTIFICATIONS command run successfully !"
		;;
	'start_service_check')
		/bin/printf "[%lu] START_EXECUTING_SVC_CHECKS\n" $now > $commandfile
		echo "The START_EXECUTING_SVC_CHECKS command run successfully !"
		;;
	'stop_service_check')
		/bin/printf "[%lu] STOP_EXECUTING_SVC_CHECKS\n" $now > $commandfile
		echo "The STOP_EXECUTING_SVC_CHECKS command run successfully !"
		;;
	'start_passive_service_check')
		/bin/printf "[%lu] START_ACCEPTING_PASSIVE_SVC_CHECKS\n" $now > $commandfile
		echo "The START_ACCEPTING_PASSIVE_SVC_CHECKS command run successfully !"
		;;
	'stop_passive_service_check')
		/bin/printf "[%lu] STOP_ACCEPTING_PASSIVE_SVC_CHECKS\n" $now > $commandfile
		echo "The STOP_ACCEPTING_PASSIVE_SVC_CHECKS command run successfully !"
		;;
	'enable_event_handler')
		/bin/printf "[%lu] ENABLE_EVENT_HANDLERS\n" $now > $commandfile
		echo "The ENABLE_EVENT_HANDLERS command run successfully !"
		;;
	'disable_event_handler')
		/bin/printf "[%lu] DISABLE_EVENT_HANDLERS\n" $now > $commandfile
		echo "The DISABLE_EVENT_HANDLERS command run successfully !"
		;;
	'start_obsess_over_svc')
		/bin/printf "[%lu] START_OBSESSING_OVER_SVC_CHECKS\n" $now > $commandfile
		echo "The START_OBSESSING_OVER_SVC_CHECKS command run successfully !"
		;;
	'stop_obsess_over_svc')
		/bin/printf "[%lu] STOP_OBSESSING_OVER_SVC_CHECKS\n" $now > $commandfile
		echo "The STOP_OBSESSING_OVER_SVC_CHECKS command run successfully !"
		;;
	'start_obsess_over_host')
		/bin/printf "[%lu] START_OBSESSING_OVER_HOST_CHECKS\n" $now > $commandfile
		echo "The START_OBSESSING_OVER_HOST_CHECKS command run successfully !"
		;;
	'stop_obsess_over_host')
		/bin/printf "[%lu] STOP_OBSESSING_OVER_HOST_CHECKS\n" $now > $commandfile
		echo "The STOP_OBSESSING_OVER_HOST_CHECKS command run successfully !"
		;;
	'enable_performance')
		/bin/printf "[%lu] ENABLE_PERFORMANCE_DATA\n" $now > $commandfile
		echo "The ENABLE_PERFORMANCE_DATA command run successfully !"
		;;
	'disable_performance')
		/bin/printf "[%lu] DISABLE_PERFORMANCE_DATA\n" $now > $commandfile
		echo "The DISABLE_PERFORMANCE_DATA command run successfully !"
		;;
	'start_host_check')
		/bin/printf "[%lu] START_EXECUTING_HOST_CHECKS\n" $now > $commandfile
		echo "The START_EXECUTING_HOST_CHECKS command run successfully !"
		;;
	'stop_host_check')
		/bin/printf "[%lu] STOP_EXECUTING_HOST_CHECKS\n" $now > $commandfile
		echo "The STOP_EXECUTING_HOST_CHECKS command run successfully !"
		;;
	'start_passive_host_check')
		/bin/printf "[%lu] START_ACCEPTING_PASSIVE_HOST_CHECKS\n" $now > $commandfile
		echo "The START_ACCEPTING_PASSIVE_HOST_CHECKS command run successfully !"
		;;
	'stop_passive_host_check')
		/bin/printf "[%lu] STOP_ACCEPTING_PASSIVE_HOST_CHECKS\n" $now > $commandfile
		echo "The STOP_ACCEPTING_PASSIVE_HOST_CHECKS command run successfully !"
		;;
	'enable_flap')
		/bin/printf "[%lu] ENABLE_FLAP_DETECTION\n" $now > $commandfile
		echo "The ENABLE_FLAP_DETECTION command run successfully !"
		;;
	'disable_flap')
		/bin/printf "[%lu] DISABLE_FLAP_DETECTION\n" $now > $commandfile
		echo "The DISABLE_FLAP_DETECTION command run successfully !"
		;;
esac 