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
$route["configurations/(:any)"] 	= "api/configurations/$1";
$route["configurations"] 			= "api/configurations";
$route["hostgroupstatus/(:any)"] 	= "api/hostgroupstatus/$1";
$route["hostgroupstatus*"] 			= "api/hostgroupstatus";
$route["hoststatus/(:any)"]			= "api/hoststatus/$1";
$route["hoststatus*"] 				= "api/hoststatus";
$route["overview"] 					= "api/tacticalOverview";
$route["nagiosinfo"] 				= "api/info";
$route["nagiosstatus"] 				= "api/programStatus";
$route["quicksearch"] 				= "api/quicksearch";
$route["servicegroupstatus/(:any)"] = "api/servicegroupstatus/$1";
$route["servicegroupstatus*"] 		= "api/servicegroupstatus";
$route["servicestate/(:any)/(:any)"]= "api/servicestate/$1/$2";
$route["download/(:any)"] 			= "api/download/$1";
$route["vshellconfig"] 				= "api/vshellconfig";
$route["name"] 						= "api/name";
$route["servicestatus*"] 			= "api/servicestatus";
$route["servicestatus/(:any)"] 		= "api/servicestatus/$1";
$route["servicestatus/(:any)/(:any)"] 			= "api/servicestatus/$1/$2";
$route["serviceremote/(:any)/(:any)/(:any)"] 	= "api/serviceremote/$1/$2/$3";
$route["servicelogs/(:any)/(:any)"] 			= "api/servicelogs/$1/$2";
$route["servicelogdownload/(:any)/(:any)/(((((?!%7C).)+)\..*)(%7C(((?!%7C).)+)\..*)*)"] = "api/servicelogdownload/$1/$2/$3";

//NAGIOS INFO SECTION END


//ATTRIBUTES SECTION START
//Hosts
$route["hostcheck/(:any)/(:any)"] 			= "api/hostCheck/$1/$2";
$route["passivehostcheck/(:any)/(:any)"] 	= "api/passiveHostCheck/$1/$2";
$route["obsessoverhost/(:any)/(:any)"] 		= "api/obsessOverHost/$1/$2";
$route["hostflapdetection/(:any)/(:any)"] 	= "api/hostFlapDetection/$1/$2"; 
$route["hostnotification/(:any)/(:any)"] 	= "api/hostNotification/$1/$2";

//Services
$route["servicecheck/(:any)/(:any)/(:any)"] 		= "api/servicecheck/$1/$2/$3";
$route["passiveservicecheck/(:any)/(:any)/(:any)"] 	= "api/passiveServiceCheck/$1/$2/$3";
$route["obsessoverservice/(:any)/(:any)/(:any)"]	= "api/obsessOverService/$1/$2/$3";
$route["serviceflapdetection/(:any)/(:any)/(:any)"] = "api/serviceFlapDetection/$1/$2/$3";
$route["servicenotification/(:any)/(:any)/(:any)"] 	= "api/serviceNotification/$1/$2/$3";

//Host services
$route["hostservicecheck/(:any)/(:any)"] 		= "api/hostServiceCheck/$1/$2";
$route["hostservicenotification/(:any)/(:any)"] = "api/hostServiceNotification/$1/$2";

//System commands
$route["deleteallcomment/(:any)/(:any)/(:any)"] = "api/deleteAllComment/$1/$2/$3";
$route["acknowledgeproblem/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)"] 	= "api/acknowledgeProblem/$1/$2/$3/$4/$5/$6/$7/$8";
$route["sendcustomnotification/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)"] 		= "api/sendCustomNotification/$1/$2/$3/$4/$5/$6/$7";
//ATTRIBUTES SECTION END


//REPORTS SECTION START
$route["availability/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)"] = "api/availability/$1/$2/$3/$4/$5/$6/$7/$8/$9/$10/$11";
$route["trend/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)"] = "api/trend/$1/$2/$3/$4/$5/$6/$7/$8/$9/$10/$11/$12";
$route["alerthistory"] = "api/alertHistory";
$route["alertsummary/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)"] = "api/alertSummary/$1/$2/$3/$4/$5/$6/$7";
$route["alerthistogram/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)"] = "api/alertHistogram/$1/$2/$3/$4/$5/$6/$7/$8/$9/$10/$11/$12";
$route["eventlog/(:any)"] = "api/eventlog/$1";
$route["notification/(:any)"] = "api/notification/$1";
//REPORTS SECTION END


//SYSTEM SECTION START
//Comments
$route["comments"] 			= "api/comments";
$route["comments/(:any)"] 	= "api/comments/$1";
$route["deletecomments/(:any)/(:any)"] = "api/deleteComments/$1/$2";
$route["addcomments/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)"] = "api/addComments/$1/$2/$3/$4/$5/$6";

//Downtime
$route["downtime/(:any)"] = "api/downtime/$1";
$route["deletedowntime/(:any)/(:any)"] = "api/deleteDowntime/$1/$2";
$route["scheduledowntime/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)"] = "api/scheduleDowntime/$1/$2/$3/$4/$5/$6/$7/$8/$9/$10";


//Performance info
$route["performanceinfo"] 						= "api/performanceInfo";
$route["nagiosoperation/(:any)"] 				= "api/nagiosOperation/$1";
$route["allnotifications/(:any)"] 				= "api/allnotifications/$1";
$route["allservicecheck/(:any)"] 				= "api/allServiceCheck/$1";
$route["allpassiveservicecheck/(:any)"] 		= "api/allPassiveServiceCheck/$1";
$route["allhostcheck/(:any)"] 					= "api/allHostCheck/$1";
$route["allpassivehostcheck/(:any)"] 			= "api/allPassiveHostCheck/$1";
$route["eventhandler/(:any)"] 					= "api/eventHandler/$1";
$route["obsessoverhostcheck/(:any)"] 			= "api/obsessOverHostCheck/$1";
$route["obsessoverservicecheck/(:any)"] 		= "api/obsessOverServiceCheck/$1";
$route["allflapdetection/(:any)"] 				= "api/allFlapDetection/$1";
$route["performancedata/(:any)"] 				= "api/performanceData/$1";

//Process info
$route["processinfo"] = "api/processInfo";

//Schedule queue
$route["schedulequeue"] = "api/scheduleQueue";
$route["schedulehostcheck/(:any)/(:any)/(:any)"] = "api/scheduleHostCheck/$1/$2/$3"; 
$route["schedulecheck/(:any)/(:any)/(:any)/(:any)/(:any)"] = "api/scheduleCheck/$1/$2/$3/$4/$5";
//SYSTEM SECTION END


//TESTING SECTION
$route["testing"] = "api/testing";


/* End of file routes.php */
/* Location: ./application/config/routes.php */
