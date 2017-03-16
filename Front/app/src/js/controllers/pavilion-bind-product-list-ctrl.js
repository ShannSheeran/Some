/**
* @Author: Kongho
* @Date:   2017-01-20 11:54:35
* @Email:  kongho@3ncto.com
* @Filename: pavilion-bind-product-list-ctrl.js
* @Last modified by:   Kongho
* @Last modified time: 2017-02-18 15:44:47
* @Copyright: 3NCTO Co., Ltd.
*/



app.controller('pavilionBindProductListCtrl', function($scope, $rootScope, pavilionModel, accountModel, $location, $uibModal, toastr, configs) {
	// 检查登录状态
	accountModel.checkLogin();

	var vm = $scope;

	// 初始化数据
	function initData() {
		vm.data = {};
		vm.data.params = {};

		if ($location.search().goBack == 1) {
            console.warn('from goBack');
			vm.data = JSON.parse(localStorage.getItem('pavilion_bind_product_list_data'));
			console.warn(vm.data);
        } else {
			vm.data.params.pavilionId = $location.search().pavilionId ? $location.search().pavilionId : 0;
			vm.data.params.pavilionName = $location.search().pavilionName ? $location.search().pavilionName : '';

			vm.data.params.page_no = 1;
        }

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
		};

		// 监听页码的变化
		vm.$watch('paginationConf.currentPage', function() {
			getList(vm.data.params);
		});
	}
	initData();

	vm.goBack = function() {
		window.location.href = configs.adminV1.basePath + configs.adminV1.pavilionList;
	}

	vm.addProductType = function() {
		$location.path('app/pavilion-bind-product-edit').search({pavilionId: vm.data.params.pavilionId, pavilionName: vm.data.params.pavilionName});
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

	// 删除绑定产品类型
	vm.deleteProductType = function() {
		vm.data.params.product_type_id = vm.toDeleteProductType.product_type_id;
		pavilionModel.productTypeDelete(vm.data.params, function(isSuccess) {
			toastr.success('删除成功');
			getList(vm.data.params);
		}, function(errorCode, errorMsg) {
			toastr.error(errorMsg);
		});
	}

	// 取消删除操作
	vm.cancelDelete = function() {
		console.log('关闭弹窗');
	}

	// 获取其他产品列表
	function getList(params) {
		pavilionModel.productTypeList(params, function(list, total, pageNo, pageNum) {
			vm.list = [];
			if (list) {
				vm.list = list;
			}
			vm.paginationConf.totalItems = total;
		}, function(errorCode, errorMsg) {
			vm.list = [];
			vm.paginationConf.totalItems = 0;
			toastr.error(errorMsg);
		});
	}

    // 监听页面状态变化
    $rootScope.$on('$stateChangeSuccess', function(ev, to, toParams, from, fromParams) {
        // assign the "from" parameter to something
        // 缓存当前页面的跳转前的状态
		localStorage.setItem('pavilion_bind_product_list_data', JSON.stringify(vm.data));
    });
});
