/**
 * @Author: Kongho
 * @Date:   2017-01-20 11:54:35
 * @Email:  kongho@3ncto.com
 * @Filename: product-type-list-ctrl.js
* @Last modified by:   Kongho
* @Last modified time: 2017-02-18 14:57:00
 * @Copyright: 3NCTO Co., Ltd.
 */



app.controller('productTypeListCtrl', function($scope, $rootScope, $location, productTypeModel, accountModel, $uibModal, toastr) {
    // 检查登录状态
    accountModel.checkLogin();

    var vm = $scope;

    // 初始化数据
    function initData() {
        vm.data = {};
        vm.data.title = '';
        vm.list = [];
        vm.data.params = {};

        if ($location.search().goBack == 1) {
            console.warn('from goBack');
            // 根据级别获取最后保存的vm对象
            vm.data.level = $location.search().level;
            initDataFromCache(vm.data.level);
        } else {
            vm.data.level = 1;
            vm.data.params.parent_id = $location.search().parentId ? $location.search().parentId : 0;

            vm.data.params.page_no = 1;

            // 分页初始化设置，每次获取列表都要更新vm.paginationConf.totalItems的值
            vm.paginationConf = {
                currentPage: vm.data.params.page_no,
                totalItems: 0,
                itemsPerPage: 10,
                pagesLength: 9,
                perPageOptions: [10],
                onChange: function() {
                    vm.data.params.page_no = this.currentPage;
                }
            }

            // 清理缓存
            clearCache();
        }

        // 监听页码的变化
        vm.$watch('paginationConf.currentPage', function() {
            getList(vm.data.params);
        });
    }
    initData();

    // 返回一级分类管理页面
    vm.manageLevel1 = function() {
        vm.data.level = 1;
        $location.path('app/product-type-list').search({});
        // 刷新数据
        initDataFromCache(vm.data.level);
    }

    // 显示二级分类管理页面
    vm.manageLevel2 = function(index) {
        // 缓存一级分类管理页面数据
        cacheData();

        var productType = vm.list[index];
        vm.data.parent_name = productType.product_type_name;
        vm.data.en_parent_name = productType.en_product_type_name;
        vm.data.params.parent_id = productType.product_type_id;
        vm.data.params.product_type_name = '';
        vm.data.level = 2;

        if (vm.data.params.page_no != 1) {
            vm.data.params.page_no = 1;
            vm.paginationConf.currentPage = vm.data.params.page_no;
        } else {
            getList(vm.data.params);
        }
    }

    // 搜索
    vm.search = function() {
        if (vm.data.params.page_no != 1) {
            vm.data.params.page_no = 1;
            vm.paginationConf.currentPage = vm.data.params.page_no;
        } else {
            getList(vm.data.params);
        }
    }

    vm.addProductType = function() {
        $location.path('app/product-type-edit').search({
            type: 'add',
            level: vm.data.level,
            productTypeId: vm.data.params.parent_id
        });
    }

    vm.delete = function(productType) {
        vm.toDeleteProductType = productType;
        $uibModal.open({
            templateUrl: 'tpl/popup/tips.html',
            controller: 'tipsCtrl',
            resolve: {
                _scope: function() {
                    return {
                        title: '温馨提示',
                        tips: "删除后不可恢复还望三思，确定要删除吗？",
                        cancelBtn: true,
                        sureCallback: vm.deleteProductType,
                        cancelCallback: vm.cancelDelete
                    }
                }
            }
        });
    }

    vm.deleteProductType = function() {
        productTypeModel.delete({
            productTypeId: vm.toDeleteProductType.product_type_id
        }, function(isSuccess) {
            toastr.success('删除成功');
            getList(vm.data.params);
        }, function(errorCode, errorMsg) {
            toastr.error(errorMsg);
        });
    }

    vm.cancelDelete = function() {
        console.log('关闭弹窗');
    }

    function initDataFromCache(level) {
        // 根据级别获取最后缓存的数据
        var cacheName = 'product_type_list_level_' + level + '_data';
        vm.data = JSON.parse(localStorage.getItem(cacheName));
        console.warn(vm.data);

        if (vm.paginationConf) {
            // 若当前页码与page_no一样，则处理刷新列表数据
            if (vm.paginationConf.currentPage == vm.data.params.page_no) {
                getList(vm.data.params);
            } else {
                // 重置分页设置
                vm.paginationConf = {
                    currentPage: vm.data.params.page_no,
                    totalItems: 0,
                    itemsPerPage: 10,
                    pagesLength: 9,
                    perPageOptions: [10],
                    onChange: function() {
                        vm.data.params.page_no = this.currentPage;
                    }
                }
            }
        } else {
            // 分页初始化设置
            vm.paginationConf = {
                currentPage: vm.data.params.page_no,
                totalItems: 0,
                itemsPerPage: 10,
                pagesLength: 9,
                perPageOptions: [10],
                onChange: function() {
                    vm.data.params.page_no = this.currentPage;
                }
            }
            getList(vm.data.params);
        }
        console.warn(vm.paginationConf);
        // getList(vm.data.params);
    }

    // 获取产品类型列表
    function getList(params) {
        console.warn(params);
        if (vm.data.level == 1) {
            vm.data.title = '（一级分类）';
        } else if (vm.data.level == 2) {
            vm.data.title = '（二级分类）';
        }

        productTypeModel.list(params, function(list, total, pageNo, pageNum) {
            vm.list = [];
            if (list) {
                vm.list = list;
            }
            vm.paginationConf.totalItems = total;
        }, function(errorCode, errorMsg) {
            vm.list = [];
            vm.paginationConf.totalItems = 0;
            console.log('errorCode: ' + errorCode + '\nerrorMsg: ' + errorMsg);
        });
    }

    // 监听页面状态变化
    $rootScope.$on('$stateChangeSuccess', function(ev, to, toParams, from, fromParams) {
        // assign the "from" parameter to something
        // 缓存当前页面的跳转前的状态
        console.warn('stateChangeSuccess');
        cacheData();
    });

    function cacheData() {
        var cacheName = 'product_type_list_level_' + vm.data.level + '_data';
        console.warn(cacheName);
        console.warn(vm.data);
        localStorage.setItem(cacheName, JSON.stringify(vm.data));
    }

    function clearCache() {
        localStorage.removeItem('product_type_list_level_1_data');
        localStorage.removeItem('product_type_list_level_2_data');
    }

});
