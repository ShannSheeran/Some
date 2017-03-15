/**
 * @Author: Kongho
 * @Date:   2017-01-20 11:54:35
 * @Email:  kongho@3ncto.com
 * @Filename: product-type-list-ctrl.js
 * @Last modified by:   Kongho
 * @Last modified time: 2017-01-24 11:19:17
 * @Copyright: 3NCTO Co., Ltd.
 */



app.controller('productTypeListCtrl', function($scope, $rootScope, $location, productTypeModel, accountModel, $uibModal, toastr) {
    // 检查登录状态
    accountModel.checkLogin();

    initData();

    // 返回一级分类管理页面
    $scope.manageLevel1 = function() {
        $scope.level = 1;
        // 修改地址栏（对数据刷新不起作用）
        $location.path('app/product-type-list').search({
            goBack: 1,
            level: 1
        });
        // 刷新数据
        initDataFromCache($scope.level);
    }

    // 显示二级分类管理页面
    $scope.manageLevel2 = function(index) {
        // 缓存一级分类管理页面数据
        cacheData();

        $location.path('app/product-type-list');

        $location.search().level = 2;
        var productType = $scope.list[index];
        $scope.parent_name = productType.product_type_name;
        $scope.en_parent_name = productType.en_product_type_name;
        $scope.params.parent_id = productType.product_type_id;
        $scope.params.product_type_name = '';
        $scope.level = 2;

        if ($scope.params.page_no != 1) {
            $scope.params.page_no = 1;
        } else {
            getList($scope.params);
        }
    }

    // 搜索
    $scope.search = function() {
        $scope.params.page_no = 1;
        getList($scope.params);
    }

    $scope.addProductType = function() {
        $location.path('app/product-type-edit').search({
            type: 'add',
            level: $scope.level,
            productTypeId: $scope.params.parent_id
        });
    }

    $scope.delete = function(productType) {
        $scope.toDeleteProductType = productType;
        $uibModal.open({
            templateUrl: 'tpl/popup/tips.html',
            controller: 'tipsCtrl',
            resolve: {
                _scope: function() {
                    return {
                        title: '温馨提示',
                        tips: "删除后不可恢复还望三思，确定要删除吗？",
                        cancelBtn: true,
                        sureCallback: $scope.deleteProductType,
                        cancelCallback: $scope.cancelDelete
                    }
                }
            }
        });
    }

    $scope.deleteProductType = function() {
        productTypeModel.delete({
            productTypeId: $scope.toDeleteProductType.product_type_id
        }, function(isSuccess) {
            toastr.success('删除成功');
            getList($scope.params);
        }, function(errorCode, errorMsg) {
            toastr.error(errorMsg);
        });
    }

    $scope.cancelDelete = function() {
        console.log('关闭弹窗');
    }

    // 初始化数据
    function initData() {
        if ($location.search().goBack == 1) {
            console.warn('from goBack');
            // 根据级别获取最后保存的$scope对象
            $scope.level = $location.search().level;
            initDataFromCache($scope.level);
        } else {
            $scope.title = '';
            $scope.list = [];
            $scope.params = {};
            $scope.level = 1;
            $scope.params.parent_id = $location.search().parentId ? $location.search().parentId : 0;

            // 分页初始化设置，每次获取列表都要更新$scope.paginationConf.totalItems的值
            $scope.paginationConf = {
                currentPage: 1,
                totalItems: 0,
                itemsPerPage: 10,
                pagesLength: 9,
                perPageOptions: [10],
                onChange: function() {
                    $scope.params.page_no = this.currentPage;
                }
            }
        }
        $scope.params.page_no = 1;

        // 监听页码的变化
        $scope.$watch('params.page_no', function() {
            getList($scope.params);
        });
    }

    function initDataFromCache(level) {
        // 根据级别获取最后保存的$scope对象
        var cacheName = 'product_type_list_l' + level + '_data';
        var cacheData = JSON.parse(localStorage.getItem(cacheName));
        console.warn(cacheData);
        $scope.title = cacheData.title;
        $scope.list = cacheData.list;
        $scope.params = cacheData.params;
        $scope.level = cacheData.level;
        $scope.paginationConf = cacheData.paginationConf;
        $scope.parent_name = cacheData.parent_name;
        $scope.en_parent_name = cacheData.en_parent_name;

        getList($scope.params);
    }

    // 获取产品类型列表
    function getList(params) {
        if ($scope.level == 1) {
            $scope.title = '（一级分类）';
        } else if ($scope.level == 2) {
            $scope.title = '（二级分类）';
        }

        productTypeModel.list(params, function(list, total, pageNo, pageNum) {
            $scope.list = [];
            if (list) {
                $scope.list = list;
            }
            $scope.paginationConf.totalItems = total;
        }, function(errorCode, errorMsg) {
            $scope.list = [];
            $scope.paginationConf.totalItems = 0;
            console.log('errorCode: ' + errorCode + '\nerrorMsg: ' + errorMsg);
        });
    }

    // 监听页面状态变化
    $rootScope.$on('$stateChangeSuccess', function(ev, to, toParams, from, fromParams) {
        // assign the "from" parameter to something
        // 缓存当前页面的跳转前的状态
        cacheData();
    });

    function cacheData() {
        var data = {
            title: $scope.title,
            list: $scope.list,
            params: $scope.params,
            level: $scope.level,
            paginationConf: $scope.paginationConf,
            parent_name: $scope.parent_name,
            en_parent_name: $scope.en_parent_name
        }
        var cacheName = 'product_type_list_l' + $scope.level + '_data';
        localStorage.setItem(cacheName, JSON.stringify(data));
    }

});
