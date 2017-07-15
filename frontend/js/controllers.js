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

.controller('EventLogCtrl', ['$scope', 'async',
    function($scope, async) {

        $scope.init = function() {

            var options = {
                name: 'testing',
                url: 'testing',
                queue: 'main'
            };


            async.api($scope, options);

        };

    }
])

.controller('AvailabilityCtrl', ['$scope', '$routeParams', '$filter', 'async',
    function($scope, $routeParams, $filter, async) {
      $scope.init = function() {
        $scope.componentName =   [
          {name : "localhost"},
          {name : "testserver"}
        ];

        $scope.availability =   [
          {name : "localhost"},
          {name : "testserver"}
        ];

        $scope.today = new Date();
        $scope.todayString = $filter('date')(Date.now(), 'MM/dd/yyyy');

        $scope.reportType = 'Hostgroup(s)';
        $scope.serviceType = 'Normal Service';
        $scope.reportComponent = $scope.componentName[0].name;
        $scope.startDate =  $scope.todayString;
        $scope.endDate =  $scope.todayString;
        $scope.reportPeriod = 'Last 7 Days';
        $scope.reportTimePeriod = 'None';
        $scope.assumeInitialStates = 'Yes';
        $scope.assumeStateRetention = 'Yes';
        $scope.assumeDowntimeStates = 'Yes';
        $scope.includeSoftStates = 'No';
        $scope.firstAssumedHostState = 'Unspecified';
        $scope.firstAssumedServiceState = 'Unspecified';
        $scope.backtrackedArchives = 4;


              /*
        var options = {
                  name: 'availability',
                  url: 'availability',
                  queue: 'main'
              };

              async.api($scope, options);
        */
      };
    }
])

.controller('TrendsCtrl', ['$scope', '$routeParams', '$filter', 'async',
    function($scope, $routeParams, $filter, async) {

        $scope.init = function() {
          $scope.componentName =   [
            {name : "localhost"},
            {name : "testserver"}
          ];

          $scope.today = new Date();
          $scope.todayString = $filter('date')(Date.now(), 'MM/dd/yyyy');

          $scope.reportType = 'Host';
    			$scope.serviceType = 'Normal Service';
          $scope.reportComponent = $scope.componentName[0].name;
          $scope.startDate =  $scope.todayString;
          $scope.endDate =  $scope.todayString;
    			$scope.reportPeriod = 'Last 7 Days';
    			$scope.reportTimePeriod = 'None';
    			$scope.assumeInitialStates = 'Yes';
    			$scope.assumeStateRetention = 'Yes';
    			$scope.assumeDowntimeStates = 'Yes';
    			$scope.includeSoftStates = 'No';
    			$scope.firstAssumedHostState = 'Unspecified';
    			$scope.firstAssumedServiceState = 'Unspecified';
    			$scope.backtrackedArchives = 4;
            /*var options = {
                name: 'trends',
                url: 'trends/',
                queue: 'main'
            };

            async.api($scope, options);*/

        };

    }
])

.controller('AlertHistogramCtrl', ['$scope', '$routeParams', '$filter', 'async',
    function($scope, $routeParams, $filter, async) {

		$scope.init = function(){
      $scope.componentName =   [
        {name : "localhost"},
        {name : "testserver"}
      ];

      $scope.today = new Date();
      $scope.todayString = $filter('date')(Date.now(), 'MM/dd/yyyy');

			$scope.reportType = 'Host';
			$scope.serviceType = 'Normal Service';
      $scope.reportComponent = $scope.componentName[0].name;
      $scope.startDate =  $scope.todayString;
      $scope.endDate =  $scope.todayString;
			$scope.reportPeriod = 'Last 7 Days';
			$scope.statisticsBreakdown = 'Day of the Month';
			$scope.eventsToGraph = 'All Hosts Events';
			$scope.stateTypesToGraph = 'Hard and Soft States';
			$scope.assumeStateRetention = 'Yes';
			$scope.initialStatesLogged = 'No';
			$scope.ignoreRepeatedStates = 'No';


            /*
			var options = {
                name: 'alerthistogram',
                url: 'alerthistogram/',
                queue: 'main'
            };

            async.api($scope, options);
			*/

	     };
    }
])

.controller('AvailabilityReportCtrl', ['$scope', 'async',
    function($scope, async) {

        $scope.init = function() {

            /*
			var options = {
                name: 'hostname',
                url: 'hostname',
                queue: 'main'
            };

            async.api($scope, options);
			*/

        };

    }
])

.controller('TrendsReportCtrl', ['$scope', 'async',
    function($scope, async) {

        $scope.init = function() {
          $scope.dataSource = {
            chart: {
              "caption": "State History For ",
              "subCaption": "-",
              "xAxisName": "Time",
              "yAxisName": "State",
              //"theme": "fint",
              //Setting gradient fill to true
              "usePlotGradientColor": "1",
              //Setting the gradient formation color
              "plotGradientColor": "#00f254"
              },
              data:
                [
                  {
                      "label": "Jul 8 15:34",
                      "value": "0"
                  },
                  {
                      "label": "Jul 8 15:34",
                      "value": "1"
                  },
                  {
                      "label": "Jul 8 15:34",
                      "value": "2"
                  },
                  {
                      "label": "Jul 8 15:34",
                      "value": "3"
                  },
                  {
                      "label": "Jul 8 15:34",
                      "value": "4"
                  },
                  {
                      "label": "Jul 8 15:34",
                      "value": "1"
                  }
              ]
          }


          /*
            var options = {
                name: 'trendsreport',
                url: 'trendsreport',
                queue: 'main'
            };

            async.api($scope, options);
*/
        };

    }
])

.controller('AlertHistogramReportCtrl', ['$scope', 'async',
    function($scope, async) {

        $scope.init = function() {

          $scope.dataSource = {
            chart: {
              "caption": "State History For ",
              "subCaption": "-",
              "xAxisName": "Time",
              "yAxisName": "State",
              "theme": "fint",
              //Setting gradient fill to true
              "usePlotGradientColor": "1",
              //Setting the gradient formation color
              "plotGradientColor": "#00f254"
              },
              data:
                [
                  {
                      "label": "Jul 8 15:34",
                      "value": "0"
                  },
                  {
                      "label": "Jul 8 15:34",
                      "value": "1"
                  },
                  {
                      "label": "Jul 8 15:34",
                      "value": "2"
                  },
                  {
                      "label": "Jul 8 15:34",
                      "value": "3"
                  },
                  {
                      "label": "Jul 8 15:34",
                      "value": "4"
                  },
                  {
                      "label": "Jul 8 15:34",
                      "value": "1"
                  }
              ]
          }


/*
            var options = {
                name: 'alerthistogramreport',
                url: 'alerthistogramreport',
                queue: 'main'
            };

            async.api($scope, options);
*/
        };

    }
])

.controller('SysCommentsCtrl', ['$scope', 'async',
    function($scope, async) {

      $scope.init = function() {
        $scope.hostHostName = '';
        $scope.hostPersistent = true;
        $scope.hostAuthor = '';
        $scope.hostComment = '';

        $scope.serviceHostName = '';
        $scope.serviceService = '';
        $scope.servicePersistent = true;
        $scope.serviceAuthor = '';
        $scope.serviceComment = '';
/*
            var options = {
                name: 'syscomments',
                url: 'syscomments',
                queue: 'main'
            };

            async.api($scope, options);
*/
        };


    }
])

.controller('SysDowntimeCtrl', ['$scope', '$filter', 'async',
    function($scope, $filter, async) {

        $scope.init = function() {
          $scope.now = new Date();
          $scope.nowString = $filter('date')(Date.now(), 'MM/dd/yyyy HH:mm:ss');

          $scope.hostHostName = '';
          $scope.hostAuthor = '';
          $scope.hostComment = "";
          $scope.hostTriggeredBy = 'N/A';
          $scope.hostStartDateTime = $scope.nowString ;
          $scope.hostEndDateTime =  $scope.nowString ;
          $scope.hostType = 'Fixed';
          $scope.hostDurationHour = 2;
          $scope.hosteDurationMin = 0;
          $scope.hostChildHost = 'doNothing';

          $scope.serviceHostName = '';
          $scope.serviceService = '';
          $scope.serviceAuthor = '';
          $scope.servicComment = '';
          $scope.serviceTriggeredBy = 'N/A';
          $scope.serviceStartDateTime = $scope.nowString;
          $scope.serviceEndDateTime = $scope.nowString;
          $scope.serviceType = 'Fixed';
          $scope.serviceDurationHour = 2;
          $scope.serviceDurationMin = 0;
/*
            var options = {
                name: 'sysdowntime',
                url: 'sysdowntime',
                queue: 'main'
            };

            async.api($scope, options);
*/
        };

    }
])

.controller('PerformanceInfoCtrl', ['$scope', 'async',
    function($scope, async) {

        $scope.init = function() {
/*
            var options = {
                name: 'performanceinfo',
                url: 'performanceinfo',
                queue: 'main'
            };

            async.api($scope, options);
*/
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
