/**
* @Author: Kongho
* @Date:   2017-01-20 11:54:35
* @Email:  kongho@3ncto.com
* @Filename: pavilion-bind-product-edit-ctrl.js
* @Last modified by:   Kongho
* @Last modified time: 2017-01-22 22:21:07
* @Copyright: 3NCTO Co., Ltd.
*/



app.controller('pavilionBindProductEditCtrl', function($scope, $rootScope, pavilionModel, productTypeModel, accountModel, $location, toastr) {
	// 检查登录状态
	accountModel.checkLogin();

	$scope.data = {};
	$scope.list = [];
	$scope.data.params = {};
	$scope.data.params.pavilionId = $location.search().pavilionId;
	$scope.data.params.pavilionName = $location.search().pavilionName;
	$scope.data.params.old_product_type_id = $location.search().productTypeId;

	initData();

	// 取消
	$scope.cancel = function() {
		$location.path('app/pavilion-bind-product-list').search({pavilionId: $scope.data.params.pavilionId});
	}

	// 保存
	$scope.save = function() {
		if ($scope.data.params.product_type_id == 0) {
			toastr.warning('请选择一级产品类型分类');
			return;
		}
		if (!$scope.data.params.old_product_type_id) {
			delete $scope.data.params.old_product_type_id;
		}
		pavilionModel.productTypeAdd($scope.data.params, function(isSuccess) {
			toastr.success('绑定成功');
			$scope.cancel();
		}, function(errorCode, errorMsg) {
			toastr.error(errorMsg);
		});
	}

	function initData() {
		// 编辑
		if ($scope.data.params.old_product_type_id) {
			$scope.data.params.product_type_id = $scope.data.params.old_product_type_id;
		} else {
			$scope.data.params.product_type_id = '0';
		}
		productTypeModel.list({}, function(list, total, pageNo, pageNum) {
			if (list) {
				$scope.list = list;
			}
		}, function(errorCode, errorMsg) {
			toastr.error(errorMsg);
		});
	}
});
