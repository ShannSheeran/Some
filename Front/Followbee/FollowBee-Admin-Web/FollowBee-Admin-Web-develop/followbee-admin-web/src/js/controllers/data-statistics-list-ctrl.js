/**
* @Author: Kongho
* @Date:   2017-01-20 11:54:35
* @Email:  kongho@3ncto.com
* @Filename: data-statistics-list-ctrl.js
* @Last modified by:   Kongho
* @Last modified time: 2017-01-24 11:07:47
* @Copyright: 3NCTO Co., Ltd.
*/



app.controller('dataStatisticsListCtrl', function($scope, $rootScope, statisticsModel, accountModel, $location) {
	// 检查登录状态
	accountModel.checkLogin();

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

	// 初始化数据
	function initData() {
		if ($location.search().goBack == 1) {
			var cacheName = 'statistics';
			$scope.data = JSON.parse(localStorage.getItem(cacheName));
		} else {
			$scope.data = {};
			$scope.data.params = {};
		}

		$scope.data.params.page_no = 1;

		// 监听页码的变化
		$scope.$watch('data.params.page_no', function() {
	        getList($scope.data.params);
	    });
	}

	// 获取展会签到数据统计列表
	function getList(params) {
		statisticsModel.pavilion(params, function(list, total, pageNo, pageNum) {
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

	// 监听页面状态变化
	$rootScope.$on('$stateChangeSuccess', function (ev, to, toParams, from, fromParams) {
	   // assign the "from" parameter to something
	   // 缓存当前页面的跳转前的状态
	   var cacheName = 'statistics';
	   localStorage.setItem(cacheName, JSON.stringify($scope.data));
	});
});
