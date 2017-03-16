/**
* @Author: Kongho
* @Date:   2017-01-20 11:54:35
* @Email:  kongho@3ncto.com
* @Filename: data-statistics-sign-details-ctrl.js
* @Last modified by:   Kongho
* @Last modified time: 2017-02-18 15:51:20
* @Copyright: 3NCTO Co., Ltd.
*/



app.controller('dataStatisticsSignDetailsCtrl', function($scope, $rootScope, statisticsModel, accountModel, $location, toastr) {
	// 检查登录状态
	accountModel.checkLogin();

	var vm = $scope;

	// 初始化数据
	function initData() {
		vm.data = {};
		vm.data.params = {};

		vm.data.type = $location.search().type;
		vm.data.pavilionId = $location.search().pavilionId;
		vm.data.params.sign_info_id = $location.search().signInfoId;

		// 分页初始化设置，每次获取列表都要更新vm.paginationConf.totalItems的值
		vm.paginationConf = {
			currentPage: 1,
			totalItems: 0,
			itemsPerPage: 10,
			pagesLength: 9,
			perPageOptions: [10],
			onChange: function() {

			}
		};

		if (vm.data.type == 1) {
			vm.data.title = '展商签到详情';
		} else if (vm.data.type == 2) {
			vm.data.title = '观众签到详情';
		}

		statisticsModel.userDetail(vm.data.params, function(detail) {
			vm.data.detail = detail;
			vm.paginationConf.totalItems = detail.invite_user ? detail.invite_user.length : 0;
		}, function(errorCode, errorMsg){
			toastr.error(errorMsg);
		});
	}
	initData();

	// 返回
	vm.goBack = function() {
		$location.path('app/data-statistics-sign').search({goBack: 1, pavilionId: vm.data.pavilionId});
	}
});
