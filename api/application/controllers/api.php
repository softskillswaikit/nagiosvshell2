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
     * @param String $type
     */
    public function name()
    {
        $Data = array();
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
            $DataHost[] = $this->quicksearch_item('host', $host->host_name, $host->host_name);
        }

        foreach($DataHost as $host)
        {
            $hostname[] = $host['name'];
        }

        $allName['host'] = $hostname;
    

        //all hostgroup name    
        $hostgroups = $this->nagios_data->get_collection('hostgroup');

        foreach($hostgroups as $hostgroup)
        {
            $DataHostgroup[] = $this->quicksearch_item('hostgroup', $hostgroup->alias, $hostgroup->hostgroup_name);
        }

        foreach ($DataHostgroup as $hostgroup) 
        {
            $hostgroupname[] = $hostgroup['name'];
        } 

        $allName['hostgroup'] = $hostgroupname;     
    

        //all service name
        $services = $this->nagios_data->get_collection('servicestatus');

        foreach ($services as $service)
        {
            $DataService[] = $this->quicksearch_item('service', $service->service_description.' on '.$service->host_name, $service->host_name.'/'.$service->service_description);
        }

        foreach ($DataService as $service) 
        {
            $servicename[] = $service['name'];

        }

        $allName['service'] = $servicename;
    

        //all service group name
        $servicegroups = $this->nagios_data->get_collection('servicegroup');

        foreach($servicegroups as $servicegroup)
        {
            $DataServicegroup[] = $this->quicksearch_item('servicegroup', $servicegroup->alias, $servicegroup->servicegroup_name);
        }

        foreach ($DataServicegroup as $servicegroup) 
        {
            $servicegroupname[] = $servicegroup['name'];
        }

        $allName['servicegroup'] = $servicegroupname;


        //all host resource
        $hostresources = $this->nagios_data->get_collection('hostresource');

        foreach ($hostresources as $hostresource) 
        {
            $DataHostresource[] = $this->quicksearch_item('hostresource', $hostresource->host_name, $hostresource->service_description);
        }

        foreach ($DataHostresource as $hostresource) 
        {
            $hostresourcename[] = array('hostname' => $hostresource['name'], 'servicename' => $hostresource['uri']);
        }

        $allName['hostresource'] = $hostresourcename;

        //all service running state
        $runningstates = $this->nagios_data->get_collection('runningstate');

        foreach ($runningstates as $runningstate)
        {
            $DataRunningstate[] = $this->quicksearch_item('runningstate', $runningstate->host_name, $runningstate->service_description);
        }

        foreach ($DataRunningstate as $runningstate) 
        {
            $runningstatename[] = array('hostname' => $runningstate['name'], 'servicename' => $servicestate['uri']);

        }

        $allName['runningstate'] = $runningstatename;
        
        $this->output($allName); 
    }

    
    
    /**
     * Fetch availability
     * 
     * @param int $reportType, 1:hostgroup , 2:host, 3:servicegroup, 4:service
     * @param string $name
     * @param string $start
     * @param string $end
     * @param bool $initialState
     * @param bool $stateRetention
     * @param bool $assumeState
     * @param bool $includeSoftState
     * @param String $firstAssumedHost
     * @param String $firstAssumedService
     * @param int $backTrack
     */
     public function availability($reportType, $name='', $start, $end, $initialState, $stateRetention, $assumeState, $includeSoftState, $firstAssumedHost='', $firstAssumedService='', $backTrack)
    {
        $Availability = array();

        //check empty input
        if(!empty($reportType) && !empty($name) && !empty($start) && !empty($end) && !empty($initialState) && !empty($stateRetention) && !empty($assumeState) && !empty($includeSoftState) && !empty($firstAssumedHost) && !empty($firstAssumedService) && !empty($backTrack))
        {
            //hostgroup
            if($reportType == 1)
            {
                $Availability = $this->availability_data->get_availability_hostgroup($name, $start, $end, $initialState, $stateRetention, $assumeState, $includeSoftState, $firstAssumedHost, $firstAssumedService, $backTrack);
            }

            //host
            else if($reportType == 2)
            {
                $Availability = $this->availability_data->get_availability_host($name, $start, $end, $initialState, $stateRetention, $assumeState, $includeSoftState, $firstAssumedHost, $firstAssumedService, $backTrack);
            }

            //service group
            else if($reportType == 3)
            {
                $Availability = $this->availability_data->get_availability_servicegroup($name, $start, $end, $initialState, $stateRetention, $assumeState, $includeSoftState, $firstAssumedHost, $firstAssumedService, $backTrack);
            }

            //service
            else if($reportType == 4)
            {
                $Availability = $this->availability_data->get_availability_service($name, $start, $end, $initialState, $stateRetention, $assumeState, $includeSoftState, $firstAssumedHost, $firstAssumedService, $backTrack);
            }
        }


        $this->output($Availability);
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
    public function alerthistory()
    {
        $AlertHistory = $this->alert_history_data->get_history_data();

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
        $Data = array();
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
            $DataHost[] = $this->quicksearch_item('host', $host->host_name, $host->host_name);
        }

        foreach($DataHost as $host)
        {
            $hostname[] = $host['name'];
        }

        $allName['host'] = $hostname;
    

        //all hostgroup name    
        $hostgroups = $this->nagios_data->get_collection('hostgroup');

        foreach($hostgroups as $hostgroup)
        {
            $DataHostgroup[] = $this->quicksearch_item('hostgroup', $hostgroup->alias, $hostgroup->hostgroup_name);
        }

        foreach ($DataHostgroup as $hostgroup) 
        {
            $hostgroupname[] = $hostgroup['name'];
        } 

        $allName['hostgroup'] = $hostgroupname;     
    

        //all service name
        $services = $this->nagios_data->get_collection('servicestatus');

        foreach ($services as $service)
        {
            $DataService[] = $this->quicksearch_item('service', $service->service_description.' on '.$service->host_name, $service->host_name.'/'.$service->service_description);
        }

        foreach ($DataService as $service) 
        {
            $servicename[] = $service['name'];

        }

        $allName['service'] = $servicename;
    

        //all service group name
        $servicegroups = $this->nagios_data->get_collection('servicegroup');

        foreach($servicegroups as $servicegroup)
        {
            $DataServicegroup[] = $this->quicksearch_item('servicegroup', $servicegroup->alias, $servicegroup->servicegroup_name);
        }

        foreach ($DataServicegroup as $servicegroup) 
        {
            $servicegroupname[] = $servicegroup['name'];
        }

        $allName['servicegroup'] = $servicegroupname;


        //all host resource
        $hostresources = $this->nagios_data->get_collection('hostresource');

        foreach ($hostresources as $hostresource) 
        {
            $DataHostresource[] = $this->quicksearch_item('hostresource', $hostresource->host_name, $hostresource->service_description);
        }

        foreach ($DataHostresource as $hostresource) 
        {
            $hostresourcename[] = array('hostname' => $hostresource['name'], 'servicename' => $hostresource['uri']);
        }

        $allName['hostresource'] = $hostresourcename;

        //all service running state
        $runningstates = $this->nagios_data->get_collection('runningstate');

        foreach ($runningstates as $runningstate)
        {
            $DataRunningstate[] = $this->quicksearch_item('runningstate', $runningstate->host_name, $runningstate->service_description);
        }

        foreach ($DataRunningstate as $runningstate) 
        {
            $runningstatename[] = array('hostname' => $runningstate['name'], 'servicename' => $servicestate['uri']);

        }

        $allName['runningstate'] = $runningstatename;
        

        $this->output($allName); 
    }

    /**
     * Schedule downtime
     *
     * @param String $type, host : host, svc : service
     * @param String $name
     * @param String $author
     * @param String $comment
     * @param String $start
     * @param String $end
     * @param String $fixed - true = fixed , false = flexible
     * @param int $triggerID
     * @param int $duration , in minutes
     * @param String $child
     */
    public function downtime($type='', $name='', $author='', $comment='', $start='', $end='', $fixed='', $triggerID, $duration, $child='')
    {
        $success = false;

        $allowed_types = array(
            'host',
            'svc'
        );

        //check empty input
        if(!empty($type) && !empty($name) && !empty($author) && !empty($comment) && !empty($start) && !empty($end) && !empty($fixed))
        {
            //compare type with allowed types
            if(in_array($type, $allowed_types))
            {
                $success = $this->system_commands->schedule_downtime($name, $start, $end, $fixed, $triggerID, $duration, $author, $comment, $type);
            }
        }
        
        $this->output($success);
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
            if( ! in_array($type, $allowed_types) )
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
     * @param String $serviceDescription
     * @param String $persistent , 1 or 0
     * @param String $author
     * @param String $comments
     */
    public function addComments($type='', $name='', $serviceDescription='', $persistent, $author='', $comments='')
    {
        $result = '';

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
        $result = '';

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
                    $result = $this->system_commands->delete_service_comment($id);
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
        if($type == 'enable')
        {
            $this->system_commands->enable_svc_check();
        }
        else if($type == 'disable')
        {
            $this->system_commands->disable_svc_check();
        }
    }

    /**
     * Enable or disable all notifications
     *
     * @param String $type, 'enable', 'disable'
     */
    public function allnotifications($type)
    {
        if($type == 'enable')
        {
            $this->system_commands->enable_all_notification();
        }
        else if($type == 'disable')
        {
            $this->system_commands->disable_all_notification();
        }
    }

    /**
     * Restart or shut down nagios
     *
     * @param String $type , 'restart', 'shutdown'
     */
    public function nagiosOperation($type)
    {
        if($type == 'restart')
        {
            $this->system_commands->restart_nagios();
        }
        else if($type == 'shutdown')
        {
            $this->system_commands->shutdown_nagios();
        }
    }

    /**
     * Enable or disable service notification
     *
     * @param String $type, 'enable', 'disable'
     * @param String $host
     * @param String $service
     */
    public function serviceNotification($type, $host, $service)
    {
        if($type == 'enable')
        {
            $this->system_commands->enable_svc_notification();
        }
        else if($type == 'disable')
        {
            $this->system_commands->disable_svc_notification();
        }
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
        if($type == 'host')
        {
            $this->system_commands->delete_all_host_comments($host);
        }
        else if($type == 'service')
        {
            $this->system_commands->delete_all_service_comments($host, $service);
        }
    }

    /**
     * Enable or disable host notification
     *
     * @param String $type, 'enable', 'disable'
     * @param String $host
     */
    public function hostNotification($type,$host)
    {
        if($type == 'enable')
        {
            $this->system_commands->enable_host_notification($host);
        }
        else if($type == 'disable')
        {
            $this->system_commands->disable_host_notification($host);
        }
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
        if($type == 'host')
        {
            $this->system_commands->schedule_host_svc_check($host, $checktime, $forceCheck);
        }
        else if($type == 'service')
        {
            $this->system_commands->schedule_svc_check($host, $service, $checktime, $forceCheck);
        }
    }

    /**
     * Enable or disable host service check
     *
     * @param String $type, 'enable', 'disable'
     * @param String $host
     */
     public function hostServiceCheck($type, $host)
     {
        if($type == 'enable')
        {
            $this->system_commands->enable_host_svc_check($host);
        }
        else if($type == 'disable')
        {
            $this->system_commands->disable_host_svc_check($host);
        }
     } 

     /**
      * Enable or disable host service notification
      *
      * @param String $type, 'enable', 'disable'
      * @param String $host
      */
     public function hostServiceNotification($type, $host)
     {
        if($type == 'enable')
        {
            $this->system_commands->enable_host_svc_notification($host);
        }
        else if($type == 'disable')
        {
            $this->system_commands->disable_host_svc_notification($host);
        }
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
        if($type == 'host')
        {
            $this->system_commands->acknowledge_host_problem($host, $sticky, $notify, $persistent, $author, $comment );
        }
        else if($type == 'service')
        {
            $this->system_commands->acknowledge_svc_problem($host, $service, $sticky, $notify, $persistent, $author, $comment);
        }
     }


    

    /**
     * Fetch performance information
     */
    public function performanceinfo()
    {
        $performanceinfos = $this->system_commands->performance_info_commands();

        $this->output($performanceinfos);
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

