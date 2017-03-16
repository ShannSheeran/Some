/**
 * @Author: Kongho
 * @Date:   2017-01-21 18:49:29
 * @Email:  kongho@3ncto.com
 * @Filename: account-model.js
 * @Last modified by:   Kongho
 * @Last modified time: 2017-01-21 20:30:39
 * @Copyright: 3NCTO Co., Ltd.
 */



app.service('accountModel', function(baseModel, apiConfig, $rootScope, $cookies, configs) {
    // 登录
    this.login = function(params, successCallback, failCallback) {
        var url = apiConfig.apiAccount.login;
        localStorage.removeItem('token');
        baseModel.request(url, 'GET', params,
            function(data, errorCode, errorMsg) {
                if (errorCode == apiConfig.errorCode.success) {
                    localStorage.setItem('token', data.token);
                    successCallback(data.token);
                } else {
                    failCallback(errorCode, errorMsg);
                }
            });
    }

    // 检查登录状态
    this.checkLogin = function() {
        var token = $cookies.get('aToken');
        // 若未登录，则跳转至旧后台登录页
        if (!token) {
            console.warn('当前未登录');
            window.location.href = configs.adminV1.basePath + configs.adminV1.login;
        } else {
            localStorage.setItem('token', token);
        }
    }
});
