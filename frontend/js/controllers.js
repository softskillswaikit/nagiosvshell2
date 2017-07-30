'use strict';

angular.module('vshell.controllers', [])

.controller('PageCtrl', ['$scope', 'async', 'paths', '$rootScope',
    function($scope, async, paths, $rootScope) {

        $scope.init = function() {

            paths.core_as_promise.then(function(value) {
                $scope.nagios_core = value;
            });

        };
        
        $rootScope.creatingLog = 0;

        // ZhengYu: Close status box
        $scope.closeStatusBox = function(){
            $rootScope.creatingLog=0;
        }
    }
])

.controller('QuicksearchCtrl', ['$scope', '$location', '$filter', 'async',
    function($scope, $location, $filter, async) {

        $scope.callback = function(data, status, headers, config) {
            var quicksearch_callback = function(e, item) {
                var base = $filter('uri')(item.type),
                    path = base + '/' + item.uri;
                $location.path(path);
                $scope.$apply();
            };

            quicksearch.init(data, quicksearch_callback);

            return data;
        };

        $scope.init = function() {

            var options = {
                name: 'quicksearch',
                url: 'quicksearch',
                queue: 'quicksearch',
                cache: true
            };

            async.api($scope, options);

        };

    }
])

.controller('StatusCtrl', ['$scope', 'async',
    function($scope, async) {

        $scope.init = function(section) {

            var options = {
                name: 'status',
                url: 'status',
                queue: 'status-' + section,
                cache: true
            };

            async.api($scope, options);

        };

    }
])

.controller('OverviewCtrl', ['$scope', 'async',
    function($scope, async) {

        $scope.init = function() {

            var options = {
                name: 'overview',
                url: 'overview',
                queue: 'main',
                cache: true
            };

            async.api($scope, options);

        };

    }
])

.controller('HostsCtrl', ['$scope', '$routeParams', '$filter', 'async',
    function($scope, $routeParams, $filter, async) {

        $scope.callback = function(data, status, headers, config) {
            var state_filter = $routeParams.state,
                problem_filter = $routeParams.handled;
            
            // set pending state if it has not been checked
            // for(var i in data){
            //     if(data[i].current_state == '0' && data[i].has_been_checked == '0')
            //         data[i].current_state = '3';
            // }

            if (state_filter) {
                data = $filter('by_state')(data, 'host', state_filter);
            } else if (problem_filter) {
                data = $filter('by_problem')(data, problem_filter);
            }

            return data;
        };
        
        $scope.init = function() {

            var options = {
                name: 'hosts',
                url: 'hoststatus',
                queue: 'main'
            };

            $scope.statefilter = $routeParams.state || '';
            $scope.problemsfilter = $routeParams.handled || '';

            async.api($scope, options);

        };

    }
])

.controller('HostDetailsCtrl', ['$scope', '$routeParams', 'async',
    function($scope, $routeParams, async) {

        $scope.init = function() {

            var options = {
                name: 'host',
                url: 'hoststatus/' + $routeParams.host,
                queue: 'main'
            };

            async.api($scope, options);
            
        };

    }
])

.controller('HostGroupsCtrl', ['$scope', 'async',
    function($scope, async) {

        $scope.init = function() {

            var options = {
                name: 'hostgroups',
                url: 'hostgroupstatus',
                queue: 'main'
            };

            async.api($scope, options);

        };

    }
])

.controller('AlertHistoryCtrl', ['$scope', 'async',
    function($scope, async) {
    var cnt = 1;
    var next = 1;
        
        $scope.init = function() {

        var utcdate = new Date();
        var timestamp = (utcdate.getTime() - 86400000) / 1000;
        var date = timestamp.toString();
	date = date.substring(0, 10);

        $scope.previousday = function() {
            
            next = 1;
            var predate = parseInt(timestamp);
            var previoustimestamp = predate - (86400 * cnt);
            console.log(previoustimestamp);
            var previousdate = previoustimestamp.toString();
	    cnt++;

            $scope.nextday = function() {
                
                cnt = 1;
                var ntdate = parseInt(previousdate);
                var nexttimestamp = ntdate + (86400 * next);
                console.log(nexttimestamp);
                var nextdate = nexttimestamp.toString();
		next++;
        
    
                        var options = {
                            name: 'alerthistorys',
                            url: 'alerthistory/' + nextdate,
                            queue: 'main'
                        };

                        async.api($scope, options);
                };
        
    
                    var options = {
                        name: 'alerthistorys',
                        url: 'alerthistory/' + previousdate,
                        queue: 'main'
                    };

                    async.api($scope, options);
            };


                var options = {
                    name: 'alerthistorys',
                    url: 'alerthistory/' + date,
                    queue: 'main'
                };

                async.api($scope, options);
        };
    }
])

.controller('AlertSummaryCtrl', ['$scope', '$attrs', 'async',
    function($scope, $attrs, async) {

	
	

        $scope.reset = function(){
            $scope.StandardReportType = '25 Most Recent Hard Alerts';
            $scope.CustomReportType = 'Most Recent Alerts';
            $scope.reportPeriod = 'Today';
            $scope.startDate = '';
            $scope.endDate = '';
            $scope.HostgroupLimit = '**ALL HOSTGROUPS**';
            $scope.ServicegroupLimit = '**ALL SERVICEGROUPS**';
            $scope.HostLimit = '**ALL HOSTS**';
            $scope.AlertTypes = 'Host and Service Alerts';
            $scope.StateTypes = 'Hard and Soft States';
            $scope.HostStates = 'All Host States';
            $scope.ServiceStates = 'All Service States';
        };

        $scope.init = function() {
            
            var options = {
                name: 'name',
                url: 'name',
                queue: 'main'
            };

            async.api($scope, options);

	    var result = {
                name: 'status',
                url: 'status',
                queue: 'status-' + '',
                cache: true
            };

	    async.api($scope, result);
	    
        };

        $scope.create = function() {

	    var date = '1500796724';
            
            var options = {
                name: 'alersummary',
                url: 'alertsummary/NORMAL/LAST 7 DAYS/' + date + '/testserver/ALL ALERT/ALL STATE TYPE/ALL SERVICE STATE',
                queue: 'main'
            };

            async.api($scope, options);
        };

        $scope.reset();
        
    }
])



.controller('NotificationsCtrl', ['$scope', 'async',
    function($scope, async) {
        
    var cnt = 1;
    var next = 1;
        
    $scope.init = function() {

        var utcdate = new Date();
        var timestamp = (utcdate.getTime()) / 1000;
        var date = timestamp.toString();
	date = date.substring(0, 10);

        $scope.previousday = function() {
            
            next = 1;
            
            var predate = parseInt(timestamp);
            var previoustimestamp = predate - (86400 * cnt);
            var previousdate = previoustimestamp.toString();
	    cnt++;

            $scope.nextday = function() {
                
                cnt = 1;
                
                var ntdate = parseInt(previousdate);
                var nexttimestamp = ntdate + (86400 * next);
                var nextdate = nexttimestamp.toString();
		next++;
        
    
                        var options = {
                            name: 'notifications',
                            url: 'notifications/' + nextdate,
                            queue: 'main'
                        };

                        async.api($scope, options);
                };
        
    
                    var options = {
                        name: 'notifications',
                        url: 'notifications/' + previousdate,
                        queue: 'main'
                    };

                    async.api($scope, options);
         };

                var options = {
                    name: 'notifications',
                    url: 'notifications/' + date,
                    queue: 'main'
                };

                async.api($scope, options);
        };
    }
])

.controller('EventLogCtrl', ['$scope', 'async',
    function($scope, async) {
        
    var cnt = 1;
    var next = 1;
        
        $scope.init = function() {

        var utcdate = new Date();
        var timestamp = (utcdate.getTime()) / 1000;
        var date = timestamp.toString();
	date = date.substring(0, 10);

        $scope.previousday = function() {
            
            next = 1;
            var predate = parseInt(timestamp);
            var previoustimestamp = predate - (86400 * cnt);
            var previousdate = previoustimestamp.toString();
	    cnt++;

            $scope.nextday = function() {
                
                cnt = 1;
                var ntdate = parseInt(previousdate);
                var nexttimestamp = ntdate + (86400 * next);
                var nextdate = nexttimestamp.toString();
		next++;
        
    
                        var options = {
                            name: 'eventlog',
                            url: 'eventlogs/' + nextdate,
                            queue: 'main'
                        };

                        async.api($scope, options);
                };
        
    
                    var options = {
                        name: 'eventlog',
                        url: 'eventlogs/' + previousdate,
                        queue: 'main'
                    };

                    async.api($scope, options);
            };

		var options = {
                        name: 'eventlog',
                        url: 'eventlogs/' + date,
                        queue: 'main'
                    };

                    async.api($scope, options);
		
        };
    }
])

.controller('ProcessInfoCtrl', ['$scope', '$routeParams', 'async',
    function($scope, $routeParams, async) {


        $scope.init = function() {

            var options = {
                name: 'processinfo',
                url: 'processinfo',
                queue: 'main'
            };

            async.api($scope, options);

        };
	
	$scope.open = function(operation) {

		console.log(operation);

		if (operation == 'shutdown'){

			var result = {
				name: 'nagiosoperation',
				url: 'nagiosOperation/' + operation,
				queue: 'main'
			};

			async.api($scope, result);

		}else if (operation == 'restart'){

			var result = {
				name: 'nagiosoperation',
				url: 'nagiosOperation/' + operation,
				queue: 'main'
			};

			async.api($scope, result);
		}
				
	};

	$scope.notification = function(operation){
		
		if (operation == "YES"){

			var type = "disable";

			var result = {
				name: 'allnotifications',
				url: 'allnotifications/' + type,
				queue: 'main'
			};

			async.api($scope, result);

		}else if (operation == 'NO'){

			var type = "enable";

			var result = {
				name: 'allnotifications',
				url: 'allnotifications/' + type,
				queue: 'main'
			};

			async.api($scope, result);
		}
	};

	$scope.activeservice = function(operation){
		
		if (operation == "YES"){

			var type = "stop";

			var result = {
				name: 'allservicecheck',
				url: 'allServiceCheck/' + type,
				queue: 'main'
			};

			async.api($scope, result);

		}else if (operation == 'NO'){

			var type = "start";

			var result = {
				name: 'allservicecheck',
				url: 'allServiceCheck/' + type,
				queue: 'main'
			};

			async.api($scope, result);
		}
	};

	$scope.passiveservice = function(operation){
		
		if (operation == "YES"){

			var type = "stop";

			var result = {
				name: 'allpassiveservicecheck',
				url: 'allPassiveServiceCheck/' + type,
				queue: 'main'
			};

			async.api($scope, result);

		}else if (operation == 'NO'){

			var type = "start";

			var result = {
				name: 'allpassiveservicecheck',
				url: 'allPassiveServiceCheck/' + type,
				queue: 'main'
			};

			async.api($scope, result);
		}
	};

	$scope.activehost = function(operation){
		
		if (operation == "YES"){

			var type = "stop";

			var result = {
				name: 'allhostcheck',
				url: 'allHostCheck/' + type,
				queue: 'main'
			};

			async.api($scope, result);

		}else if (operation == 'NO'){

			var type = "start";

			var result = {
				name: 'allhostcheck',
				url: 'allHostCheck/' + type,
				queue: 'main'
			};

			async.api($scope, result);
		}
	};

	$scope.passivehost = function(operation){
		
		if (operation == "YES"){

			var type = "stop";

			var result = {
				name: 'allpassivehostcheck',
				url: 'allPassiveHostCheck/' + type,
				queue: 'main'
			};

			async.api($scope, result);

		}else if (operation == 'NO'){

			var type = "start";

			var result = {
				name: 'allpassivehostcheck',
				url: 'allPassiveHostCheck/' + type,
				queue: 'main'
			};

			async.api($scope, result);
		}
	};

	$scope.event = function(operation){
		
		if (operation == "YES"){

			var type = "disable";

			var result = {
				name: 'eventhandler',
				url: 'eventHandler/' + type,
				queue: 'main'
			};

			async.api($scope, result);

		}else if (operation == 'NO'){

			var type = "enable";

			var result = {
				name: 'eventhandler',
				url: 'eventHandler/' + type,
				queue: 'main'
			};

			async.api($scope, result);
		}
	};

	$scope.obsessservice = function(operation){
		
		if (operation == "YES"){

			var type = "stop";

			var result = {
				name: 'obsessservice',
				url: 'obsessOverServiceCheck/' + type,
				queue: 'main'
			};

			async.api($scope, result);

		}else if (operation == 'NO'){

			var type = "start";

			var result = {
				name: 'obsessservice',
				url: 'obsessOverServiceCheck/' + type,
				queue: 'main'
			};

			async.api($scope, result);
		}
	};
	
	$scope.obsesshost = function(operation){
		
		if (operation == "YES"){

			var type = "stop";

			var result = {
				name: 'obsesshost',
				url: 'obsessOverHostCheck/' + type,
				queue: 'main'
			};

			async.api($scope, result);

		}else if (operation == 'NO'){

			var type = "start";

			var result = {
				name: 'obsesshost',
				url: 'obsessOverHostCheck/' + type,
				queue: 'main'
			};

			async.api($scope, result);
		}
	};
	
	$scope.flap = function(operation){
		
		if (operation == "YES"){

			var type = "disable";

			var result = {
				name: 'flapdetection',
				url: 'allFlapDetection/' + type,
				queue: 'main'
			};

			async.api($scope, result);

		}else if (operation == 'NO'){

			var type = "enable";

			var result = {
				name: 'flapdetection',
				url: 'allFlapDetection/' + type,
				queue: 'main'
			};

			async.api($scope, result);
		}
	};
	
	$scope.perform = function(operation){
		
		if (operation == "YES"){

			var type = "disable";

			var result = {
				name: 'performancedata',
				url: 'performanceData/' + type,
				queue: 'main'
			};

			async.api($scope, result);

		}else if (operation == 'NO'){

			var type = "enable";

			var result = {
				name: 'performancedata',
				url: 'performanceData/' + type,
				queue: 'main'
			};

			async.api($scope, result);
		}
	};

    }
])

.controller('SchedulingQueueCtrl', ['$scope', 'async',
    function($scope, async) {


        $scope.init = function() {

            var options = {
                name: 'schedulequeue',
                url: 'schedulequeue',
                queue: 'main'
            };

            async.api($scope, options);

        };

    }
])

.controller('HostGroupDetailsCtrl', ['$scope', '$routeParams', 'async',
    function($scope, $routeParams, async) {

        $scope.callback = function(data, status, headers, config) {
            return (data && data[0]) ? data[0] : data;
        };

        $scope.init = function() {

            var options = {
                name: 'hostgroup',
                url: 'hostgroupstatus/' + $routeParams.group,
                queue: 'main'
            };

            async.api($scope, options);

        };

    }
])

.controller('HostServicesCtrl', ['$scope', '$routeParams', 'async',
    function($scope, $routeParams, async) {

        $scope.init = function() {

            var options = {
                name: 'hostservices',
                url: 'servicestatus/' + $routeParams.host,
                queue: 'main'
            };

            $scope.host_name = $routeParams.host;

            async.api($scope, options);

        };

    }
])

.controller('ServicesCtrl', ['$scope', '$routeParams', '$filter', 'async',
    function($scope, $routeParams, $filter, async) {

        $scope.callback = function(data, status, headers, config) {
            var state_filter = $routeParams.state,
                problem_filter = $routeParams.handled;

            // set pending state if it has not been checked
            // for(var i in data){
            //     if(data[i].current_state == '0' && data[i].has_been_checked == '0')
            //         data[i].current_state = '4';
            //     data[i].plugin_output = data[i].plugin_output.split('$nl$').join('<br/>').split('$tab$').join('<pre class="inlinePre">&#9;</pre>');
            // }

            if (state_filter) {
                data = $filter('by_state')(data, 'service', state_filter);
            } else if (problem_filter) {
                data = $filter('by_problem')(data, problem_filter);
            }

            return data;
        };

        $scope.init = function() {

            var options = {
                name: 'services',
                url: 'servicestatus',
                queue: 'main'
            };

            $scope.statefilter = $routeParams.state || '';
            $scope.problemsfilter = $routeParams.handled || '';

            async.api($scope, options);

        };

    }
])
.controller('RemoteServiceCtrl', ['$scope', '$routeParams', 'async', '$http', 'ngToast',
    function($scope, $routeParams, async, $http, ngToast){

        $scope.init = function() {
            
            var options = {
                name: 'remote',
                url: 'servicestate/' + $routeParams.host + '/' + $routeParams.service,
                queue: 'main'
            };

            async.api($scope, options);

        };
        $scope.startRule = "23456";
        $scope.pauseRule = "123567";
        $scope.stopRule = "12356";
        $scope.disabled = false;
         // ZhengYu: Start function for service
        $scope.start = function() {
            $scope.disabled = true;
            $scope.loadRemote = $http.get('api/serviceremote/' + $routeParams.host + '/' + $routeParams.service + '/start').then(function (resp) {
                $scope.disabled = false;
                $scope.remote.state = resp.data.state;
                ngToast.create({content:resp.data.message,timeout:5000});
            });
        }

        // ZhengYu: Pause function for service
        $scope.pause = function() {
            $scope.disabled = true;
            $scope.loadRemote = $http.get('api/serviceremote/' + $routeParams.host + '/' + $routeParams.service + '/pause').then(function (resp) {
                $scope.disabled = false;
                $scope.remote.state = resp.data.state;
                ngToast.create({content:resp.data.message,timeout:5000});
            });

        }

        // ZhengYu: Stop function for service
        $scope.stop = function() {
            $scope.disabled = true;
            $scope.loadRemote = $http.get('api/serviceremote/' + $routeParams.host + '/' + $routeParams.service + '/stop').then(function (resp) {
                $scope.disabled = false;
                $scope.remote.state = resp.data.state;
                ngToast.create({content:resp.data.message,timeout:5000});
            });
        }

    }
])
.controller('ServiceDetailsCtrl', ['$scope', '$routeParams', 'async',
    function($scope, $routeParams, async) {
    
        $scope.init = function() {

            var options = {
                name: 'service',
                url: 'servicestatus/' + $routeParams.host + '/' + $routeParams.service,
                queue: 'main'
            };

            async.api($scope, options);
    
        };

        
       
    }
])
.controller('ServiceLogCtrl', ['$scope', '$routeParams', '$http', 'ngToast', 'async', "$rootScope",
    function($scope, $routeParams, $http, ngToast, async, $rootScope) {
        
        // ZhengYu: Set selected logs and deselect selected logs
        $scope.setSelected = function (log) {
            if($scope.selectedLogs.includes(log.name)) {
                var index = $scope.selectedLogs.indexOf(log.name);
                $scope.selectedLogs.splice(index, 1);
            } else {
                if(log.size < 1000000000)
                    $scope.selectedLogs.push(log.name);
                else
                    ngToast.create({className: 'alert alert-danger',content:'File is more than 1 GB.',timeout:3000});
            }
        };
        
        // ZhengYu: Clear selectedLogs
        $scope.clearSelected = function(){
            $scope.selectedLogs = [];
        };
        
        // ZhengYu: Request download code with files requested
        $scope.downloadLogs = function(){
            if($scope.selectedLogs.length > 0){
                $rootScope.creatingLog = 1;
                $rootScope.file = null;
                $rootScope.isloading = true;
                $http.get('api/servicelogdownload/'+ $routeParams.host+ '/' + $routeParams.service +'/'+ encodeURIComponent($scope.selectedLogs.join('|'))).then(function (resp) {
                    $rootScope.file = resp.data;
                    $rootScope.isloading = false;
                });
                
            } else {
                ngToast.create({className: 'alert alert-danger',content:'No logs are selected.',timeout:3000});
            }
        }
        
        $scope.init = function() {
            $scope.selectedLogs = [];
            $scope.is_loading = true;

            $scope.loadTable = $http.get('api/servicelogs/'+ $routeParams.host+ '/' + $routeParams.service).then(function(resp) {
                $scope.is_loading = false;
                $scope.resp = resp.data;
            });

        };

    }
])

.controller('ServiceGroupsCtrl', ['$scope', 'async',
    function($scope, async) {
        
        $scope.init = function() {

            var options = {
                name: 'servicegroups',
                url: 'servicegroupstatus',
                queue: 'main'
            };

            async.api($scope, options);

        };

    }
])

.controller('ServiceGroupDetailsCtrl', ['$scope', '$routeParams', 'async',
    function($scope, $routeParams, async) {

        $scope.callback = function(data, status, headers, config) {
            return (data && data[0]) ? data[0] : data;
        };

        $scope.init = function() {

            var options = {
                name: 'servicegroup',
                url: 'servicegroupstatus/' + $routeParams.group,
                queue: 'main'
            };

            async.api($scope, options);

        };

    }
])

.controller('ConfigurationsCtrl', ['$scope', '$routeParams', 'async',
    function($scope, $routeParams, async) {

        var type = $routeParams.type || '';

        $scope.callback = function(data, status, headers, config) {
            if (type) {
                data = data[type] || {};
            }
            return data;
        };

        $scope.init = function() {

            var options = {
                name: 'configurations',
                url: 'configurations/' + type,
                queue: 'main'
            };

            async.api($scope, options);

        };

    }
])

.controller('ConfigurationDetailsCtrl', ['$scope', '$routeParams', '$filter', 'async',
    function($scope, $routeParams, $filter, async) {

        var type = ($routeParams.type || ''),
            name = $routeParams.name,
            name_key = $filter('configuration_anchor_key')(type);

        $scope.callback = function(data, status, headers, config) {
            if (!data || !data[type]) {
                return data;
            }
            data = data[type]['items'];
            data = $filter('property')(data, name_key, name)[0];
            return data;
        };

        $scope.init = function() {

            $scope.configuration_type = $routeParams.type;
            $scope.configuration_name = $routeParams.name;

            var options = {
                name: 'configuration',
                url: 'configurations/' + type,
                queue: 'main'
            };

            async.api($scope, options);

        };

    }
])

.controller('CommentsCtrl', ['$scope', 'async',
    function($scope, async) {

        $scope.init = function() {

            var options = {
                name: 'comments',
                url: 'comments',
                queue: 'main'
            };

            async.api($scope, options);

        };

    }
])

//created by gnzhen
//modified by soon wei liang
.controller('AvailabilityCtrl', ['$scope', 'async',
    function($scope, async) {
        
        $scope.init = function() {

            var options = {
                name: 'hostname',
                url: 'hostname',
                queue: 'main'
            };

       
            async.api($scope, options);

        };

    }
])
.controller('OptionsCtrl', ['$scope', '$http', 'paths',
    function($scope, $http, paths) {

        $scope.init = function() {

            var uri = paths.app + 'package.json';

            $http.get(uri).then(function(response) {
                $scope.vshell = response.data;
            });

        };

    }
])
;
