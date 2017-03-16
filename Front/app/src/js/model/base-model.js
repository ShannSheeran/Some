/**
 * @Author: Kongho
 * @Date:   2017-01-18 12:12:17
 * @Email:  kongho@3ncto.com
 * @Filename: base-model.js
* @Last modified by:   Kongho
* @Last modified time: 2017-01-21 23:17:45
 * @Copyright: 3NCTO Co., Ltd.
 */



app.service('baseModel', function($http, configs) {

    /**
     * 构建接口请求url
     * @param  {string} path 接口地址
     * @return {string}      构建后的完整接口url
     */
    this.structureApiUrl = function(path) {
        var url = configs.api.protocol + '://' + configs.api.host + configs.api.basePath + path
        return url;
    }

    /**
     * 通用调用接口方法
     * @param  {string} url             	接口url
     * @param  {string} method          	请求方法
     * @param  {object} params            	请求参数JS对象
     * @param  {function} successCallback 	请求成功回调
     * @return {void}
     */
    this.request = function(url, method, params, successCallback) {
        console.warn('[api]request url: ' + url);
        console.warn('[api]params: ');
        console.warn(params);
        if (localStorage.getItem('token')) {
            url += '?token=' + localStorage.getItem('token');
        }
        if (method == 'GET') {
            $http.get(url, {
                    params: params
                })
                .then(function(res) {
                    handleResponse(res, function(data, errorCode, errorMsg) {
                        successCallback(data, errorCode, errorMsg);
                    });
                });
        } else if (method == 'POST') {
            $http.post(url, params)
                .then(function(res) {
                    handleResponse(res, function(data, errorCode, errorMsg) {
                        successCallback(data, errorCode, errorMsg);
                    });
                });
        } else if (method == 'PUT') {
            $http.put(url, params)
                .then(function(res) {
                    handleResponse(res, function(data, errorCode, errorMsg) {
                        successCallback(data, errorCode, errorMsg);
                    });
                });
        } else if (method == 'DELETE') {
            $http.delete(url, {
                    data: params
                })
                .then(function(res) {
                    handleResponse(res, function(data, errorCode, errorMsg) {
                        successCallback(data, errorCode, errorMsg);
                    });
                });
        }
    }

    /**
     * 接口请求返回处理
     * @param  {object} res            接口请求返回对象
     * @param  {function} handleCallback 处理回调
     * @return {void}
     */
    var handleResponse = function(res, handleCallback) {
        var errorCode = res.data.error_code;
        var errorMsg = res.data.error_msg;
        var data = res.data.data;

        console.warn('[api]errorCode: ' + errorCode + '\n[api]errorMsg: ' + errorMsg);
        console.warn('[api]data: ');
        console.warn(data);

        handleCallback(data, errorCode, errorMsg);
    }
})
