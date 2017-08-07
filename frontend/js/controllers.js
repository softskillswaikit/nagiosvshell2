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

.controller('HostDetailsCtrl', ['$scope', '$routeParams', 'async', '$timeout', '$interval', '$route', 'ngToast',
    function($scope, $routeParams, async, $timeout, $interval, $route, ngToast) {

        $scope.init = function() {
          var options = {
              name: 'host',
              url: 'hoststatus/' + $routeParams.host,
              queue: 'main'
          };

          async.api($scope, options);

          //get author
          var optionsstatus = {
              name: 'status',
              url: 'status',
              queue: 'status-' + '',
              cache: true
          };

          async.api($scope, optionsstatus);

          };

          $scope.resetModal = function(){
            $scope.hostName = $routeParams.host;
            $scope.service = ' ';
            $scope.persistent = true;
            $timeout(function(){$scope.author = $scope.status.username}, 500);
            $scope.comment = '';
            if($scope.addHostComment)
              $scope.addHostComment.$setPristine();

            console.log("comment");
            console.log($scope.comment);
          };

        $scope.toggle = function(action, is_enabled){

          $scope.toggleAction = function(){
            console.log('toggle');
            $scope.reset();
          };
        };

        $scope.addComment = function(type){
          $scope.resetModal();

          $scope.add = function(persistent, comment){
            console.log("addComment");
            console.log("type="+type);
            console.log("host="+$scope.hostName);
            console.log("service="+$scope.service);
            console.log("persistent="+persistent);
            console.log("author="+$scope.status.username);
            console.log("comment="+comment);

            var options = {
                name: 'success',
                url: 'addcomments/'+ type + '/' + $routeParams.host + '/' + ' '
                  + '/' + persistent + '/' + $scope.status.username + '/' + comment,
                queue: 'main'
            };
            async.api($scope, options);

            $scope.callback = function(data, status, headers, config) {

              if(data)
                ngToast.create({className: 'alert alert-success',content:'Success!',timeout:3000});
              else
                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
            };

          };
        };

      $scope.deleteComment = function(id, type){

          $scope.delete = function(){

            var options = {
                name: 'success',
                url: 'deletecomments/' + id + '/' + type,
                queue: 'main'
            };

            async.api($scope, options);

            $scope.callback = function(data, status, headers, config) {

              if(data)
                ngToast.create({className: 'alert alert-success',content:'Success!',timeout:3000});
              else
                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
            };
          }
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

.controller('ServiceDetailsCtrl', ['$scope', '$routeParams', 'async', '$timeout', 'ngToast',
    function($scope, $routeParams, async, $timeout, ngToast) {

        $scope.init = function() {

            var options = {
                name: 'service',
                url: 'servicestatus/' + $routeParams.host + '/' + $routeParams.service,
                queue: 'main'
            };

            async.api($scope, options);

            //get author
            var optionsstatus = {
                name: 'status',
                url: 'status',
                queue: 'status-' + '',
                cache: true
            };

            async.api($scope, optionsstatus);

            //reset modal
            $scope.resetModal();
        };

        $scope.resetModal = function(){
          //initialize scope
          $scope.hostName = $routeParams.host;
          $scope.service = 'routeParams.service';
          $scope.persistent = true;
          $timeout(function(){$scope.author = $scope.status.username;}, 1000);
          $scope.comment = '';
          if($scope.addServiceComment)
            $scope.addServiceComment.$setPristine();

            console.log("scope service");
            console.log($scope.status.username);
        };

        $scope.toggle = function(action, is_enabled){

          $scope.toggleAction = function(){

          };
        };

        $scope.addComment = function(type){

          console.log("add comment");
          $scope.add = function(persistent, comment){
            console.log("addComment");
            console.log("type="+type);
            console.log("host="+$routeParams.host);
            console.log("service="+$routeParams.service);
            console.log("persistent="+persistent);
            console.log("author="+$scope.status.username);
            console.log("comment="+comment);

            var options = {
                name: 'success',
                url: 'addcomments/'+ type + '/' + $routeParams.host + '/' + $routeParams.service
                  + '/' + persistent + '/' + $scope.status.username + '/' + comment,
                queue: 'main'
            };
            async.api($scope, options);

            $scope.callback = function(data, status, headers, config) {
              if(data)
                ngToast.create({className: 'alert alert-success',content:'Success!',timeout:3000});
              else
                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
            };
          };
        };

        $scope.deleteComment = function(id, type){
            $scope.delete = function(){

              var options = {
                  name: 'success',
                  url: 'deletecomments/' + id + '/' + type,
                  queue: 'main'
              };

              async.api($scope, options);

              $scope.callback = function(data, status, headers, config) {
                if(data)
                  ngToast.create({className: 'alert alert-success',content:'Success!',timeout:3000});
                else
                  ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
              };

            };
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

.controller('AvailabilityCtrl', ['$scope', '$routeParams', '$filter', 'async', '$rootScope',
    function($scope, $routeParams, $filter, async, $rootScope) {

      $scope.init = function() {
        $scope.componentName =   [
          {name : "localhost"},
          {name : "testserver"}
        ];

        $scope.reset();
      };

      $scope.reset = function(){
        $scope.today = new Date();
        $scope.todayString = $filter('date')(Date.now(), 'MM/dd/yyyy');

        $scope.reportType = 'Hostgroup(s)';
        $scope.reportComponent = 'ALL';
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
      };

      $scope.savedRT = localStorage.getItem('reportType');
      $scope.reportType = $scope.savedRT;
      $scope.savedRC = localStorage.getItem('reportComponent');
      $scope.reportComponent = $scope.savedRC;
      console.log("reportType");
      console.log($scope.reportType);
      console.log("reportComponent");
      console.log($scope.reportComponent);

      $scope.createReport = function(){
        localStorage.setItem('reportType', $scope.reportType);
        localStorage.setItem('reportComponent', $scope.reportComponent);

        //data for test
        $scope.testdata=[
          {
            host:"app_server",
            data:[
              {0:"C: Drive Space",5:0,1:0,2:0,3:0,4:0},
              {0:"CPEExtractor",5:0,1:0,2:0,3:0,4:0}
            ]
          },
          {
            host:"localhost",
            data:[
              {0:"C: Drive Space",5:0,1:0,2:0,3:0,4:0},
              {0:"CPEExtractor",5:0,1:0,2:0,3:0,4:0}
            ]
          }
        ];
        //data for event log(host-one service-one)
        $scope.report3=[
          {
              0:"07-17-2017 00:00:00",
              1:"07-18-2017 00:00:00",
              2:"1d 0h 0m 0s",
              3:"SERVICE OK (HARD)",
              4:"c:\ - total: 23.66 Gb - used: 16.59 Gb (70%) - free 7.06 Gb (30%)"
          },
          {
            0:"07-17-2017 00:00:00",
            1:"07-18-2017 00:00:00",
            2:"1d 0h 0m 0s",
            3:"SERVICE OK (HARD)",
            4:"c:\ - total: 23.66 Gb - used: 16.59 Gb (70%) - free 7.06 Gb (30%)"
          },
          {
            0:"07-17-2017 00:00:00",
            1:"07-18-2017 00:00:00",
            2:"1d 0h 0m 0s",
            3:"SERVICE OK (HARD)",
            4:"c:\ - total: 23.66 Gb - used: 16.59 Gb (70%) - free 7.06 Gb (30%)"
          }
        ];
        //data for host-one service-one
        $scope.report2=[
          {
            host:"app_server",
            data:[
              {0:"C: Drive Space",5:0,1:0,2:0,3:0,4:0},
              {0:"CPEExtractor",5:0,1:0,2:0,3:0,4:0}
            ]
          },
          {
            host:"localhost",
            data:[
              {0:"C: Drive Space",5:0,1:0,2:0,3:0,4:0},
              {0:"CPEExtractor",5:0,1:0,2:0,3:0,4:0}
            ]
          }
        ];
        //data for host one
        $scope.report4=[
          {
            type:"Service",
            data:[
              {0:"C: Drive Space",5:0,1:0,2:0,3:0,4:0},
              {0:"CPEExtractor",5:0,1:0,2:0,3:0,4:0}
            ]
          },
          {
            type:"Resource",
            data:[
              {0:"C: Drive Space",5:0,1:0,2:0,3:0,4:0},
              {0:"CPEExtractor",5:0,1:0,2:0,3:0,4:0}
            ]
          },
          {
            type:"Service Running State",
            data:[
              {0:"C: Drive Space",5:0,1:0,2:0,3:0,4:0},
              {0:"CPEExtractor",5:0,1:0,2:0,3:0,4:0}
            ]
          }
        ];
        //data for servicegroup-all/one
        $scope.report5=[
          {
            host:"app_server",
            service:[
              {0:"C: Drive Space",5:0,1:0,2:0,3:0,4:0},
              {0:"CPEExtractor",5:0,1:0,2:0,3:0,4:0}
            ]
          }
        ];
        //data for one host/one service
        $scope.reports1 = [
          {
            state:"OK",
            data:[
              {0:"Unscheduled", 1:"7d 0h 0m 0s", 2:"1.00", 3:"2.00"},
              {0:"Scheduled", 1:"0d 0h 0m 0s", 2:"1.00", 3:"2.00"},
              {0:"Total", 1:"7d 0h 0m 0s", 2:"1.00", 3:"2.00"}
            ]
          },
          {
            state: "WARNING",
            data:[
              {0:"Unscheduled", 1:"7d 0h 0m 0s", 2:"1.00", 3:"2.00"},
              {0:"Scheduled", 1:"0d 0h 0m 0s", 2:"1.00", 3:"2.00"},
              {0:"Total", 1:"7d 0h 0m 0s", 2:"1.00", 3:"2.00"}
            ]
          },
          {
            state:"UNKNOWN",
            data:[
              {0:"Unscheduled", 1:"7d 0h 0m 0s", 2:"1.00", 3:"2.00"},
              {0:"Scheduled", 1:"0d 0h 0m 0s", 2:"1.00", 3:"2.00"},
              {0:"Total", 1:"7d 0h 0m 0s", 2:"1.00", 3:"2.00"}
            ]
          },
          {
            state: "PENDING",
            data:[
              {0:"Nagios Not Running", 1: "0d 0h 0m 0s", 2: "0.00", 3: "0.00"},
              {0:"insufficient Data", 1: "0d 0h 0m 0s", 2: "0.00", 3: "0.00"},
              {0:"Total", 1: "7d 0h 0m 0s", 2: "100.00", 3: "100.00"}
            ]
          },
          {
            state:"ALL",
            data:[
              {0:"Total", 1: "7d 0h 0m 0s", 2: "100.00", 3: "100.00"}
            ]
          }
        ];
        //data for hostgroup/servicegroup
        $scope.reports=[
          {
            hostgroup:"linux_servers",
            data:[
              {0:"localhost",1:"0.00",2:"0.00",3:"0.00",4:"0.00"}
            ]
          },
          {
            hostgroup:"windows-servers",
            data:[
              {0:"app_server",1:"0.00",2:"0.00",3:"0.00",4:"0.00"},
              {0:"web_server",1:"0.00",2:"0.00",3:"0.00",4:"0.00"}
            ]
          }
        ];
        //data for host-all
        $scope.hostall=[
          {0:"localhost",1:"0.00",2:"0.00",3:"0.00",4:"0.00"},
          {0:"app_server",1:"0.00",2:"0.00",3:"0.00",4:"0.00"},
          {0:"web_server",1:"0.00",2:"0.00",3:"0.00",4:"0.00"}
        ];
      };



      $scope.create = function(){

        var options = {
               name: 'testing',
               url: 'testing',
               queue: 'main'
           };


           async.api($scope, options);
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
            /*var options = {
                name: 'trends',
                url: 'trends/',
                queue: 'main'
            };

            async.api($scope, options);*/
            $scope.reset();
        };

        $scope.reset = function(){
          $scope.today = new Date();
          $scope.todayString = $filter('date')(Date.now(), 'MM/dd/yyyy');

          $scope.reportType = 'Host';
    			$scope.serviceType = 'Normal Service';
          $scope.reportHost = 'ALL';
          $scope.reportService = 'ALL';
          $scope.reportHostResource = 'ALL';
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
        };

        $scope.createReport = function(){
          $scope.report =
            {
              host : "app_server",
              reportType : "Host",

                time : ["Mon", "Tue", "Wed", "Thu", "Fri"]
            };

          $scope.hostdata = {
            "chart": {
                "caption": "State History For Host " + $scope.report['host'],
                "captionfontsize": "16",
                "subCaption": "From " + $scope.report['time'][0] + " To " + $scope.report['time'][4],
                "xaxisname": "Time",
                "yaxisname": " ",
                "yaxisnamepadding": "80",
                "showyaxisvalues": "0",
                "theme": "fint",
                "showvalues": "0",
                "showtooltip": "0",
                "linethickness": "4",
                "anchorhoverradius": "8",
                "anchorradius": "4",
                "anchorborderthickness": "2"
            },
            "annotations": {
                "groups": [
                    {
                        "id": "yaxisline",
                        "items": [
                            {
                                "id": "line",
                                "type": "line",
                                "color": "#1a1a1a",
                                "x": "$canvasstartx - 5",
                                "y": "$canvasstarty",
                                "tox": "$canvasstartx - 5",
                                "toy": "$canvasendy",
                                "thickness": "1"
                            },
                            {
                                "id": "pending-label-bg",
                                "type": "rectangle",
                                "fillcolor": "#858585",
                                "x": "$canvasstartx - 85",
                                "tox": "$canvasstartx - 15",
                                "y": "$canvasendy - 10",
                                "toy": "$canvasendy + 10",
                                "radius": "3"
                            },
                            {
                                "id": "pending-dot",
                                "type": "circle",
                                "radius": "5",
                                "x": "$canvasstartx - 5",
                                "y": "$canvasendy",
                                "color": "#858585"
                            },
                            {
                                "id": "pending-label",
                                "type": "text",
                                "fillcolor": "#ffffff",
                                "text": "Pending",
                                "x": "$canvasstartx - 50",
                                "y": "$canvasendy",
                                "fontsize": "13"
                            },
                            {
                                "id": "unreachable-label-bg",
                                "type": "rectangle",
                                "fillcolor": "#ee8425",
                                "x": "$canvasstartx - 102",
                                "tox": "$canvasstartx - 15",
                                "y": "$canvasendy - 69",
                                "toy": "$canvasendy - 49",
                                "radius": "3"
                            },
                            {
                                "id": "unreachable-dot",
                                "type": "circle",
                                "radius": "5",
                                "x": "$canvasstartx - 5",
                                "y": "$canvasendy - 59",
                                "color": "#ee8425"
                            },
                            {
                                "id": "unreachable-label",
                                "type": "text",
                                "fillcolor": "#ffffff",
                                "text": "Unreachable",
                                "x": "$canvasstartx - 59",
                                "y": "$canvasendy - 59",
                                "fontsize": "13"
                            },
                            {
                                "id": "down-label-bg",
                                "type": "rectangle",
                                "fillcolor": "#d24555",
                                "x": "$canvasstartx - 85",
                                "tox": "$canvasstartx - 15",
                                "y": "$canvasendy - 127",
                                "toy": "$canvasendy - 107",
                                "radius": "3"
                            },
                            {
                                "id": "down-dot",
                                "type": "circle",
                                "radius": "5",
                                "x": "$canvasstartx - 5",
                                "y": "$canvasendy - 117",
                                "color": "#d24555"
                            },
                            {
                                "id": "down-label",
                                "type": "text",
                                "fillcolor": "#ffffff",
                                "text": "Down",
                                "x": "$canvasstartx - 50",
                                "y": "$canvasendy - 117",
                                "fontsize": "13"
                            },
                            {
                                "id": "up-label-bg",
                                "type": "rectangle",
                                "fillcolor": "#6cb22f",
                                "x": "$canvasstartx - 88",
                                "tox": "$canvasstartx - 15",
                                "y": "$canvasendy - 185",
                                "toy": "$canvasendy - 165",
                                "radius": "3"
                            },
                            {
                                "id": "up-dot",
                                "type": "circle",
                                "radius": "5",
                                "x": "$canvasstartx - 5",
                                "y": "$canvasendy - 175",
                                "color": "#6cb22f"
                            },
                            {
                                "id": "up-label",
                                "type": "text",
                                "fillcolor": "#ffffff",
                                "text": "Up",
                                "x": "$canvasstartx - 52",
                                "y": "$canvasendy - 175",
                                "fontsize": "13"
                            }
                        ]
                    }
                ]
            },
            "dataset": [
                {
                    "seriesname": "Host State Trends",
                    "data": [
                        {"label" : "Mon","value": "3"},
                        {"label" : "Tue","value": "3"},
                        {"label" : "Wed","value": "2"},
                        {"label" : "Thu","value": "3"},
                        {"label" : "Fri","value": "1"},
                        {"label" : "Sat","value": "2"},
                        {"label" : "Sun","value": "0"}
                    ]
                }
            ]
          }

          $scope.servicedata = {
            "chart": {
                "caption": "State History For Service " + $scope.report['host'],
                "captionfontsize": "16",
                "subCaption": "From " + $scope.report['time'][0] + " To " + $scope.report['time'][4],
                "xaxisname": "Time",
                "yaxisname": " ",
                "yaxisnamepadding": "80",
                "showyaxisvalues": "0",
                "theme": "fint",
                "showvalues": "0",
                "showtooltip": "0",
                "linethickness": "4",
                "anchorhoverradius": "8",
                "anchorradius": "4",
                "anchorborderthickness": "2"
            },
            "annotations": {
                "groups": [
                    {
                        "id": "yaxisline",
                        "items": [
                            {
                                "id": "line",
                                "type": "line",
                                "color": "#1a1a1a",
                                "x": "$canvasstartx - 5",
                                "y": "$canvasstarty",
                                "tox": "$canvasstartx - 5",
                                "toy": "$canvasendy",
                                "thickness": "1"
                            },
                            {
                                "id": "pending-label-bg",
                                "type": "rectangle",
                                "fillcolor": "#858585",
                                "x": "$canvasstartx - 88",
                                "tox": "$canvasstartx - 15",
                                "y": "$canvasendy - 10",
                                "toy": "$canvasendy + 10",
                                "radius": "3"
                            },
                            {
                                "id": "pending-dot",
                                "type": "circle",
                                "radius": "5",
                                "x": "$canvasstartx - 5",
                                "y": "$canvasendy",
                                "color": "#858585"
                            },
                            {
                                "id": "pending-label",
                                "type": "text",
                                "fillcolor": "#ffffff",
                                "text": "Pending",
                                "x": "$canvasstartx - 50",
                                "y": "$canvasendy",
                                "fontsize": "13"
                            },
                            {
                                "id": "critical-label-bg",
                                "type": "rectangle",
                                "fillcolor": "#d24555",
                                "x": "$canvasstartx - 88",
                                "tox": "$canvasstartx - 15",
                                "y": "$canvasendy - 69",
                                "toy": "$canvasendy - 49",
                                "radius": "3"
                            },
                            {
                                "id": "critical-dot",
                                "type": "circle",
                                "radius": "5",
                                "x": "$canvasstartx - 5",
                                "y": "$canvasendy - 59",
                                "color": "#d24555"
                            },
                            {
                                "id": "critical-label",
                                "type": "text",
                                "fillcolor": "#ffffff",
                                "text": "Critical",
                                "x": "$canvasstartx - 52",
                                "y": "$canvasendy - 59",
                                "fontsize": "13"
                            },
                            {
                                "id": "unknown-label-bg",
                                "type": "rectangle",
                                "fillcolor": "#ee8425",
                                "x": "$canvasstartx - 88",
                                "tox": "$canvasstartx - 15",
                                "y": "$canvasendy - 127",
                                "toy": "$canvasendy - 107",
                                "radius": "3"
                            },
                            {
                                "id": "unknown-dot",
                                "type": "circle",
                                "radius": "5",
                                "x": "$canvasstartx - 5",
                                "y": "$canvasendy - 117",
                                "color": "#ee8425"
                            },
                            {
                                "id": "unknown-label",
                                "type": "text",
                                "fillcolor": "#ffffff",
                                "text": "Unknown",
                                "x": "$canvasstartx - 50",
                                "y": "$canvasendy - 117",
                                "fontsize": "13"
                            },
                            {
                                "id": "warning-label-bg",
                                "type": "rectangle",
                                "fillcolor": "#dba102",
                                "x": "$canvasstartx - 88",
                                "tox": "$canvasstartx - 15",
                                "y": "$canvasendy - 185",
                                "toy": "$canvasendy - 165",
                                "radius": "3"
                            },
                            {
                                "id": "warning-dot",
                                "type": "circle",
                                "radius": "5",
                                "x": "$canvasstartx - 5",
                                "y": "$canvasendy - 175",
                                "color": "#dba102"
                            },
                            {
                                "id": "warning-label",
                                "type": "text",
                                "fillcolor": "#ffffff",
                                "text": "Warning",
                                "x": "$canvasstartx - 52",
                                "y": "$canvasendy - 175",
                                "fontsize": "13"
                            },
                            {
                                "id": "ok-label-bg",
                                "type": "rectangle",
                                "fillcolor": "#6cb22f",
                                "x": "$canvasstartx - 88",
                                "tox": "$canvasstartx - 15",
                                "y": "$canvasendy - 243",
                                "toy": "$canvasendy - 223",
                                "radius": "3"
                            },
                            {
                                "id": "ok-dot",
                                "type": "circle",
                                "radius": "5",
                                "x": "$canvasstartx - 5",
                                "y": "$canvasendy - 233",
                                "color": "#6cb22f"
                            },
                            {
                                "id": "ok-label",
                                "type": "text",
                                "fillcolor": "#ffffff",
                                "text": "Ok",
                                "x": "$canvasstartx - 52",
                                "y": "$canvasendy - 233",
                                "fontsize": "13"
                            }
                        ]
                    }
                ]
            },
            "dataset": [
                {
                    "seriesname": "Host State Trends",
                    "data": [
                        {"label" : "Mon","value": "3"},
                        {"label" : "Tue","value": "3"},
                        {"label" : "Wed","value": "2"},
                        {"label" : "Thu","value": "3"},
                        {"label" : "Fri","value": "1"},
                        {"label" : "Sat","value": "2"},
                        {"label" : "Sun","value": "0"}
                    ]
                }
            ]
          }
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

            /*
			var options = {
                name: 'alerthistogram',
                url: 'alerthistogram/',
                queue: 'main'
            };

            async.api($scope, options);
			*/
            $scope.reset();

	     };

       $scope.reset = function(){
          $scope.today = new Date();
          $scope.todayString = $filter('date')(Date.now(), 'MM/dd/yyyy');

     			$scope.reportType = 'Host';
     			$scope.serviceType = 'Normal Service';
          $scope.reportHost = 'ALL';
          $scope.reportService = 'ALL';
          $scope.reportHostResource = 'ALL';
          $scope.startDate =  $scope.todayString;
          $scope.endDate =  $scope.todayString;
     			$scope.reportPeriod = 'Last 7 Days';
     			$scope.statisticsBreakdown = 'Day of the Month';
     			$scope.eventsToGraph = 'All Hosts Events';
     			$scope.stateTypesToGraph = 'Hard and Soft States';
     			$scope.assumeStateRetention = 'Yes';
     			$scope.initialStatesLogged = 'No';
     			$scope.ignoreRepeatedStates = 'No';
       };

       $scope.createReport = function(){

         $scope.report = {
           host : "app_server",
           reportType : "Host",
           statisticsBreakdown:"Day of the Month"
         };

         $scope.hostdata = {
           "chart": {
               "caption": "State History For Host " + $scope.report['host'],
               "captionfontsize": "16",
               "subCaption": "From " + " To ",
               "paletteColors": "#6cb22f,#d24555,#ee8425",
               "xaxisname": $scope.report['statisticsBreakdown'],
               "yaxisname": "Number of Events",
               "showyaxisvalues": "1",
               "theme": "fint",
               "showvalues": "0",
               "showtooltip": "0",
               "linethickness": "2",
               "anchorhoverradius": "4",
               "anchorradius": "2",
               "anchorborderthickness": "2"
           },
           "categories": [
            {
              "category": [
                {"label": "Mon"},{"label": "Tue"},{"label": "Wed"},  {"label": "Thu"},{"label": "Fri"},{"label": "Sat"},{"label": "Sun"}
              ]
            }
          ],
           "dataset": [
               {
                   "seriesname": "Recovery(Up)",
                   "data": [{"value": "6"},{"value": "7"},{"value": "4"},{"value": "13"},{ "value": "7"},{"value": "12"},{"value": "10"}]
               },
               {
                   "seriesname": "Down",
                   "data": [{"value": "1"},{"value": "5"},{"value": "7"},{"value": "5"},{"value": "3"},{"value": "3"},{"value": "0"}]
               },
               {
                   "seriesname": "Unreachable",
                   "data": [{"value": "0"},{"value": "0"},{"value": "2"},{"value": "1"},{"value": "0"},{"value": "2"},{"value": "0"}]
               }
           ]
         }

         $scope.servicedata = {
           "chart": {
               "caption": "State History For Service " + $scope.report['host'],
               "captionfontsize": "16",
               "subCaption": "From " + " To ",
               "paletteColors": "#6cb22f,#dba102,#ee8425,#d24555",
               "xaxisname": $scope.report['statisticsBreakdown'],
               "yaxisname": "Number of Events",
               "showyaxisvalues": "1",
               "theme": "fint",
               "showvalues": "0",
               "showtooltip": "0",
               "linethickness": "2",
               "anchorhoverradius": "4",
               "anchorradius": "2",
               "anchorborderthickness": "2"
           },
           "categories": [
            {
              "category": [
                {"label": "Mon"},{"label": "Tue"},{"label": "Wed"},{"label": "Thu"},{"label": "Fri"},{"label": "Sat"},{"label": "Sun"}
              ]
            }
          ],
           "dataset": [
               {
                   "seriesname": "Recovery(Up)",
                   "data": [
                       {"value": "6"},{"value": "7"},{"value": "4"},{"value": "13"}, {"value": "7"},{"value": "12"},{"value": "10"}
                   ]
               },
               {
                   "seriesname": "Warning",
                   "data": [
                       {"value": "1"},{"value": "5"},{"value": "7"},{"value": "5"},{"value": "7"},{"value": "3"},{"value": "0"}
                   ]
               },
               {
                   "seriesname": "Unknown",
                   "data": [
                       {"value": "0"},{"value": "0"},{"value": "2"},{"value": "1"},{"value": "0"},{"value": "2"},{"value": "0"}
                   ]
               },
               {
                   "seriesname": "Critical",
                   "data": [
                       {"value": "0"},{"value": "0"},{"value": "0" },{"value": "3" },{"value": "0"},{"value": "3"},{"value": "0"}
                   ]
               }
           ]
         }
       };
    }
])

.controller('SysCommentsCtrl', ['$scope', 'async', '$timeout', '$window', 'ngToast',
    function($scope, async, $timeout, $window, ngToast) {

      $scope.init = function() {
        //get comments data
        var optionshost = {
            name: 'hostcomments',
            url: 'comments/' + 'hostcomment',
            queue: 'main'
        };
        async.api($scope, optionshost);

        var optionsservice = {
            name: 'servicecomments',
            url: 'comments/' + 'servicecomment',
            queue: 'main'
        };
        async.api($scope, optionsservice);

        //get author
        var options1 = {
            name: 'status',
            url: 'status',
            queue: 'status-' + '',
            cache: true
        };
        async.api($scope, options1);

        //$scope.resetModal();
      };

      $scope.resetModal = function(){
        $scope.hostName = '';
        $scope.service = ' ';
        $scope.persistent = true;
        $timeout(function(){$scope.author = $scope.status.username;}, 1000);
        $scope.comment = '';
        if($scope.addHostComment)
          $scope.addHostComment.$setPristine();
        if($scope.addServiceComment)
          $scope.addServiceComment.$setPristine();
      };

      $scope.addComment = function(type){

        $scope.resetModal();

        $scope.add = function(hostName, service, persistent, comment){
          console.log("addComment");
          console.log("type="+type);
          console.log("host="+hostName);
          console.log("service="+service);
          console.log("persistent="+persistent);
          console.log("author="+$scope.status.username);
          console.log("comment="+comment);

          var options = {
              name: 'success',
              url: 'addcomments/'+ type + '/' + hostName + '/' + service
                + '/' + persistent + '/' + $scope.status.username + '/' + comment,
              queue: 'main'
          };

          async.api($scope, options);

          $scope.callback = function(data, status, headers, config) {
            if(data)
              ngToast.create({className: 'alert alert-success',content:'Success!',timeout:3000});
            else
              ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
          };
        };
      };

      $scope.deleteComment = function(id, type){

        $scope.delete = function(){
          var options = {
              name: 'success',
              url: 'deletecomments/' + id + '/' + type,
              queue: 'main'
          };

          async.api($scope, options);

          $scope.callback = function(data, status, headers, config) {
            if(data)
              ngToast.create({className: 'alert alert-success',content:'Success!',timeout:3000});
            else
              ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
          };

        };
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
