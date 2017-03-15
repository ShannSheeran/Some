/**
* @Author: Kongho
* @Date:   2017-01-20 11:54:35
* @Email:  kongho@3ncto.com
* @Filename: data-statistics-model.js
* @Last modified by:   Kongho
* @Last modified time: 2017-01-22 22:17:53
* @Copyright: 3NCTO Co., Ltd.
*/



app.service('statisticsModel', function(baseModel, apiConfig, $rootScope){
	// 获取展会签到数据统计列表
    this.pavilion = function(params, successCallback, failCallback) {
        var url = baseModel.structureApiUrl(apiConfig.apiStatistics.pavilion);
        baseModel.request(url, 'GET', params,
            function(data, errorCode, errorMsg) {
                if (errorCode == apiConfig.errorCode.success) {
                    successCallback(data.data_list, data.count, data.page_no, data.page_num);
                } else {
                    failCallback(errorCode, errorMsg);
                }
            });
    }

	// 获取展商/观众签到数据统计列表
    this.user = function(params, successCallback, failCallback) {
        var url = baseModel.structureApiUrl(apiConfig.apiStatistics.user);
        baseModel.request(url, 'GET', params,
            function(data, errorCode, errorMsg) {
                if (errorCode == apiConfig.errorCode.success) {
                    successCallback(data.data_list, data.count, data.page_no, data.page_num);
                } else {
                    failCallback(errorCode, errorMsg);
                }
            });
    }

	// 获取展商/观众签到数据统计详情
    this.userDetail = function(params, successCallback, failCallback) {
        var url = baseModel.structureApiUrl(apiConfig.apiStatistics.userDetail);
        baseModel.request(url, 'GET', params,
            function(data, errorCode, errorMsg) {
                if (errorCode == apiConfig.errorCode.success) {
                    successCallback(data);
                } else {
                    failCallback(errorCode, errorMsg);
                }
            });
    }
});
