/**
* @Author: Kongho
* @Date:   2017-01-21 15:37:48
* @Email:  kongho@3ncto.com
* @Filename: product-type-other-list-ctrl.js
* @Last modified by:   Kongho
* @Last modified time: 2017-01-24 14:23:51
* @Copyright: 3NCTO Co., Ltd.
*/



app.controller('productTypeOtherListCtrl', function($scope, $rootScope, productTypeModel, accountModel, toastr, $filter, baseModel, apiConfig) {
	// 检查登录状态
	accountModel.checkLogin();

	$scope.data = {};
	$scope.data.params = {};

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

    // 时间插件设置
     $scope.dateOptions = {
        dateDisabled: false,
        formatYear: 'yyyy',
        startingDay: 0,
        showWeeks: false
    };

	// 搜索
	$scope.search = function() {
		console.warn($scope.data.params);
		if ($scope.data.start_time || $scope.data.end_time) {
			if (!$scope.data.start_time) {
				toastr.warning('请选择开始时间');
				return;
			}
			if (!$scope.data.end_time) {
				toastr.warning('请选择结束时间');
				return;
			}
			if ($scope.data.end_time < $scope.data.start_time) {
				toastr.warning('结束时间不能早于开始时间');
				return;
			}

			$scope.data.params.start_time = $filter('date')($scope.data.start_time, 'yyyy-MM-dd');
			$scope.data.params.end_time = $filter('date')($scope.data.end_time, 'yyyy-MM-dd');
		}
		$scope.data.params.page_no = 1;
		$scope.data.params.is_export = 2;

		// 更新导出url
		structureExportUrl();

		getList($scope.data.params);
	}

	// 构建导出下载链接
	function structureExportUrl() {
		var url = baseModel.structureApiUrl(apiConfig.apiProductType.export) + '?token=' + localStorage.getItem('token');
		console.warn(url);
		if ($scope.data.params) {
			var params = '';
			angular.forEach($scope.data.params, function(value, key) {
				params += '&' + key + '=' + value;
			});
			url += params;
		}
		$scope.exportUrl = url;
	}

	// 初始化数据
	function initData() {
		$scope.data.params.page_no = 1;
		$scope.data.params.is_export = 2;

		if ($scope.data.params.start_time) {
			$scope.data.start_time = new Date(Date.parse($scope.data.params.start_time));
		}
		if ($scope.data.params.end_time) {
			$scope.data.end_time = new Date(Date.parse($scope.data.params.end_time));
		}

		// 监听页码的变化
		$scope.$watch('data.params.page_no', function() {
	        getList($scope.data.params);
	    });

		structureExportUrl();
	}

	// 获取其他产品列表
	function getList(params) {
		productTypeModel.otherList(params, function(list, total, pageNo, pageNum) {
			$scope.list = [];
			if (list) {
				$scope.list = list;
			}
			$scope.data.paginationConf.totalItems = total;
		}, function(errorCode, errorMsg) {
			$scope.list = [];
			$scope.data.paginationConf.totalItems = 0;
			console.log('errorCode: ' + errorCode + '\nerrorMsg: ' + errorMsg);
		});
	}

});
