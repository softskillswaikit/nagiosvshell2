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
    public function programStatus()
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
    public function tacticalOverview() 
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


    /**
     * Fetch name based on type : host, hostgroup, service, servicegroup
     */
    public function name()
    {
        $Name = array();

        //all host name
        $hosts = $this->nagios_data->get_collection('hoststatus');

        foreach($hosts as $host)
        {
            $host_name[] = $host->host_name;
        }

        $Name['host'] = $host_name;
    

        //all hostgroup name    
        $hostgroups = $this->nagios_data->get_collection('hostgroup');

        foreach($hostgroups as $hostgroup)
        {
            $hostgroup_name[] = $hostgroup->alias;
        }

        $Name['hostgroup'] = $hostgroup_name;     
    

        //all service name
        $services = $this->nagios_data->get_collection('servicestatus');

        foreach ($services as $service)
        {
            $service_name[] = array('host' =>$service->host_name, 'service'=> $service->service_description);
        }

        $Name['service'] = $service_name;
    

        //all service group name
        $servicegroups = $this->nagios_data->get_collection('servicegroup');

        foreach($servicegroups as $servicegroup)
        {
            $servicegroup_name[] = $servicegroup->alias;
        }

        $Name['servicegroup'] = $servicegroup_name;


        //all host resource
        $hostresources = $this->nagios_data->get_collection('hostresource');

        foreach ($hostresources as $hostresource) 
        {
            $hostresource_name[] = array('host'=> $hostresource->host_name, 'service'=> $hostresource->service_description);
        }

        $Name['hostresource'] = $hostresource_name;

        //all service running state
        $runningstates = $this->nagios_data->get_collection('runningstate');

        foreach ($runningstates as $runningstate)
        {
            $runningstate_name[] = array('host'=> $runningstate->host_name, 'service' => $runningstate->service_description);
        }

        $Name['runningstate'] = $runningstate_name;
        
        $this->output($Name); 
    }

    
    
    /**
     * Fetch availability
     * 
     * @param String $return_type, 5 - hostgroup, 2 - servicegroup, 3 - host, 4 - service, 5 - host resource, 6 - running state
     * @param string $period, 'TODAY', 'LAST 24 HOURS', 'YESTERDAY', 'THIS WEEK', 'LAST 7 DAYS', 'LAST WEEK', 'THIS MONTH', 'LAST 31 DAYS', 'LAST MONTH', 'THIS YEAR', 'LAST YEAR', 'CUSTOM'
     * @param string $start_date, for standard report, start_date = current unix timestamp
     * @param string $end_date
     * @param String $host_name
     * @param String $service_description
     * @param String $assume_initial_state, true, false
     * @param String $assume_state_retention, true, false
     * @param String $assume_state_downtime, true, false
     * @param String $include_soft, true, false
     * @param String $first_assume_host_state
     * @param String $backtrack_archive, integer
     * @param String $first_assume_service_state
     */
     public function availability()
    {
        $Availability = array();

        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $return_type = $json_request->reportType;
        $period = $json_request->reportPeriod;
        $start_date = $json_request->startUnix;
        $end_date = $json_request->endUnix;
        $host_name = $json_request->reportHost;
        $service_description = $json_request->reportService;
        $assume_initial_state = $json_request->assumeInitialStates;
        $assume_state_retention = $json_request->assumeStateRetention;
        $assume_state_downtime = $json_request->assumeDowntimeStates;
        $include_soft = $json_request->includeSoftStates;
        $first_assume_host_state = $json_request->firstAssumedHostState;
        $backtrack_archive = $json_request->backtrackedArchives;
        $first_assume_service_state = $json_request->firstAssumedServiceState;

        //convert input to bool
        $assume_initial_state = $this->convert_data_bool($assume_initial_state);
        $assume_state_retention = $this->convert_data_bool($assume_state_retention);
        $assume_state_downtime = $this->convert_data_bool($assume_state_downtime);
        $include_soft = $this->convert_data_bool($include_soft);

        //custom report date
        if($period == 'CUSTOM')
        {
            $date = array($start_date, $end_date);
        }
        //standard report date
        else
        {
            $date = $start_date;
        }


        //check empty inputs
        $validate = $this->validate_data(array($return_type, $period, $date, $first_assume_host_state, $first_assume_service_state, $backtrack_archive));

        if($validate)
        {
            $Availability = $this->reports_data->get_availability($return_type, $period, $date, $host_name, $service_description, $assume_initial_state, $assume_state_retention, $assume_state_downtime, $include_soft, $first_assume_host_state, $first_assume_service_state, $backtrack_archive);
        }
        //invalid input
        else
        {
            $Availability = 1;
        }

        $this->output($Availability);

    }

    /**
     * Fetch trend
     *
     * @param string $return_type, 1 - 'host', 2 - 'service', 3- 'host resource', 4- 'running state'
     * @param string $period, 'TODAY', 'LAST 24 HOURS', 'YESTERDAY', 'THIS WEEK', 'LAST 7 DAYS', 'LAST WEEK', 'THIS MONTH', 'LAST 31 DAYS', 'LAST MONTH', 'THIS YEAR', 'LAST YEAR', 'CUSTOM'
     * @param string $start_date, for standard report, $start_date = current unix time
     * @param string $end_date
     * @param string $host_name
     * @param string $service_description
     * @param string $assume_initial_state, 'true', 'false'
     * @param string $assume_state_retention, 'true', 'false'
     * @param string $assume_state_downtime, 'true', 'false'
     * @param string $include_soft, 'true', 'false'
     * @param string $backtrack_archive, integer
     * @param string $first_assume_host_service, possible value (host) = 'UP', 'DOWN', 'UNREACHABLE', 'UNDETERMINED', 'ALL', 'HOST PROBLEM STATE'; possible value (service) = 'OK', 'WARNING', 'UNKNOWN', 'CRITICAL', 'UNDETERMINED', 'ALL', 'SERVICE PROBLEM STATE'
     *
     * return code
     * 1 = fail
     */
    public function trend()
    {
        $Trend = array();

        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $return_type = $json_request->reportType;
        $period = $json_request->reportPeriod;
        $start_date = $json_request->startUnix;
        $end_date = $json_request->endUnix;
        $host_name = $json_request->reportHost;
        $service_description = $json_request->service;
        $assume_initial_state = $json_request->assumeInitialStates;
        $assume_state_retention = $json_request->assumeStateRetention;
        $assume_state_downtime = $json_request->assumeDowntimeStates;
        $include_soft = $json_request->includeSoftStates;
        $first_assume_host_state = $json_request->firstAssumedHostState;
        $backtrack_archive = $json_request->backtrackedArchives;
        $first_assume_host_service = $json_request->firstAssumedState;

        //convert input to bool
        $assume_initial_state = $this->convert_data_bool($assume_initial_state);
        $assume_state_retention = $this->convert_data_bool($assume_state_retention);
        $assume_state_downtime = $this->convert_data_bool($assume_state_downtime);
        $include_soft = $this->convert_data_bool($include_soft);

        //custom report date
        if($period == 'CUSTOM')
        {
            $date = array($start_date, $end_date);
        }
        //standard report date
        else
        {
            $date = $start_date;
        }

        //check empty inputs
        $validate = $this->validate_data(array($return_type, $period, $start_date, $host_name));

        if($validate)
        {
            $Trend = $this->reports_data->get_trend($return_type, $period, $date, $host_name, $service_description, $assume_initial_state, $assume_state_retention, $assume_state_downtime, $include_soft, $first_assume_host_service, $backtrack_archive);
        }
        //incorrect inputs
        else
        {
            $Trend = 1;
        }

        $this->output($Trend);
    }

    /**
     * Fetch alert history
     *
     * @param String $date
     */
    public function alertHistory()
    {
        $Alert_history = array();

        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $date = $json_request->date;

        //check empty inputs
        $validate = $this->validate_data(array($date));

        if($validate)
        {
            $Alert_history = $this->reports_data->get_alert_history($date);
        }
        //incorrect inputs
        else
        {
            $Alert_history = 1;
        }

        $this->output($Alert_history);
    }

    /**
     * Fetch alert summary
     *
     * @param string $return_type,   
     * 1 : Top producer, 
     * 2 : Alert total by host, 
     * 3 : Alert total by hostgroup,  
     * 4 : Alert total by service, 
     * 5 : Alert total by servicegroup, 
     * 6 : Most recent alert
     * @param string $period,
     * 'TODAY', 'LAST 24 HOURS', 'YESTERDAY', 'THIS WEEK', 'LAST 7 DAYS', 'LAST WEEK', 'THIS MONTH', 'LAST 31 DAYS', 'LAST MONTH', 'THIS YEAR', 'LAST YEAR', 'CUSTOM'  
     * @param string $start_date, for standard report, $start_date = current unix time
     * @param string $end_date
     * @param string $host_name
     * @param string $service_description
     * @param string $logtype, 'HOST ALERT' / 'SERVICE ALERT' / 'ALL'
     * @param string $statetype, 'HARD', 'SOFT', 'ALL'
     * @param string $state
     */
    public function alertSummary()
    {
        $Alert_summary = array();

        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $return_type = $json_request->reportType;
        $period = $json_request->reportPeriod;
        $start_date = $json_request->start_date;
        $end_date = $json_request->end_date;
        $host_name = $json_request->hostLimit;
        $service_description = $json_request->service;
        $logtype = $json_request->alertTypes;
        $statetype = $json_request->stateTypes;
        $state = $json_request->state;

        //custom report date
        if($period == 'CUSTOM')
        {
            $date = array($start_date, $end_date);
        }
        //standard report date
        else
        {
            $date = $start_date;
        }

        //check empty and invalid inputs
        $validate = $this->validate_data(array($return_type, $period, $date, $service_description, $logtype, $statetype, $state));

        if($validate)
        {
            $Alert_summary = $this->reports_data->get_alert_summary($return_type, $period, $date, $host_name, $service_description, $logtype, $statetype, $state);
        }

        $this->output($Alert_summary);
    }

    /**
     * Fetch alert histogram
     *
     * @param string $return_type, '1' - host, '2' - service, '3' - 'hostresource', '4' - 'runningstate'
     * @param string $host_name, for more than one host , store hosts in array, 'ALL' for all host
     * @param string $service_description, for more than one service, store services, in array, 'ALL' for all the services
     * @param string $period, 'TODAY', 'LAST 24 HOURS', 'YESTERDAY', 'THIS WEEK', 'LAST 7 DAYS', 'LAST WEEK', 'THIS MONTH', 'LAST 31 DAYS', 'LAST MONTH', 'THIS YEAR', 'LAST YEAR', 'CUSTOM'     
     * @param string $start_date, for standard report , $start_date = current_time
     * @param string $end_date
     * @param String $statistic_breakdown, '1' - month, '2' - day of the month, '3' - day of the week, '4' - hour of the day
     * @param String $event_graph, 'UP', 'DOWN', 'UNREACHABLE', 'HOST PROBLEM STATE', 'OK', 'WARNING', 'UNKNOWN', 'CRITICAL', 'PENDING', 'ALL', 'SERVICE PROBLEM STATE'
     * @param String $state_type_graph, 'HARD', 'SOFT', 'ALL'
     * @param string $assume_state_retention, 'true', 'false'
     * @param string $initial_state_logged, 'true', 'false'
     * @param string $ignore_repeated_state, 'true', 'false'
     */
    public function alertHistogram()
    {
        $Alert_histogram = array();

        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $return_type = $json_request->reportType;
        $host_name = $json_request->reportHost;
        $service_description = $json_request->service;
        $period = $json_request->reportPeriod;
        $start_date = $json_request->startUnix;
        $end_date = $json_request->endUnix;
        $statistic_breakdown = $json_request->statisticsBreakdown;
        $event_graph = $json_request->eventsToGraph;
        $state_type_graph = $json_request->stateTypesToGraph;
        $assume_state_retention = $json_request->assumeStateRetention;
        $initial_state_logged = $json_request->initialStatesLogged;
        $ignore_repeated_state = $json_request->ignoreRepeatedStates;

        //convert inputs to boolean
        $assume_state_retention = $this->convert_data_bool($assume_state_retention);
        $initial_state_logged = $this->convert_data_bool($initial_state_logged);
        $ignore_repeated_state = $this->convert_data_bool($ignore_repeated_state);

        //convert date into array
        if($period == 'CUSTOM')
        {
            $date = array($start_date, $end_date);
        }
        else
        {
            $date = $start_date;
        }

        $validate = $this->validate_data(array($return_type, $statistic_breakdown, $host_name, $period, $start_date, $event_graph));
        
        if($validate)
        {
            $Alert_histogram = $this->reports_data->get_alert_histogram($return_type, $host_name, $service_description, $period, $date, $statistic_breakdown, $event_graph, $state_type_graph);
        }

        $this->output($Alert_histogram);
    }


    /**
     * Fetch all event log
     *
     * @param  String $date
     */
    public function eventlog()
    {
        $Event_logs = array();

        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $date = $json_request->date;

        //check empty inputs
        $validate = $this->validate_data(array($date));
        
        if($validate)
        {
            $Event_logs = $this->reports_data->get_event_log($date);
        }
        //invalid input
        else
        {
            $Event_logs = 1;
        }

        $this->output($Event_logs);
    }

    /**
     * Fetch all notifications
     *
     * @param String $date
     */
    public function notification()
    {
        $Notifications = array();

        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $date = $json_request->date;

        //check empty inputs
        $validate = $this->validate_data(array($date));

        if($validate)
        {
            $Notifications = $this->reports_data->get_notification($date);  
        }
        //invalid input
        else
        {
            $Notifications = 1;
        }

        $this->output($Notifications);
    }

    public function testing()
    {
        $return_type = '1';
        $period = 'THIS%20YEAR';
        $start_date = '150000000';
        $host_name = 'localhost';
        $service_description = 'ALL';
        $assume_state_retention = 'true';
        $assume_initial_state = 'true';
        $assume_state_downtime = 'true';
        $include_soft = 'true';
        $backtrack_archive = '4';
        $first_assume_host_service = 'UNDETERMINED';

        $Trend = array();

        //decode input with spacing
        $period = urldecode($period);
        $host_name = urldecode($host_name);
        $service_description = urldecode($service_description);
        $first_assume_host_service = urldecode($first_assume_host_service);

        //convert input to int
        $return_type = (int)$return_type;
        $backtrack_archive = (int)$backtrack_archive;

        //convert input to bool
        $assume_initial_state = $this->convert_data_bool($assume_initial_state);
        $assume_state_retention = $this->convert_data_bool($assume_state_retention);
        $assume_state_downtime = $this->convert_data_bool($assume_state_downtime);
        $include_soft = $this->convert_data_bool($include_soft);

        //custom report date
        if($period == 'CUSTOM')
        {
            $date = array($start_date, $end_date);
        }
        //standard report date
        else
        {
            $date = $start_date;
        }

        //check empty inputs
        $validate = $this->validate_data(array($return_type, $period, $start_date, $host_name));

        if($validate)
        {
            $Trend = $this->reports_data->get_trend($return_type, $period, $date, $host_name, $service_description, $assume_initial_state, $assume_state_retention, $assume_state_downtime, $include_soft, $first_assume_host_service, $backtrack_archive);
        }
        //incorrect inputs
        else
        {
            $Trend = 1;
        }

        $this->output($Trend);
    }

    /**
     * Add configurations in maintenance screen
     *
     * @param String $type
     * @param String $input_item
     * @param String $is_variable
     */
    public function addMaintenance()
    {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $input_item = $json_request->input;
        $is_variable = $json_request->isVar;

        $Result = 1;

        //convert input to bool
        $is_variable = $this->convert_data_bool($is_variable);

        //validate data
        $validate = $this->validate_data(array($type, $input_item));

        if($validate)
        {
            $Result = $this->maintenance_command->add($type, $input_item, $is_variable);
            $Result = $this->check_result($Result);
        }

        //invalid inputs
        else
        {
            $Result = 1;
        }

        $this->output($Result);
    }

    /**
     * Edit configurations in maintenance screen
     *
     * @param String $type
     * @param String $input_item
     */
    public function editMaintenance()
    {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $input_item = $json_request->input;

        $Result = 1;

        //convert input to int
        $type = (int)$type;

        //decode input with spacing
        $input_item = urldecode($input_item);

        //validate data
        $validate = $this->validate_data(array($type, $input_item));

        if($validate)
        {
            $Result = $this->maintenance_command->edit($type,$input_item);
            $Result = $this->check_result($Result);
        }

        //invalid inputs
        else
        {
            $Result = 1;
        }

        $this->output($Result);
    }

    /**
     * Delete configurations in maintenance screen
     *
     * @param String $type
     * @param String $input_item
     * @param String $is_variable
     */
    public function deleteMaintenance()
    {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $input_item = $json_request->input;
        $is_variable = $json_request->isVar;

        $Result = 1;

        //convert input to int
        $type = (int)$type;

        //decode input with spacing
        $input_item = urldecode($input_item);

        //convert input to bool
        $is_variable = $this->convert_data_bool($is_variable);

        //validate data
        $validate = $this->validate_data(array($type, $input_item));

        if($validate)
        {
            $Result = $this->maintenance_command->delete($type, $input_item, $is_variable);
            $Result = $this->check_result($Result);
        }

        //invalid inputs
        else
        {
            $Result = 1;
        }

        $this->output($Result);
    }

    /**
     * Fetch host or service downtime
     *
     * @param String $type, host : host, svc: service
     */
    public function downtime()
    {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;

        $Downtime = array();

        $allowed_types = array(
            'host',
            'svc'
        );

        //check empty inputs
        $validate = $this->validate_data(array($type));

        if($validate)
        {
            if(in_array($type, $allowed_types))
            {
                //get host downtime
                if($type == 'host')
                {
                    $host_downtimes = $this->nagios_data->get_collection('hostdowntime');
                    
                    foreach ($host_downtimes as $host_downtime) 
                    {
                        $Downtime[] = array(
                            'host'          => $host_downtime->host_name, 
                            'entry_time'    => $host_downtime->entry_time, 
                            'author'        => $host_downtime->author, 
                            'comment'       => $host_downtime->comment, 
                            'start_time'    => $host_downtime->start_time, 
                            'end_time'      => $host_downtime->end_time, 
                            'fixed'         => $host_downtime->fixed, 
                            'duration'      => $host_downtime->duration, 
                            'downtime_id'   => $host_downtime->downtime_id, 
                            'triggered_id'  => $host_downtime->triggered_by, 
                            'entry_time'    => $host_downtime->entry_time
                        );
                    }
                }

                //get service downtime
                else
                {
                    $service_downtimes = $this->nagios_data->get_collection('servicedowntime');

                    foreach ($service_downtimes as $service_downtime) 
                    {
                        $Downtime[] = array(
                            'host'          => $service_downtime->host_name, 
                            'service'       => $service_downtime->service_description, 
                            'entry_time'    => $service_downtime->entry_time, 
                            'author'        => $service_downtime->author, 
                            'comment'       => $service_downtime->comment, 
                            'start_time'    => $service_downtime->start_time, 
                            'end_time'      => $service_downtime->end_time, 
                            'fixed'         => $service_downtime->fixed, 
                            'duration'      => $service_downtime->duration, 
                            'downtime_id'   => $service_downtime->downtime_id, 
                            'triggered_id'  => $service_downtime->triggered_by, 
                            'entry_time'    => $service_downtime->entry_time
                        );
                    }
                }
            }
            //incorrect input
            else
            {
                $Downtime = 1;
            }
        }
        //invalid input
        else
        {
            $Downtime = 1;
        }

        $this->output($Downtime);
    }

    /**
     * Delete host or service downtime
     *
     * @param String $type, 'host' : host, 'svc' : service
     * @param String $downtime_id
     */
    public function deleteDowntime()
    {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $downtime_id = $json_request->id;
        $type = $json_request->type;

        $Result = 1;

        //check empty inputs
        $validate = $this->validate_data(array($type, $downtime_id));

        if($validate)
        {
            //delete host downtime
            if($type == 'host')
            {
                $Result = $this->system_commands->delete_host_downtime($downtime_id);
                $Result = $this->check_result($Result);
            }

            //delete service downtime
            else if($type == 'svc')
            {
                $Result = $this->system_commands->delete_svc_downtime($downtime_id);
                $Result = $this->check_result($Result);
            }

            //incorrect input
            else
            {
                $Result = 1;
            }
        }
        //invalid input
        else
        {
            $Result = 1;
        }

        $this->output($Result);
    }

    

    /**
     * Schedule downtime
     *
     * @param String $type, host : host, svc : service, hostsvc : hostservice
     * @param String $host_name
     * @param String $service_description, [if type is host or hostsvc, $service = '']
     * @param String $start_time
     * @param String $end_time
     * @param String $fixed, "true", "false"
     * @param String $trigger_id
     * @param String $duration , in minutes
     * @param String $author
     * @param String $comments
     */
    public function scheduleDowntime()
    {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $host_name = $json_request->host;
        $service_description = $json_request->service;
        $start_time = $json_request->start_time;
        $end_time = $json_request->end_time;
        $fixed = $json_request->fixed;
        $trigger_id = $json_request->trigger;
        $duration = $json_request->duration;
        $author = $json_request->author;
        $comments = $json_request->comment;

        $Result = 1;

        $allowed_types = array(
            'host',
            'svc',
            'hostsvc'
        );

        //convert fixed to boolean
        $fixed = $this->convert_data_bool($fixed);
        
        //check empty inputs
        $validate = $this->validate_data(array($type, $host_name, $start_time, $end_time, $duration, $author, $comments));

        if($validate)
        {
            //compare type with allowed types
            if(in_array($type, $allowed_types))
            {
                //schedule host downtime
                if($type == 'host')
                {
                    $Result = $this->system_commands->schedule_host_downtime($host_name, $start_time, $end_time, $fixed, $trigger_id, $duration, $author, $comments);
                    $Result = $this->check_result($Result);
                }

                //schedule service downtime
                else if($type == 'svc')
                {
                    $Result = $this->system_commands->schedule_svc_downtime($host_name, $service_description, $start_time, $end_time, $fixed, $trigger_id, $duration, $author, $comments);
                    $Result = $this->check_result($Result);
                }

                //schedule host service downtime
                else
                {
                    $Result = $this->system_commands->schedule_host_svc_downtime($host_name, $start_time, $end_time, $fixed, $trigger_id, $duration, $author, $comments);
                    $Result = $this->check_result($Result);
                }
            }
            //incorrect input
            else
            {
                $Result = 1;
            }
        }
        //invalid input
        else
        {
            $Result = 1;
        }
        
        $this->output($Result);
    }



    /**
     * Return performance info of nagios
     */
    public function performanceInfo()
    {
        $PerformanceInfo = $this->system_commands->get_return_array('PERFORMANCE');

        $this->output($PerformanceInfo);
    }

    /**
     * Return process info of nagios
     */
    public function processInfo()
    {
        $ProcessInfo = $this->system_commands->get_return_array('PROCESS');

        $this->output($ProcessInfo);
    }

    /**
     * Return schedule queue of host and service
     */
    public function scheduleQueue()
    {
        $Schedule = array();

        //get data of hosts, services, host resources and running states
        $hosts = $this->nagios_data->get_collection("hoststatus");
        $services = $this->nagios_data->get_collection("servicestatus");
        $hostresources = $this->nagios_data->get_collection("hostresourcestatus");
        $runningstates = $this->nagios_data->get_collection("runningstatestatus");
        
        foreach ($hosts as $host) 
        {
            $Schedule[] = array(
                'type'          => "host", 
                'hostname'      => $host->host_name, 
                'lastcheck'     => $host->last_check, 
                'nextcheck'     => $host->next_check, 
                'activecheck'   => $host->active_checks_enabled
            );
        }

        foreach ($services as $service) 
        {
            $Schedule[] = array(
                'type'          =>"service", 
                'hostname'      => $service->host_name, 
                'servicename'   => $service->service_description, 
                'lastcheck'     => $service->last_check, 
                'nextcheck'     => $service->next_check,
                'activecheck'   => $service->active_checks_enabled
            );
        }

        foreach ($hostresources as $hostresource) 
        {
            $Schedule[] = array(
                'type'          => "hostresource", 
                'hostname'      => $hostresource->host_name, 
                'servicename'   => $hostresource->service_description, 
                'lastcheck'     => $hostresource->last_check, 
                'nextcheck'     => $hostresource->next_check, 
                'activecheck'   => $hostresource->active_checks_enabled
            );
        }

        foreach ($runningstates as $runningstate) 
        {
            $Schedule[] = array(
                'type'          => "runningstate", 
                'hostname'      => $runningstate->host_name, 
                'servicename'   => $runningstate->service_description, 
                'lastcheck'     => $runningstate->last_check, 
                'nextcheck'     => $runningstate->next_check, 
                'activecheck'   => $runningstate->active_checks_enabled
            );
        }

        //sort schedule according to the next check time
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
    public function comments() 
    {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;

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
     * @param String $host_name
     * @param String $service_description
     * @param String $persistent 
     * @param String $author
     * @param String $comments
     */
    public function addComments()
    {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $host_name = $json_request->host;
        $service_description = $json_request->service;
        $persistent = $json_request->persistent;
        $author = $json_request->author;
        $comments = $json_request->comment;

        $Result = 1;

        $allowed_types = array(
            'host',
            'svc'
        );
        
        //convert persistent to bool
        $persistent = $this->convert_data_bool($persistent);

        //check for empty input
        $validate = $this->validate_data(array($type, $host_name, $author, $comments));
        
        if($validate)
        {
            //compare types with allowed types
            if(in_array($type, $allowed_types))
            {
                if($type == 'host')
                {
                    $Result = $this->system_commands->add_host_comment($host_name, $persistent, $author, $comments);
                    $Result = $this->check_result($Result);
                }
                else
                {
                    $Result = $this->system_commands->add_svc_comment($host_name, $service_description, $persistent, $author, $comments);
                    $Result = $this->check_result($Result);
                }
            }
            //incorrect inputs
            else
            {
                $Result = 1;
            }
        }
        //invalid inputs
        else
        {
            $Result = 1;
        }

        $this->output($Result);
    }

    /**
     * Delete comments
     *
     * @param String $id
     * @param String $type, host : host, svc : service
     */
    public function deleteComments()
    {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $id = $json_request->id;
        $type = $json_request->type;

        $Result = 1;

        $allowed_types = array(
            'host',
            'svc'
        );

        //check for empty input
        $validate = $this->validate_data(array($id, $type));

        if($validate)
        {
            //compare type with allowed types
            if(in_array($type, $allowed_types))
            {
                //delete host comment
                if($type == 'host')
                {
                    $Result = $this->system_commands->delete_host_comment($id);
                    $Result = $this->check_result($Result);
                }

                //delete service comment
                else
                {
                    $Result = $this->system_commands->delete_svc_comment($id);
                    $Result = $this->check_result($Result);
                }
            }
            //incorrect input
            else
            {
                $Result = 1;
            }
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
    }

    

    /**
     * Enable or disable service check
     *
     * @param String $type , true - 'enable' ,false - 'disable'
     * @param String $hostname
     * @param String $service_description
     */
    public function servicecheck()
    {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $host_name = $json_request->host;
        $service_description = $json_request->service;

        $Result = 1;

        $validate = $this->validate_data($type, $host_name, $service_description);

        if($validate)
        {
            if($type == 'true')
            {
                $Result = $this->system_commands->enable_svc_check($host_name, $service_description);
                $Result = $this->check_result($Result);
            }
            else if ($type == 'false')
            {
                $Result = $this->system_commands->disable_svc_check($host_name, $service_description);
                $Result = $this->check_result($Result);
            }
            //incorrect inputs
            else
            {
                $Result = 1;
            }
        }
        //invalid inputs
        else
        {
            $Result = 1;
        }

        $this->output($Result);
    }

    /**
     * Enable or disable all notifications
     *
     * @param String $type, true = 'enable', false = 'disable'
     */
    public function allnotifications()
    {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        
        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->enable_all_notification();
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result = $this->system_commands->disable_all_notification();
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
    }

    /**
     * Restart or shut down nagios
     *
     * @param String $type , 'restart', 'shutdown'
     */
    public function nagiosOperation()
    {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;

        $Result = 1;

        if($type == 'restart')
        {
            $Result = $this->system_commands->restart_nagios();
            $Result = $this->check_result($Result);
        }
        else if($type == 'shutdown')
        {
            $Result = $this->system_commands->shutdown_nagios();
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
    }

    /**
     * Enable or disable service notification
     *
     * @param String $type, true = 'enable', false ='disable'
     * @param String $host_name
     * @param String $service_description
     */
    public function serviceNotification()
    {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $host_name = $json_request->host;
        $service_description = $json_request->service;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->enable_svc_notification($host_name, $service_description);
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result = $this->system_commands->disable_svc_notification($host_name, $service_description);
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
    }

    /**
     * Delete all host or service comment
     *
     * @param String $type, 'host', 'service'
     * @param String $host_name
     * @param String $service_description , [if delete all host name, $service_description should be '']
     */
    public function deleteAllComment()
    {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $host_name = $json_request->host;
        $service_description = $json_request->service;

        $Result = 1;

        //delete all host comment
        if($type == 'host')
        {
           $Result =  $this->system_commands->delete_all_host_comment($host_name);
           $Result = $this->check_result($Result);
        }

        //delete all service comment
        else if($type == 'service')
        {
            $Result = $this->system_commands->delete_all_svc_comment($host_name, $service_description);
            $Result = $this->check_result($Result);
        }

        else
        {
            $Result = 1;
        }

        $this->output($Result);
    }

    /**
     * Enable or disable host notification
     *
     * @param Bool $type, true = 'enable', false ='disable'
     * @param String $host_name
     */
    public function hostNotification()
    {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $host_name = $json_request->host;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->enable_host_notification($host_name);
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result =  $this->system_commands->disable_host_notification($host_name);
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
    }

    /**
     * Schedule host or service check
     *
     * @param String $type, 'host', 'service', 'hostsvc'
     * @param String $host_name
     * @param String $service_description , [if type is host, service_description should be '']
     * @param String $checktime
     * @param String $force_check 
     */
    public function scheduleCheck()
    {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $host_name = $json_request->host;
        $service_description = $json_request->service;
        $checktime = $json_request->time;
        $force_check = $json_request->force;

        $Result = 1;

        //convert force check to bool
        $force_check = $this->convert_data_bool($force_check);

        if($type == 'host')
        {
            $Result = $this->system_commands->schedule_host_check($host_name, $checktime, $force_check);
            $Result = $this->check_result($Result);
        }
        else if($type == 'hostsvc')
        {
            $Result = $this->system_commands->schedule_host_svc_check($host_name, $checktime, $force_check);
            $Result = $this->check_result($Result);
        }
        else if($type == 'service')
        {
            $Result = $this->system_commands->schedule_svc_check($host_name, $service_description, $checktime, $force_check);
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
    }

    /**
     * Enable or disable host service check
     *
     * @param String $type, true = 'enable', false ='disable'
     * @param String $host_name
     */
     public function hostServiceCheck()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $host_name = $json_request->host;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->enable_host_svc_check($host_name);
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result = $this->system_commands->disable_host_svc_check($host_name);
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
     } 

     /**
      * Enable or disable host service notification
      *
      * @param String $type, true = 'enable', false ='disable'
      * @param String $host_name
      */
     public function hostServiceNotification()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $host_name = $json_request->host;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->enable_host_svc_notification($host_name);
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result = $this->system_commands->disable_host_svc_notification($host_name);
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
     }

     /**
      * Acknowledge host or service problem
      *
      * @param String $type, 'host', 'service'
      * @param String $host_name
      * @param String $service_description, [if type is 'host', service_description will be '']
      * @param String $sticky, true, false
      * @param String $notify, true, false
      * @param String $persistent, true, false
      * @param String $author
      * @param String $comment
      */
     public function acknowledgeProblem()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $host_name = $json_request->host;
        $service_description = $json_request->service;
        $sticky = $json_request->stickyack;
        $notify = $json_request->sendnotify;
        $persistent = $json_request->persistent;
        $author = $json_request->author;
        $comment = $json_request->comment;

        $Result = 1;

        //convert input to bool
        $sticky = $this->convert_data_bool($sticky);
        $notify = $this->convert_data_bool($notify);
        $persistent = $this->convert_data_bool($persistent);

        if($type == 'host')
        {
            $Result = $this->system_commands->acknowledge_host_problem($host_name, $sticky, $notify, $persistent, $author, $comment);
            $Result = $this->check_result($Result);
        }
        else if($type == 'service')
        {
            $Result = $this->system_commands->acknowledge_svc_problem($host_name, $service_description, $sticky, $notify, $persistent, $author, $comment);
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
     }

     /**
      * Start or stop all service check
      *
      * @param String $type, true = 'start', false = 'stop'
      */
     public function allServiceCheck()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->start_svc_check();
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result = $this->system_commands->stop_svc_check();
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
     }

     /**
      * Start or stop all passive service check
      *
      * @param String $type, true = 'start', false = 'stop'
      */
     public function allPassiveServiceCheck()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->start_passive_svc_check();
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result = $this->system_commands->stop_passive_svc_check();
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
     }

     /**
      * Enable or disable passive service check
      *
      * @param String $type, true - start, false - disable
      * @param String $host_name
      * @param String $service_description
      */
     public function passiveServiceCheck()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $host_name = $json_request->host;
        $service_description = $json_request->service;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->enable_passive_svc_check($host_name, $service_description);
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result = $this->system_commands->disable_passive_svc_check($host_name, $service_description);
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
     }

     /**
      * Enable or disable event handler
      *
      * @param String $type, true = 'enable', false ='disable'
      */
     public function eventHandler()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->enable_event_handler();
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result = $this->system_commands->disable_event_handler();
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
     }

     /**
      * Enable or disable host check
      *
      * @param String $type, true = 'enable', false ='disable'
      * @param String $host_name
      */
     public function hostCheck()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $host_name = $json_request->host;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->enable_host_check($host_name);
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result = $this->system_commands->disable_host_check($host_name);
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
     }

     /**
      * Start or stop obsess over service check
      *
      * @param String $type, true - start, false - disable
      */
     public function obsessOverServiceCheck()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->start_obsess_over_svc_check();
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result = $this->system_commands->stop_obsess_over_svc_check();
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
     }

     /**
      * Start or stop obsess over host check
      *
      * @param String $type, true - start, false - stop
      */
     public function obsessOverHostCheck()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->start_obsess_over_host_check();
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result = $this->system_commands->stop_obsess_over_host_check();
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
     }

     /**
      * Start or stop obsess over host
      *
      * @param Bool $type, true - start, false - stop
      * @param String $host_name
      */
     public function obsessOverHost()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $host_name = $json_request->host;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->start_obsess_over_host($host_name);
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result = $this->system_commands->stop_obsess_over_host($host_name);
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
     }

     /**
      * Start or stop obsess over service
      *
      * @param Bool $type, true - start, false - stop
      * @param String $host_name
      * @param String $service_description
      */
     public function obsessOverService()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $host_name = $json_request->host;
        $service_description = $json_request->service;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->start_obsess_over_svc($host_name, $service_description);
            $Result = $this->check_result($Result);
        }
        else if ($type == 'false')
        {
            $Result = $this->system_commands->stop_obsess_over_svc($host_name, $service_description);
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
     }

     /**
      * Enable or disable performance data
      *
      * @param Bool $type, true = 'enable', false ='disable'
      */
     public function performanceData()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->enable_performance_data();
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result = $this->system_commands->disable_performance_data();
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
     }

     /**
      * Start or stop all host check
      *
      * @param Bool $type, true - start, false - stop
      */
     public function allHostCheck()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->start_host_check();
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result = $this->system_commands->stop_host_check();
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
     }

     /**
      * Start or stop all passive host check
      *
      * @param Bool $type, true - start, false - stop
      */
     public function allPassiveHostCheck()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->start_passive_host_check();
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result = $this->system_commands->stop_passive_host_check();
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
     }

     /**
      * Enable or disable passive host check
      *
      * @param Bool $type, true = 'enable', false ='disable'
      * @param String $host_name
      */
     public function passiveHostCheck()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $host_name = $json_request->host;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->enable_passive_host_check($host_name);
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result = $this->system_commands->disable_passive_host_check($host_name);
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
     }

     /**
      * Enable or disable all flap detection
      *
      * @param Bool $type, true = 'enable', false ='disable'
      */
     public function allFlapDetection()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->enable_flap_detection();
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result = $this->system_commands->disable_flap_detection();
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
     }

     /**
      * Enable or disable host flap detection
      *
      * @param Bool $type, true = 'enable', false ='disable'
      * @param String $host_name
      */
     public function hostFlapDetection()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $host_name = $json_request->host;

        $Result = 1;

        if($type == 'true')
        {
            $Result = $this->system_commands->enable_host_flap_detection($host_name);
            $Result = $this->check_result($Result);
        }
        else if($type == 'false')
        {
            $Result = $this->system_commands->disable_host_flap_detection($host_name);
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
     }

     /**
      * Enable or disable service flap detection
      *
      * @param Bool $type, true = 'enable', false ='disable'
      * @param String $host_name
      * @param String $service_description
      */
     public function serviceFlapDetection()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $host_name = $json_request->host;
        $service_description = $json_request->service;

        $Result = 1;

        //check empty inputs
        $validate = $this->validate_data($type, $host_name, $service_description);

        if($validate)
        {
            if($type == 'true')
            {
                $Result = $this->system_commands->enable_svc_flap_detection($host_name, $service_description);
                $Result = $this->check_result($Result);
            }
            else if($type == 'false')
            {
                $Result = $this->system_commands->disable_svc_flap_detection($host_name, $service_description);
                $Result = $this->check_result($Result);
            }
        }
        //invalid inputs
        else
        {
            $Result = 1; 
        }

        $this->output($Result);
     }


     /**
      * Send custom host or service notifications
      *
      * @param String $type , 'host', 'service'
      * @param String $host_name
      * @param String $service_description
      * @param String $force, true, false
      * @param String $broadcast, true, false
      * @param String $author
      * @param String $comment
      */
     public function sendCustomNotification()
     {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $type = $json_request->type;
        $host_name = $json_request->host;
        $service_description = $json_request->service;
        $force = $json_request->force;
        $broadcast = $json_request->broadcast;
        $author = $json_request->hostAuthor;
        $comment = $json_request->hostComment;

        $Result = 1;

        //convert inputs to bool
        $force = $this->convert_data_bool($force);
        $broadcast = $this->convert_data_bool($broadcast);

        if($type == 'host')
        {
            $Result = $this->system_commands->send_custom_host_notification($host_name, $force, $broadcast, $author, $comment);
            $Result = $this->check_result($Result);
        }
        else if($type == 'service')
        {
            $Result = $this->system_commands->send_custom_svc_notification($host_name, $service_description, $force, $broadcast, $author, $comment);
            $Result = $this->check_result($Result);
        }
        else
        {
            $Result = 1;
        }

        $this->output($Result);
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
     * @param  string $service_description   service description (requires host name)
     * @param  string $operation operation
     */
    public function serviceremote($host_name='',$service_description='',$operation='')
    {
        $service_description = urldecode($service_description);

        $Data = $this->nagios_data->get_collection('servicestatus');
        $Result = array();

        //fetch by host name
        if(!empty($host_name))
        {

            if(empty($service_description))
            {
                $Result['code'] = -1;//Please provide a service name.
                return $this->output($Result);
            } 
            else 
            {
                $Data = $Data->get_index_key('host_name',$host_name)->get_where('service_description',$service_description)->first();
                
                if( empty($Data) )
                {
                    $Result['code'] = -2;//Unknown service name: '.$service_description
                    return $this->output($Result);
                } 
                
                if(!$this->is_remote_enabled($Data))
                {
                    $Result['code'] = -4;//Fail to remote the service.
                    return $this->output($Result);
                }

                if($operation !== '')
                {
                    $operation = strtolower($operation);
                    switch ($operation) 
                    {
                        case 'start':
                        case 'stop':
                        case 'pause':
                            $Result['code'] = 0;
                            $Result['message'] = shell_exec("nohup ./application/scripts/remote_windows_services.sh $host_name.'$this->domain'.local $operation $service_description 2>&1 &");
                            $Result['state'] = $this->remove_string_spaces(shell_exec("nohup ./application/scripts/get_windows_services.sh $host_name.'$this->domain'.local $service_description 2>&1 &"));
                            $this->output($Result);
                            return;
                        default:
                            $Result['code'] = -3;//Unknown service operation.
                            return $this->output($Result);
                    }
                }
            }

        }

        $Result['code'] = -4;//Fail to remote the service.
        return $this->output($Result);
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
        $Result = array();

        //fetch by host name
        if(!empty($host_name))
        {

            if(empty($service_description))
            {
                $Result['code'] = -1;//Please provide a service name.
                return $this->output($Result);
            } 
            else 
            {
                $Data = $Data->get_index_key('host_name',$host_name)->get_where('service_description',$service_description)->first();
                
                if( empty($Data) )
                {
                    $Result['code'] = -2;//Unknown service name: '.$service_description
                    return $this->output($Result);
                } 
                
                if(!$this->is_remote_enabled($Data))
                {
                    $Result['code'] = -4;//Remote service is disabled.
                    return $this->output($Result);
                }

                $Result['code'] = 0;
                $Result['state'] = $this->remove_string_spaces(shell_exec("nohup ./application/scripts/get_windows_services.sh $host_name.'$this->domain'.local $service_description 2>&1 &"));
                $this->output($Result);
                return;
            }

        }

        $Result['code'] = -3;//Fail to get the service.
        return $this->output($Result);
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
        $Result = array();

        //fetch by host name
        if(!empty($host_name))
        {

            if(empty($service_description))
            {
                $Result['code'] = -1;//Please provide a service name.
                return $this->output($Result);
            } 
            else 
            {
                $Data = $Data->get_index_key('host_name',$host_name)->get_where('service_description',$service_description)->first();
                if( empty($Data) )
                {
                    $Result['code'] = -2;//Unknown service name: '.$service_description
                    return $this->output($Result);
                } 
                
                if($this->is_logfile_defined($Data))
                {
                    $this->get_service_log_path($Data, $log_dir, $log_files);
                }
                else
                {
                    log_message('error', 'No log files for this service.');
                    $Result['code'] = -6;//No log files for this service.
                    return $this->output($Result);
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
                                $Result['code'] = -3;//Logs directory cannot be accessed.
                                $this->sftp_close($connection);
                                return $this->output($Result);
                            }

                            
                            while (false != ($entry = readdir($handle)))
                            {
                                if (preg_match('/'.$regex_log_file.'/i', $entry) === 1)
                                {
                                    $tmp = stat("ssh2.sftp://$sftp/".$log_dir."/$entry");
                                    if($tmp == false)
                                    {
                                        log_message('error', 'Log file cannot be read.'.$entry);
                                        $Result['code'] = -4;//Log file cannot be read.
                                        $this->sftp_close($connection);
                                        return $this->output($Result);
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
                                $Result['code'] = -3;//Logs directory cannot be accessed.
                                $this->sftp_close($connection);
                                return $this->output($Result);
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
                                        $Result['code'] = -4;//Log file cannot be read.
                                        $this->sftp_close($connection);
                                        return $this->output($Result);
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
                    $Result['code'] = -5;//Could not connect to the remote host: '.$host_name
                    $this->sftp_close($connection);
                    return $this->output($Result);
                }

                $this->sftp_close($connection);

                if(empty($Data))
                {
                    log_message('error', 'No log files for this service.');
                    $Result['code'] = -6;//No log files for this service.
                    return $this->output($Result);
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
    public function servicelogdownload()
    {
        // Retrieve raw data since Code Igniter doesn't handle JSON request
        $raw_request_body = file_get_contents('php://input');
        $json_request = json_decode($raw_request_body);

        // Obtain parameters from post method
        $host_name = $json_request->host;
        $service_description = $json_request->service;
        $filenames = $json_request->filenames;

        if(!preg_match('/^((((?!%7C).)+)\..*)(%7C(((?!%7C).)+)\..*)*$/', $filenames))
        {
            $Result['code'] = -5;//Failed to prepare download.
            return $this->output($Result);
        }
        
        $Data = $this->nagios_data->get_collection('servicestatus');
        $Result = array();
        
        //fetch by host name
        if(!empty($host_name))
        {

            if(empty($service_description))
            {
                $Result['code'] = -1;//Please provide a service name.
                return $this->output($Result);
            } 
            else 
            {
                $Data = $Data->get_index_key('host_name',$host_name)->get_where('service_description',$service_description)->first();
                if( empty($Data) )
                {
                    $Result['code'] = -2;//Unknown service name: '.$service_description
                    return $this->output($Result);
                }
                
                if($this->is_logfile_defined($Data))
                {
                    $this->get_service_log_path($Data, $log_dir, $log_files);
                }
                else
                {
                    log_message('error', 'No log files for this service.');
                    $Result['code'] = -6;//No log files for this service.
                    return $this->output($Result);
                }

                $filenames = explode('|', $filenames);
                $connection;

                //If the service is being monitored, download the log file
                $sftp = $this->sftp_connect($host_name.'.'.$this->domain.'.local', $this->user_domain.'\\'.$this->user, $this->passwd, $connection);

                if(! $sftp)
                {
                    log_message('error', 'Failed to access SFTP server.');
                    $Result['code'] = -3;//Failed to access SFTP server
                    $this->sftp_close($connection);
                    return $this->output($Result);
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
                            $Result['code'] = -4;//Invalid file request('.$filename.') for service:'.$service_description
                            return $this->output($Result);
                        }
                    }
                    else
                    {
                        log_message('error', 'Invalid file request('.$filename.') for service:'.$service_description);
                        $this->sftp_close($connection);
                        $Result['code'] = -4;//Invalid file request('.$filename.') for service:'.$service_description
                        return $this->output($Result);
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
                $Result['code'] = 0;
                $Result['key'] = $newKey;
                return $this->output($Result);
            }
        }

        $Result['code'] = -5;//Failed to prepare download.
        return $this->output($Result);
    }

    /**
     * Download service log file based on key
     * 
     * @param  string $key host name filter
     */
    public function download($key='')
    {
        $files = $this->session->userdata('files');
        $Result = array();

        if(empty($key))
        {
            $Result['code'] = -1;//Missing key.
            return $this->output($Result);
        }

        if(!array_key_exists($key, $files))
        {
            $Result['code'] = -2;//Invalid or expired key.
            return $this->output($Result);
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

    //check and validate data
    private function validate_data($data)
    {
        $validate = false;
        
        foreach($data as $input)
        {
            //empty input
            if(empty($input))
            {
                $validate = true;
                break;
            }
            else
            {
                if(is_string($input))
                {
                    //invalid input
                    if(strpos($input, ';') != false)
                    {
                        $validate = true;
                    }
                }
            }
        }

        return !$validate;
    }

    //convert string data to boolean
    private function convert_data_bool($data)
    {
        if($data == 'true')
        {
            return true;
        }
        else if($data == 'false')
        {
            return false;
        }
        else
        {
            return null;
        }
    }

    //convert result to result code
    private function check_result($data)
    {
        if($data)
        {
            return 0; //command run successed
        }
        else
        {
            return 2; //command run failed
        }
    }

    //get hosts inside a host group
    private function get_hosts($data)
    {
        $hostgroups = $this->nagios_data->get_collection('hostgroup');

        foreach($hostgroups as $hostgroup)
        {
            $hostgroup_name[] = array('hostgroup_name'=> $hostgroup->alias, 'members'=> $hostgroup->members);
        }

        foreach ($hostgroup_name as $hostgroup) 
        {
            if($hostgroup['hostgroup_name'] == $data)
            {
                $host[] = $hostgroup['members'];
            }
        }

        return $host;
    }

    //get services inside a service group
    private function get_services($data)
    {
        $servicegroups = $this->nagios_data->get_collection('servicegroup');

        foreach ($servicegroups as $servicegroup) 
        {
            $servicegroup_name = array('servicegroup_name'=> $servicegroup->alias, 'members' => $servicegroup->members);
        }

        foreach ($servicegroup_name as $servicegroup) 
        {
            if($servicegroup['servicegroup_name'] == $data)
            {
                $service[] = $servicegroup['members'];
            }
        }

        return $service;
    }

}

/* End of file api.php */
/* Location: ./application/controllers/api.php */
