/**
 * @Author: Kongho
 * @Date:   2017-01-18 12:12:17
 * @Email:  kongho@3ncto.com
 * @Filename: config.router.js
* @Last modified by:   Kongho
* @Last modified time: 2017-01-22 21:30:46
 * @Copyright: 3NCTO Co., Ltd.
 */



/**
 * Config for the router
 */
angular.module('app')
    .run(
        ['$rootScope', '$state', '$stateParams',
            function($rootScope, $state, $stateParams) {
                $rootScope.$state = $state;
                $rootScope.$stateParams = $stateParams;
            }
        ])
    .config(
        ['$stateProvider', '$urlRouterProvider',
            function($stateProvider, $urlRouterProvider) {

                $urlRouterProvider
                    .otherwise('/app');
                $stateProvider
                    .state('app', {
                        url: '/app',
                        templateUrl: 'tpl/app.html',
                        controller: 'appCtrl',
                        resolve: {
                            deps: ['$ocLazyLoad', function($ocLazyLoad) {
                                return $ocLazyLoad.load([
                                    'js/model/base-model.js',
                                    'js/model/account-model.js',
                                    'js/controllers/app-ctrl.js',
                                    'js/controllers/popup/tips-ctrl.js'
                                ]);
                            }]
                        }
                    })
                    .state('app.product-type-list', {
                        url: '/product-type-list',
                        templateUrl: 'tpl/product-type-list.html',
                        controller: 'productTypeListCtrl',
                        resolve: {
                            deps: ['$ocLazyLoad', function($ocLazyLoad) {
                                return $ocLazyLoad.load([
                                    'js/model/product-type-model.js',
                                    'js/controllers/product-type-list-ctrl.js'
                                ]);
                            }]
                        }
                    })
                    .state('app.product-type-edit', {
                        url: '/product-type-edit',
                        templateUrl: 'tpl/product-type-edit.html',
                        controller: 'productTypeEditCtrl',
                        resolve: {
                            deps: ['$ocLazyLoad', function($ocLazyLoad) {
                                return $ocLazyLoad.load([
                                    'js/model/product-type-model.js',
                                    'js/controllers/product-type-edit-ctrl.js'
                                ]);
                            }]
                        }
                    })
                    .state('app.product-type-other-list', {
                        url: '/product-type-other-list',
                        templateUrl: 'tpl/product-type-other-list.html',
                        controller: 'productTypeOtherListCtrl',
                        resolve: {
                            deps: ['$ocLazyLoad', function($ocLazyLoad) {
                                return $ocLazyLoad.load([
                                    'js/model/product-type-model.js',
                                    'js/controllers/product-type-other-list-ctrl.js'
                                ]);
                            }]
                        }
                    })
                    .state('app.pavilion-list', {
                        url: '/pavilion-list',
                        templateUrl: 'tpl/pavilion-list.html',
                        controller: 'pavilionListCtrl',
                        resolve: {
                            deps: ['$ocLazyLoad', function($ocLazyLoad) {
                                return $ocLazyLoad.load([
                                    'js/model/pavilion-model.js',
                                    'js/controllers/pavilion-list-ctrl.js'
                                ]);
                            }]
                        }
                    })
                    .state('app.pavilion-bind-product-list', {
                        url: '/pavilion-bind-product-list',
                        templateUrl: 'tpl/pavilion-bind-product-list.html',
                        controller: 'pavilionBindProductListCtrl',
                        resolve: {
                            deps: ['$ocLazyLoad', function($ocLazyLoad) {
                                return $ocLazyLoad.load([
                                    'js/model/pavilion-model.js',
                                    'js/controllers/pavilion-bind-product-list-ctrl.js'
                                ]);
                            }]
                        }
                    })
                    .state('app.pavilion-bind-product-edit', {
                        url: '/pavilion-bind-product-edit',
                        templateUrl: 'tpl/pavilion-bind-product-edit.html',
                        controller: 'pavilionBindProductEditCtrl',
                        resolve: {
                            deps: ['$ocLazyLoad', function($ocLazyLoad) {
                                return $ocLazyLoad.load([
                                    'js/model/pavilion-model.js',
                                    'js/model/product-type-model.js',
                                    'js/controllers/pavilion-bind-product-edit-ctrl.js'
                                ]);
                            }]
                        }
                    })
                    .state('app.data-statistics-list', {
                        url: '/data-statistics-list',
                        templateUrl: 'tpl/data-statistics-list.html',
                        controller: 'dataStatisticsListCtrl',
                        resolve: {
                            deps: ['$ocLazyLoad', function($ocLazyLoad) {
                                return $ocLazyLoad.load([
                                    'js/model/data-statistics-model.js',
                                    'js/controllers/data-statistics-list-ctrl.js',
                                ]);
                            }]
                        }
                    })
                    .state('app.data-statistics-sign', {
                        url: '/data-statistics-sign',
                        templateUrl: 'tpl/data-statistics-sign.html',
                        controller: 'dataStatisticsSignCtrl',
                        resolve: {
                            deps: ['$ocLazyLoad', function($ocLazyLoad) {
                                return $ocLazyLoad.load([
                                    'js/model/data-statistics-model.js',
                                    'js/controllers/data-statistics-sign-ctrl.js',
                                ]);
                            }]
                        }
                    })
                    .state('app.data-statistics-sign-details', {
                        url: '/data-statistics-sign-details',
                        templateUrl: 'tpl/data-statistics-sign-details.html',
                        controller: 'dataStatisticsSignDetailsCtrl',
                        resolve: {
                            deps: ['$ocLazyLoad', function($ocLazyLoad) {
                                return $ocLazyLoad.load([
                                    'js/model/data-statistics-model.js',
                                    'js/controllers/data-statistics-sign-details-ctrl.js',
                                ]);
                            }]
                        }
                    })
                    .state('app.user-system-message-list', {
                        url: '/user-system-message-list',
                        templateUrl: 'tpl/user-system-message-list.html',
                        controller: 'userSystemMessageListCtrl',
                        resolve: {
                            deps: ['$ocLazyLoad', function($ocLazyLoad) {
                                return $ocLazyLoad.load([
                                    'js/model/user-model.js',
                                    'js/controllers/user-system-message-list-ctrl.js',
                                ]);
                            }]
                        }
                    })
                    .state('app.user-system-message-edit', {
                        url: '/user-system-message-edit',
                        templateUrl: 'tpl/user-system-message-edit.html',
                        controller: 'userSystemMessageEditCtrl',
                        resolve: {
                            deps: ['$ocLazyLoad', function($ocLazyLoad) {
                                return $ocLazyLoad.load([
                                    'js/model/user-model.js',
                                    'js/controllers/user-system-message-edit-ctrl.js',
                                ]);
                            }]
                        }
                    })
            }
        ])
