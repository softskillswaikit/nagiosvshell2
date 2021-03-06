'use strict';

angular.module('vshell', [
    'cgBusy',
    'ngRoute',
    'ngToast',
    'ngAnimate',
    'ngSanitize',
    'vshell.filters',
    'vshell.services',
    'vshell.directives',
    'vshell.controllers',
    'ng-fusioncharts'
])

.config(['$routeProvider',
    function($routeProvider) {
        $routeProvider.when('/overview', {
            templateUrl: 'frontend/partials/overview.html'
        });
        $routeProvider.when('/hosts', {
            templateUrl: 'frontend/partials/hosts.html'
        });
        $routeProvider.when('/hosts/state/:state', {
            templateUrl: 'frontend/partials/hosts.html'
        });
        $routeProvider.when('/hosts/problems/:handled', {
            templateUrl: 'frontend/partials/hosts.html'
        });
        $routeProvider.when('/hosts/:host*', {
            templateUrl: 'frontend/partials/host-details.html'
        });
        $routeProvider.when('/hostgroups/summary', {
            templateUrl: 'frontend/partials/host-groups-summary.html'
        });
        $routeProvider.when('/hostgroups/grid', {
            templateUrl: 'frontend/partials/host-groups-grid.html'
        });
        $routeProvider.when('/hostgroups/:group*', {
            templateUrl: 'frontend/partials/host-group-details.html'
        });
        $routeProvider.when('/services', {
            templateUrl: 'frontend/partials/services.html'
        });
        $routeProvider.when('/services/state/:state', {
            templateUrl: 'frontend/partials/services.html'
        });
        $routeProvider.when('/services/problems/:handled', {
            templateUrl: 'frontend/partials/services.html'
        });
        $routeProvider.when('/services/on-host/:host', {
            templateUrl: 'frontend/partials/host-services.html'
        });
        $routeProvider.when('/services/:host/:service*', {
            templateUrl: 'frontend/partials/service-details.html'
        });
        $routeProvider.when('/servicegroups/summary', {
            templateUrl: 'frontend/partials/service-groups-summary.html'
        });
        $routeProvider.when('/servicegroups/grid', {
            templateUrl: 'frontend/partials/service-groups-grid.html'
        });
        $routeProvider.when('/servicegroups/:group*', {
            templateUrl: 'frontend/partials/service-group-details.html'
        });
        $routeProvider.when('/configurations', {
            templateUrl: 'frontend/partials/configurations-grid.html'
        });
        $routeProvider.when('/configurations/:type', {
            templateUrl: 'frontend/partials/configurations.html'
        });
        $routeProvider.when('/configurations/:type/:name*', {
            templateUrl: 'frontend/partials/configuration-details.html'
        });
        $routeProvider.when('/comments', {
            templateUrl: 'frontend/partials/comments.html'
        });
		    $routeProvider.when('/report/availability', {
            templateUrl: 'frontend/partials/report-availability.html'
        });
		    $routeProvider.when('/report/availability/report', {
            templateUrl: 'frontend/partials/report-availability-report.html'
        });
		    $routeProvider.when('/report/trends', {
            templateUrl: 'frontend/partials/report-trends.html'
        });
		    $routeProvider.when('/report/trends/report', {
            templateUrl: 'frontend/partials/report-trends-report.html'
        });
            $routeProvider.when("/reports/alert-history", {
            templateUrl: "frontend/partials/reports-alert-history.html"
        });
            $routeProvider.when("/reports/alert-summary", {
            templateUrl: "frontend/partials/reports-alert-summary.html"
        });
            $routeProvider.when("/reports/alert-summary-detail", {
            templateUrl: "frontend/partials/reports-alert-summary-detail.html"
        });
		    $routeProvider.when('/report/alerthistogram', {
            templateUrl: 'frontend/partials/report-alert-histogram.html'
        });
		    $routeProvider.when('/report/alerthistogram/report', {
            templateUrl: 'frontend/partials/report-alert-histogram-report.html'
        });
            $routeProvider.when("/reports/notifications", {
            templateUrl: "frontend/partials/reports-notifications.html"
        });
            $routeProvider.when("/reports/event-log", {
            templateUrl: "frontend/partials/reports-event-log.html"
        });
		    $routeProvider.when('/system/comments', {
            templateUrl: 'frontend/partials/system-comments.html'
        });
		    $routeProvider.when('/system/downtime', {
            templateUrl: 'frontend/partials/system-downtime.html'
        });
            $routeProvider.when("/system/process-info", {
            templateUrl: "frontend/partials/system-process-info.html"
        });
		    $routeProvider.when('/system/performanceinfo', {
            templateUrl: 'frontend/partials/system-performance-info.html'
        });
            $routeProvider.when("/system/scheduling-queue", {
            templateUrl: "frontend/partials/system-scheduling-queue.html"
        });
        $routeProvider.when('/options', {
            templateUrl: 'frontend/partials/options.html'
        });
        $routeProvider.otherwise({
            redirectTo: '/overview'
        });
    }
])
.config(['ngToastProvider', function(ngToastProvider) {
  ngToastProvider.configure({
    //combineDuplications: true,
    animation: 'slide' // or 'fade'
  });
}])
.config(['$compileProvider', function ($compileProvider) {
    $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|blob|ftp):/);
}])
