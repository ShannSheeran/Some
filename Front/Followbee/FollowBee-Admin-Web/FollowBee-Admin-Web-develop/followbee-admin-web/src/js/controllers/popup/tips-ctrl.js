
/**
 *   在需要弹窗的地方加上这个open事件开启弹窗
        var modalInstance = $uibModal.open({
            templateUrl: 'tpl/popup/tips.html',
            controller: 'tipsCtrl',
            resolve: {
                _scope: function() {
                    return {
                        title: '温馨提示',
                        tips: "删除后不可恢复还望三思，一定要删吗？",
                        cancelBtn: true,
                        sureCallback: $scope.successCallback,
                        cancelCallback: $scope.failCallback
                    }
                }
            }
        });
 * [description]
 * @param   templateUrl      弹窗页面路径，固定的，无需修改
 * @param   controller       弹窗所用的控制器，固定的，无需修改
 * @param   _scope           传给弹窗的对象，固定的，无需修改
 * @param   title            弹窗标题，可修改，默认为'提示'
 * @param   tips             弹窗提示文字，可修改
 * @param   cancelBtn        取消按钮，true或false，true为显示取消按钮，false为不显示取消按钮,可不传，不传填默认为true
 * @param   sureCallback     确定按钮回调函数, 必须传        
 * @param   cancelCallback    取消按钮回调函数，可不传
 *  同时该控制器需要依赖注入 $uibModal
 *  全局已引入这个控制器，故不必重复引入      
 */
app.controller('tipsCtrl', function($scope, $uibModalInstance, _scope) {

        $scope.title = _scope.title;
        $scope.msg = _scope.tips;
        if (_scope.cancelBtn == false) {
            $scope.close_show = false;
        } else {
            $scope.close_show  = true;
        }

        // 单纯关闭弹窗
        $scope.close = function() {
            $uibModalInstance.dismiss('cancel');
        }

        // 取消按钮回调
        $scope.cancel = function() {  
             _scope.cancelCallback();          
            $uibModalInstance.dismiss('cancel');
        };
        // 确定按钮回调
        $scope.ok = function() {
            _scope.sureCallback();
            $uibModalInstance.dismiss('cancel');
        }
    }
);