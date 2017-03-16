app.controller('userSystemMessageEditCtrl', function($scope, userModel, $rootScope) {
	// console.log('userSystemMessageEditCtrl');
	$scope.state = false;
	$scope.Statefalse = function(){
		$scope.state = false;
	}
	$scope.Statetrue = function(){
		$scope.state = true;
	}
	$scope.simpleConfig = {
            //这里可以选择自己需要的工具按钮名称,此处仅选择如下五个
            toolbars:[['source', '|', 'undo', 'redo', '|',
            'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat',
            'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 
            'insertunorderedlist', 'selectall', 'cleardoc', '|',
            'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
            'directionalityltr', 'directionalityrtl', 'indent', '|',
            'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
            'emotion','|',
            'horizontal', 'date', 'time', 'spechars', '|',
            'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
            'print', 'searchreplace', 'help']],
            //focus时自动清空初始化时的内容
            autoClearinitialContent:true,
            //关闭字数统计
            wordCount:true,
            //关闭elementPath
            elementPathEnabled:false,
            //编辑器获得焦点
            focus:true
      }
});