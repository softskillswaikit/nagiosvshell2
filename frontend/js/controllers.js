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

// Host Details Controller enhanced by Choi Yi Zhen and Wan Choon Yean
.controller('HostDetailsCtrl', ['$scope', '$routeParams', '$filter', 'async', '$timeout', '$interval', '$route', 'ngToast',
    function($scope, $routeParams, $filter, async, $timeout, $interval, $route, ngToast) {


        // The function used to reset the value of the form to the default value.
        $scope.resetWan = function() {

            var optionsstatus = {
                name: 'status',
                url: 'status',
                queue: 'status-' + '',
                cache: true
            };

            async.api($scope, optionsstatus);

            //Reset the schedule downtime for host modal
            $scope.hostName = $routeParams.host;
            $scope.hostComment = "";
            $scope.hostTriggeredBy = "N/A";
            $scope.hostStartDateTime = "";
            $scope.hostEndDateTime = "";
            $scope.hostType = 'Fixed';
            $scope.hostDurationHour = 0;
            $scope.hostDurationMin = 0;
            $scope.hostChildHost = "doNothing";
            $scope.force = false;
            $scope.broadcast = false;
            $scope.force_check = true;
            $scope.stickyack = true;
            $scope.sendnotify = true;
            $scope.persistent = false;

            $scope.callback = function(data, status, headers, config){
                if(config != null){
                    if(config.url.includes("status")){
                        $scope.hostAuthor = data.username;
                    }
                }
            };
        };

        // The function used to reset the value of the form to the default value.
        $scope.reset = function(){
            //get author
            var optionsstatus = {
                name: 'status',
                url: 'status',
                queue: 'status-' + '',
                cache: true
            };
            async.api($scope, optionsstatus);

              $scope.hostName = $routeParams.host;
              $scope.service = ' ';
              $scope.persistent = true;
              $scope.comment = '';
              if($scope.addHostComment)
                $scope.addHostComment.$setPristine();

              $scope.callback = function(data, status, headers, config) {
                if(config != null){
                  console.log("callback");
                  if(config.url.includes("status")){
                    $scope.author = data.username;
                    console.log($scope.author);
                  }
                }
              };
        };

        $scope.init = function() {

            var options = {
                name: 'host',
                url: 'hoststatus/' + $routeParams.host,
                queue: 'main'
            };

            async.api($scope, options);           
        };

        /* <=====================Yi Zhen Part=====================> */
        $scope.toggle = function(action, is_enabled){
            $scope.toggleAction = function(){
              console.log('toggle');
              $scope.reset();
            };
        };

        $scope.addComment = function(type){
            $scope.reset();

            $scope.add = function(persistent, author, comment){
                console.log("addComment");
                console.log("type="+type);
                console.log("host="+$scope.hostName);
                console.log("service="+$scope.service);
                console.log("persistent="+persistent);
                console.log("author="+author);
                console.log("comment="+comment);

                var options = {
                    name: 'success',
                    url: 'addcomments/'+ type + '/' + $routeParams.host + '/' + ' '
                      + '/' + persistent + '/' + author + '/' + comment,
                    queue: 'main'
                };
                async.api($scope, options);

                $scope.callback = function(data, status, headers, config) {
                    if(config != null){
                        if(config.url.includes("addcomments")){
                            if(data == 'true'){
                              ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                              $('.modal').modal('hide');
                            }
                            else
                              ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
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
                if(config != null){
                    if(config.url.includes("deletecomments")){
                        if(data == 'true')
                          ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                        else
                          ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                    }
                }
            };
          }
        };

        $scope.deleteAllComment = function(type){

            $scope.deleteAll = function(){

              if(type == 'host'){

                var options = {
                    name: 'success',
                    url: 'deleteallcomment/' + type + '/' +  $routeParams.host + '/' + ' ' + '/',
                    queue: 'main'
                };

                async.api($scope, options);

                $scope.callback = function(data, status, headers, config) {
                    if(config != null){
                        if(config.url.includes("deleteallcomment")){
                            if(data == 'true')
                              ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                            else
                              ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };
              }
            }
        };

        /* <=====================Choon Yean Part=====================> */
        /* Choon Yean configure the functionality for the command of host detail:
            Task 1: Send Host Custom Notification
                passcustom function     =   configure the data to the form field of the pop up modal
                    forcechange fucntion    =   detect the status change of the force check box
                                                status -> value of ng-checked (true / false)
                    broadcastchange function=   detect the status change of the broadcast check box
                                                status -> value of ng-checked (true / false)
                    custom fucntion         =   call the sendcustomnotification api service through the GET method
                                                comment -> the input of comment value in the modal
                                                

            Task 2: Enable / Disable Notification of all services of particular host
                notification_check function     =   call the hostservicenotification api service through the GET method
                                                    type        -> "enable" / "disable"
                                                    hostname    -> name of the host that show in detail in the Host Details panel
                                                   

            Task 3: Schedule Downtime
                passhostdowntime function       =   configure the modal option
                    schedule function           =   receive the input from the form input field and pass into the controller
                                                    for implementation. Convert the hour and minute parameter into duration by
                                                    multiply the hour and minute in order to convert into minute.
                                                    Get the UTC timezone for start date and end date.
                                                    Call the api service through the GET method
                                                    
                                                    
            Task 4: Schedule downtime for this host and all services
                passservicedowntime function       =   configure the modal option
                    schedule function           =   receive the input from the form input field and pass into the controller
                                                    for implementation. Convert the hour and minute parameter into duration by
                                                    multiply the hour and minute in order to convert into minute.
                                                    Get the UTC timestamp for start date and end date.
                                                    Call the api service through the GET method

            Task 5: Acknowledge Problem
                passack function    =   configure the modal option for acknowledge problem.
                    sticky function =   detect the status change of the sticky check box
                                        status -> value of ng-checked (true / false)
                    notify function =   detect the status change of the send notify check box
                                        status -> value of ng-checked (true / false)
                    persist function =   detect the status change of the persist check box
                                        status -> value of ng-checked (true / false)
                    acknowledge function =  receive the input from the form input field and pass into the controller
                                            for implementation.
                                            comment -> (string) value from the input field

            Task 6: Enable / Disable checks of all service on this host
                enable_service_check function =  enable the service checks of all service
                    hostname -> target host (string)
                disable_service_check function = disable the service checks of all service
                    hostnme -> target host (string)

            Task 7: Schedule next check for this host
                host_schedule function =    schedule the next check time for the particular host
                    host -> target host (String)
                    time -> next check of target host which in unix timestamp format
                    schedule function =     schedule the next check time with the new next check time
                                            input from the form.
                                            time -> new input of next check (String)

            Task 8: Schedule checks for all service of this host
                all_service_schedule function =    schedule the next check time for all services of this host
                    host -> target host (String)
                    time -> next check of target host which in unix timestamp format
                    schedule function =     schedule the next check time with the new next check time
                                            input from the form.
                                            time -> new input of next check (String)*/

        $scope.notification_check = function (type) {

        
            var options = {
                name: 'ToggleHostServiceNotification',
                url: 'hostservicenotification/' + type + '/' + $routeParams.host,
                queue: 'main'
            };

            async.api($scope, options);

            $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("hostservicenotification")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };
        };

        $scope.passcustom = function() {
        
            $scope.resetWan();
            var type = 'host';
            var service = null;
        
            $scope.forcechange = function (status){
                if (status == false){
                    $scope.force = "false";
                }else if (status == true){
                    $scope.force = "true";
                }
            };

            $scope.broadcastchange = function (status){
                if (status == false){
                    $scope.broadcast = "false";
                
                }else if (status == true){
                    $scope.broadcast = "true";
                }
            };
        
            $scope.custom = function(comment) {
                var options = {
                        name: 'CustomNotification',
                        url: 'sendcustomnotification/' + type + '/' + $scope.custom_host + '/' + service + '/' + $scope.force + '/'
                        + $scope.broadcast + '/' + $scope.author + '/' + comment,
                        queue: 'main'
                };
                async.api($scope, options);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("sendcustomnotification")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };
            };
        };

        $scope.schedule_downtime = function(type) {
            $scope.resetWan();
            var service = null;
        
            $scope.schedulehost = function (hour, minute, comment, startdate, enddate, triggerby, fixed, childhost) {
                var duration = 0;
                var start_date = new Date(startdate);
                var start_timestamp = (start_date.getTime() / 1000).toString();
                var end_date = new Date(enddate);
                var end_timestamp = (end_date.getTime() / 1000).toString();
                var fixed_type = "";
            
                if (fixed == 'Flexible'){
                    fixed_type = "false";
                    duration = (hour * 60) + minute;
                }
                else if (fixed == 'Fixed'){
                    fixed_type = "true";
                    duration = (end_date.getHours() * 60 + end_date.getMinutes()) - (start_date.getHours() * 60 + start_date.getMinutes());
                }

                var options = {
                    name: 'HostDownTime',
                    url: 'scheduledowntime/' + type + '/' + $routeParams.host + '/' + service + '/' + start_timestamp + '/' + end_timestamp + '/'
                        + fixed_type + '/' + triggerby + '/' + duration + '/' + $scope.hostAuthor + '/' + comment,
                    queue: 'main'
                };

                async.api($scope, options);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("scheduledowntime")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };
            };

            $scope.schedulehostsvc = function (hour, minute, comment, startdate, enddate, triggerby, fixed) {
                var duration = 0;
                var start_date = new Date(startdate);
                var start_timestamp = (start_date.getTime() / 1000).toString();
                var end_date = new Date(enddate);
                var end_timestamp = (end_date.getTime() / 1000).toString();
                var fixed_type = "";
            
                if (fixed == 'Flexible'){
                    fixed_type = "false";
                    duration = (hour * 60) + minute;
                }
                else if (fixed == 'Fixed'){
                    fixed_type = "true";
                    duration = (end_date.getHours() * 60 + end_date.getMinutes()) - (start_date.getHours() * 60 + start_date.getMinutes());
                }

                var options = {
                    name: 'HostSvcDownTime',
                    url: 'scheduledowntime/' + type + '/' + $routeParams.host + '/' + service + '/' + start_timestamp + '/' + end_timestamp + '/'
                        + fixed_type + '/' + triggerby + '/' + duration + '/' + $scope.hostAuthor + '/' + comment,
                    queue: 'main'
                };

                async.api($scope, options);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("scheduledowntime")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };
            };
        };

        $scope.schedule_check = function (type, check_time) {
            $scope.resetWan();
            $scope.check_time = $filter('date')(check_time * 1000, "yyyy-MM-dd HH:mm:ss");
            $scope.input_check_time = $scope.check_time;
            var service = null;

            $scope.forcecheck = function (status) {
            if (status == false)
                $scope.force_check = 'false';
            else
                $scope.force_check = 'true';
            };

            $scope.schedule = function (time) {
                var date = new Date(time);
                var next_check = date.getTime() / 1000;
                var timestamp = next_check.toString();
        
                var options = {
                    name: 'schedulecheck',
                    url: 'schedulecheck/' + type + '/' + $routeParams.host + '/' + service + '/' + timestamp 
                            + '/' + $scope.force_check,
                    queue: 'main'
                };

                async.api($scope, options);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("schedulecheck")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };
            };
        };

        $scope.host_service_check = function (type) {

            var options = {
                name: 'hostServiceCheck',
                url: 'hostservicecheck/' + type + '/' + $routeParams.host,
                queue: 'main'
            };

            async.api($scope, options);

            $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("hostservicecheck")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };
        };

        $scope.passack = function () {
    
            $scope.resetWan();
            var type = 'host';
            var service = null;

            $scope.sticky = function (status){
                if (status == false){
                    $scope.stickyack = "false";
                }else if (status == true){
                    $scope.stickyack = "true";
                }
            };

            $scope.notify = function (status){
                if (status == false){
                    $scope.sendnotify = "false";
                
                }else if (status == true){
                    $scope.sendnotify = "true";
                }
            };

            $scope.persist = function (status){
                if (status == false){
                    $scope.persistent = "false";
                
                }else if (status == true){
                    $scope.persistent = "true";
                }
            };

            $scope.acknowledge = function (comment) {
                $scope.comment = comment;
                var options = {
                    name: 'acknowledge',
                    url: 'acknowledgeproblem/' + type + '/' + $routeParams.host + '/' + service + '/'
                    + $scope.stickyack + '/' + $scope.sendnotify + '/' + $scope.persistent + '/'
                    + $scope.author + '/' + $scope.comment,
                    queue: 'main'
                };

                async.api($scope, options);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("acknowledgeproblem")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };
            };
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

/* <====================Reports Controller====================> */
// Availability Panel Controller created by Choi Yi Zhen
.controller('AvailabilityCtrl', ['$scope', '$routeParams', '$filter', 'async', '$window',
    function($scope, $routeParams, $filter, async, $window) {

      $scope.init = function() {
        //get component name
        var options = {
            name: 'name',
            url: 'name',
            queue: 'main'
        };

        async.api($scope, options);

        $scope.reset();
      };

      $scope.reset = function(){
        $scope.today = new Date();
        $scope.todayString = $filter('date')(Date.now(), 'yyyy-MM-dd');

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

      $scope.createReport = function(){
        $window.location.href="#/report/availability/report";

        localStorage.setItem('reportType', $scope.reportType);
        localStorage.setItem('reportComponent', $scope.reportComponent);

        console.log("reportType");
        console.log($scope.reportType);
        console.log("reportComponent");
        console.log($scope.reportComponent);
        console.log("startDate");
        console.log($scope.startDate);
        console.log("endDate");
        console.log($scope.endDate);
        console.log("reportPeriod");
        console.log($scope.reportPeriod);
        console.log("backtrackedArchives");
        console.log($scope.backtrackedArchives);

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
              {0:"localhost",1:"100.00",2:"0.00",3:"0.00",4:"0.00"}
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

// Trends Panel Controller created by Choi Yi Zhen
.controller('TrendsCtrl', ['$scope', '$routeParams', '$filter', 'async', '$timeout', '$window',
    function($scope, $routeParams, $filter, async, $timeout, $window) {

        $scope.init = function() {
          //get component name
          var options = {
              name: 'name',
              url: 'name',
              queue: 'main'
          };

          async.api($scope, options);

          $scope.reset();
        };

        $scope.reset = function(){
          $scope.today = new Date();
          $scope.todayString = $filter('date')(Date.now(), 'yyyy-MM-dd');

          $scope.reportType = 'Host';
                $timeout(function(){
            $scope.reportHost = $scope.name.host[0];
            $scope.reportService = $scope.name.service[0].service;
          }, 1000);
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

          $window.location.href="#/report/trends/report";

          var startUnix = parseInt((new Date($scope.startDate).getTime() / 1000).toFixed(0));
          var endUnix = parseInt((new Date($scope.endDate).getTime() / 1000).toFixed(0));
          console.log("reportType");
          console.log($scope.reportType);
          console.log("reportHost");
          console.log($scope.reportHost);
          console.log("reportService");
          console.log($scope.reportService);
          console.log("hostresource");
          console.log($scope.reportHostResource);
          console.log("reportPeriod");
          console.log($scope.reportPeriod);
          console.log("startdate");
          console.log($scope.startDate);
          console.log("backtrackedArchives");
          console.log($scope.backtrackedArchives);

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

// Alert History Panel Controller created by Wan Choon Yean
.controller('AlertHistoryCtrl', ['$scope', 'async',
    function($scope, async) {

        /*  Declare the variable for the number of count for the previous day button
        *   click and next day button click.
        */
        var previous = 1;
        var next = 1;

        // Load the alert history data through the api
        $scope.init = function() {

            // Get the unix timestamp of the current day in string
            var utcdate = new Date();
            var timestamp = (utcdate.getTime() - 86400000) / 1000;
            var date = timestamp.toString();
            // subsitute out the timestamp length to match the condition in the api
            date = date.substring(0, 10);

            // Get the unix timestamp of the previous day in string
            $scope.previousday = function() {
            
                next = 1;
                var predate = parseInt(timestamp);
                var previoustimestamp = predate - (86400 * previous);
                var previousdate = previoustimestamp.toString();
                // increment the button clicked count
                previous++;

                // Get the unix timestamp of the next day in string
                $scope.nextday = function() {
                
                    previous = 1;
                    var ntdate = parseInt(previousdate);
                    var nexttimestamp = ntdate + (86400 * next);
                    console.log(nexttimestamp);
                    var nextdate = nexttimestamp.toString();
                    // increment the button clicked count
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

// Alert Summary Panel Controller created by Wan Choon Yean
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

// Alert Histogram Panel Controller created by Choi Yi Zhen
.controller('AlertHistogramCtrl', ['$scope', '$routeParams', '$filter', 'async', '$timeout', '$window',
    function($scope, $routeParams, $filter, async, $timeout, $window) {

        $scope.init = function(){
      //get component name
      var options = {
          name: 'name',
          url: 'name',
          queue: 'main'
      };

      async.api($scope, options);

      $scope.reset();

      };

       $scope.reset = function(){
          $scope.today = new Date();
          $scope.todayString = $filter('date')(Date.now(), 'yyyy-MM-dd');

          $scope.reportType = 'Host';
          $timeout(function(){
            $scope.reportHost = $scope.name.host[0];
            $scope.reportService = $scope.name.service[0].service;
          }, 1000);

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
         $window.location.href="#/report/alerthistogram/report";

         var startUnix = parseInt((new Date($scope.startDate).getTime() / 1000).toFixed(0));
         var endUnix = parseInt((new Date($scope.endDate).getTime() / 1000).toFixed(0));
         console.log("reportType");
         console.log($scope.reportType);
         console.log("reportHost");
         console.log($scope.reportHost);
         console.log("reportService");
         console.log($scope.reportService);
         console.log("hostresource");
         console.log($scope.reportHostResource);
         console.log("reportPeriod");
         console.log($scope.reportPeriod);
         console.log("startdate");
         console.log($scope.startDate);
         console.log("backtrackedArchives");
         console.log($scope.backtrackedArchives);

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

// Notification Panel Controller created by Wan Choon Yean
.controller('NotificationsCtrl', ['$scope', 'async',
    function($scope, async) {

        /*  Declare the variable for the number of count for the previous day button
        *   click and next day button click.
        */
        var previous = 1;
        var next = 1;
        
        // Load the notification data through the api
        $scope.init = function() {

            // Get the unix timestamp of the current day in string
            var utcdate = new Date();
            var timestamp = (utcdate.getTime()) / 1000;
            var date = timestamp.toString();
            // subsitute out the timestamp length to match the condition in the api
            date = date.substring(0, 10);

            // Get the unix timestamp of the previous day in string
            $scope.previousday = function() {
            
                next = 1;
                var predate = parseInt(timestamp);
                var previoustimestamp = predate - (86400 * previous);
                var previousdate = previoustimestamp.toString();
                // increment the button clicked count
                previous++;

                // Get the unix timestamp of the next day in string
                $scope.nextday = function() {

                    previous = 1;
                    var ntdate = parseInt(previousdate);
                    var nexttimestamp = ntdate + (86400 * next);
                    var nextdate = nexttimestamp.toString();
                    // increment the button clicked count
                    next++;

                    var options = {
                        name: 'notifications',
                        url: 'notification/' + nextdate,
                        queue: 'main'
                    };

                    async.api($scope, options);
                };
        
    
                var options = {
                    name: 'notifications',
                    url: 'notification/' + previousdate,
                    queue: 'main'
                };

                async.api($scope, options);
            };

            var options = {
                name: 'notifications',
                url: 'notification/' + date,
                queue: 'main'
            };

            async.api($scope, options);
        };
    }
])

// Event Log Controller created by Wan Choon Yean
.controller('EventLogCtrl', ['$scope', 'async',
    function($scope, async) {
        
        /*  Declare the variable for the number of count for the previous day button
        *   click and next day button click.
        */
        var previous = 1;
        var next = 1;
        
        // Load the event log data through the api
        $scope.init = function() {

            // Get the unix timestamp of the current day in string
            var utcdate = new Date();
            var timestamp = (utcdate.getTime()) / 1000;
            var date = timestamp.toString();
            // subsitute out the timestamp length to match the condition in the api
            date = date.substring(0, 10);

            // Get the unix timestamp of the previous day in string
            $scope.previousday = function() {
            
                next = 1;
                var predate = parseInt(timestamp);
                var previoustimestamp = predate - (86400 * previous);
                var previousdate = previoustimestamp.toString();
                // increment the button clicked count
                previous++;

                // Get the unix timestamp of the next day in string
                $scope.nextday = function() {

                    previous = 1;
                    var ntdate = parseInt(previousdate);
                    var nexttimestamp = ntdate + (86400 * next);
                    var nextdate = nexttimestamp.toString();
                    // increment the button clicked count
                    next++;

                    var options = {
                        name: 'eventlog',
                        url: 'eventlog/' + nextdate,
                        queue: 'main'
                    };

                    async.api($scope, options);
                };
        
    
                var options = {
                    name: 'eventlog',
                    url: 'eventlog/' + previousdate,
                    queue: 'main'
                };

                async.api($scope, options);
            };

            var options = {
                name: 'eventlog',
                url: 'eventlog/' + date,
                queue: 'main'
            };

            async.api($scope, options);

        };
    }
])
/* <====================Reports Controller====================> */


/* <====================System Controller====================> */
// Comments Panel Controller created by Choi Yi Zhen
.controller('SysCommentsCtrl', ['$scope', 'async', '$timeout', '$window', 'ngToast', '$interval',
    function($scope, async, $timeout, $window, ngToast, $interval) {

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

        //get host and service name
        var options = {
            name: 'name',
            url: 'name',
            queue: 'main'
        };

        async.api($scope, options);

      };

      $scope.reset = function(){
        //get author
        var options1 = {
            name: 'status',
            url: 'status',
            queue: 'status-' + '',
            cache: true
        };
        async.api($scope, options1);

        $scope.hostName = $scope.name.host[0];
        //$scope.service = $scope.name.service[0].service;
        $scope.persistent = true;
        $scope.comment = '';

        if($scope.addHostComment)
          $scope.addHostComment.$setPristine();
        if($scope.addServiceComment)
          $scope.addServiceComment.$setPristine();

        $scope.callback = function(data, status, headers, config) {
          if(config != null){
            if(config.url.includes("status")){
              $scope.author = data.username;
            }
          }
        };
      };

      $scope.addComment = function(type){

        $scope.reset();

        $scope.add = function(hostName, service, persistent, author, comment){

          var options = {
              name: 'success',
              url: 'addcomments/'+ type + '/' + hostName + '/' + service
                + '/' + persistent + '/' + author + '/' + comment,
              queue: 'main'
          };

          async.api($scope, options);

          $scope.callback = function(data, status, headers, config) {
            if(config != null){
                if(config.url.includes("addcomments")){
                    if(data == 'true'){
                      ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                      $('.modal').modal('hide');
                    }
                    else
                      ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                    //$timeout(function(){$window.location.reload()}, 2000);
                }
            }
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
              if(config != null){
                if(config.url.includes("deletecomments")){
                  if(data == 'true')
                    ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                  else
                    ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                  //$timeout(function(){$window.location.reload()}, 2000);
                }
              }
          };
        };
      };
    }
])

// Downtime Panel Controller created by Choi Yi Zhen
.controller('SysDowntimeCtrl', ['$scope', '$filter', 'async', '$timeout', 'ngToast', '$window',
    function($scope, $filter, async, $timeout, ngToast, $window) {

        $scope.init = function() {

          var optionshost = {
              name: 'hostdowntime',
              url: 'downtime/' + 'host',
              queue: 'main'
          };
          async.api($scope, optionshost);

          var optionsservice = {
              name: 'svcdowntime',
              url: 'downtime/' + 'svc',
              queue: 'main'
          };
          async.api($scope, optionsservice);

            //get host and service name
            var options = {
                name: 'name',
                url: 'name',
                queue: 'main'
            };

            async.api($scope, options);

        };

        $scope.reset = function(){
          console.log("reset");

          //get author
          var options1 = {
              name: 'status',
              url: 'status',
              queue: 'status-' + '',
              cache: true
          };
          async.api($scope, options1);

          $scope.now = new Date();
          $scope.nowString = $filter('date')(Date.now(), 'yyyy-MM-ddTHH:mm');

          $scope.hostName = $scope.name.host[0];
          $scope.comment = '';
          $scope.triggeredBy = 'N/A';
          $scope.startDate = $scope.nowString;
          $scope.endDate = $scope.nowString;
          $scope.type = 'Fixed';
          $scope.durationHour = 2;
          $scope.durationMin = 0;
          $scope.childHost = 'doNothing';

          if($scope.scdhostdowntime)
            $scope.scdhostdowntime.$setPristine();
          if($scope.scdsvcdowntime)
            $scope.scdsvcdowntime.$setPristine();

          $scope.callback = function(data, status, headers, config) {
            if(config != null){
              if(config.url.includes("status")){
                $scope.author = data.username;
              }
            }
          };

        };

        $scope.scheduleDowntime = function(scheduletype){

          $scope.reset();

          $scope.schedule = function(hostName, service, start, end, type, triggerID, durationHour, durationMin, author, comment){

            var duration  = durationHour * 60 + durationMin;
            var startUnix = parseInt((new Date(start).getTime() / 1000).toFixed(0));
            var endUnix = parseInt((new Date(end).getTime() / 1000).toFixed(0));
            console.log("host=" + hostName);
            console.log("service=" + service);
            console.log("start=" + startUnix);
            console.log("end=" + endUnix);
            console.log("type=" + type);
            console.log("triggerID=" + triggerID);
            console.log("durationHour=" + durationHour);
            console.log("durationMin=" + durationMin);
            console.log("duration=" + duration);
            console.log("author=" + author);
            console.log("comment=" + comment);

            var options = {
                name: 'success',
                url: 'scheduledowntime/'+ scheduletype + '/' + hostName + '/' + service + '/'+ startUnix
                  + '/' + endUnix + '/' + type + '/' + triggerID + '/' + duration
                  + '/' + author + '/' + comment,
                queue: 'main'
            };
            async.api($scope, options);

            $scope.callback = function(data, status, headers, config) {
              if(config != null){
                  if(config.url.includes("scheduledowntime")){
                      if(data == 'true'){
                        ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                        $('.modal').modal('hide');
                      }
                      else
                        ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                      //$timeout(function(){$window.location.reload()}, 2000);
                  }
              }
            };
          };
        };

        $scope.deleteDowntime = function(id, type){

          $scope.delete = function(){
            var options = {
                name: 'success',
                url: 'deletedowntime/' + id + '/' + type,
                queue: 'main'
            };

            async.api($scope, options);

            $scope.callback = function(data, status, headers, config) {
                if(config != null){
                  if(config.url.includes("deletedowntime")){
                    if(data == 'true')
                      ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                    else
                      ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                    //$timeout(function(){$window.location.reload()}, 2000);
                  }
                }
            };
          };
        };


    }
])

// Process Info Controller created by Wan Choon Yean
.controller('ProcessInfoCtrl', ['$scope', '$interval', '$location', '$timeout', 'async', 'ngToast',
    function($scope, $interval, $location, $timeout, async, ngToast) {


        /*  function used to show the modal
        *   String modal_id
        */
        $scope.showModal = function(modal_id){
            $(modal_id).modal('show');
        };

        /*  function used to dismiss or hide the modal
        *   String modal_id
        */
        $scope.closeModal = function(modal_id){
            $(modal_id).modal('hide');
        };


        // function that load the process info data
        $scope.init = function() {

            var options = {
                name: 'processinfo',
                url: 'processinfo',
                queue: 'main'
            };

            async.api($scope, options);

            // update the page every 3 second until it reach 20 times
            $interval(function(){

                var options = {
                    name: 'processinfo',
                    url: 'processinfo',
                    queue: 'main'
                };

                async.api($scope, options);

            }, 3000, 20);   
        };

        /*  function used to shutdown / restart the nagios process
        *   String operation ('shutdown' / 'restart')
        */
        $scope.open = function(operation) {

            if (operation == 'shutdown'){

                var result = {
                    name: 'nagiosoperation',
                    url: 'nagiosoperation/' + operation,
                    queue: 'main'
                };

                async.api($scope, result);

            }else if (operation == 'restart'){

                var result = {
                    name: 'nagiosoperation',
                    url: 'nagiosoperation/' + operation,
                    queue: 'main'
                };

                async.api($scope, result);
            }
        };

        /*  function used to enable / disable notification of nagios process
        *   bool operation (true / false)
        */
        $scope.notification = function(operation){

            if (operation == true){

                var type = "false";

                var result = {
                    name: 'allnotifications',
                    url: 'allnotifications/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allnotifications")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };

            }else if (operation == false){

                var type = "true";

                var result = {
                    name: 'allnotifications',
                    url: 'allnotifications/' + type,
                    queue: 'main'
                };

                async.api($scope, result);
                console.log($scope.allnotifications);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allnotifications")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };
            }
        };

        /*  function used to start / stop executing active check of service
        *   bool operation (true / false)
        */
        $scope.activeservice = function(operation){
        
            if (operation == true){

                var type = "false";

                var result = {
                    name: 'allservicecheck',
                    url: 'allservicecheck/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allservicecheck")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };

            }else if (operation == false){

                var type = "true";

                var result = {
                    name: 'allservicecheck',
                    url: 'allservicecheck/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allservicecheck")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };

            }   
        };

        /*  function used to start / stop accepting passive check of service
        *   bool operation (true / false)
        */
        $scope.passiveservice = function(operation){
        
            if (operation == true){

                var type = "false";

                var result = {
                    name: 'allpassiveservicecheck',
                    url: 'allpassiveservicecheck/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allpassiveservicecheck")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };

            }else if (operation == false){

                var type = "true";

                var result = {
                    name: 'allpassiveservicecheck',
                    url: 'allpassiveservicecheck/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allpassiveservicecheck")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };
            }            
        };

        /*  function used to start / stop executing active check of host
        *   bool operation (true / false)
        */
        $scope.activehost = function(operation){
        
            if (operation == true){

                var type = "false";

                var result = {
                    name: 'allhostcheck',
                    url: 'allhostcheck/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allhostcheck")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };

            }else if (operation == false){

                var type = "true";

                var result = {
                    name: 'allhostcheck',
                    url: 'allhostcheck/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allhostcheck")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };
            }
        };

        /*  function used to start / stop accepting passive check of host
        *   bool operation (true / false)
        */
        $scope.passivehost = function(operation){
        
            if (operation == true){

                var type = "false";

                var result = {
                    name: 'allpassivehostcheck',
                    url: 'allpassivehostcheck/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allpassivehostcheck")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };

            }else if (operation == false){

                var type = "true";

                var result = {
                    name: 'allpassivehostcheck',
                    url: 'allpassivehostcheck/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allpassivehostcheck")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };
            }
        };

        /*  function used to enable / disable event handler
        *   bool operation (true / false)
        */
        $scope.event = function(operation){
        
            if (operation == true){

                var type = "false";

                var result = {
                    name: 'eventhandler',
                    url: 'eventhandler/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("eventhandler")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };

            }else if (operation == false){

                var type = "true";

                var result = {
                    name: 'eventhandler',
                    url: 'eventhandler/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("eventhandler")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };
            }
        };

        /*  function used to start / stop the obsess over service
        *   bool operation (true / false)
        */
        $scope.obsessservice = function(operation){
        
            if (operation == true){

                
                var type = 'false';

                var result = {
                    name: 'ObsessService',
                    url: 'obsessoverservicecheck/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("obsessoverservicecheck")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };

            }else if (operation == false){

                
                var type = 'true';

                var result = {
                    name: 'ObsessService',
                    url: 'obsessoverservicecheck/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("obsessoverservicecheck")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };
            }
        };

        /*  function used to start / stop the obsess over host
        *   bool operation (true / false)
        */
        $scope.obsesshost = function(operation){
        
            if (operation == true){
                
                var type = 'false';

                var result = {
                    name: 'ObsessHost',
                    url: 'obsessoverhostcheck/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("obsessoverhostcheck")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };

            }else if (operation == false){

                
                var type = 'true';

                var result = {
                    name: 'ObsessHost',
                    url: 'obsessoverhostcheck/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("obsessoverhostcheck")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };
            }
        };

        /*  function used to enable / disable the flap detection
        *   bool operation (true / false)
        */
        $scope.flap = function(operation){

            if (operation == true){

                var type = "false";

                var result = {
                    name: 'flapdetection',
                    url: 'allflapdetection/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allflapdetection")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };

            }else if (operation == false){

                var type = "true";

                var result = {
                    name: 'flapdetection',
                    url: 'allflapdetection/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allflapdetection")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };
            }
        };

        /*  function used to enable / disable the performance data
        *   bool operation (true / false)
        */
        $scope.perform = function(operation){
        
            if (operation == true){

                var type = "false";

                var result = {
                    name: 'performancedata',
                    url: 'performancedata/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("performancedata")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };

            }else if (operation == false){

                var type = "true";

                var result = {
                    name: 'performancedata',
                    url: 'performancedata/' + type,
                    queue: 'main'
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("performancedata")){
                            if(data == 'true'){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                $('.modal').modal('hide');
                            }
                            else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                        }
                    }
                };
            }
        };

    }
])

// Performance Info Panel Controller created by Choi Yi Zhen
.controller('PerformanceInfoCtrl', ['$scope', 'async', '$timeout', '$window',
    function($scope, async, $timeout, $window) {

        $scope.init = function() {
            var options = {
                name: 'pinfo',
                url: 'performanceinfo',
                queue: 'main'
            };

            async.api($scope, options);

        };
        $timeout(function(){console.log(typeof $scope.pinfo.active_host_checked_since_program_start)}, 1000);

    }
])

// Scheduling Queue Controller created by Wan Choon Yean
.controller('SchedulingQueueCtrl', ['$scope', '$filter', 'async', 'ngToast',
    function($scope, $filter, async, ngToast) {

        $('[data-toggle="tooltip"]').tooltip();

        // Function uused to load the scheduling queue data
        $scope.init = function() {

            var options = {
                name: 'scheduleQueue',
                url: 'schedulequeue',
                queue: 'main'
            };

            async.api($scope, options);
        };

        //function used to disable the active check of host or service in particular host
        $scope.parameterdisable = function(host, service){
        
            $scope.hostName = host;
            $scope.serviceName = service;
            var type = false;

            if ($scope.serviceName != null){
                $scope.Disableshow = false;
            }else{
                $scope.Disableshow = true;
            }

            $scope.disable = function(){
                if ($scope.Disableshow == false){
                
                    var options = {
                        name: 'servicecheck',
                        url: 'servicecheck/' + type + '/' + $scope.hostName + '/' + $scope.serviceName,
                        queue: 'main'
                    };

                    async.api($scope, options);

                    $scope.callback = function(data, status, headers, config){
                        if(config != null){
                            if(config.url.includes("servicecheck")){
                                if(data == 'true'){
                                    ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                    $('.modal').modal('hide');
                                }
                                else
                                    ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                            }
                        }
                    };
                }
                else{

                    var options = {
                        name: 'hostcheck',
                        url: 'hostcheck/' + type + '/' + $scope.hostName,
                        queue: 'main'
                    };

                    async.api($scope, options);

                    $scope.callback = function(data, status, headers, config){
                        if(config != null){
                            if(config.url.includes("hostcheck")){
                                if(data == 'true'){
                                    ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                    $('.modal').modal('hide');
                                }
                                else
                                    ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                            }
                        }
                    };
                }
            };
        };

        // function used to schedule the check time for host or service in particular host
        $scope.parameterschedule = function(host, service, check_time){

            $scope.check_time = $filter('date')(check_time * 1000, "yyyy-MM-dd HH:mm:ss");
            $scope.input_check_time = $scope.check_time;
            $scope.hostName = host;
            $scope.serviceName = service;
            $scope.force_check = true;

            $scope.forcecheck = function (status){
                if (status == false){
                    $scope.force_check = "false"; 
                }else if (status == true){
                    $scope.force_check = "true"; 
                }
            };
        
            if ($scope.serviceName != null){
                $scope.Scheduleshow = false;
            }else{
                $scope.Scheduleshow = true;
            } 

            $scope.schedule = function(time){
                var date = new Date(time);
                var next_check = date.getTime() / 1000;
                var timestamp = next_check.toString();

                if ($scope.Scheduleshow == false){
                    var type = 'service';
                    var options = {
                        name: 'schedulecheck',
                        url: 'schedulecheck/' + type + '/' + $scope.hostName + '/' + $scope.serviceName + '/' + timestamp + '/' + $scope.force_check,
                        queue: 'main'
                    };

                    async.api($scope, options);

                     $scope.callback = function(data, status, headers, config){
                        if(config != null){
                            if(config.url.includes("schedulecheck")){
                                if(data == 'true'){
                                    ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                    $('.modal').modal('hide');
                                }
                                else
                                    ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                            }
                        }
                    };
                }
                else{
                    var type = 'host';
                    var options = {
                        name: 'schedulecheck',
                        url: 'schedulecheck/' + type + '/' + $scope.hostName + '/' + $scope.serviceName + '/' + timestamp + '/' + $scope.force_check,
                        queue: 'main'
                    };

                    async.api($scope, options);

                     $scope.callback = function(data, status, headers, config){
                        if(config != null){
                            if(config.url.includes("schedulecheck")){
                                if(data == 'true'){
                                    ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                                    $('.modal').modal('hide');
                                }
                                else
                                    ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                            }
                        }
                    };
                }
            };
        };

    }
])
/* <====================System Controller====================> */


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

// Service Details Controller implemented and enhanced by Choi Yi Zhen
.controller('ServiceDetailsCtrl', ['$scope', '$filter', '$routeParams', 'async', '$timeout', 'ngToast',
    function($scope, $filter, $routeParams, async, $timeout, ngToast) {

        // The function used to reset the value of the form to the default value.
        $scope.resetWan = function() {

            var optionsstatus = {
                name: 'status',
                url: 'status',
                queue: 'status-' + '',
                cache: true
            };

            async.api($scope, optionsstatus);

            $scope.comment = '';
            $scope.custom_host = $routeParams.host;
            $scope.hostName = $routeParams.host;
            $scope.ack_host = $routeParams.host;
            $scope.custom_service = $routeParams.service;
            $scope.serviceName = $routeParams.service;
            $scope.ack_service = $routeParams.service;
            $scope.serviceTriggeredBy = "N/A";
            $scope.startDate = "";
            $scope.endDate = "";
            $scope.serviceType = "Fixed";
            $scope.durationHour = 0;
            $scope.durationMin = 0;
            $scope.force = false;
            $scope.broadcast = false;
            $scope.force_check = true;
            $scope.stickyack = true;
            $scope.sendnotify = true;
            $scope.persistent = false;

            $scope.callback = function(data, status, headers, config){
                if(config != null){
                    if(config.url.includes("status")){
                        $scope.author = data.username;
                    }
                }
            };
        };

        $scope.reset = function(){
          //get author
          var optionsstatus = {
              name: 'status',
              url: 'status',
              queue: 'status-' + '',
              cache: true
          };

          async.api($scope, optionsstatus);

          //initialize scope
          $scope.hostName = $routeParams.host;
          $scope.serviceName = $routeParams.service;
          $scope.persistent = true;
          $scope.comment = '';
          if($scope.addServiceComment)
            $scope.addServiceComment.$setPristine();

            $scope.callback = function(data, status, headers, config) {
              if(config != null){
                if(config.url.includes("status")){
                  $scope.author = data.username;
                }
              }
            };
        };

        $scope.init = function() {

            var options = {
                name: 'service',
                url: 'servicestatus/' + $routeParams.host + '/' + $routeParams.service,
                queue: 'main'
            };

            async.api($scope, options);
        };

        /* <=====================Yi Zhen Part=====================> */
        $scope.toggle = function(action, is_enabled){

          $scope.toggleAction = function(){

          };
        };

        $scope.addComment = function(type){

          $scope.reset();

          $scope.add = function(persistent, author, comment){
            console.log("addComment");
            console.log("type="+type);
            console.log("host="+$routeParams.host);
            console.log("service="+$routeParams.service);
            console.log("persistent="+persistent);
            console.log("author="+author);
            console.log("comment="+comment);

            var options = {
                name: 'success',
                url: 'addcomments/'+ type + '/' + $routeParams.host + '/' + $routeParams.service
                  + '/' + persistent + '/' + author + '/' + comment,
                queue: 'main'
            };
            async.api($scope, options);

            $scope.callback = function(data, status, headers, config) {
                if(config != null){
                    if(config.url.includes("addcomments")){
                      if(data == 'true'){
                        ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                        $('.modal').modal('hide');
                      }
                      else
                        ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                    }
                }
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
                  if(config != null){
                      if(config.url.includes("deletecomments")){
                          if(data == 'true')
                            ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                          else
                            ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                      }
                  }
              };

            };
          };

          $scope.deleteAllComment = function(type){

              $scope.deleteAll = function(){

                if(type == 'service'){

                  var options = {
                      name: 'success',
                      url: 'deleteallcomment/' + type + '/' +  $routeParams.host + '/' + $routeParams.service,
                      queue: 'main'
                  };

                  async.api($scope, options);

                  $scope.callback = function(data, status, headers, config) {
                      if(config != null){
                          if(config.url.includes("deleteallcomment")){
                              if(data == 'true')
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:3000});
                              else
                                ngToast.create({className: 'alert alert-danger',content:'Fail!',timeout:3000});
                          }
                      }
                  };
                }
            };
        };

        /* <=====================Choon Yean Part=====================> */
        /* Choon Yean configure the functionality for the command of service detail:
            Task 1: Send Service Custom Notification
                passcustom function             =   configure the data to the form field of the pop up modal
                    forcechange fucntion        =   detect the status change of the force check box
                                                    status -> value of ng-checked (true / false)
                    broadcastchange function    =   detect the status change of the broadcast check box
                                                    status -> value of ng-checked (true / false)
                    custom fucntion             =   call the sendcustomnotification api service through the GET method
                                                    comment -> the input of comment value in the modal                 


            Task 2: Schedule Downtime
                passdowntime function           =   configure the modal option
                    schedule function           =   receive the input from the form input field and pass into the controller
                                                    for implementation. Convert the hour and minute parameter into duration by
                                                    multiply the hour and minute in order to convert into minute.
                                                    Get the UTC timezone for start date and end date.
                                                    Call the api service through the GET method


            Task 3: Reschedule Next Check
                passreschedule function         =    schedule the next check time for service
                    host -> target host (String)
                    service -> target service (String)
                    time -> next check of target host which in unix timestamp format
                    schedule function           =       schedule the next check time with the new next check time
                                                        input from the form.
                                                        time -> new input of next check (String)


            Task 4: Acknowledge Problem
                passack function    =   configure the modal option for acknowledge problem.
                    sticky function =   detect the status change of the sticky check box
                                        status -> value of ng-checked (true / false)
                    notify function =   detect the status change of the send notify check box
                                        status -> value of ng-checked (true / false)
                    persist function =   detect the status change of the persist check box
                                        status -> value of ng-checked (true / false)
                    acknowledge function =  receive the input from the form input field and pass into the controller
                                            for implementation.
                                            comment -> (string) value from the input field*/

        $scope.passcustom = function (service, host, author) {

            $scope.resetWan();
            var type = "service";
	   
	    
            $scope.forcechange = function (status){
                if (status == false){
                    $scope.force = status;
                }else if (status == true){
                    $scope.force = status;
                }
            };

            $scope.broadcastchange = function (status){
                if (status == false){
                    $scope.broadcast = status;
                
                }else if (status == true){
                    $scope.broadcast = status;
                }
            };    
        
            $scope.custom = function(comment) {
		
                $scope.comment = comment;
                var options = {
                        name: 'CustomNotification',
                        url: 'sendcustomnotification/' + type + '/' + $scope.custom_host + '/' + $scope.custom_service + '/' + $scope.force + '/'
                        + $scope.broadcast + '/' + $scope.author + '/' + $scope.comment,
                        queue: 'main'
                };
                async.api($scope, options);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("sendcustomnotification")){
                            if(data == 'true'){
                                console.log('success');
                                $('.modal').modal('hide');
                            }
                            else
                                console.log('failure');
                        }
                    }
                }; 
            };
        };

        $scope.passdowntime = function(service, host, author) {
            $scope.resetWan();
            var type = "service";

            $scope.schedule = function (hour, minute, comment, startdate, enddate, triggerby, type) {
                var duration = hour * minute;
                var start_date = new Date(startdate);
                var start_timestamp = (start_date.getTime() / 1000).toString();
                var end_date = new Date(enddate);
                var end_timestamp = (end_date.getTime() / 1000).toString();
                var fixed = true;
            
                if (type = '"Flexible"'){
                    fixed = false;
                }
                else{
                    fixed = true;
                }

                var options = {
                    name: 'servicedowntime',
                    url: 'scheduledowntime/' + type + '/' + $scope.hostName + '/' + $scope.serviceName + '/' + start_timestamp + '/' + end_timestamp + '/'
                        + fixed + '/' + TriggerBy + '/' + duration + '/' + $scope.author + '/' + comment,
                    queue: 'main'
                };

                async.api($scope, options);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("scheduledowntime")){
                            if(data == 'true'){
                                console.log('success');
                                $('.modal').modal('hide');
                            }
                            else
                                console.log('failure');
                        }
                    }
                };
            };
        };

        $scope.passreschedule = function (service, host, next_check) {
            $scope.resetWan();
            $scope.check_time = $filter('date')(next_check * 1000, "yyyy-MM-dd HH:mm:ss");
            $scope.input_check_time = $scope.check_time;
            var type = "service";
        
            $scope.forcecheck = function (status){
                if (status == false){
                    $scope.force_check = status;
                }else if (status == true){
                    $scope.force_check = status;
                }
            };

            $scope.schedule = function(time) {
                var date = new Date(time);
                var next_check = (date.getTime() / 1000).toString();
            
                var options = {
                    name: 'schedulecheck',
                    url: 'schedulecheck/' + type + '/' + $scope.hostName + '/' + $scope.serviceName + '/' + next_check + '/' + $scope.force_check,
                    queue: 'main'
                };

                async.api($scope, options);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("schedulecheck")){
                            if(data == 'true'){
                                console.log('success');
                                $('.modal').modal('hide');
                            }
                            else
                                console.log('failure');
                        }
                    }
                };
            };
        };

        $scope.passack = function (service, host, next_check){
            $scope.resetWan();
            var type = 'service';

            $scope.sticky = function (status){
                if (status == false){
                    $scope.stickyack = status;
                }else if (status == true){
                    $scope.stickyack = status;
                }
            };

            $scope.notify = function (status){
                if (status == false){
                    $scope.sendnotify = status;
                
                }else if (status == true){
                    $scope.sendnotify = status;
                }
            };

            $scope.persist = function (status){
                if (status == false){
                    $scope.persistent = status;
                
                }else if (status == true){
                    $scope.persistent = status;
                }
            };

            $scope.acknowledge = function (comment) {
                $scope.comment = comment;
                var options = {
                    name: 'acknowledge',
                    url: 'acknowledgeproblem/' + type + '/' + $scope.ack_host + '/' + $scope.ack_service + '/'
                    + $scope.stickyack + '/' + $scope.sendnotify + '/' + $scope.persistent + '/'
                    + $scope.author + '/' + $scope.comment,
                    queue: 'main'
                };

                async.api($scope, options);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("acknowledgeproblem")){
                            if(data == 'true'){
                                console.log('success');
                                $('.modal').modal('hide');
                            }
                            else
                                console.log('failure');
                        }
                    }
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


