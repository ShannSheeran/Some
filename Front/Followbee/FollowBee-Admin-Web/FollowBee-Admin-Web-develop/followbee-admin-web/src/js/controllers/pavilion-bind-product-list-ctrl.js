/**
* @Author: Kongho
* @Date:   2017-01-20 11:54:35
* @Email:  kongho@3ncto.com
* @Filename: pavilion-bind-product-list-ctrl.js
* @Last modified by:   Kongho
* @Last modified time: 2017-01-22 21:15:20
* @Copyright: 3NCTO Co., Ltd.
*/



app.controller('pavilionBindProductListCtrl', function($scope, $rootScope, pavilionModel, accountModel, $location, $uibModal, toastr) {
	// 检查登录状态
	accountModel.checkLogin();

	$scope.data = {};
	$scope.data.params = {};
	$scope.data.params.pavilionId = $location.search().pavilionId ? $location.search().pavilionId : 0;
	$scope.data.params.pavilionName = $location.search().pavilionName ? $location.search().pavilionName : '';

	initData();

	// 分页初始化设置，每次获取列表都要更新$scope.paginationConf.totalItems的值
	$scope.data.paginationConf = {
		currentPage: 1,
		totalItems: 0,
		itemsPerPage: 10,
		pagesLength: 9,
		perPageOptions: [10],
		onChange: function() {
			$scope.data.params.page_no = this.currentPage;
		}
	};

	$scope.addProductType = function() {
		$location.path('app/pavilion-bind-product-edit').search({pavilionId: $scope.data.params.pavilionId, pavilionName: $scope.data.params.pavilionName});
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
		$scope.data.params.product_type_id = $scope.toDeleteProductType.product_type_id;
		pavilionModel.productTypeDelete($scope.data.params, function(isSuccess) {
			toastr.success('删除成功');
			getList($scope.data.params);
		}, function(errorCode, errorMsg) {
			toastr.error(errorMsg);
		});
	}

	$scope.cancelDelete = function() {
		console.log('关闭弹窗');
	}

	// 初始化数据
	function initData() {
		$scope.data.params.page_no = 1;

		// 监听页码的变化
		$scope.$watch('data.params.page_no', function() {
	        getList($scope.data.params);
	    });
	}

	// 获取其他产品列表
	function getList(params) {
		pavilionModel.productTypeList(params, function(list, total, pageNo, pageNum) {
			$scope.list = [];
			if (list) {
				$scope.list = list;
			}
			$scope.data.paginationConf.totalItems = total;
		}, function(errorCode, errorMsg) {
			$scope.list = [];
			$scope.paginationConf.totalItems = 0;
			console.log('errorCode: ' + errorCode + '\nerrorMsg: ' + errorMsg);
		});
	}
});
