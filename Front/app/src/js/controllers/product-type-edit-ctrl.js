/**
* @Author: Kongho
* @Date:   2017-01-20 11:54:35
* @Email:  kongho@3ncto.com
* @Filename: product-type-edit-ctrl.js
* @Last modified by:   Kongho
* @Last modified time: 2017-01-24 11:10:42
* @Copyright: 3NCTO Co., Ltd.
*/



app.controller('productTypeEditCtrl', function($scope, $rootScope, $location, productTypeModel, toastr, accountModel) {
	// 检查登录状态
	accountModel.checkLogin();

	var vm = $scope;

	vm.title = '';
	vm.type = $location.search().type; // view-查看，add-新增，edit-编辑

	vm.params = {}
	vm.params.productTypeId = $location.search().productTypeId;
	vm.params.level = $location.search().level;

	if (vm.params.level == 1) {
		vm.title = '产品类型（一级分类）';
	} else if (vm.params.level == 2) {
		vm.title = '产品类型（二级分类）';
	}

	if (vm.type == 'add') {
		vm.title = '新增' + vm.title;
		vm.params.parent_id = vm.params.productTypeId ? vm.params.productTypeId : 0;
		vm.buttonTitle = '新增';
	} else {
		if (vm.type == 'edit') {
			vm.title = '编辑' + vm.title;
			vm.buttonTitle = '保存';
		}
		productTypeModel.detail(vm.params, function(productType) {
			vm.params.product_type_name = productType.product_type_name;
			vm.params.en_product_type_name = productType.en_product_type_name;
		}, function(errorCode, errorMsg) {
			toastr.error(errorMsg);
		});
	}

	// 返回
	vm.goBack = function() {
		$location.path('app/product-type-list').search({goBack: 1, level: vm.params.level});
	}

	// 保存
	vm.save = function() {
		if (!vm.params.product_type_name) {
			toastr.warning('"产品类型（中文）"不能为空');
			return;
		}
		if (!vm.params.en_product_type_name) {
			toastr.warning('"产品类型（中文）"不能为空');
			return;
		}


		if (vm.type == 'add') {
			// 若添加一级产品类型，则从提交参数中删除parent_id
			if (vm.params.parent_id == 0) {
				delete vm.params.parent_id;
			}
			productTypeModel.add(vm.params, function(isSuccess) {
				toastr.success('新增成功');
				vm.goBack();
			}, function(errorCode, errorMsg) {
				toastr.error(errorMsg);
			});
		} else if (vm.type == 'edit') {
			productTypeModel.edit(vm.params, function(isSuccess) {
				toastr.success('保存成功');
				vm.goBack();
			}, function(errorCode, errorMsg) {
				toastr.error(errorMsg);
			});
		}
	}
});
