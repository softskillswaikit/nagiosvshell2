<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class API extends VS_Controller
{

    private $domain;
    private $user;
    private $user_domain;
    private $passwd;
    private $zippath;
    private $zipexpire;
    private $zipmem;

    public function __construct()
    {
        parent::__construct();
        $this->config->load('sftp_config');
        $this->domain = $this->config->item('sftp_domain');
        $this->user = $this->config->item('sftp_user');
        $this->user_domain = $this->config->item('sftp_user_domain');
        $this->passwd = $this->config->item('sftp_password');
        $this->zippath = $this->config->item('zip_path');
        $this->zipexpire = $this->config->item('zip_expire');
        $this->zipmem = $this->config->item('zip_memory');
        log_message('debug', 'SFTP config retrieved successfully');
        log_message('debug', 'Checking downloaded zip files expire date');

        $path = $this->zippath;
        $filelist = $this->session->userdata('files');
        if ($handle = opendir($path)) 
        {
            while (false !== ($file = readdir($handle)))
            {
                if ((time()-filectime($path.$file)) > intval($this->zipexpire)) // expire time in seconds
                {
                    if (preg_match('/\.zip$/i', $file)) 
                    {
                        if(unlink($path.$file))
                            log_message('debug', $file.' expired and deleted successfully.');
                        else
                            log_message('error', $file.' cannot be deleted.');
                    }
                }
            }

            $removelist = array();
            if(!empty($filelist))
            {
                foreach($filelist as $key => $filename)
                {
                    if(!file_exists($path.$filename))
                    {
                        $removelist[] = $key;
                    }
                }
            }

            foreach($removelist as $key)
            {
                unset($filelist[$key]);
            }

            $this->session->set_userdata('files', $filelist);
            //log_message('debug', print_r($filelist, true));
        }
        else 
        {
            log_message('error', 'Failed to access download directory.');
        }
    }


    /**
     * Default to Tactical overview data
     */
    public function index()
    {
        $Data = $this->tac_data->get_tac_data();
        $Data['username'] = $this->nagios_user->get_username();
        $this->output($Data);
    }


    /**
     * Retrieve program status
     */
    public function programstatus()
    {
        $Program = $this->nagios_data->get_collection('programstatus');
        $this->output($Program);
    }


    /**
     * Retrieve info status
     * @return [type] [description]
     */
    public function info()
    {
        $Info = $this->nagios_data->get_collection('info');
        $this->output($Info);
    }

    /**
     * Fetch object names
     */
    public function quicksearch()
    {

        $Data = array();

        $hosts = $this->nagios_data->get_collection('hoststatus');
        $services = $this->nagios_data->get_collection('servicestatus');
        $hostgroups = $this->nagios_data->get_collection('hostgroup');
        $servicegroups = $this->nagios_data->get_collection('servicegroup');

        foreach($hosts as $host)
        {
            $Data[] = $this->quicksearch_item('host', $host->host_name, $host->host_name);
        }

        foreach($services as $service)
        {
            $Data[] = $this->quicksearch_item('service', $service->service_description.' on '.$service->host_name, $service->host_name.'/'.$service->service_description);
        }

        foreach($hostgroups as $hostgroup)
        {
            $Data[] = $this->quicksearch_item('hostgroup', $hostgroup->alias, $hostgroup->hostgroup_name);
        }

        foreach($servicegroups as $servicegroup)
        {
            $Data[] = $this->quicksearch_item('servicegroup', $servicegroup->alias, $servicegroup->servicegroup_name);
        }

        $this->output($Data);
    }

    private function quicksearch_item($type, $name, $uri)
    {
        return array(
            'type' => $type,
            'name' => $name,
            'uri' => $uri
        );
    }


    /**
     * Retrieve tactical overview data
     */
    public function tacticaloverview() 
    {
        $Data = $this->tac_data->get_tac_data();
        $this->output($Data);
    }


    /**
     * Fetch /etc/vshell2.conf file values, as parsed by CodeIgniter
     */
    function vshellconfig() 
    {
        $config = array(
            'baseurl'        => BASEURL,
            'cgicfg'         => CGICFG,
            'coreurl'        => COREURL,
            'lang'           => LANG,
            'objectsfile'    => OBJECTSFILE,
            'statusfile'     => STATUSFILE,
            'ttl'            => TTL,
            'updateinterval' => UPDATEINTERVAL
        );

        $this->output($config);
    }


    //Written by Soon Wei Liang
    /**
     * Fetch name based on type : host, hostgroup, service, servicegroup
     *
     */
    public function name()
    {
        $hostname = array();
        $hostgroupname = array();
        $servicename = array();
        $servicegroupname = array();
        $runningstatename = array();
        $hostresourcename = array();
        $allName;

        
        //all host name
        $hosts = $this->nagios_data->get_collection('hoststatus');

        foreach($hosts as $host)
        {
            $hostname[] = $host->host_name;
        }

        $allName['host'] = $hostname;
    

        //all hostgroup name    
        $hostgroups = $this->nagios_data->get_collection('hostgroup');

        foreach($hostgroups as $hostgroup)
        {
            $hostgroupname[] = $hostgroup->alias;
        }

        $allName['hostgroup'] = $hostgroupname;     
    

        //all service name
        $services = $this->nagios_data->get_collection('servicestatus');

        foreach ($services as $service)
        {
            $servicename[] = array('host' =>$service->host_name, 'service'=> $service->service_description);
        }

        $allName['service'] = $servicename;
    

        //all service group name
        $servicegroups = $this->nagios_data->get_collection('servicegroup');

        foreach($servicegroups as $servicegroup)
        {
            $servicegroupname[] = $servicegroup->alias;
        }

        $allName['servicegroup'] = $servicegroupname;


        //all host resource
        $hostresources = $this->nagios_data->get_collection('hostresource');

        foreach ($hostresources as $hostresource) 
        {
            $hostresourcename[] = array('host'=> $hostresource->host_name, 'service'=> $hostresource->service_description);
        }

        $allName['hostresource'] = $hostresourcename;

        //all service running state
        $runningstates = $this->nagios_data->get_collection('runningstate');

        foreach ($runningstates as $runningstate)
        {
            $runningstatename[] = array('host'=> $runningstate->host_name, 'service' => $runningstate->service_description);
        }

        $allName['runningstate'] = $runningstatename;
        
        $this->output($allName); 
    }

    
    
    /**
     * Fetch availability
     * 
     * @param String $type
     * @param string $period
     * @param string $start
     * @param string $end
     * @param String $hostservice
     * @param bool $initialState
     * @param bool $stateRetention
     * @param bool $assumeState
     * @param bool $includeSoftState
     * @param String $firstAssumedHost
     * @param String $firstAssumedService
     * @param String $backTrack
     */
     public function availability($type, $repiod, $start, $end, $hostservice, $initialState, $stateRetention, $assumeState, $includeSoftState, $firstAssumedHost='', $firstAssumedService='', $backTrack)
    {
        
    }

    /**
     * Fetch trend
     *
     * @param int $reportType
     * @param string $name
     * @param string $start
     * @param string $end
     * @param bool $initialState
     * @param bool $stateRetention
     * @param bool $assumeState
     * @param bool $includeSoftState
     * @param String $firstAssumedHost
     * @param int $backTrack
     * @param bool suppressImage
     * @param bool suppressPopups
     */
    public function trend($reportType, $name='', $start='', $end='', $initialState, $stateRetention, $assumeState, $includeSoftState, $firstAssumedHost, $backTrack, $suppressImage, $suppressPopups)
    {
        $Trend = array();
        

        //host
        if($reportType == 1)
        {
            $Trend = $this->trend_data->get_trend_host();
        }

        //service
        else if($reportType == 2)
        {
            $Trend = $this->trend_data->get_trend_service();
        }

        $this->output($Trend);
    }

    /**
     * Fetch alert history
     *
     * @param String $date
     */
    public function alerthistory($date)
    {
        $AlertHistory = $this->reports_data->get_history_data($date);

        $this->output($AlertHistory);
    }

    /**
     * Fetch alert summary
     *
     * @param string $type
     * @param string $period 
     * @param string $date , for custom period : date in array (start date, end date)
     * @param string $service
     * @param string $logtype
     * @param string $statetype
     * @param string $state
     */
    public function alertsummary($type, $period, $date, $service, $logtype, $statetype, $state)
    {
        //allowed type of alert
        $allowed_types = array(
            'TOP_PRODUCER',
            'ALERT_TOTAL',
            'NORMAL'
        );

        //allowed type of period
        $allowed_periods = array(
            'TODAY',
            'LAST 24 HOURS',
            'YESTERDAY',
            'THIS WEEK',
            'LAST 7 DAYS',
            'LAST WEEK',
            'THIS MONTH',
            'LAST 31 DAYS',
            'LAST MONTH', 
            'THIS YEAR', 
            'LAST YEAR', 
            'CUSTOM'
        );

        //allowed logtype
        $allowed_logtypes = array(
            'HOST ALERT',
            'SERVICE ALERT',
            'ALL ALERT'
        );

        //allowed statetype
        $allowed_statetypes = array(
            'HARD',
            'SOFT',
            'ALL STATE TYPE'
        );

        //check empty inputs
        if(!empty($type) && !empty($period) && !empty($date) && !empty($service) && !empty($logtype) && !empty($statetype) && !empty($state))
        {
            //verify inputs
            if(in_array($type, $allowed_types) && in_array($period, $allowed_periods) && in_array($logtype, $allowed_logtypes) && in_array($statetype, $allowed_statetypes))
            {
                $AlertSummary = $this->reports_data->get_alert_summary($type, $period, $date, $service, $logtype, $statetype, $state);
            }
        }

        $this->output($AlertSummary);
    }

    /**
     * Fetch alert histogram
     *
     * @param string $returnType,   'TOP_PRODUCER', 'ALERT_TOTAL', 'NORMAL'
     * @param string $period,       
     * @param Date $period
     * @param String $breakdown
     * @param String $eventsToGraph
     * @param String $typesToGraph
     * @param bool $stateRetention
     * @param bool $initialStateLogged
     * @param bool $ignoreRepeated
     *
     */
    public function alerthistogram($reportType, $name='', $period, $breakdown='', $eventsToGraph='', $typesToGraph='', $stateRention, $initialStateLogged, $ignoreRepeated)
    {
        $AlertHistogram = $this->alert_histogram_data->get_alert_histogram();

        //Host
        if($reportType == 1)
        {

        }

        //Service
        else if($reportType == 2)
        {

        }

        $this->output($AlertHistogram);
    }


    /**
     * Fetch all event log
     *
     * @param  String $date
     */
    public function eventlogs($date)
    {
        $Eventlogs = array();
        //$date = "1490279712";

        $Data = $this->reports_data->get_event_log($date);

        if(!empty($date) && strlen($date) == 10)
        {
            foreach ($Data as $Eventlog) 
            {
                $Eventlogs[] = $Eventlog;
            }
        }

        $this->output($Eventlogs);
    }

    /**
     * Fetch all notifications
     *
     * @param String $date
     */
    public function notifications($date)
    {
        $Notificaions = array();
        //$date = "1490279712";

        $Data = $this->reports_data->get_notification($date);

        if(!empty($date) && strlen($date) == 10)
        {
            $Data = $this->reports_data->get_notification($date);

            foreach ($Data as $Notification) 
            {
                $Notifications[] = $Notification;
            }
        }

        $this->output($Notifications);
    }

    public function testing()
    {
        $Downtime = array();

        $allowed_types = array(
            'host',
            'svc'
        );

        if(in_array($type, $allowed_types))
        {
            if($type == 'host')
            {
                $Datahostdowntime = $this->nagios_data->get_collection('hostdowntime');
                
                foreach ($Datahostdowntime as $hostdowntime) 
                {
                    $Downtime[] = array('host' => $hostdowntime->host_name, 'entry_time'=> $hostdowntime->entry_time, 'author' => $hostdowntime->author, 'comment'=> $hostdowntime->comment, 'start_time'=> $hostdowntime->start_time, 'end_time' => $hostdowntime->end_time, 'fixed' => $hostdowntime->fixed, 'duration' => $hostdowntime->duration, 'downtime_id' => $hostdowntime->downtime_id, 'trigged_id' => $hostdowntime->triggered_by);
                }
            }
            else
            {
                $Dataservicedowntime = $this->nagios_data->get_collection('servicedowntime');

                foreach ($Dataservicedowntime as $servicedowntime) 
                {
                    $Downtime[] = array('host' => $servicedowntime->host_name, 'service'=> $servicedowntime->service_description, 'entry_time'=> $servicedowntime->entry_time, 'author' => $servicedowntime->author, 'comment'=> $servicedowntime->comment, 'start_time'=> $servicedowntime->start_time, 'end_time' => $servicedowntime->end_time, 'fixed' => $servicedowntime->fixed, 'duration' => $servicedowntime->duration, 'downtime_id' => $servicedowntime->downtime_id, 'trigged_id' => $servicedowntime->triggered_by);
                }

            }
        }

        $this->output($Downtime);
    }

    /**
     * Fetch host or service downtime
     *
     * @param String $type, host : host, svc: service
     */
    public function downtime($type)
    {
        $Downtime = array();

        $allowed_types = array(
            'host',
            'svc'
        );

        if(in_array($type, $allowed_types))
        {
            if($type == 'host')
            {
                $Datahostdowntime = $this->nagios_data->get_collection('hostdowntime');
                
                foreach ($Datahostdowntime as $hostdowntime) 
                {
                    $Downtime[] = array('host' => $hostdowntime->host_name, 'entry_time'=> $hostdowntime->entry_time, 'author' => $hostdowntime->author, 'comment'=> $hostdowntime->comment, 'start_time'=> $hostdowntime->start_time, 'end_time' => $hostdowntime->end_time, 'fixed' => $hostdowntime->fixed, 'duration' => $hostdowntime->duration, 'downtime_id' => $hostdowntime->downtime_id, 'trigged_id' => $hostdowntime->triggered_by);
                }
            }
            else
            {
                $Dataservicedowntime = $this->nagios_data->get_collection('servicedowntime');

                foreach ($Dataservicedowntime as $servicedowntime) 
                {
                    $Downtime[] = array('host' => $servicedowntime->host_name, 'service'=> $servicedowntime->service_description, 'entry_time'=> $servicedowntime->entry_time, 'author' => $servicedowntime->author, 'comment'=> $servicedowntime->comment, 'start_time'=> $servicedowntime->start_time, 'end_time' => $servicedowntime->end_time, 'fixed' => $servicedowntime->fixed, 'duration' => $servicedowntime->duration, 'downtime_id' => $servicedowntime->downtime_id, 'trigged_id' => $servicedowntime->triggered_by);
                }

            }
        }

        $this->output($Downtime);
    }

    /**
     * Schedule downtime
     *
     * @param String $type, host : host, svc : service, hostsvc : hostservice
     * @param String $host
     * @param String $service, [if type is host or hostsvc, $service = '']
     * @param String $start
     * @param String $end
     * @param Bool $fixed
     * @param String $triggerID
     * @param String $duration , in minutes
     * @param String $author
     * @param String $comments
     */
    public function scheduleDowntime($type='', $host='', $service='', $start, $end, $fixed, $triggerID, $duration, $author, $comments='')
    {
        $success = false;

        $allowed_types = array(
            'host',
            'svc',
            'hostsvc'
        );

        //check empty input
        if(!empty($type) && !empty($name) && !empty($author) && !empty($comment) && !empty($start) && !empty($end) && !empty($fixed))
        {
            //compare type with allowed types
            if(in_array($type, $allowed_types))
            {
                if($type == 'host')
                {
                    $success = $this->system_commands->schedule_host_downtime($host, $start, $end, $fixed, $triggerID, $duration, $author, $comments);
                }
                else if($type == 'svc')
                {
                    $success = $this->system_commands->schedule_svc_downtime($host, $service, $start, $end, $fixed, $triggerID, $duration, $author, $comments);
                }
                else
                {
                    $success = $this->system_commands->schedule_host_svc_downtime($host, $start, $end, $fixed, $triggerID, $duration, $author, $comments);
                }
            }
        }
        
        $this->output($success);
    }

    /**
     * Return performance info of nagios
     */
    public function performanceInfo()
    {
        $performanceInfo = $this->system_commands->get_return_array('PERFORMANCE');

        $this->output($performanceInfo);
    }

    /**
     * Return process info of nagios
     */
    public function processInfo()
    {
        $processInfo = $this->system_commands->get_return_array('PROCESS');

        $this->output($processInfo);
    }

    /**
     * Return schedule queue of host and service
     *
     */
    public function scheduleQueue()
    {
        $DataHost = $this->nagios_data->get_collection("hoststatus");
        $DataService = $this->nagios_data->get_collection("servicestatus");
        $DataHostresource = $this->nagios_data->get_collection("hostresourcestatus");
        $DataRunningstate = $this->nagios_data->get_collection("runningstatestatus");

        $Host = array();
        $Service = array();
        $Hostresource = array();
        $Runningstate = array();
        

        foreach ($DataHost as $host) 
        {
            $Schedule[] = array('type'=>"host", 'hostname'=> $host->host_name, 'lastcheck'=>$host->last_check, 'nextcheck'=> $host->next_check, 'activecheck'=>$host->active_checks_enabled);
        }

        foreach ($DataService as $service) 
        {
            $Schedule[] = array('type'=>"service", 'hostname'=> $service->host_name, 'servicename'=> $service->service_description, 'lastcheck'=> $service->last_check, 'nextcheck'=>$service->next_check,'activecheck'=>$service->active_checks_enabled);
        }

        foreach ($DataHostresource as $hostresource) 
        {
            $Schedule[] = array('type'=> "hostresource", 'hostname'=> $hostresource->host_name, 'servicename'=> $hostresource->service_description, 'lastcheck'=> $hostresource->last_check, 'nextcheck'=>$hostresource->next_check, 'activecheck'=>$hostresource->active_checks_enabled);
        }

        foreach ($DataRunningstate as $runningstate) 
        {
            $Schedule[] = array('type'=> "runningstate", 'hostname'=> $runningstate->host_name, 'servicename'=> $runningstate->service_description, 'lastcheck'=> $runningstate->last_check, 'nextcheck'=>$runningstate->next_check, 'activecheck'=>$runningstate->active_checks_enabled);
        }

        usort($Schedule, function($a, $b)
        {
            return strcmp($a->nextcheck, $b->nextcheck);
        });
        
        $this->output($Schedule);
    }

    

    /**
     * Fetch all comments or only those of a certain type.
     * Returns a flat array of comment objects.
     *
     * @param  string $type, '' return all 
     */
    public function comments($type = '') 
    {
        $allowed_types = array(
            'hostcomment',
            'servicecomment'
        );

        if( $type != '' )
        {
            if(! in_array($type, $allowed_types))
            {
                return $this->output(array());
            }

            $specific_comments = $this->nagios_data->get_collection($type)->get_index('host_name');
            $comments = $this->comments_flatten($specific_comments);
        } 
        else
        {
            $host_comments = $this->nagios_data->get_collection('hostcomment')->get_index('host_name');
            $service_comments = $this->nagios_data->get_collection('servicecomment')->get_index('host_name');
            $comments = $this->comments_merge($host_comments, $service_comments);
        }

        $this->output($comments);
    }

    /**
     * Add comments
     *
     * @param String $type, host : host, svc : service
     * @param String $name
     * @param String $service
     * @param bool $persistent 
     * @param String $author
     * @param String $comments
     */
    public function addComments($type, $name, $service='', $persistent, $author, $comments)
    {
        $result = false;

        $allowed_types = array(
            'host',
            'svc'
        );

        //check for empty input
        if(!empty($type) && !empty($name) && !empty($persistent) && !empty($author) && !empty($comments))
        {
            //compare types with allowed types
            if(in_array($type, $allowed_types))
            {
                if($type == 'host')
                {
                    $result = $this->system_commands->add_host_comment($name, $persistent, $author, $comments);
                }
                else
                {
                    $result = $this->system_commands->add_svc_comment($name, $service, $persistent, $author, $comments);
                }
            }
        }

        $this->output($result);
    }

    /**
     * Delete comments
     *
     * @param String $id
     * @param String $type, host : host, svc : service
     */
    public function deleteComments($id, $type='')
    {
        $result = false;

        $allowed_types = array(
            'host',
            'svc'
        );

        //check for empty input
        if(!empty($id) && !empty($type))
        {
            //compare type with allowed types
            if(in_array($type, $allowed_types))
            {
                if($type == 'host')
                {
                    $result = $this->system_commands->delete_host_comment($id);
                }
                else
                {
                    $result = $this->system_commands->delete_svc_comment($id);
                }
            }
        }

        $this->output($result);
    }

    

    /**
     * Enable or disable service check
     *
     * @param String $type , 'enable' ,'disable'
     * @param String $hostname
     * @param String $service
     */
    public function servicecheck($type, $hostname, $service)
    {
        $result = false;

        if($type == 'enable')
        {
            $result = $this->system_commands->enable_svc_check();
        }
        else if($type == 'disable')
        {
            $result = $this->system_commands->disable_svc_check();
        }

        $this->output($result);
    }

    /**
     * Enable or disable all notifications
     *
     * @param Bool $type, true = 'enable', false = 'disable'
     */
    public function allnotifications($type)
    {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->enable_all_notification();
        }
        else
        {
            $result = $this->system_commands->disable_all_notification();
        }

        $this->output($result);
    }

    /**
     * Restart or shut down nagios
     *
     * @param String $type , 'restart', 'shutdown'
     */
    public function nagiosOperation($type)
    {
        $result = false;

        if($type == 'restart')
        {
            $result = $this->system_commands->restart_nagios();
        }
        else if($type == 'shutdown')
        {
            $result = $this->system_commands->shutdown_nagios();
        }

        $this->output($result);
    }

    /**
     * Enable or disable service notification
     *
     * @param Bool $type, true = 'enable', false ='disable'
     * @param String $host
     * @param String $service
     */
    public function serviceNotification($type, $host, $service)
    {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->enable_svc_notification();
        }
        else
        {
            $result = $this->system_commands->disable_svc_notification();
        }

        $this->output($result);
    }

    /**
     * Delete all host or service comment
     *
     * @param String $type, 'host', 'service'
     * @param String $host
     * @param String $service , [if delete all host name, $service should be '']
     */
    public function deleteAllComment($type, $host, $service='')
    {
        $result = false;

        if($type == 'host')
        {
           $result =  $this->system_commands->delete_all_host_comments($host);
        }
        else if($type == 'service')
        {
            $result = $this->system_commands->delete_all_service_comments($host, $service);
        }

        $this->output($result);
    }

    /**
     * Enable or disable host notification
     *
     * @param Bool $type, true = 'enable', false ='disable'
     * @param String $host
     */
    public function hostNotification($type, $host)
    {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->enable_host_notification($host);
        }
        else
        {
            $result =  $this->system_commands->disable_host_notification($host);
        }

        $this->output($result);
    }

    /**
     * Schedule host or service check
     *
     * @param String $type, 'host', 'service'
     * @param String $host
     * @param String $service , [if type is host, service should be '']
     * @param String $checktime
     * @param bool $forceCheck 
     */
    public function scheduleCheck($type, $host, $service='', $checktime, $forceCheck)
    {
        $result = false;

        if($type == 'host')
        {
            $result = $this->system_commands->schedule_host_svc_check($host, $checktime, $forceCheck);
        }
        else if($type == 'service')
        {
            $result = $this->system_commands->schedule_svc_check($host, $service, $checktime, $forceCheck);
        }

        $this->output($result);
    }

    /**
     * Enable or disable host service check
     *
     * @param Bool $type, true = 'enable', false ='disable'
     * @param String $host
     */
     public function hostServiceCheck($type, $host)
     {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->enable_host_svc_check($host);
        }
        else
        {
            $result = $this->system_commands->disable_host_svc_check($host);
        }

        $this->output($result);
     } 

     /**
      * Enable or disable host service notification
      *
      * @param Bool $type, true = 'enable', false ='disable'
      * @param String $host
      */
     public function hostServiceNotification($type, $host)
     {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->enable_host_svc_notification($host);
        }
        else
        {
            $result = $this->system_commands->disable_host_svc_notification($host);
        }

        $this->output($result);
     }

     /**
      * Acknowledge host or service problem
      *
      * @param String $type, 'host', 'service'
      * @param String $host
      * @param String $service, [if type is 'host', service will be '']
      * @param bool $sticky
      * @param bool $notify
      * @param bool $persistent
      * @param String $author
      * @param String $comment
      */
     public function acknowledgeProblem($type, $host, $service='', $sticky, $notify, $persistent, $author, $comment)
     {
        $result = false;

        if($type == 'host')
        {
            $result = $this->system_commands->acknowledge_host_problem($host, $sticky, $notify, $persistent, $author, $comment);
        }
        else if($type == 'service')
        {
            $result = $this->system_commands->acknowledge_svc_problem($host, $service, $sticky, $notify, $persistent, $author, $comment);
        }

        $this->output($result);
     }

     /**
      * Start or stop all service check
      *
      * @param Bool $type, true = 'start', false = 'stop'
      */
     public function allServiceCheck($type)
     {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->start_svc_check();
        }
        else
        {
            $result = $this->system_commands->stop_svc_check();
        }

        $this->output($result);
     }

     /**
      * Start or stop all passive service check
      *
      * @param Bool $type, true = 'start', false = 'stop'
      */
     public function allPassiveServiceCheck($type)
     {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->start_passive_svc_check();
        }
        else
        {
            $result = $this->system_commands->stop_passive_svc_check();
        }

        $this->output($result);
     }

     /**
      * Enable or disable passive service check
      *
      * @param Bool $type, true - start, false - disable
      * @param String $host
      * @param String $service
      */
     public function passiveServiceCheck($type, $host, $service)
     {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->enable_passive_svc_check($host, $service);
        }
        else
        {
            $result = $this->system_commands->disable_passive_svc_check($host, $service);
        }

        $this->output($result);
     }

     /**
      * Enable or disable event handler
      *
      * @param Bool $type, true = 'enable', false ='disable'
      */
     public function eventHandler($type)
     {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->enable_event_handler();
        }
        else
        {
            $result = $this->system_commands->disable_event_handler();
        }

        $this->output($result);
     }

     /**
      * Enable or disable host check
      *
      * @param Bool $type, true = 'enable', false ='disable'
      * @param String $host
      */
     public function hostCheck($type, $host)
     {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->enable_host_check($host);
        }
        else
        {
            $result = $this->system_commands->disable_host_check($host);
        }

        $this->output($result);
     }

     /**
      * Start or stop obsess over service check
      *
      * @param Bool $type, true - start, false - disable
      */
     public function obsessOverServiceCheck($type)
     {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->start_obsess_over_svc_check();
        }
        else
        {
            $result = $this->system_commands->stop_obsess_over_svc_check();
        }

        $this->output($result);
     }

     /**
      * Start or stop obsess over host check
      *
      * @param Bool $type, true - start, false - stop
      */
     public function obsessOverHostCheck($type)
     {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->start_obsess_over_host_check();
        }
        else
        {
            $result = $this->system_commands->stop_obsess_over_host_check();
        }

        $this->output($result);
     }

     /**
      * Start or stop obsess over host
      *
      * @param Bool $type, true - start, false - stop
      * @param String $host
      */
     public function obsessOverHost($type, $host)
     {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->start_obsess_over_host($host);
        }
        else
        {
            $result = $this->system_commands->stop_obsess_over_host($host);
        }

        $this->output($result);
     }

     /**
      * Start or stop obsess over service
      *
      * @param Bool $type, true - start, false - stop
      * @param String $host
      * @param String $service
      */
     public function obsessOverService($type, $host, $service)
     {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->start_obsess_over_svc($host, $service);
        }
        else
        {
            $result = $this->system_commands->stop_obsess_over_svc($host, $service);
        }

        $this->output($result);
     }

     /**
      * Enable or disable performance data
      *
      * @param Bool $type, true = 'enable', false ='disable'
      */
     public function performanceData($type)
     {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->enable_performance_data();
        }
        else
        {
            $result = $this->system_commands->disable_performance_data();
        }

        $this->output($result);
     }

     /**
      * Start or stop all host check
      *
      * @param Bool $type, true - start, false - stop
      */
     public function allHostCheck($type)
     {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->start_host_check();
        }
        else
        {
            $result = $this->system_commands->stop_host_check();
        }

        $this->output($result);
     }

     /**
      * Start or stop all passive host check
      *
      * @param Bool $type, true - start, false - stop
      */
     public function allPassiveHostCheck($type)
     {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->start_passive_host_check();
        }
        else
        {
            $result = $this->system_commands->stop_passive_host_check();
        }

        $this->output($result);
     }

     /**
      * Enable or disable passive host check
      *
      * @param Bool $type, true = 'enable', false ='disable'
      * @param String $host
      */
     public function passiveHostCheck($type, $host)
     {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->enable_passive_host_check($host);
        }
        else
        {
            $result = $this->system_commands->disable_passive_host_check($host);
        }

        $this->output($result);
     }

     /**
      * Enable or disable all flap detection
      *
      * @param Bool $type, true = 'enable', false ='disable'
      */
     public function allFlapDetection($type)
     {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->enable_flap_detection();
        }
        else
        {
            $result = $this->system_commands->disable_flap_detection();
        }

        $this->output($result);
     }

     /**
      * Enable or disable host flap detection
      *
      * @param Bool $type, true = 'enable', false ='disable'
      * @param String $host
      */
     public function hostFlapDetection($type, $host)
     {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->enable_host_flap_detection($host);
        }
        else
        {
            $result = $this->system_commands->disable_host_flap_detection($host);
        }

        $this->output($result);
     }

     /**
      * Enable or disable service flap detection
      *
      * @param Bool $type, true = 'enable', false ='disable'
      * @param String $host
      * @param String $service
      */
     public function serviceFlapDetection($type, $host, $service)
     {
        $result = false;

        if($type)
        {
            $result = $this->system_commands->enable_svc_flap_detection($host, $service);
        }
        else
        {
            $result = $this->system_commands->disable_svc_flap_detection($host, $service);
        }

        $this->output($result);
     }


     /**
      * Schedule host check
      *
      * @param String $host
      * @param String $checktime
      * @param bool $forceCheck
      */
     public function scheduleHostCheck($host, $checktime, $forceCheck)
     {
        $result = false;

        $result = $this->system_commands->schedule_host_check($host, $checktime, $forceCheck);

        $this->output($result);
     }

     /**
      * Send custom host or service notifications
      *
      * @param String $type , 'host', 'service'
      * @param String $host
      * @param String $service
      * @param bool $force
      * @param bool $broadcast
      * @param String $author
      * @param String $comment
      */
     public function sendCustomNotification($type, $host, $service, $force, $broadcast, $author, $comment)
     {
        $result = false;

        if($type == 'host')
        {
            $result = $this->system_commands->send_custom_host_notification($host, $force, $broadcast, $author, $comment);
        }
        else if($type == 'service')
        {
            $result = $this->system_commands->send_custom_svc_notification($host, $service, $force, $broadcast, $author, $comment);
        }

        $this->output($result);
     }


    /**
     * Fetch host status
     *
     * @param  string $host_name
     */
    public function hoststatus($host_name='') 
    {

        $Data = $this->nagios_data->get_collection('hoststatus');

        //fetch by host name
        if(!empty($host_name))
        {
            $Data = $Data->get_index_key('host_name', $host_name);
            
            if( empty($Data) )
            {
                return $this->output($Data);
            } 

            $Data = $Data->first();

            //add comments
            $all_comments = $this->nagios_data->get_collection('hostcomment');
            $host_comments = $all_comments->get_index_key('host_name',$host_name);
            $Data->hostcomments = $host_comments ? $host_comments : array();

            //add host resources
            $all_resources = $this->nagios_data->get_collection('hostresourcestatus');
            $host_resources = $all_resources->get_index_key('host_name', $host_name);
            $Data->hostresources = $host_resources ? $host_resources->to_array() : array();
        }

        $this->output($Data);
    }

    /**
     * Remote control service objects based on parameters
     * 
     * @param  string $host_name host name filter
     * @param  string $service   service description (requires host name)
     * @param  string $operation operation
     */
    public function serviceremote($host_name='',$service_description='',$operation='')
    {
        $service_description = urldecode($service_description);

        $Data = $this->nagios_data->get_collection('servicestatus');
        $result = array();

        //fetch by host name
        if(!empty($host_name))
        {

            if(empty($service_description))
            {
                $result['code'] = -1;//Please provide a service name.
                return $this->output($result);
            } 
            else 
            {
                $Data = $Data->get_index_key('host_name',$host_name)->get_where('service_description',$service_description)->first();
                
                if( empty($Data) )
                {
                    $result['code'] = -2;//Unknown service name: '.$service_description
                    return $this->output($result);
                } 
                
                if(!$this->is_remote_enabled($Data))
                {
                    $result['code'] = -4;//Fail to remote the service.
                    return $this->output($result);
                }

                if($operation !== '')
                {
                    $operation = strtolower($operation);
                    switch ($operation) 
                    {
                        case 'start':
                        case 'stop':
                        case 'pause':
                            $result['code'] = 0;
                            $result['message'] = shell_exec("nohup ./application/scripts/remote_windows_services.sh $host_name.'$this->domain'.local $operation $service_description 2>&1 &");
                            $result['state'] = $this->remove_string_spaces(shell_exec("nohup ./application/scripts/get_windows_services.sh $host_name.'$this->domain'.local $service_description 2>&1 &"));
                            $this->output($result);
                            return;
                        default:
                            $result['code'] = -3;//Unknown service operation.
                            return $this->output($result);
                    }
                }
            }

        }

        $result['code'] = -4;//Fail to remote the service.
        return $this->output($result);
    }

    /**
     * Retrieve service status objects based on parameters
     * 
     * @param  string $host_name host name filter
     * @param  string $service   service description (requires host name)
     */
    public function servicestate($host_name='',$service_description='')
    {
        $service_description = urldecode($service_description);

        $Data = $this->nagios_data->get_collection('servicestatus');
        $result = array();

        //fetch by host name
        if(!empty($host_name))
        {

            if(empty($service_description))
            {
                $result['code'] = -1;//Please provide a service name.
                return $this->output($result);
            } 
            else 
            {
                $Data = $Data->get_index_key('host_name',$host_name)->get_where('service_description',$service_description)->first();
                
                if( empty($Data) )
                {
                    $result['code'] = -2;//Unknown service name: '.$service_description
                    return $this->output($result);
                } 
                
                if(!$this->is_remote_enabled($Data))
                {
                    $result['code'] = -4;//Remote service is disabled.
                    return $this->output($result);
                }

                $result['code'] = 0;
                $result['state'] = $this->remove_string_spaces(shell_exec("nohup ./application/scripts/get_windows_services.sh $host_name.'$this->domain'.local $service_description 2>&1 &"));
                $this->output($result);
                return;
            }

        }

        $result['code'] = -3;//Fail to get the service.
        return $this->output($result);
    }


    /**
     * Retrieve service status objects based on parameters
     * 
     * @param  string $host_name host name filter
     * @param  string $service   service description (requires host name)
     */
    public function servicestatus($host_name='',$service_description='')
    {
        $service_description = urldecode($service_description);

        $Data = $this->nagios_data->get_collection('servicestatus');

        //fetch by host name
        if(!empty($host_name))
        {

            if(empty($service_description))
            {
                $Data = $Data->get_index_key('host_name',$host_name);
            } 
            else 
            {
                $Data = $Data->get_index_key('host_name',$host_name)->get_where('service_description',$service_description)->first();

                if( empty($Data) )
                {
                    return $this->output(array());
                } 

                //add comments
                $all_comments = $this->nagios_data->get_collection('servicecomment');
                $service_comments = $all_comments->get_where('service_description',$service_description);
                $Data->servicecomments = $service_comments ? $service_comments : array();
            }

        }

        $this->output($Data);
    }

    /**
     * Retrieve service log files based on parameters
     * 
     * @param  string $host_name host name filter
     * @param  string $service   service description (requires host name)
     */
    public function servicelogs($host_name='',$service_description='')
    {
        $service_description = urldecode($service_description);

        $Data = $this->nagios_data->get_collection('servicestatus');
        $result = array();

        //fetch by host name
        if(!empty($host_name))
        {

            if(empty($service_description))
            {
                $result['code'] = -1;//Please provide a service name.
                return $this->output($result);
            } 
            else 
            {
                $Data = $Data->get_index_key('host_name',$host_name)->get_where('service_description',$service_description)->first();
                if( empty($Data) )
                {
                    $result['code'] = -2;//Unknown service name: '.$service_description
                    return $this->output($result);
                } 
                
                if($this->is_logfile_defined($Data))
                {
                    $this->get_service_log_path($Data, $log_dir, $log_files);
                }
                else
                {
                    log_message('error', 'No log files for this service.');
                    $result['code'] = -6;//No log files for this service.
                    return $this->output($result);
                }
                
                $Data = array();
                $connection;

                //If the service is being monitored, get the log files list
                $sftp = $this->sftp_connect($host_name.'.'.$this->domain.'.local', $this->user_domain.'\\'.$this->user, $this->passwd, $connection);
                
                if($sftp)
                {
                    $Data['logs'] = array();
                    foreach($log_files as $log_file)
                    {
                        if(pathinfo($log_file, PATHINFO_DIRNAME) === '.')
                        {
                            //If not found '/', means it just a file name
                            $handle = opendir("ssh2.sftp://$sftp/".$log_dir."/");
                            
                            $regex_log_file = $this->format_string_for_regex($log_file, $service_description);

                            if (! $handle)
                            {
                                log_message('error', 'Logs directory cannot be accessed.');
                                $result['code'] = -3;//Logs directory cannot be accessed.
                                $this->sftp_close($connection);
                                return $this->output($result);
                            }

                            
                            while (false != ($entry = readdir($handle)))
                            {
                                if (preg_match('/'.$regex_log_file.'/i', $entry) === 1)
                                {
                                    $tmp = stat("ssh2.sftp://$sftp/".$log_dir."/$entry");
                                    if($tmp == false)
                                    {
                                        log_message('error', 'Log file cannot be read.'.$entry);
                                        $result['code'] = -4;//Log file cannot be read.
                                        $this->sftp_close($connection);
                                        return $this->output($result);
                                    }
                                    
                                    unset($tmp['0'], $tmp['1'], $tmp['2'], $tmp['3'], $tmp['4'], 
                                        $tmp['5'], $tmp['6'], $tmp['7'], $tmp['8'], $tmp['9'], $tmp['10'], $tmp['11'], $tmp['12']);
                                        
                                    $tmp['name'] = $entry;
                                    $Data['logs'][] = $tmp;
                                }
                            }
                            
                            closedir($handle);
                        }
                        else
                        {
                            //If found '/', means it contains subdirectory
                            $sub_dir = dirname($log_file);
                            $sub_log_file = basename($log_file);
                            
                            $handle = opendir("ssh2.sftp://$sftp/".$log_dir."/".$sub_dir."/");
                            $regex_log_file = $this->format_string_for_regex($sub_log_file, $service_description);
                            
                            if (! $handle)
                            {
                                log_message('error', 'Logs directory cannot be accessed.');
                                $result['code'] = -3;//Logs directory cannot be accessed.
                                $this->sftp_close($connection);
                                return $this->output($result);
                            }
                            
                            while (false != ($entry = readdir($handle)))
                            {
                                log_message('debug', 'Matching pattern on '.$entry.': '.$regex_log_file);
                                if (preg_match('/'.$regex_log_file.'/i', $entry) === 1)
                                {
                                    $tmp = stat("ssh2.sftp://$sftp/".$log_dir."/".$sub_dir."/$entry");
                                    if($tmp == false)
                                    {
                                        log_message('error', 'Log file cannot be read.'.$entry);
                                        $result['code'] = -4;//Log file cannot be read.
                                        $this->sftp_close($connection);
                                        return $this->output($result);
                                    }
                                    
                                    unset($tmp['0'], $tmp['1'], $tmp['2'], $tmp['3'], $tmp['4'], 
                                        $tmp['5'], $tmp['6'], $tmp['7'], $tmp['8'], $tmp['9'], $tmp['10'], $tmp['11'], $tmp['12']);

                                    $tmp['name'] = $sub_dir."/".$entry;
                                    $Data['logs'][] = $tmp;
                                }
                            }
                            
                            closedir($handle);
                        }
                    }
                }
                else
                {
                    $result['code'] = -5;//Could not connect to the remote host: '.$host_name
                    $this->sftp_close($connection);
                    return $this->output($result);
                }

                $this->sftp_close($connection);

                if(empty($Data))
                {
                    log_message('error', 'No log files for this service.');
                    $result['code'] = -6;//No log files for this service.
                    return $this->output($result);
                }
            }
        }
        $Data['code'] = 0;
        $this->output($Data);
    }

    /**
     * Prepare download service log file based on parameters
     * 
     * @param  string $host_name host name filter
     * @param  string $service   service description (requires host name)
     * @param  string $filename  file name
     */
    public function servicelogdownload($host_name='',$service_description='',$filenames='')
    {
        $service_description = urldecode($service_description);
        $filenames = urldecode($filenames);
        
        $Data = $this->nagios_data->get_collection('servicestatus');
        $result = array();
        
        //fetch by host name
        if(!empty($host_name))
        {

            if(empty($service_description))
            {
                $result['code'] = -1;//Please provide a service name.
                return $this->output($result);
            } 
            else 
            {
                $Data = $Data->get_index_key('host_name',$host_name)->get_where('service_description',$service_description)->first();
                if( empty($Data) )
                {
                    $result['code'] = -2;//Unknown service name: '.$service_description
                    return $this->output($result);
                }
                
                if($this->is_logfile_defined($Data))
                {
                    $this->get_service_log_path($Data, $log_dir, $log_files);
                }
                else
                {
                    log_message('error', 'No log files for this service.');
                    $result['code'] = -6;//No log files for this service.
                    return $this->output($result);
                }

                $filenames = explode('|', $filenames);
                $connection;

                //If the service is being monitored, download the log file
                $sftp = $this->sftp_connect($host_name.'.'.$this->domain.'.local', $this->user_domain.'\\'.$this->user, $this->passwd, $connection);

                if(! $sftp)
                {
                    log_message('error', 'Failed to access SFTP server.');
                    $result['code'] = -3;//Failed to access SFTP server
                    $this->sftp_close($connection);
                    return $this->output($result);
                }

                ini_set('memory_limit', $this->zipmem); //increase size of memory limit to cache the download file
                $this->load->library('zip');
                foreach($filenames as $filename)
                {
                    $valid_file = false;
                    
                    foreach($log_files as $log_file)
                    {
                        $regex_log_file = $this->format_string_for_regex($log_file, $service_description);
                        if (preg_match('/'.$regex_log_file.'/i', $filename) === 1)
                        {
                            $valid_file = true;
                        }
                    }
                    
                    if($valid_file)
                    {
                        //filename can be subdir/filename or filename
                        if(TRUE !== ($this->zip->read_file("ssh2.sftp://$sftp/".$log_dir."/".$filename)))
                        {
                            log_message('error', 'Invalid file request('.$filename.') for service:'.$service_description);
                            $this->sftp_close($connection);
                            $result['code'] = -4;//Invalid file request('.$filename.') for service:'.$service_description
                            return $this->output($result);
                        }
                    }
                    else
                    {
                        log_message('error', 'Invalid file request('.$filename.') for service:'.$service_description);
                        $this->sftp_close($connection);
                        $result['code'] = -4;//Invalid file request('.$filename.') for service:'.$service_description
                        return $this->output($result);
                    }
                }

                $this->load->helper('string');

                $newKey = random_string('unique');
                $newFileName = $service_description.'_'.$newKey.'.zip';

                $curList = $this->session->userdata('files');
                if($curList == null)
                {
                    $newFile = array(
                        $newKey  => $newFileName
                    );
                    $this->session->set_userdata('files', $newFile);
                }
                else
                {
                    $curList[$newKey] = $newFileName;
                    $this->session->set_userdata('files', $curList);
                }

                $this->zip->archive($this->zippath.$newFileName);
                $this->sftp_close($connection);
                $result['code'] = 0;
                $result['key'] = $newKey;
                return $this->output($result);
            }
        }

        $result['code'] = -5;//Failed to prepare download.
        return $this->output($result);
    }

    /**
     * Download service log file based on key
     * 
     * @param  string $key host name filter
     */
    public function download($key='')
    {
        $files = $this->session->userdata('files');
        $result = array();

        if(empty($key))
        {
            $result['code'] = -1;//Missing key.
            return $this->output($result);
        }

        if(!array_key_exists($key, $files))
        {
            $result['code'] = -2;//Invalid or expired key.
            return $this->output($result);
        }

        $filename = $files[$key];

        header('X-Sendfile: '.$this->zippath.$filename);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        return;
    }
    

    /**
     * Fetch host group status
     *
     * @param  string $hostgroup_name
     */
    public function hostgroupstatus($hostgroup_name = '')
    {

        $HostgroupStatus = new HostStatusCollection();
        $Hostgroups = $this->nagios_data->get_collection('hostgroup');
        $found = False; 

        foreach($Hostgroups as $Hostgroup)
        {
            if( $hostgroup_name != '' ) 
            {
                if( $Hostgroup->hostgroup_name == $hostgroup_name )
                {
                    $Hostgroup->hydrate();
                    $HostgroupStatus[] = $Hostgroup;
                    $found = True;
                }
            }
            else
            {
                $Hostgroup->hydrate();
                $HostgroupStatus[] = $Hostgroup;
                $found = True;
            }
        }

        if( empty($found) )
        {
            return $this->output(array());
        }

        $this->output($HostgroupStatus);
    }


    /**
     * Fetch service group status
     *
     * @param  string $servicegroup_name
     */
    public function servicegroupstatus($servicegroup_name = '')
    {

        $ServicegroupStatus = new ServiceStatusCollection();
        $Servicegroups = $this->nagios_data->get_collection('servicegroup');
        $found = False; 

        foreach($Servicegroups as $Servicegroup)
        {
            if( $servicegroup_name != '' ) 
            {
                if( $Servicegroup->servicegroup_name == $servicegroup_name )
                {
                    $Servicegroup->hydrate();
                    $ServicegroupStatus[] = $Servicegroup;
                    $found = True;
                }
            }
            else
            {
                $Servicegroup->hydrate();
                $ServicegroupStatus[] = $Servicegroup;
                $found = True;
            }
        }

        if( empty($found) )
        {
            return $this->output(array());
        }

        $this->output($ServicegroupStatus);
    }


    /**
     * Fetch configurations
     *
     * @param  string $type
     */
    public function configurations($type = '')
    {
        $configurations = array();

        $key_lookup = array(
            'hosts'         => 'hosts_objs',
            'services'      => 'services_objs',
            'hostgroups'    => 'hostgroups_objs',
            'servicegroups' => 'servicegroups_objs',
            'timeperiods'   => 'timeperiods',
            'contacts'      => 'contacts',
            'contactgroups' => 'contactgroups',
            'commands'      => 'commands'
        );

        $keys = array();

        if( $type != '' )
        {
            if( isset($key_lookup[$type]) )
            {
                $keys[$type] = $key_lookup[$type];
            }
        }
        else
        {
            $keys = $key_lookup;
        }

        foreach($keys as $name => $objtype)
        {

            $data = object_data($objtype);

            $configurations[$name] = array(
                'items'   => $data,
                'name'    => $name,
                'objtype' => $objtype,
            );
        }

        $this->output($configurations);
    }

    
    
    /**
     * Check if the service is allowed to remote control.
     * Returns boolean to determine remote enable or not.
     *
     * @param  array $NagiosData
     */
    private function is_remote_enabled($Data)
    {
        //Check if _REMOTE_ENABLED is defined in the Nagios
        if(array_key_exists('_REMOTE_ENABLED', $Data))
        {
            log_message('debug', '_REMOTE_ENABLED exists for '.$Data->service_description.': '.substr($Data->_REMOTE_ENABLED, 2));
            if(substr($Data->_REMOTE_ENABLED, 2) === '1')
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Check if the service is available to monitor log files.
     * Returns boolean to determine log files available or not.
     *
     * @param  array $NagiosData
     */
    private function is_logfile_defined($Data)
    {
        //Check if _LOG_DIR and _LOG_FILE is defined in the Nagios
        if(array_key_exists('_LOG_DIR', $Data) && array_key_exists('_LOG_FILE', $Data))
        {
            log_message('debug', '_LOG_DIR exists for '.$Data->service_description.': '.substr($Data->_LOG_DIR, 2));
            log_message('debug', '_LOG_FILE exists for '.$Data->service_description.': '.substr($Data->_LOG_FILE, 2));
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Get the logfiles path as defined in _LOG_DIR and _LOG_FILE.
     * Returns array of string logfile path defined in Nagios.
     *
     * @param  array  $NagiosData
     * @param  string $log_dir
     * @param  array  $log_files
     */
    private function get_service_log_path($Data, &$log_dir, &$log_files)
    {
        log_message('debug', 'Retrieving log files path for '.$Data->service_description);
        $log_dir = substr($Data->_LOG_DIR, 2);
        $log_files = explode('|', substr($Data->_LOG_FILE, 2));
        
        log_message('debug', 'Complete retrieve log files path for '.$Data->service_description);
    }

    /**
     * Get the sftp connection object.
     * Returns sftp connection object.
     *
     * @param  string $remote
     * @param  string $user
     * @param  string $passwd
     * @param  object $connection (output)
     */
    private function sftp_connect($remote, $user, $passwd, &$connection)
    {
        $connection = ssh2_connect($remote, 22);
        if (! $connection)
            log_message('error', 'Could not connect to '.$remote.'.');

        if (! ssh2_auth_password($connection, $user, $passwd))
            log_message('error', 'Could not authenticate with user '.$user.'.');

        $sftp = ssh2_sftp($connection);

        if (! $sftp)
            log_message('error', 'Could not connect to sftp server for host '.$remote.'.');

        return $sftp;
    }

    /**
     * Close the sftp connection object.
     *
     * @param  object $connection
     */
    private function sftp_close(&$connection)
    {
        if($connection)
        {
            ssh2_exec($connection, 'exit');
            unset($connection);
        }
    }
    
    /**
     * Format the string for regex operation.
     * return formatted string for regex
     *
     * @param  string $input
     * @param  string $service_description
     * operation include:
     *   Map custom macro $SERVICEDESC$ to actual service name
     *   Map date macro $YYYYMMDD$ to 8 digits match
     *   Map time macro $HHmmSS$ to 6 digits match
     *   Map wildcard '*' to regex wildcard '.*'
     *   Map dot '.' to regex dot '\.'
     *   Map dot '/' to regex dot '\/'
     */
    private function format_string_for_regex($input, $service_description)
    {
        $output = str_replace('$SERVICEDESC$', $service_description, $input);
        $output = str_replace('$YYYYMMDD$', '[0-9]{8}', $output);
        $output = str_replace('$HHmmSS$', '[0-9]{6}', $output);
        
        $output = str_replace('\\', '\\\\', $output);
        $output = str_replace('.', '\.', $output);
        $output = str_replace('*', '.*', $output);
        $output = str_replace('/', '\/', $output);
        return $output;
    }

    private function remove_string_spaces($string)
    {
        return trim(preg_replace('/\s\s+/', ' ', $string));
    }

    private function comments_flatten($array)
    {
        $flattened = array();
        foreach($array as $comments)
        {
            $flattened = array_merge($flattened, $comments);
        }

        return $flattened;
    }

    private function comments_merge($first, $second)
    {
        $first = $this->comments_flatten($first);
        $second = $this->comments_flatten($second);
        return array_merge($first, $second);
    }

}

/* End of file api.php */
/* Location: ./application/controllers/api.php */

