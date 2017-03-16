<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo ($item["item_name"]); ?> ShowDoc</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="/apidoc/Public/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/apidoc/Public/css/showdoc.css" rel="stylesheet">
      <script type="text/javascript">
      var DocConfig = {
          host: window.location.origin,
          app: "<?php echo U('/');?>",
          pubile:"/apidoc/Public",
      }

      DocConfig.hostUrl = DocConfig.host + "/" + DocConfig.app;
      </script>
      <script src="/apidoc/Public/js/lang.<?php echo LANG_SET;?>.js?v=21"></script>
  </head>
  <body>
<link rel="stylesheet" href="/apidoc/Public/css/login.css" />
<style type="text/css">
.choose_type{
    margin-bottom: 20px;
    text-align: center;
}
#choose_item{
    width: 100%;
}
</style>
    <div class="container">

      <form class="form-signin" method="post">
        <input type="hidden" id="item_id" name="item_id" value="<?php echo ($item["item_id"]); ?>">
        <!-- <h3 class="form-signin-heading">新建项目</h3> -->
        <div class="choose_type">
            <label class="radio inline">
              <input type="radio" name="item_type" id="item_type1" value="1" <?php if(!$item['item_type'] || $item['item_type'] == 1 )echo 'checked'?> >
              常规项目
            </label>
            <label class="radio inline">
              <input type="radio" name="item_type" id="item_type2" value="2" <?php if($item['item_type'] == 2 )echo 'checked'?>>
              单页项目
              &nbsp;
              <a href="https://www.showdoc.cc/page/65391" target="_blank"><i class="icon-question-sign"></i></a>

            </label>
        </div>


        <input type="text" class="input-block-level" id="item_name" name="item_name" placeholder="<?php echo (L("item_name")); ?>" autocomplete="off" value="<?php echo ($item["item_name"]); ?>" >
        <input type="text" class="input-block-level" id="item_description" name="item_description" placeholder="<?php echo (L("item_description")); ?>" autocomplete="off" value="<?php echo ($item["item_description"]); ?>">
        <input type="text" class="input-block-level"  name="item_domain" placeholder="<?php echo (L("item_domain")); ?>" autocomplete="off" value="<?php echo ($item["item_domain"]); ?>" >
        <input style="display:none"><!-- for disable autocomplete on chrome -->
        <input style="display:none"><!-- for disable autocomplete on chrome -->
        <input type="text" onfocus="this.type='password'" id="password" class="input-block-level" name="password" placeholder="<?php echo (L("visit_password_placeholder")); ?>" title="<?php echo (L("visit_password_placeholder")); ?>" autocomplete="off" value="<?php echo ($item["password"]); ?>">
        <label class="checkbox">
            <input type="checkbox" id="show_copy"> 复制已存在项目
        </label>
        <div >
            <select id="choose_item" name="copy_item_id">

            </select>
        </div>
        <br>
        <button class="btn  btn-primary" type="submit"><?php echo (L("submit")); ?></button>
        <a href="javascript:history.go(-1)" class="btn"><?php echo (L("goback")); ?></a>
      </form>

    </div> <!-- /container -->


    
	<script src="/apidoc/Public/js/common/jquery.min.js"></script>
    <script src="/apidoc/Public/bootstrap/js/bootstrap.min.js"></script>
    <script src="/apidoc/Public/js/common/showdoc.js?v=1.1"></script>
    <div style="display:none">
    	<?php echo C("STATS_CODE");?>
    </div>
  </body>
</html> 

 <script type="text/javascript">
 var password = $("#password").val();
 if (password) {
    $("#password").val('');
    $("#password").attr('type','password');
    $("#password").val(password);  
 };

$("#choose_item").hide();

//如果是编辑项目，则禁用复制项目功能
if ($("#item_id").val()) {
    $("#show_copy").parent().hide();
    $(".choose_type").html("");
};

$("#show_copy").change(function(){
    if ($("#show_copy").is(':checked')) {
        $("#choose_item").show();
        $("#item_type1").attr("disabled","disabled");
        $("#item_type2").attr("disabled","disabled");
        $("#item_type1").removeAttr("checked");
        $("#item_type2").removeAttr("checked");
    }else{
        $("#choose_item").hide();
        $("#item_type1").removeAttr("disabled");
        $("#item_type2").removeAttr("disabled");
        $("#item_name").val("");
        $("#item_description").val('');
        $("#password").val('');
    }
    get_item_list();
});


function get_item_list(){
    //获取已有项目列表
    $.get(
            "?s=/home/item/itemList",
            {},
            function(data){
             if (data.error_code === 0) {
                var json = data.data ;
                var html = '<option>请选择</option>';
                for (var i = 0; i < json.length; i++) {
                    html += '<option value="'+json[i].item_id+'" item_description="'+json[i].item_description+'" password="'+json[i].password+'" >'+json[i].item_name+'</option>';
                };
                $("#choose_item").html(html);

             };
            },
            "json"
        );    
}


//当用户选择了某个复制项目，则填充信息
$("#choose_item").change(function(){
    var a = $(this).find("option:selected");
    var item_name = a.text();
    var item_description = a.attr("item_description");
    var password = a.attr("password");
    $("#item_name").val(item_name+"--copy");
    $("#item_description").val(item_description);
    $("#password").val(password);

});
 </script>