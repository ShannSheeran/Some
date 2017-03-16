angular.module('Demo')
    .directive('fileModel', ['$parse', function ($parse) {
        return {
            restrict: 'A',
            link: function(scope, element, attrs, ngModel) {
                var model = $parse(attrs.fileModel);
                var modelSetter = model.assign;
                scope.fileInfo = '';
                element.bind('change', function(event) {
                    var files = event.target.files;
                    var reader = new FileReader();
                    reader.readAsDataURL(files[0]);
                    reader.onloadend = function(e) {
                        scope.fileInfo = e.target.result;
                    };
                    scope.$apply(function() {
                        modelSetter(scope, files[0]);
                    });
                    //附件预览
                    scope.file = (event.srcElement || event.target).files[0];
                });
            }
        };
    }]);

angular.module('Demo')
    .directive('filesTxt', ['$parse', function ($parse) {
        return {
            restrict: 'A',
            link: function(scope, element, attrs, ngModel) {
                var model = $parse(attrs.filesTxt);
                var modelSetter = model.assign;
                element.bind('change', function(event){
                    scope.$apply(function(){
                        modelSetter(scope, element[0].files[0]);
                    });
                    //附件预览
                    scope.file = (event.srcElement || event.target).files[0];
                    scope.getFiles(scope.file);
                });
            }
        };
    }]);

//编辑框指令
angular.module('Demo').directive('editorDirective', function(){
    return {
        link: function(scope, elm, attrs) {
            elm.bind('blur', function() {
                scope._html = elm.html();
                //console.log(scope._html);
            });
        }
    };
});

//图例上传图片
angular.module('Demo')
    .directive('fileModellist', ['$parse', function ($parse) {
        return {
            restrict: 'A',
            link: function(scope, element, attrs, ngModel) {
                var model = $parse(attrs.fileModel);
                var modelSetter = model.assign;
                scope.fileInfo = '';
                element.bind('change', function(event) {
                    var files = event.target.files;
                    var reader = new FileReader();
                    reader.readAsDataURL(files[0]);
                    reader.onloadend = function(e) {
                        scope.fileInfo = e.target.result;
                        element.prev().attr('src',scope.fileInfo);
                        // console.log(element.parents('.imgList').index());
                        // console.log(scope.fileInfo);
                    };
                    // scope.$apply(function() {
                    //   modelSetter(scope, files[0]);
                    // });
                    //附件预览
                    scope.file = (event.srcElement || event.target).files[0];
                });
            }
        };
    }]);