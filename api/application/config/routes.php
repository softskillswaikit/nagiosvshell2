<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route["default_controller"] 	= "api";
$route["404_override"] 			= "";
$route["status"] 				= "api";

/*
* Create by Soon Wei Liang
*/
//NAGIOS INFO SECTION START
$route["configurations/(:any)"]             = "api/configurations/$1";
$route["configurations"]                    = "api/configurations";
$route["hostgroupstatus/(:any)"]            = "api/hostgroupstatus/$1";
$route["hostgroupstatus*"]                  = "api/hostgroupstatus";
$route["hoststatus/(:any)"]                 = "api/hoststatus/$1";
$route["hoststatus*"]                       = "api/hoststatus";
$route["overview"]                          = "api/tacticalOverview";
$route["nagiosinfo"]                        = "api/info";
$route["nagiosstatus"]                      = "api/programStatus";
$route["quicksearch"]                       = "api/quicksearch";
$route["servicegroupstatus/(:any)"]         = "api/servicegroupstatus/$1";
$route["servicegroupstatus*"]               = "api/servicegroupstatus";
$route["servicestate/(:any)/(:any)"]        = "api/servicestate/$1/$2";
$route["download/(:any)"]                   = "api/download/$1";
$route["vshellconfig"]                      = "api/vshellconfig";
$route["name"]                              = "api/name";
$route["servicestatus*"]                    = "api/servicestatus";
$route["servicestatus/(:any)"]              = "api/servicestatus/$1";
$route["servicestatus/(:any)/(:any)"]       = "api/servicestatus/$1/$2";
$route["serviceremote/(:any)/(:any)/(:any)"]= "api/serviceremote/$1/$2/$3";
$route["servicelogs/(:any)/(:any)"]         = "api/servicelogs/$1/$2";
$route["servicelogdownload"]                = "api/servicelogdownload";
//NAGIOS INFO SECTION END


//ATTRIBUTES SECTION START
//Hosts
$route["hostcheck"]                         = "api/hostCheck";
$route["passivehostcheck"]                  = "api/passiveHostCheck";
$route["obsessoverhost"]                    = "api/obsessOverHost";
$route["hostflapdetection"]                 = "api/hostFlapDetection"; 
$route["hostnotification"]                  = "api/hostNotification";

//Services
$route["servicecheck"]                      = "api/servicecheck";
$route["passiveservicecheck"]               = "api/passiveServiceCheck";
$route["obsessoverservice"]                 = "api/obsessOverService";
$route["serviceflapdetection"]              = "api/serviceFlapDetection";
$route["servicenotification"]               = "api/serviceNotification";

//Host services
$route["hostservicecheck"]                  = "api/hostServiceCheck";
$route["hostservicenotification"]           = "api/hostServiceNotification";

//System commands
$route["deleteallcomment"]                  = "api/deleteAllComment";
$route["acknowledgeproblem"]                = "api/acknowledgeProblem";
$route["sendcustomnotification"]            = "api/sendCustomNotification";
//ATTRIBUTES SECTION END


//REPORTS SECTION START
$route["availability"]                      = "api/availability";
$route["trend"]                             = "api/trend";
$route["alerthistory"]                      = "api/alertHistory";
$route["alertsummary"]                      = "api/alertSummary";
$route["alerthistogram"]                    = "api/alertHistogram";
$route["eventlog"]                          = "api/eventlog";
$route["notification"]                      = "api/notification";
//REPORTS SECTION END


//SYSTEM SECTION START
//Comments
$route["comments"]                          = "api/comments";
$route["deletecomments"]                    = "api/deleteComments";
$route["addcomments"]                       = "api/addComments";

//Downtime
$route["downtime"]                          = "api/downtime";
$route["deletedowntime"]                    = "api/deleteDowntime";
$route["scheduledowntime"]                  = "api/scheduleDowntime";


//Performance info
$route["performanceinfo"]                   = "api/performanceInfo";
$route["nagiosoperation"]                   = "api/nagiosOperation";
$route["allnotifications"]                  = "api/allnotifications";
$route["allservicecheck"]                   = "api/allServiceCheck";
$route["allpassiveservicecheck"]            = "api/allPassiveServiceCheck";
$route["allhostcheck"]                      = "api/allHostCheck";
$route["allpassivehostcheck"]               = "api/allPassiveHostCheck";
$route["eventhandler"]                      = "api/eventHandler";
$route["obsessoverhostcheck"]               = "api/obsessOverHostCheck";
$route["obsessoverservicecheck"]            = "api/obsessOverServiceCheck";
$route["allflapdetection"]                  = "api/allFlapDetection";
$route["performancedata"]                   = "api/performanceData";

//Process info
$route["processinfo"]                       = "api/processInfo";

//Schedule queue
$route["schedulequeue"]                     = "api/scheduleQueue";
$route["schedulecheck"]                     = "api/scheduleCheck";
//SYSTEM SECTION END


//MAINTAINANCE SCREEN START
$route["addmaintenance"]                    = "api/addMaintenance";
$route["editmaintenance"]                   = "api/editMaintenance";
$route["deletemaintenance"]                 = "api/deleteMaintenance";
//MAINTAINANCE SCREEN END


//TESTING SECTION
$route["testing"]                           = "api/testing";


/* End of file routes.php */
/* Location: ./application/config/routes.php */
