/**
* @Author: Kongho
* @Date:   2017-01-20 11:54:35
* @Email:  kongho@3ncto.com
* @Filename: data-statistics-sign-details-ctrl.js
* @Last modified by:   Kongho
* @Last modified time: 2017-01-24 14:41:04
* @Copyright: 3NCTO Co., Ltd.
*/



app.controller('dataStatisticsSignDetailsCtrl', function($scope, $rootScope, statisticsModel, accountModel, $location, toastr) {
	// 检查登录状态
	accountModel.checkLogin();
	
	$scope.data = {};
	$scope.data.type = $location.search().type;
	$scope.data.pavilionId = $location.search().pavilionId;
	$scope.data.params = {};
	$scope.data.params.sign_info_id = $location.search().signInfoId;

	initData();
	
	// 分页初始化设置，每次获取列表都要更新$scope.paginationConf.totalItems的值
	$scope.data.paginationConf = {
		currentPage: 1,
		totalItems: 0,
		itemsPerPage: 10,
		pagesLength: 9,
		perPageOptions: [10],
		onChange: function() {

		}
	};

	// 返回
	$scope.goBack = function() {
		$location.path('app/data-statistics-sign').search({goBack: 1, pavilionId: $scope.data.pavilionId});
	}

	// 初始化数据
	function initData() {
		if ($scope.data.type == 1) {
			$scope.data.title = '展商签到详情';
		} else if ($scope.data.type == 2) {
			$scope.data.title = '观众签到详情';
		}

		statisticsModel.userDetail($scope.data.params, function(detail) {
			$scope.data.detail = detail;
			$scope.data.paginationConf.totalItems = detail.invite_user ? detail.invite_user.length : 0;
		}, function(errorCode, errorMsg){
			toastr.error(errorMsg);
		});
	}
});
