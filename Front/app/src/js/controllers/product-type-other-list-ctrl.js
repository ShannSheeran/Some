/**
* @Author: Kongho
* @Date:   2017-01-21 15:37:48
* @Email:  kongho@3ncto.com
* @Filename: product-type-other-list-ctrl.js
* @Last modified by:   Kongho
* @Last modified time: 2017-02-18 15:57:22
* @Copyright: 3NCTO Co., Ltd.
*/



app.controller('productTypeOtherListCtrl', function($scope, $rootScope, productTypeModel, accountModel, toastr, $filter, baseModel, apiConfig) {
	// 检查登录状态
	accountModel.checkLogin();

	var vm = $scope;

	// 初始化数据
	function initData() {
		vm.data = {};
		vm.data.params = {};
		vm.data.params.page_no = 1;
		vm.data.params.is_export = 2;

		if (vm.data.params.start_time) {
			vm.data.start_time = new Date(Date.parse(vm.data.params.start_time));
		}
		if (vm.data.params.end_time) {
			vm.data.end_time = new Date(Date.parse(vm.data.params.end_time));
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

		structureExportUrl();
	}
	initData();

    // 时间插件设置
     vm.dateOptions = {
        dateDisabled: false,
        formatYear: 'yyyy',
        startingDay: 0,
        showWeeks: false
    };

	// 搜索
	vm.search = function() {
		console.warn(vm.data.params);
		if (vm.data.start_time || vm.data.end_time) {
			if (!vm.data.start_time) {
				toastr.warning('请选择开始时间');
				return;
			}
			if (!vm.data.end_time) {
				toastr.warning('请选择结束时间');
				return;
			}
			if (vm.data.end_time < vm.data.start_time) {
				toastr.warning('结束时间不能早于开始时间');
				return;
			}

			vm.data.params.start_time = $filter('date')(vm.data.start_time, 'yyyy-MM-dd');
			vm.data.params.end_time = $filter('date')(vm.data.end_time, 'yyyy-MM-dd');
		}
		vm.data.params.is_export = 2;
		if (vm.data.params.page_no != 1) {
            vm.data.params.page_no = 1;
            vm.paginationConf.currentPage = vm.data.params.page_no;
        } else {
            getList(vm.data.params);
        }

		// 更新导出url
		structureExportUrl();

		getList(vm.data.params);
	}

	// 构建导出下载链接
	function structureExportUrl() {
		var url = baseModel.structureApiUrl(apiConfig.apiProductType.export) + '?token=' + localStorage.getItem('token');
		console.warn(url);
		if (vm.data.params) {
			var params = '';
			angular.forEach(vm.data.params, function(value, key) {
				params += '&' + key + '=' + value;
			});
			url += params;
		}
		vm.exportUrl = url;
	}

	// 获取其他产品列表
	function getList(params) {
		productTypeModel.otherList(params, function(list, total, pageNo, pageNum) {
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

});
