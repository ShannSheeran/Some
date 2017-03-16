/**
* @Author: Kongho
* @Date:   2017-01-20 11:54:35
* @Email:  kongho@3ncto.com
* @Filename: product-type-model.js
* @Last modified by:   Kongho
* @Last modified time: 2017-01-22 22:15:13
* @Copyright: 3NCTO Co., Ltd.
*/



app.service('productTypeModel', function(baseModel, apiConfig, $rootScope) {
	// 获取产品类型列表
    this.list = function(params, successCallback, failCallback) {
        var url = baseModel.structureApiUrl(apiConfig.apiProductType.list);
		params.from = 2;
        baseModel.request(url, 'GET', params,
            function(data, errorCode, errorMsg) {
                if (errorCode == apiConfig.errorCode.success) {
                    successCallback(data.data_list, data.count, data.page_no, data.page_num);
                } else {
                    failCallback(errorCode, errorMsg);
                }
            });
    }

    // 产品类型详情
	this.detail = function(params, successCallback, failCallback) {
        var url = baseModel.structureApiUrl(apiConfig.apiProductType.detail) + '/' + params.productTypeId;
        baseModel.request(url, 'GET', params,
            function(data, errorCode, errorMsg) {
                if (errorCode == apiConfig.errorCode.success) {
                    successCallback(data);
                } else {
                    failCallback(errorCode, errorMsg);
                }
            });
    }

	// 产品类型新增
	this.add = function(params, successCallback, failCallback) {
        var url = baseModel.structureApiUrl(apiConfig.apiProductType.add);
        baseModel.request(url, 'POST', params,
            function(data, errorCode, errorMsg) {
                if (errorCode == apiConfig.errorCode.success) {
                    successCallback(true);
                } else {
                    failCallback(errorCode, errorMsg);
                }
            });
    }

	// 产品类型更新
	this.edit = function(params, successCallback, failCallback) {
        var url = baseModel.structureApiUrl(apiConfig.apiProductType.edit) + '/' + params.productTypeId;
        baseModel.request(url, 'PUT', params,
            function(data, errorCode, errorMsg) {
                if (errorCode == apiConfig.errorCode.success) {
                    successCallback(true);
                } else {
                    failCallback(errorCode, errorMsg);
                }
            });
    }

	// 产品类型更新
	this.delete = function(params, successCallback, failCallback) {
        var url = baseModel.structureApiUrl(apiConfig.apiProductType.delete) + '/' + params.productTypeId;
        baseModel.request(url, 'DELETE', params,
            function(data, errorCode, errorMsg) {
                if (errorCode == apiConfig.errorCode.success) {
                    successCallback(true);
                } else {
                    failCallback(errorCode, errorMsg);
                }
            });
    }

    // 获取其他产品列表
    this.otherList = function(params, successCallback, failCallback) {
        var url = baseModel.structureApiUrl(apiConfig.apiProductType.otherList);
        baseModel.request(url, 'GET', params,
            function(data, errorCode, errorMsg) {
                if (errorCode == apiConfig.errorCode.success) {
                    successCallback(data.data_list, data.count, data.page_no, data.page_num);
                } else {
                    failCallback(errorCode, errorMsg);
                }
            });
    }
});
