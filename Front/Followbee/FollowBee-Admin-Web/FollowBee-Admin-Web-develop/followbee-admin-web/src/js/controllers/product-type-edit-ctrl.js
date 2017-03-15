/**
* @Author: Kongho
* @Date:   2017-01-20 11:54:35
* @Email:  kongho@3ncto.com
* @Filename: product-type-edit-ctrl.js
* @Last modified by:   Kongho
* @Last modified time: 2017-01-24 11:10:42
* @Copyright: 3NCTO Co., Ltd.
*/



app.controller('productTypeEditCtrl', function($scope, $rootScope, $location, productTypeModel, toastr) {
	$scope.title = '';
	$scope.type = $location.search().type; // view-查看，add-新增，edit-编辑

	$scope.params = {}
	$scope.params.productTypeId = $location.search().productTypeId;
	$scope.params.level = $location.search().level;

	if ($scope.params.level == 1) {
		$scope.title = '产品类型（一级分类）';
	} else if ($scope.params.level == 2) {
		$scope.title = '产品类型（二级分类）';
	}

	if ($scope.type == 'add') {
		$scope.title = '新增' + $scope.title;
		$scope.params.parent_id = $scope.params.productTypeId ? $scope.params.productTypeId : 0;
		$scope.buttonTitle = '新增';
	} else {
		if ($scope.type == 'edit') {
			$scope.title = '编辑' + $scope.title;
			$scope.buttonTitle = '保存';
		}
		productTypeModel.detail($scope.params, function(productType) {
			$scope.params.product_type_name = productType.product_type_name;
			$scope.params.en_product_type_name = productType.en_product_type_name;
		}, function(errorCode, errorMsg) {
			toastr.error(errorMsg);
		});
	}

	// 返回
	$scope.goBack = function() {
		$location.path('app/product-type-list').search({goBack: 1, level: $scope.params.level});
	}

	// 保存
	$scope.save = function() {
		if (!$scope.params.product_type_name) {
			toastr.warning('"产品类型（中文）"不能为空');
			return;
		}
		if (!$scope.params.en_product_type_name) {
			toastr.warning('"产品类型（中文）"不能为空');
			return;
		}


		if ($scope.type == 'add') {
			// 若添加一级产品类型，则从提交参数中删除parent_id
			if ($scope.params.parent_id == 0) {
				delete $scope.params.parent_id;
			}
			productTypeModel.add($scope.params, function(isSuccess) {
				toastr.success('新增成功');
				$scope.goBack();
			}, function(errorCode, errorMsg) {
				toastr.error(errorMsg);
			});
		} else if ($scope.type == 'edit') {
			productTypeModel.edit($scope.params, function(isSuccess) {
				toastr.success('保存成功');
				$scope.goBack();
			}, function(errorCode, errorMsg) {
				toastr.error(errorMsg);
			});
		}
	}
});
