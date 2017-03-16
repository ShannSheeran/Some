/**
* @Author: Kongho
* @Date:   2017-01-20 11:54:35
* @Email:  kongho@3ncto.com
* @Filename: data-statistics-list-ctrl.js
* @Last modified by:   Kongho
* @Last modified time: 2017-02-18 15:32:20
* @Copyright: 3NCTO Co., Ltd.
*/



app.controller('dataStatisticsListCtrl', function($scope, $rootScope, statisticsModel, accountModel, $location) {
	// 检查登录状态
	accountModel.checkLogin();

	var vm = $scope;

	// 初始化数据
	function initData() {
		vm.data = {};
		vm.data.params = {};

		if ($location.search().goBack == 1) {
			var cacheName = 'statistics';
			vm.data = JSON.parse(localStorage.getItem(cacheName));
		} else {
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

	// 获取展会签到数据统计列表
	function getList(params) {
		statisticsModel.pavilion(params, function(list, total, pageNo, pageNum) {
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
	$rootScope.$on('$stateChangeSuccess', function (ev, to, toParams, from, fromParams) {
	   // assign the "from" parameter to something
	   // 缓存当前页面的跳转前的状态
	   var cacheName = 'statistics';
	   localStorage.setItem(cacheName, JSON.stringify(vm.data));
	});
});
