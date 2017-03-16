/**
* @Author: Kongho
* @Date:   2017-01-20 11:54:35
* @Email:  kongho@3ncto.com
* @Filename: pavilion-model.js
* @Last modified by:   Kongho
* @Last modified time: 2017-01-22 22:15:07
* @Copyright: 3NCTO Co., Ltd.
*/



app.service('pavilionModel', function(baseModel, apiConfig, $rootScope){
    // 获取展会产品类型绑定列表
    this.productTypeList = function(params, successCallback, failCallback) {
        var url = baseModel.structureApiUrl(apiConfig.apiPavilion.productTypeList).replace(':id', params.pavilionId);
        baseModel.request(url, 'GET', params,
            function(data, errorCode, errorMsg) {
                if (errorCode == apiConfig.errorCode.success) {
                    successCallback(data.data_list, data.count, data.page_no, data.page_num);
                } else {
                    failCallback(errorCode, errorMsg);
                }
            });
    }

	// 展会产品类型绑定
    this.productTypeAdd = function(params, successCallback, failCallback) {
        var url = baseModel.structureApiUrl(apiConfig.apiPavilion.productTypeAdd).replace(':id', params.pavilionId);
        baseModel.request(url, 'POST', params,
            function(data, errorCode, errorMsg) {
                if (errorCode == apiConfig.errorCode.success) {
                    successCallback(true);
                } else {
                    failCallback(errorCode, errorMsg);
                }
            });
    }

	// 展会产品类型绑定删除
    this.productTypeDelete = function(params, successCallback, failCallback) {
        var url = baseModel.structureApiUrl(apiConfig.apiPavilion.productTypeDelete).replace(':id', params.pavilionId);
        baseModel.request(url, 'POST', params,
            function(data, errorCode, errorMsg) {
                if (errorCode == apiConfig.errorCode.success) {
                    successCallback(true);
                } else {
                    failCallback(errorCode, errorMsg);
                }
            });
    }
});
