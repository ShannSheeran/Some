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

	var vm = $scope;

	function initData() {
		vm.data = {};
		vm.list = [];
		vm.data.params = {};
		vm.data.params.pavilionId = $location.search().pavilionId;
		vm.data.params.pavilionName = $location.search().pavilionName;
		vm.data.params.old_product_type_id = $location.search().productTypeId;

		// 编辑
		if (vm.data.params.old_product_type_id) {
			vm.data.params.product_type_id = vm.data.params.old_product_type_id;
		} else {
			vm.data.params.product_type_id = '0';
		}
		productTypeModel.list({}, function(list, total, pageNo, pageNum) {
			if (list) {
				vm.list = list;
			}
		}, function(errorCode, errorMsg) {
			toastr.error(errorMsg);
		});
	}
	initData();

	// 取消
	vm.cancel = function() {
		$location.path('app/pavilion-bind-product-list').search({goBack: 1});
	}

	// 保存
	vm.save = function() {
		if (vm.data.params.product_type_id == 0) {
			toastr.warning('请选择一级产品类型分类');
			return;
		}
		if (!vm.data.params.old_product_type_id) {
			delete vm.data.params.old_product_type_id;
		}
		pavilionModel.productTypeAdd(vm.data.params, function(isSuccess) {
			toastr.success('绑定成功');
			vm.cancel();
		}, function(errorCode, errorMsg) {
			toastr.error(errorMsg);
		});
	}
});
