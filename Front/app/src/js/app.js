/**
 * @Author: Kongho
 * @Date:   2017-01-18 12:12:17
 * @Email:  kongho@3ncto.com
 * @Filename: app.js
 * @Last modified by:   Kongho
 * @Last modified time: 2017-01-18 15:40:25
 * @Copyright: 3NCTO Co., Ltd.
 */



angular.module('app', [
    'ngCookies',
    'ui.router',
    'oc.lazyLoad',
    'ui.bootstrap',
    'toastr',
    // 'ng.ueditor'
]);

// config
var app =
    angular.module('app')
    .config(
        ['$controllerProvider', '$compileProvider', '$filterProvider', '$provide',
            function($controllerProvider, $compileProvider, $filterProvider, $provide) {

                // lazy controller, directive and service
                app.controller = $controllerProvider.register;
                app.directive = $compileProvider.directive;
                app.filter = $filterProvider.register;
                app.factory = $provide.factory;
                app.service = $provide.service;
                app.constant = $provide.constant;
                app.value = $provide.value;
            }
        ])
    .config(['$httpProvider', function($httpProvider) {
        //Reset headers to avoid OPTIONS request (aka preflight)
        $httpProvider.defaults.headers.common = {};
        $httpProvider.defaults.headers.post = {};
        $httpProvider.defaults.headers.put = {};
        $httpProvider.defaults.headers.delete = {};
        $httpProvider.defaults.headers.patch = {};
    }])
    .config(["$httpProvider",
        function($httpProvider) {
            $httpProvider.interceptors.push('UserInterceptor');
        }
    ])
    // 定义toastr的显示方式
    .config(["toastrConfig",
        function(toastrConfig) {
            angular.extend(toastrConfig, {
                autoDismiss: false,
                containerId: 'toast-container',
                maxOpened: 0,
                newestOnTop: true,
                positionClass: 'toast-top-center',
                preventDuplicates: false,
                preventOpenDuplicates: false,
                target: 'body'
            });
        }
    ])
    .factory('UserInterceptor', ["$q", "$log", "$cookieStore", "$rootScope", "$window", "configs", "apiConfig",
        function($q, $log, $cookieStore, $rootScope, $window, configs, apiConfig) {
            $rootScope.loading = false;
            var index_num = 0;
            return {
                request: function(config) {
                    $rootScope.loading = true;
                    $rootScope.tipPopup = false;
                    $rootScope.tipMsg = '';
                    index_num += 1;
                    return config;
                },
                response: function(response) {
                    if (response.data.error_code) {
                        if (response.data.error_code !== 1) {
                            $rootScope.loading = false;
                            index_num = 0;
                            $rootScope.tipMsg = response.data.error_msg;
                            $rootScope.tipPopup = true;
                            // alert(response.data.error_msg);
                            // return $q.reject();
                        }
                    }
                    if (index_num > 0) {
                        index_num -= 1;
                        if (index_num == 0) {
                            $rootScope.loading = false;
                        }
                    }
                    return response;
                },
                requestError: function(rejection) {
                    $rootScope.loading = false;
                    index_num = 0;
                    alert("404！");
                    return rejection;
                },
                responseError: function(rejection) {
                    $rootScope.loading = false;
                    index_num = 0;
                    // alert("请求失败，请重新刷新页面s！");
                    return rejection;
                }
            };
        }
    ])
