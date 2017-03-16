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
<style type="text/css">
.single-cat{
  margin: 10px;

}
</style>
 <div id="edit-cat" class="modal hide fade">
  <!-- 编辑框 -->
  <div class="cat-edit">
      <div class="modal-header">
      <h4><?php echo (L("new_member")); ?></h4>
      </div>
      <input type="hidden" id="item_id" value="<?php echo ($item_id); ?>">
      <div class="add-cat">
          <form class="form-horizontal">
            <div class="control-group">
              <label class="control-label" for="inputEmail"><?php echo (L("username")); ?></label>
              <div class="controls">
                <input type="text" id="username" placeholder="<?php echo (L("username")); ?>" value="">
              </div>
            </div>
            <div class="control-group">
              <div class="controls">
                <label class="checkbox">
                  <input type="checkbox" id="member_group_id"><?php echo (L("member_group_id")); ?>
                </label>
              </div>
            </div>
            <div class="control-group">
              <div class="controls">
                <button type="submit" class="btn" id="save-cat"><?php echo (L("save")); ?></button>
              </div>
            </div>
          </form>

      </div>
    </div>
  <!-- 成员列表 -->
  <div class="cat-list">
    <div class="modal-header">
    <h4><?php echo (L("member_list")); ?>&nbsp;<small>（<?php echo (L("click_to_delete")); ?>）</small></h4>
    </div>
    <div id="show-cat">

    <br>
    <br>
    </div>
  </div>

    <div class="modal-footer">
      <a href="#" class="btn exist-cat"><?php echo (L("close")); ?></a>
    </div>
 </div>

    
	<script src="/apidoc/Public/js/common/jquery.min.js"></script>
    <script src="/apidoc/Public/bootstrap/js/bootstrap.min.js"></script>
    <script src="/apidoc/Public/js/common/showdoc.js?v=1.1"></script>
    <div style="display:none">
    	<?php echo C("STATS_CODE");?>
    </div>
  </body>
</html> 

<script src="/apidoc/Public/js/member/edit.js?v=12"></script>