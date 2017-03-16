/**
 * @Author: Kongho
 * @Date:   2017-01-18 15:34:44
 * @Email:  kongho@3ncto.com
 * @Filename: app-ctrl.js
* @Last modified by:   Kongho
* @Last modified time: 2017-02-20 20:03:24
 * @Copyright: 3NCTO Co., Ltd.
 */



app.controller('appCtrl', function($scope, $uibModal, $rootScope, accountModel) {
	// 检查登录状态
	accountModel.checkLogin();
    
    $rootScope.tipPopup = false;

    $scope.successCallback = function() {
        $rootScope.tipPopup = false;
    }

    /**
     * 直接在控制器依赖注入$rootScope
     * 直接改变$rootScope.tipPopup = true 即会开启消息弹窗
     * 把消息内容赋值给 $rootScope.tipMsg 就可以了
     * 只有确定按钮，点击确定只关闭消息弹窗
     */
    // 全局消息提示弹窗
    $rootScope.$watch('tipPopup', function() {
        if ($rootScope.tipPopup) {
            var modalInstance = $uibModal.open({
                templateUrl: 'tpl/popup/tips.html',
                controller: 'tipsCtrl',
                resolve: {
                    _scope: function() {
                        return {
                            title: '提示',
                            tips: $rootScope.tipMsg,
                            cancelBtn: false,
                            sureCallback: $scope.successCallback
                        }
                    }
                }
            });
        }
    });
});
