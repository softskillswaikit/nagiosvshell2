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

.controller('HostDetailsCtrl', ['$scope', '$routeParams', 'async', '$timeout', '$interval', '$route', 'ngToast', '$rootScope',
    function($scope, $routeParams, async, $timeout, $interval, $route, ngToast, $rootScope) {

        $scope.init = function() {

            var options = {
                name: 'host',
                url: 'hoststatus/' + $routeParams.host,
                queue: 'main'
            };

            async.api($scope, options);
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

            $scope.hostName = $routeParams.host;
            $scope.service = ' ';
            $scope.persistent = true;
            $scope.comment = '';

            $scope.hostComment = '';
            $scope.hostTriggeredBy = "N/A";
            $scope.hostStartDateTime = '';
            $scope.hostEndDateTime = '';
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

            if($scope.addHostComment)
                $scope.addHostComment.$setPristine();

            $scope.callback = function(data, status, headers, config) {
                if(config != null){
                    if(config.url.includes("status")){
                        $scope.hostAuthor = data.username;
                        $scope.author = data.username;
                    }
                }
            };
        };

        $scope.toggle = function(action, is_enabled){

            if(is_enabled == 0)
                var todo = 'true';
            else if(is_enabled == 1)
                var todo = 'false';

            $scope.toggleAction = function(){
                if(action == 'active_checks'){
                    var options = {
                        name: 'hostcheck',
                        url: 'hostcheck/' + todo + '/' + $routeParams.host,
                        queue: 'main'
                    };

                    async.api($scope, options);
                }
                else if(action == 'passive_checks'){
                    var options = {
                        name: 'passivehostcheck',
                        url: 'passivehostcheck/' + todo + '/' + $routeParams.host,
                        queue: 'main'
                    };
                    async.api($scope, options);
                }
                else if(action == 'obsess'){
                    var options = {
                        name: 'obsessoverhost',
                        url: 'obsessoverhost/' + todo + '/' + $routeParams.host,
                        queue: 'main'
                    };
                    async.api($scope, options);
                }
                else if(action == 'notifications'){
                    var options = {
                        name: 'hostnotification',
                        url: 'hostnotification/' + todo + '/' + $routeParams.host,
                        queue: 'main'
                    };
                    async.api($scope, options);
                }
                else if(action == 'flap_detection'){
                    var options = {
                        name: 'hostflapdetection',
                        url: 'hostflapdetection/' + todo + '/' + $routeParams.host,
                        queue: 'main'
                    };
                    async.api($scope, options);
                }

                $scope.callback = function(data, status, headers, config) {
                    if(config != null){
                        if(config.url.includes("check") || config.url.includes("obsessoverhost") || config.url.includes("hostflapdetection") || config.url.includes("hostnotification") ){
                            if(Object.getOwnPropertyNames(data).length == 0){
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:1500});
                                $('.modal').modal('hide');
                            }
                            else if(data == 1)
                                ngToast.create({className: 'alert alert-danger',content:'Fail! Wrong Data!',timeout:1500});
                            else if(data == 2)
                                ngToast.create({className: 'alert alert-danger',content:'Fail! Command Error!',timeout:1500});
                        }
                    }
                };
            };
        };

        $scope.addComment = function(type){
            $scope.reset();

            $scope.add = function(persistent, author, comment){

                var options = {
                    name: 'addcomments',
                    url: 'addcomments/'+ type + '/' + $routeParams.host + '/' + ' '
                      + '/' + persistent + '/' + author + '/' + comment
                };
                async.api($scope, options);

                $scope.callback = function(data, status, headers, config) {
                    if(config != null){
                        if(config.url.includes("addcomments")){
                            if(Object.getOwnPropertyNames(data).length == 0){
                              ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:1500});
                              $('.modal').modal('hide');
                            }
                            else if(data == 1)
                              ngToast.create({className: 'alert alert-danger',content:'Fail! Wrong Data!',timeout:1500});
                            else if(data == 2)
                              ngToast.create({className: 'alert alert-danger',content:'Fail! Command Error!',timeout:1500});
                        }
                    }
                };
              };
        };

        $scope.deleteComment = function(id, type){

          $scope.delete = function(){

            var options = {
                name: 'deletecomments',
                url: 'deletecomments/' + id + '/' + type
            };

            async.api($scope, options);

            $scope.callback = function(data, status, headers, config) {
                if(config != null){
                    if(config.url.includes("deletecomments")){
                        if(Object.getOwnPropertyNames(data).length == 0)
                          ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:1500});
                          else if(data == 1)
                            ngToast.create({className: 'alert alert-danger',content:'Fail! Wrong Data!',timeout:1500});
                          else if(data == 2)
                            ngToast.create({className: 'alert alert-danger',content:'Fail! Command Error!',timeout:1500});
                    }
                }
            };
          }
        };

        $scope.deleteAllComment = function(type){

            $scope.deleteAll = function(){

                if(type == 'host'){

                    var options = {
                        name: 'deleteallcomment',
                        url: 'deleteallcomment/' + type + '/' +  $routeParams.host + '/' + ' ' + '/'
                    };

                    async.api($scope, options);

                    $scope.callback = function(data, status, headers, config) {
                        if(config != null){
                            if(config.url.includes("deleteallcomment")){
                                if(Object.getOwnPropertyNames(data).length == 0)
                                    ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:1500});
                                else if(data == 1)
                                    ngToast.create({className: 'alert alert-danger',content:'Fail! Wrong Data!',timeout:1500});
                                else if(data == 2)
                                    ngToast.create({className: 'alert alert-danger',content:'Fail! Command Error!',timeout:1500});
                            }
                        }
                    };
                }
            }
        };

        $scope.notification_check = function (type) {

        
            var options = {
                name: 'ToggleHostServiceNotification',
                url: 'hostservicenotification/' + type + '/' + $routeParams.host
            };

            async.api($scope, options);

            $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("hostservicenotification")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
        
            $scope.reset();
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
                $scope.hostComment = comment;
                var options = {
                        name: 'CustomNotification',
                        url: 'sendcustomnotification/' + type + '/' + $routeParams.host + '/' + service + '/' + $scope.force + '/'
                        + $scope.broadcast + '/' + $scope.hostAuthor + '/' + $scope.hostComment,
                };
                async.api($scope, options);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("sendcustomnotification")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
            $scope.reset();
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
                        + fixed_type + '/' + triggerby + '/' + duration + '/' + $scope.hostAuthor + '/' + comment
                };

                async.api($scope, options);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("scheduledowntime")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                        + fixed_type + '/' + triggerby + '/' + duration + '/' + $scope.hostAuthor + '/' + comment
                };

                async.api($scope, options);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("scheduledowntime")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
            $scope.reset();
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
                            + '/' + $scope.force_check
                };

                async.api($scope, options);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("schedulecheck")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                url: 'hostservicecheck/' + type + '/' + $routeParams.host
            };

            async.api($scope, options);

            $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("hostservicecheck")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
    
            $scope.reset();
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
                    + $scope.author + '/' + $scope.comment
                };

                async.api($scope, options);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("acknowledgeproblem")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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

            //reset modal
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

            $scope.serviceTriggeredBy = "N/A";
            $scope.serviceStartDateTime = "";
            $scope.serviceEndDateTime = "";
            $scope.serviceType = "Fixed";
            $scope.serviceDurationHour = 0;
            $scope.serviceDurationMin = 0;
            $scope.force = false;
            $scope.broadcast = false;
            $scope.force_check = true;
            $scope.stickyack = true;
            $scope.sendnotify = true;
            $scope.persistent = false;

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

        $scope.toggle = function(action, is_enabled){

          if(is_enabled == 0)
            var todo = 'true';
          else if(is_enabled == 1)
            var todo = 'false';
            $scope.toggleAction = function(){
              if(action == 'active_checks'){
                var options = {
                    name: 'servicecheck',
                    url: 'servicecheck/' + todo + '/' + $routeParams.host + '/' + $routeParams.service
                };
                async.api($scope, options);
              }
              else if(action == 'passive_checks'){
                var options = {
                    name: 'passiveservicecheck',
                    url: 'passiveservicecheck/' + todo + '/' + $routeParams.host + '/' + $routeParams.service
                };
                async.api($scope, options);
              }
              else if(action == 'obsess'){
                var options = {
                    name: 'obsessoverservice',
                    url: 'obsessoverservice/' + todo + '/' + $routeParams.host + '/' + $routeParams.service
                };
                async.api($scope, options);
              }
              else if(action == 'notifications'){
                var options = {
                    name: 'servicenotification',
                    url: 'servicenotification/' + todo + '/' + $routeParams.host + '/' + $routeParams.service
                };
                async.api($scope, options);
              }
              else if(action == 'flap_detection'){
                var options = {
                    name: 'serviceflapdetection',
                    url: 'serviceflapdetection/' + todo + '/' + $routeParams.host + '/' + $routeParams.service
                };
                async.api($scope, options);
              }

              $scope.callback = function(data, status, headers, config) {
                  if(config != null){
                      if(config.url.includes("check") || config.url.includes("obsessoverservice") || config.url.includes("serviceflapdetection") || config.url.includes("servicenotification") ){
                          if(Object.getOwnPropertyNames(data).length == 0){
                            ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:1500});
                            $('.modal').modal('hide');
                          }
                          else if(data == 1)
                            ngToast.create({className: 'alert alert-danger',content:'Fail! Wrong Data!',timeout:1500});
                          else if(data == 2)
                            ngToast.create({className: 'alert alert-danger',content:'Fail! Command Error!',timeout:1500});
                      }
                  }
              };
            };
        };

        $scope.addComment = function(type){

          $scope.reset();

          $scope.add = function(persistent, author, comment){

            var options = {
                name: 'addcomments',
                url: 'addcomments/'+ type + '/' + $routeParams.host + '/' + $routeParams.service
                  + '/' + persistent + '/' + author + '/' + comment
            };
            async.api($scope, options);

            $scope.callback = function(data, status, headers, config) {
                if(config != null){
                    if(config.url.includes("addcomments")){
                      if(Object.getOwnPropertyNames(data).length == 0){
                        ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:1500});
                        $('.modal').modal('hide');
                      }
                      else if(data == 1)
                        ngToast.create({className: 'alert alert-danger',content:'Fail! Wrong Data!',timeout:1500});
                      else if(data == 2)
                        ngToast.create({className: 'alert alert-danger',content:'Fail! Command Error!',timeout:1500});
                    }
                }
            };
          };
        };

        $scope.deleteComment = function(id, type){
            $scope.delete = function(){

                var options = {
                    name: 'deletecomments',
                    url: 'deletecomments/' + id + '/' + type
                };

                async.api($scope, options);

                $scope.callback = function(data, status, headers, config) {
                    if(config != null){
                        if(config.url.includes("deletecomments")){
                            if(Object.getOwnPropertyNames(data).length == 0)
                                ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:1500});
                            else if(data == 1)
                                ngToast.create({className: 'alert alert-danger',content:'Fail! Wrong Data!',timeout:1500});
                            else if(data == 2)
                                ngToast.create({className: 'alert alert-danger',content:'Fail! Command Error!',timeout:1500});
                        }
                    }
                };
            };
        };

        $scope.deleteAllComment = function(type){

            $scope.deleteAll = function(){

                if(type == 'service'){

                    var options = {
                        name: 'deleteallcomment',
                        url: 'deleteallcomment/' + type + '/' +  $routeParams.host + '/' + $routeParams.service
                    };

                async.api($scope, options);

                $scope.callback = function(data, status, headers, config) {
                        if(config != null){
                            if(config.url.includes("deleteallcomment")){
                                if(Object.getOwnPropertyNames(data).length == 0)
                                    ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:1500});
                                else if(data == 1)
                                    ngToast.create({className: 'alert alert-danger',content:'Fail! Wrong Data!',timeout:1500});
                                else if(data == 2)
                                    ngToast.create({className: 'alert alert-danger',content:'Fail! Command Error!',timeout:1500});
                            }
                        }
                    };
                }
            };
        };

        $scope.passcustom = function () {

            $scope.reset();
            var type = "service";
       
        
            $scope.forcechange = function (status){
                if (status == false){
                    $scope.force = 'false';
                }else if (status == true){
                    $scope.force = 'true';
                }
            };

            $scope.broadcastchange = function (status){
                if (status == false){
                    $scope.broadcast = 'false';
                
                }else if (status == true){
                    $scope.broadcast = 'true';
                }
            };    
        
            $scope.custom = function(comment) {
        
                $scope.comment = comment;
                var options = {
                        name: 'CustomNotification',
                        url: 'sendcustomnotification/' + type + '/' + $routeParams.host + '/' + $routeParams.service + '/' + $scope.force + '/'
                        + $scope.broadcast + '/' + $scope.author + '/' + $scope.comment,
                };
                async.api($scope, options);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("sendcustomnotification")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
            $scope.reset();

            $scope.schedule = function (hour, minute, comment, startdate, enddate, triggerby, fixed) {
                var duration = 0;
                var start_date = new Date(startdate);
                var start_timestamp = (start_date.getTime() / 1000).toString();
                var end_date = new Date(enddate);
                var end_timestamp = (end_date.getTime() / 1000).toString();
                var fixed_type = "";
            
                if (fixed == 'Flexible'){
                    fixed_type = 'false';
                    duration = (hour * 60) + minute;
                }
                else if (fixed == 'Fixed'){
                    fixed_type = 'true';
                    duration = (end_date.getHours() * 60 + end_date.getMinutes()) - (start_date.getHours() * 60 + start_date.getMinutes());
                }

                var options = {
                    name: 'ServiceDownTime',
                    url: 'scheduledowntime/' + type + '/' + $routeParams.host + '/' + $routeParams.service + '/' + start_timestamp + '/' + end_timestamp + '/'
                        + fixed_type + '/' + triggerby + '/' + duration + '/' + $scope.author + '/' + comment
                };

                async.api($scope, options);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                       if(config.url.includes("scheduledowntime")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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

        $scope.schedule_check = function (type, next_check) {
            $scope.reset();
            $scope.check_time = $filter('date')(next_check * 1000, "yyyy-MM-dd HH:mm:ss");
            $scope.input_check_time = $scope.check_time;
        
            $scope.forcecheck = function (status){
                if (status == false){
                    $scope.force_check = 'false';
                }else if (status == true){
                    $scope.force_check = 'true';
                }
            };

            $scope.schedule = function(time) {
                var date = new Date(time);
                var next_check = date.getTime() / 1000
                var timestamp = next_check.toString();

                var options = {
                    name: 'schedulecheck',
                    url: 'schedulecheck/' + type + '/' + $routeParams.host + '/' + $routeParams.service + '/' + timestamp + '/' + $scope.force_check
                };

                async.api($scope, options);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("schedulecheck")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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

        $scope.passack = function (){
            $scope.reset();
            var type = 'service';

            $scope.sticky = function (status){
                if (status == false){
                    $scope.stickyack = 'false';
                }else if (status == true){
                    $scope.stickyack = 'true';
                }
            };

            $scope.notify = function (status){
                if (status == false){
                    $scope.sendnotify = 'false';
                
                }else if (status == true){
                    $scope.sendnotify = 'true';
                }
            };

            $scope.persist = function (status){
                if (status == false){
                    $scope.persistent = 'false';
                
                }else if (status == true){
                    $scope.persistent = 'true';
                }
            };

            $scope.acknowledge = function (comment) {
                $scope.comment = comment;
                var options = {
                    name: 'acknowledge',
                    url: 'acknowledgeproblem/' + type + '/' + $routeParams.host + '/' + $routeParams.service + '/'
                    + $scope.stickyack + '/' + $scope.sendnotify + '/' + $scope.persistent + '/'
                    + $scope.author + '/' + $scope.comment
                };

                async.api($scope, options);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("acknowledgeproblem")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    ngToast.create({className: 'alert alert-danger',content:'File is more than 1 GB.',timeout:1500});
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
                ngToast.create({className: 'alert alert-danger',content:'No logs are selected.',timeout:1500});
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

.controller('AvailabilityCtrl', ['$scope', '$routeParams', '$filter', 'async', '$window', '$rootScope', 'dataService', 'ngToast',
    function($scope, $routeParams, $filter, async, $window, $rootScope, dataService, ngToast) {

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
        var today = $filter('date')(Date.now(), 'yyyy-MM-dd');
        var date = new Date();
        var firstDayOfMonth = $filter('date')((new Date(date.getFullYear(), date.getMonth(), 1)), 'yyyy-MM-dd');

        $scope.reportType = 5;
        $scope.reportHost = 'ALL';
        //$scope.reportService = 'ALL';
        $scope.startDate =  firstDayOfMonth;
        $scope.endDate =  today;
        $scope.reportPeriod = 'LAST 7 DAYS';
        $scope.assumeInitialStates = 'true';
        $scope.assumeStateRetention = 'true';
        $scope.assumeDowntimeStates = 'true';
        $scope.includeSoftStates = 'false';
        $scope.firstAssumedHostState = 'UNDETERMINED';
        $scope.firstAssumedServiceState = 'UNDETERMINED';
        $scope.backtrackedArchives = 4;

        //if this page is called from other other view
        if(dataService.getInfo() != null){

          $rootScope.param = dataService.getInfo();
          $scope.reportType = $rootScope.param.type;
          $scope.reportHost = $rootScope.param.host;
          $scope.reportService = $rootScope.param.service;
          $scope.reportPeriod = $rootScope.param.period;
          $scope.startDate = $rootScope.param.start;
          $scope.endDate = $rootScope.param.end;
          $scope.assumeStateRetention = $rootScope.param.assumeStateRetention;
          if($rootScope.param.assumeInitialStates != null)
            $scope.assumeInitialStates = $rootScope.param.assumeInitialStates;
          if($rootScope.param.assumeDowntimeStates != null)
            $scope.assumeDowntimeStates = $rootScope.param.assumeDowntimeStates;
          if($rootScope.param.includeSoftStates != null)
            $scope.includeSoftStates = $rootScope.param.includeSoftStates;
          if($rootScope.param.backtrackedArchives != null)
            $scope.backtrackedArchives = $rootScope.param.backtrackedArchives;

          dataService.setInfo(null);

          $scope.createReport();
        }
      };

      $scope.createReport = function(){

        var startUnix = parseInt((new Date($scope.startDate).getTime() / 1000).toFixed(0));
        var endUnix = parseInt((new Date($scope.endDate).getTime() / 1000).toFixed(0));

        if($scope.reportPeriod != 'CUSTOM'){
          $scope.startDate = $scope.endDate;
          $scope.endDate = ' ';
            startUnix = endUnix;
            endUnix = ' ';
         }

         if($scope.reportType == 1 || $scope.reportType == 5)
            $scope.reportService = 'ALL';
          if($scope.reportType == 6)
             $scope.reportHost = 'ALL';
          if(($scope.reportType == 2 || $scope.reportType == 3 || $scope.reportType == 4) && $scope.reportService == 'ALL')
            $scope.reportHost = 'ALL';

        //get component name
        var options = {
            name: 'availability',
            url: 'availability' + '/' + $scope.reportType + '/' + $scope.reportPeriod + '/' + startUnix + '/' + endUnix + '/'
                      + $scope.reportHost + '/' + $scope.reportService + '/' + $scope.assumeInitialStates + '/' + $scope.assumeStateRetention + '/'
                      + $scope.assumeDowntimeStates + '/' + $scope.includeSoftStates + '/'
                      + $scope.firstAssumedHostState + '/' + $scope.backtrackedArchives + '/' + $scope.firstAssumedServiceState
        };

        async.api($scope, options);

        $scope.callback = function(data, status, headers, config) {
          if(config != null){
            if(config.url.includes("availability")){

              $rootScope.data = data;
              $rootScope.param = {
                "type" : $scope.reportType,
                "host" : $scope.reportHost,
                "service" : $scope.reportService,
                "period" : $scope.reportPeriod,
                "start" : new Date($scope.startDate),
                "end" : new Date($scope.endDate),
                "assumeInitialStates" : $scope.assumeInitialStates,
                "assumeStateRetention" : $scope.assumeStateRetention,
                "assumeDowntimeStates" : $scope.assumeDowntimeStates,
                "includeSoftStates" : $scope.includeSoftStates,
                "backtrackedArchives" : $scope.backtrackedArchives
              };

              if(data == 1)
                ngToast.create({className: 'alert alert-danger',content:'Fail! Wrong Data!',timeout:1500});
              else if(data == 2)
                ngToast.create({className: 'alert alert-danger',content:'Fail! Command Error!',timeout:1500});
              else
                $window.location.href="#/report/availability/report";
            }
          }
        };
      };

      $scope.showReport = function(){

      };

      $scope.viewTrends = function(type){
         if(type == 'host')
            $rootScope.param.type = 1;
         dataService.setInfo($rootScope.param);
         $window.location.href="#/report/trends";
      };

      $scope.viewAvailability = function(type){
        if(type == 'host')
           $rootScope.param.type = 1;
          dataService.setInfo($rootScope.param);
          $window.location.href="#/report/availability";
      };

      $scope.viewAlertHistogram = function(type){
          dataService.setInfo($rootScope.param);
          $window.location.href="#/report/alerthistogram";
      };
    }
])

.controller('TrendsCtrl', ['$scope', '$routeParams', '$filter', 'async', '$timeout', '$window','$rootScope', 'ngToast','dataService',
    function($scope, $routeParams, $filter, async, $timeout, $window, $rootScope, ngToast, dataService) {

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

          var today = $filter('date')(Date.now(), 'yyyy-MM-dd');
          var date = new Date();
          var firstDayOfMonth = $filter('date')((new Date(date.getFullYear(), date.getMonth(), 1)), 'yyyy-MM-dd');

          $scope.reportType = 1;
    			$timeout(function(){$scope.reportHost = $scope.name.host[0];}, 1200);
          $scope.startDate =  firstDayOfMonth;
          $scope.endDate =  today;
    			$scope.reportPeriod = 'LAST 7 DAYS';
    			$scope.assumeInitialStates = 'true';
    			$scope.assumeStateRetention = 'true';
    			$scope.assumeDowntimeStates = 'true';
    			$scope.includeSoftStates = 'false';
    			$scope.firstAssumedHostState = 'UNDETERMINED';
    			$scope.firstAssumedServiceState = 'UNDETERMINED';
    			$scope.backtrackedArchives = 4;

          //if this page is called from other other view
          if(dataService.getInfo() != null){

            $rootScope.param = dataService.getInfo();
            $scope.reportType = $rootScope.param.type;
            $scope.reportHost = $rootScope.param.host;
            $scope.reportService = $rootScope.param.service;
            $scope.reportPeriod = $rootScope.param.period;
            $scope.startDate = $rootScope.param.start;
            if($rootScope.param.period != 'CUSTOM')
              $scope.endDate = $rootScope.param.start;
            else
              $scope.endDate = $rootScope.param.end;

            $scope.assumeStateRetention = $rootScope.param.assumeStateRetention;

            if($rootScope.param.assumeInitialStates != null)
              $scope.assumeInitialStates = $rootScope.param.assumeInitialStates;
            if($rootScope.param.assumeDowntimeStates != null)
              $scope.assumeDowntimeStates = $rootScope.param.assumeDowntimeStates;
            if($rootScope.param.includeSoftStates != null)
              $scope.includeSoftStates = $rootScope.param.includeSoftStates;
            if($rootScope.param.backtrackedArchives != null)
              $scope.backtrackedArchives = $rootScope.param.backtrackedArchives;

            dataService.setInfo(null);

            $scope.createReport();
          }
        };


        $scope.createReport = function(){

          var startUnix = parseInt((new Date($scope.startDate).getTime() / 1000).toFixed(0));
          var endUnix = parseInt((new Date($scope.endDate).getTime() / 1000).toFixed(0));
          var service = $scope.reportService;
          var firstAssumedState = $scope.firstAssumedHostState;

          if($scope.reportType == 1){
             service = 'ALL';
             firstAssumedState = $scope.firstAssumedHostState;
          }
          if($scope.reportType != 1){
             firstAssumedState = $scope.firstAssumedServiceState;
          }

          if($scope.reportPeriod != 'CUSTOM'){
            $scope.startDate = $scope.endDate;
            $scope.endDate = ' ';
            startUnix = endUnix;
             endUnix = ' ';
          }

          //get component name
          var options = {
              name: 'trend',
              url: 'trend' + '/' + $scope.reportType + '/' + $scope.reportPeriod + '/' + startUnix + '/'
                       + endUnix + '/' + $scope.reportHost + '/' + service + '/' + $scope.assumeInitialStates + '/'
                       + $scope.assumeStateRetention + '/' + $scope.assumeDowntimeStates + '/' + $scope.includeSoftStates + '/'
                       + $scope.backtrackedArchives + '/' + firstAssumedState
          };

          async.api($scope, options);

          $scope.callback = function(data, status, headers, config) {
            if(config != null){
              if(config.url.includes("trend")){

                $rootScope.data = data;
                $rootScope.param = {
                  "type" : $scope.reportType,
                  "host" : $scope.reportHost,
                  "service" : service,
                  "period" : $scope.reportPeriod,
                  "start" : new Date($scope.startDate),
                  "end" : new Date($scope.endDate),
                  "assumeInitialStates" : $scope.assumeInitialStates,
                  "assumeStateRetention" : $scope.assumeStateRetention,
                  "assumeDowntimeStates" : $scope.assumeDowntimeStates,
                  "includeSoftStates" : $scope.includeSoftStates,
                  "backtrackedArchives" : $scope.backtrackedArchives
                };
                if(data == 1)
                  ngToast.create({className: 'alert alert-danger',content:'Fail! Wrong Data!',timeout:1500});
                else if(data == 2)
                  ngToast.create({className: 'alert alert-danger',content:'Fail! Command Error!',timeout:1500});
                else
                  $window.location.href="#/report/trends/report";
              }
            }
          };
        };

        $scope.showReport = function(){

          var data = [];

          // sort by date
          var sorted_date = $rootScope.data[0]

          $rootScope.data[0].forEach(function(d){
            var date = new Date(d.start_time * 1000);
            var datay = $filter('date')(date, 'EEE MMM d H:mm:ss yyyy');
            var datax = d.state;
            data.push({"label" : datay,"value": datax});
          })

          var start = $rootScope.data[0][0].start_time ;
          var start_time = $filter('date')(start * 1000, 'EEE MMM d H:mm:ss yyyy');
          var end = $rootScope.data[0][$rootScope.data[0].length - 1].end_time ;
          var end_time = $filter('date')(end * 1000, 'EEE MMM d H:mm:ss yyyy');

          $scope.hostdata = {
            "chart": {
                "caption": "State History For Host " + $rootScope.param.host,
                "captionfontsize": "16",
                "subCaption": "From " + start_time + " To " + end_time,
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
                            {"id": "line","type": "line","color": "#1a1a1a","x": "$canvasstartx - 5","y": "$canvasstarty","tox": "$canvasstartx - 5","toy": "$canvasendy","thickness": "1"},
                            {"id": "pending-label-bg","type": "rectangle","fillcolor": "#858585","x": "$canvasstartx - 85","tox": "$canvasstartx - 15","y": "$canvasendy - 10","toy": "$canvasendy + 10","radius": "3"},
                            {"id": "pending-dot","type": "circle","radius": "5","x": "$canvasstartx - 5","y": "$canvasendy","color": "#858585"},
                            {"id": "pending-label","type": "text","fillcolor": "#ffffff","text": "Pending","x": "$canvasstartx - 50","y": "$canvasendy","fontsize": "13"},
                            {"id": "unreachable-label-bg","type": "rectangle","fillcolor": "#ee8425","x": "$canvasstartx - 102","tox": "$canvasstartx - 15","y": "$canvasendy - 69","toy": "$canvasendy - 49","radius": "3"},
                            {"id": "unreachable-dot","type": "circle","radius": "5","x": "$canvasstartx - 5","y": "$canvasendy - 59","color": "#ee8425"},
                            {"id": "unreachable-label","type": "text","fillcolor": "#ffffff","text": "Unreachable","x": "$canvasstartx - 59","y": "$canvasendy - 59","fontsize": "13"},
                            {"id": "down-label-bg","type": "rectangle","fillcolor": "#d24555","x": "$canvasstartx - 85","tox": "$canvasstartx - 15","y": "$canvasendy - 127","toy": "$canvasendy - 107","radius": "3"},
                            {"id": "down-dot","type": "circle","radius": "5","x": "$canvasstartx - 5","y": "$canvasendy - 117","color": "#d24555"},
                            {"id": "down-label","type": "text","fillcolor": "#ffffff","text": "Down","x": "$canvasstartx - 50","y": "$canvasendy - 117","fontsize": "13"},
                            {"id": "up-label-bg","type": "rectangle","fillcolor": "#6cb22f","x": "$canvasstartx - 88","tox": "$canvasstartx - 15","y": "$canvasendy - 185","toy": "$canvasendy - 165","radius": "3"},
                            {"id": "up-dot","type": "circle","radius": "5","x": "$canvasstartx - 5","y": "$canvasendy - 175","color": "#6cb22f"},
                            {"id": "up-label","type": "text","fillcolor": "#ffffff","text": "Up","x": "$canvasstartx - 52","y": "$canvasendy - 175","fontsize": "13"}
                        ]
                    }
                ]
            },
            "dataset": [
                {
                    "seriesname": "Host State Trends",
                    "data": data
                }
            ]
          }

          $scope.servicedata = {
            "chart": {
                "caption": "State History For Service " + $rootScope.param.service + " On Host " + $rootScope.param.host,
                "captionfontsize": "16",
                "subCaption": "From " + start_time + " To " + end_time,
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
                            {"id": "line","type": "line","color": "#1a1a1a","x": "$canvasstartx - 5","y": "$canvasstarty","tox": "$canvasstartx - 5","toy": "$canvasendy","thickness": "1"},
                            {"id": "pending-label-bg","type": "rectangle","fillcolor": "#858585","x": "$canvasstartx - 88","tox": "$canvasstartx - 15","y": "$canvasendy - 10","toy": "$canvasendy + 10","radius": "3"},
                            {"id": "pending-dot","type": "circle","radius": "5","x": "$canvasstartx - 5","y": "$canvasendy","color": "#858585"},
                            {"id": "pending-label","type": "text","fillcolor": "#ffffff","text": "Pending","x": "$canvasstartx - 50","y": "$canvasendy","fontsize": "13"},
                            {"id": "critical-label-bg","type": "rectangle","fillcolor": "#d24555","x": "$canvasstartx - 88","tox": "$canvasstartx - 15","y": "$canvasendy - 69","toy": "$canvasendy - 49","radius": "3"},
                            {"id": "critical-dot","type": "circle","radius": "5","x": "$canvasstartx - 5","y": "$canvasendy - 59","color": "#d24555"},
                            {"id": "critical-label","type": "text","fillcolor": "#ffffff","text": "Critical","x": "$canvasstartx - 52","y": "$canvasendy - 59","fontsize": "13"},
                            {"id": "unknown-label-bg","type": "rectangle","fillcolor": "#ee8425","x": "$canvasstartx - 88","tox": "$canvasstartx - 15","y": "$canvasendy - 127","toy": "$canvasendy - 107","radius": "3"},
                            {"id": "unknown-dot","type": "circle","radius": "5","x": "$canvasstartx - 5","y": "$canvasendy - 117","color": "#ee8425"},
                            {"id": "unknown-label","type": "text","fillcolor": "#ffffff","text": "Unknown","x": "$canvasstartx - 50","y": "$canvasendy - 117","fontsize": "13"},
                            {"id": "warning-label-bg","type": "rectangle","fillcolor": "#dba102","x": "$canvasstartx - 88","tox": "$canvasstartx - 15","y": "$canvasendy - 185","toy": "$canvasendy - 165","radius": "3"},
                            {"id": "warning-dot","type": "circle","radius": "5","x": "$canvasstartx - 5","y": "$canvasendy - 175","color": "#dba102"},
                            {"id": "warning-label","type": "text","fillcolor": "#ffffff","text": "Warning","x": "$canvasstartx - 52","y": "$canvasendy - 175","fontsize": "13"},
                            {"id": "ok-label-bg","type": "rectangle","fillcolor": "#6cb22f","x": "$canvasstartx - 88","tox": "$canvasstartx - 15","y": "$canvasendy - 243","toy": "$canvasendy - 223","radius": "3"},
                            {"id": "ok-dot","type": "circle","radius": "5","x": "$canvasstartx - 5","y": "$canvasendy - 233","color": "#6cb22f"},
                            {"id": "ok-label","type": "text","fillcolor": "#ffffff","text": "Ok","x": "$canvasstartx - 52","y": "$canvasendy - 233","fontsize": "13"}
                        ]
                    }
                ]
            },
            "dataset": [
                {
                    "seriesname": "Service State Trends",
                    "data": data
                }
            ]
          }
        };

        $scope.viewTrends = function(type){
           if(type == 'host')
              $rootScope.param.type = 1;
           dataService.setInfo($rootScope.param);
           $window.location.href="#/report/trends";
        };

        $scope.viewAvailability = function(type){
            dataService.setInfo($rootScope.param);
            $window.location.href="#/report/availability";
        };

        $scope.viewAlertHistogram = function(type){
            dataService.setInfo($rootScope.param);
            $window.location.href="#/report/alerthistogram";
        };

    }
])

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
        
        };

        $scope.create = function() {
            console.log($scope.CustomReportType);
            console.log($scope.HostLimit);
            console.log($scope.AlertTypes);

            if ($scope.CustomReportType == 'Most Recent Alerts') {
                $scope.CustomReportType = 6;
            }

            if ($scope.reportPeriod == 'Today'){
                $scope.reportPeriod = 'TODAY';
                var utcdate = new Date();
                var timestamp = (utcdate.getTime()) / 1000;
                var date = timestamp.toString();
                date = date.substring(0, 10);
                $scope.start_date = date;
                $scope.end_date = null;
            }

            if ($scope.HostLimit == '**ALL HOSTS**'){
                $scope.HostLimit = 'ALL';
                var service = 'ALL';
            }

            if ($scope.AlertTypes == 'Host and Service Alerts'){
                $scope.AlertTypes = 'ALL';
            }

            if ($scope.StateTypes == 'Hard and Soft States'){
                $scope.StateTypes = 'ALL';
            }

            if (($scope.HostStates == 'All Host States') && ($scope.ServiceStates == 'All Service States')){
                var state = 'ALL';
            }

            var options = {
                name: 'summary',
                url: 'alertsummary/' + $scope.CustomReportType + '/' + $scope.reportPeriod + '/' + $scope.start_date
                        + '/' + $scope.end_date + '/' + $scope.HostLimit + '/' + service + '/' + $scope.AlertTypes + '/'
                        + $scope.StateTypes + '/' + state,
                queue: 'main'
            };

            async.api($scope, options);
        };

        $scope.reset();
        
    }
])

.controller('AlertHistogramCtrl', ['$scope', '$routeParams', '$filter', 'async', '$timeout', '$window', '$rootScope', 'dataService',
    function($scope, $routeParams, $filter, async, $timeout, $window, $rootScope, dataService) {

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

        //reset form
       $scope.reset = function(){

          var today = $filter('date')(Date.now(), 'yyyy-MM-dd');
          var date = new Date();
          var firstDayOfMonth = $filter('date')((new Date(date.getFullYear(), date.getMonth(), 1)), 'yyyy-MM-dd');

          $scope.reportType = '1';
          $timeout(function(){$scope.reportHost = $scope.name.host[0]}, 1200);
          $scope.startDate =  firstDayOfMonth;
          $scope.endDate =  today;
     			$scope.reportPeriod = 'LAST 7 DAYS';
     			$scope.statisticsBreakdown = '2';
     			$scope.eventsToGraph = 'ALL';
     			$scope.stateTypesToGraph = 'ALL';
     			$scope.assumeStateRetention = 'true';
     			$scope.initialStatesLogged = 'false';
     			$scope.ignoreRepeatedStates = 'false';

          if(dataService.getInfo() != null){

            $rootScope.param = dataService.getInfo();
            $scope.reportType = $rootScope.param.type;
            $scope.reportHost = $rootScope.param.host;
            $scope.reportService = $rootScope.param.service;
            $scope.reportPeriod = $rootScope.param.period;
            $scope.startDate = $rootScope.param.start;
            $scope.endDate = $rootScope.param.end;

            dataService.setInfo(null);

            $scope.createReport();
          }
       };

       $scope.createReport = function(){

         var startUnix = parseInt((new Date($scope.startDate).getTime() / 1000).toFixed(0));
         var endUnix = parseInt((new Date($scope.endDate).getTime() / 1000).toFixed(0));
         var service = $scope.reportService;

         if($scope.reportType == 1){
            service = 'ALL';
          }

          if($scope.reportPeriod != 'CUSTOM'){
            $scope.startDate = $scope.endDate;
            $scope.endDate = ' ';
            startUnix = endUnix;
            endUnix = ' ';
          }

         //get component name
         var options = {
             name: 'alerthistogram',
             url: 'alerthistogram' + '/' + $scope.reportType + '/' + $scope.reportHost + '/' + service + '/'
                      + $scope.reportPeriod + '/' + startUnix + '/' + endUnix + '/' + $scope.statisticsBreakdown + '/'
                      + $scope.eventsToGraph + '/' + $scope.stateTypesToGraph + '/' + $scope.assumeStateRetention + '/'
                      + $scope.initialStatesLogged + '/' + $scope.ignoreRepeatedStates
         };

         async.api($scope, options);

         $scope.callback = function(data, status, headers, config) {
           if(config != null){
             if(config.url.includes("alerthistogram")){

               $rootScope.data = data;
               $rootScope.param = {
                 "type" : $scope.reportType,
                 "host" : $scope.reportHost,
                 "service" : service,
                 "period" : $scope.reportPeriod,
                 "start" : new Date($scope.startDate),
                 "end" : new Date($scope.endDate),
                 "assumeStateRetention" : $scope.assumeStateRetention,
                 "statisticsBreakdown" : $filter('alerthistogram-x')($scope.statisticsBreakdown)
               };

               if(data == 1)
                 ngToast.create({className: 'alert alert-danger',content:'Fail! Wrong Data!',timeout:1500});
               else if(data == 2)
                 ngToast.create({className: 'alert alert-danger',content:'Fail! Command Error!',timeout:1500});
               else
                 $window.location.href="#/report/alerthistogram/report";
             }
           }
         };
       };

       $scope.showReport = function(){
         //generate label for x-axis
          if($rootScope.param.statisticsBreakdown == 'Month'){
              var category = [];
              var data;

              if($rootScope.param.type == 1)
                data = $rootScope.data.down_count;
              else
                data = $rootScope.data.ok_count;

              for(var key in data)
                category.push({"label" : $filter('month')(key)});
          }

          else if($rootScope.param.statisticsBreakdown == 'Day of the Month'){

            var category = [];

            for(var i = 1; i <= 31; i++)
              category.push({"label" : i});
          }

          else if($rootScope.param.statisticsBreakdown == 'Day of the Week'){
            var category = [];

            if($rootScope.param.type == 1)
              data = $rootScope.data.down_count;
            else
              data = $rootScope.data.ok_count;

            for(var key in data){
              category.push({"label" : $filter('week')(key)});
            }
          }

          else if($rootScope.param.statisticsBreakdown == 'Hour of the Day'){
            var category = [];

            for(var i = 1; i <= 24; i++)
              category.push({"label" : i});
          }


          //generate label for y-axis
          if($rootScope.param.type == 1){
            var data_up = [];
            var data_down = [];
            var data_unreachable = [];

            $rootScope.data.up_count.forEach(function(data){
              data_up.push({"value" : data});
            })

            $rootScope.data.down_count.forEach(function(data){
              data_down.push({"value" : data});
            })

            $rootScope.data.unreachable_count.forEach(function(data){
              data_unreachable.push({"value" : data});
            })
          }
          else {
            var data_ok = [];
            var data_warning = [];
            var data_unknown = [];
            var data_critical = [];

            $rootScope.data.ok_count.forEach(function(data){
              data_ok.push({"value" : data});
            })

            $rootScope.data.warning_count.forEach(function(data){
              data_warning.push({"value" : data});
            })

            $rootScope.data.unknown_count.forEach(function(data){
              data_unknown.push({"value" : data});
            })

            $rootScope.data.critical_count.forEach(function(data){
              data_critical.push({"value" : data});
            })
          }

          var start = $rootScope.data.start_date;
          var start_time = $filter('date')(start * 1000, 'EEE MMM d H:mm:ss yyyy');
          var end = $rootScope.data.end_date;
          var end_time = $filter('date')(end * 1000, 'EEE MMM d H:mm:ss yyyy');

         $scope.hostdata = {
           "chart": {
               "caption": "State History For Host " + $rootScope.param.host,
               "captionfontsize": "16",
               "subCaption": "From " + start_time + " To " + end_time,
               "paletteColors": "#6cb22f,#d24555,#ee8425",
               "xaxisname": $rootScope.param.statisticsBreakdown,
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
              "category": category
            }
          ],
           "dataset": [
               {
                   "seriesname": "Recovery(Up)",
                   "data": data_up
               },
               {
                   "seriesname": "Down",
                   "data": data_down
               },
               {
                   "seriesname": "Unreachable",
                   "data": data_unreachable
               }
           ]
         }

         $scope.servicedata = {
           "chart": {
               "caption": "State History For Service " + $rootScope.param.service + " On Host " + $rootScope.param.host,
               "captionfontsize": "16",
               "subCaption": "From " + start_time + " To " + end_time,
               "paletteColors": "#6cb22f,#dba102,#ee8425,#d24555",
               "xaxisname": $rootScope.param.statisticsBreakdown,
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
              "category": category
            }
          ],
           "dataset": [
               {
                   "seriesname": "Recovery(Up)",
                   "data": data_ok
               },
               {
                   "seriesname": "Warning",
                   "data": data_warning
               },
               {
                   "seriesname": "Unknown",
                   "data": data_unknown
               },
               {
                   "seriesname": "Critical",
                   "data": data_critical
               }
           ]
         }
       };

      $scope.viewTrends = function(type){
           dataService.setInfo($rootScope.param);
           $window.location.href="#/report/trends";
      };

      $scope.viewAvailability = function(type){
          dataService.setInfo($rootScope.param);
          $window.location.href="#/report/availability";
      };
    }
])

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

.controller('SysCommentsCtrl', ['$scope', 'async', '$timeout', 'ngToast',
    function($scope, async, $timeout, ngToast) {

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

        $timeout(function(){$scope.hostName = $scope.name.host[0];}, 800);
        $timeout(function(){$scope.service = $scope.name.service[0].service}, 800);
        $scope.persistent = true;
        $scope.comment = '';

        if($scope.addHostComment)
          $scope.addHostComment.$setPristine();
        if($scope.addServiceComment)
          $scope.addServiceComment.$setPristine();

        $scope.callback = function(data, status, headers, config) {
          if(config != null){
            if(config.url.includes("status"))
              $scope.author = data.username;
          }
        };
      };

      $scope.addComment = function(type){

        $scope.reset();

        $scope.add = function(hostName, service, persistent, author, comment){

          var options = {
              name: 'addcomments',
              url: 'addcomments/'+ type + '/' + hostName + '/' + service
                + '/' + persistent + '/' + author + '/' + comment,
          };

          async.api($scope, options);

          $scope.callback = function(data, status, headers, config) {
            if(config != null){
                if(config.url.includes("addcomments")){
                    if(Object.getOwnPropertyNames(data).length == 0){
                      ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:1500});
                      $('.modal').modal('hide');
                    }
                    else if(data == 1)
                      ngToast.create({className: 'alert alert-danger',content:'Fail! Wrong Data!',timeout:1500});
                    else if(data == 2)
                      ngToast.create({className: 'alert alert-danger',content:'Fail! Command Error!',timeout:1500});
                }
            }
          };
        };
      };

      $scope.deleteComment = function(id, type){

        $scope.delete = function(){

          var options = {
              name: 'deletecomments',
              url: 'deletecomments/' + id + '/' + type
          };

          async.api($scope, options);

          $scope.callback = function(data, status, headers, config) {
              if(config != null){
                if(config.url.includes("deletecomments")){
                  if(Object.getOwnPropertyNames(data).length == 0)
                    ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:1500});
                    else if(data == 1)
                      ngToast.create({className: 'alert alert-danger',content:'Fail! Wrong Data!',timeout:1500});
                    else if(data == 2)
                      ngToast.create({className: 'alert alert-danger',content:'Fail! Command Error!',timeout:1500});
                }
              }
          };
        };
      };
    }
])

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

          //get author
          var options1 = {
              name: 'status',
              url: 'status',
              queue: 'status-' + '',
              cache: true
          };
          async.api($scope, options1);

          var now = $filter('date')(Date.now(), 'yyyy-MM-ddTHH:mm');
          var date = new Date();
          var twohourslater = $filter('date')((new Date(date.getTime() + (2*60*60*1000))), 'yyyy-MM-ddTHH:mm');

          $timeout(function(){$scope.hostName = $scope.name.host[0];}, 800);
          $scope.comment = null;
          $scope.triggeredBy = 0;
          $scope.startDate = now;
          $scope.endDate = twohourslater;
          $scope.type = 'true';
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

          $scope.schedule = function(hostName, service, start, end, fixed, triggerID, durationHour, durationMin, author, comment){
            if(service == '')
              service = null;

            var startUnix = parseInt((new Date(start).getTime() / 1000).toFixed(0));
            var endUnix = parseInt((new Date(end).getTime() / 1000).toFixed(0));

            var duration;
            if(fixed == "true"){
                var difference  = endUnix - startUnix;
                var minutesDifference = Math.floor(difference/60);
                duration = minutesDifference;
            }
            else
              duration  = durationHour * 60 + durationMin;

            var options = {
                name: 'scheduledowntime',
                url: 'scheduledowntime/'+ scheduletype + '/' + hostName + '/' + service + '/'+ startUnix
                  + '/' + endUnix + '/' + fixed + '/' + triggerID + '/' + duration
                  + '/' + author + '/' + comment
            };
            async.api($scope, options);

            $scope.callback = function(data, status, headers, config) {
              if(config != null){
                  if(config.url.includes("scheduledowntime")){
                      if(Object.getOwnPropertyNames(data).length == 0){
                        ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:1500});
                        $('.modal').modal('hide');
                      }
                      else if(data == 1)
                        ngToast.create({className: 'alert alert-danger',content:'Fail! Wrong Data!',timeout:1500});
                      else if(data == 2)
                        ngToast.create({className: 'alert alert-danger',content:'Fail! Command Error!',timeout:1500});
                  }
              }
            };
          };
        };

        $scope.deleteDowntime = function(id, type){

          $scope.delete = function(){
            var options = {
                name: 'deletedowntime',
                url: 'deletedowntime/' + id + '/' + type
            };

            async.api($scope, options);

            $scope.callback = function(data, status, headers, config) {
                if(config != null){
                  if(config.url.includes("deletedowntime")){
                    if(Object.getOwnPropertyNames(data).length == 0)
                      ngToast.create({className: 'alert alert-success',content:'Success! It may take some time to update.',timeout:1500});
                      else if(data == 1)
                        ngToast.create({className: 'alert alert-danger',content:'Fail! Wrong Data!',timeout:1500});
                      else if(data == 2)
                        ngToast.create({className: 'alert alert-danger',content:'Fail! Command Error!',timeout:1500});
                  }
                }
            };
          };
        };


    }
])

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

            }, 12000, 1);   
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
                    url: 'allnotifications/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allnotifications")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'allnotifications/' + type
                };

                async.api($scope, result);
                console.log($scope.allnotifications);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allnotifications")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'allservicecheck/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allservicecheck")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'allservicecheck/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allservicecheck")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'allpassiveservicecheck/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allpassiveservicecheck")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'allpassiveservicecheck/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allpassiveservicecheck")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'allhostcheck/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allhostcheck")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'allhostcheck/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allhostcheck")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'allpassivehostcheck/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allpassivehostcheck")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'allpassivehostcheck/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allpassivehostcheck")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'eventhandler/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("eventhandler")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'eventhandler/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("eventhandler")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'obsessoverservicecheck/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("obsessoverservicecheck")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'obsessoverservicecheck/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("obsessoverservicecheck")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'obsessoverhostcheck/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("obsessoverhostcheck")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'obsessoverhostcheck/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("obsessoverhostcheck")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'allflapdetection/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allflapdetection")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'allflapdetection/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("allflapdetection")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'performancedata/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("performancedata")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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
                    url: 'performancedata/' + type
                };

                async.api($scope, result);

                $scope.callback = function(data, status, headers, config){
                    if(config != null){
                        if(config.url.includes("performancedata")){
                            if(Object.getOwnPropertyNames(data).length == 0){
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

    }
])

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
                        url: 'servicecheck/' + type + '/' + $scope.hostName + '/' + $scope.serviceName
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
                        url: 'hostcheck/' + type + '/' + $scope.hostName
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
                        url: 'schedulecheck/' + type + '/' + $scope.hostName + '/' + $scope.serviceName + '/' + timestamp + '/' + $scope.force_check
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
                        url: 'schedulecheck/' + type + '/' + $scope.hostName + '/' + $scope.serviceName + '/' + timestamp + '/' + $scope.force_check
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
