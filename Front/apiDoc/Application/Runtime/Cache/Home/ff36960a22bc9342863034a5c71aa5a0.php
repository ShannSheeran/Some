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
<link href="/apidoc/Public/highlight/default.min.css" rel="stylesheet"> 
<link href="/apidoc/Public/lightbox/css/lightbox.css?v=1.1234567" rel="stylesheet"> 
<link rel="stylesheet" href="/apidoc/Public/css/item/show_single_page.css" />

<div id="header">

</div>
<div class="container doc-container">
   <div class="doc-title-box">
      <span id="doc-title-span" class="dn"></span>
      <h3 id="doc-title"><?php echo ($page["page_title"]); ?></h3>
      <ul class="tool-bar inline pull-right">
        <li><a href="#" id="share">分享</a></li> 
        <?php if($ItemPermn): ?><li><a href="?s=/home/page/edit/page_id/<?php echo ($page["page_id"]); ?>">编辑</a></li>
        <li>
            <div class="btn-group ">
              <a class="btn btn-link dropdown-toggle" data-toggle="dropdown" href="#">
                <?php echo (L("item")); ?>
                <span class="caret"></span>
              </a>
            <ul class="dropdown-menu">
            <!-- dropdown menu links -->
               <li><a href="<?php echo U('Home/Item/word',array('item_id'=>$item['item_id']));?>"><?php echo (L("export")); ?></a></li>
               <?php if($ItemCreator): ?><li><a href="<?php echo U('Home/Item/add',array('item_id'=>$item['item_id']));?>"><?php echo (L("update_info")); ?></a></li>          
                <li><a href="<?php echo U('Home/Member/edit',array('item_id'=>$item['item_id']));?>"><?php echo (L("manage_members")); ?></a></li>
                <li><a href="<?php echo U('Home/Attorn/index',array('item_id'=>$item['item_id']));?>"><?php echo (L("attorn")); ?></a></li>
                <li><a href="<?php echo U('Home/Item/delete',array('item_id'=>$item['item_id']));?>"><?php echo (L("delete")); ?></a></li><?php endif; ?>
              <li><a href="<?php echo U('Home/Item/index');?>" ><?php echo (L("goback")); ?></a></li>
            </ul>
        </li>
        <?php else: endif; ?>

      </ul>

      

  </div>
  <div id="doc-body" >

  <div id="page_md_content" ><textarea style="display:none;"><?php echo ($page["page_content"]); ?></textarea></div>

    </textarea>
  </div>

</div>
  <div id="footer">
    <?php if(! $login_user): ?><div id="copyright-text">本页面使用<a href="https://www.showdoc.cc/">showdoc</a>编写<?php endif; ?>
    </div>
  </div>

<!-- 分享项目框 -->
<div class="modal hide fade" id="share-modal">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3><?php echo (L("share")); ?></h3>
  </div>
  <div class="modal-body">
  <div class="modal-body" style="text-align: center;">
    <p><?php echo (L("item_address")); ?>：<code id="share-item-link"><?php echo ($share_url); ?></code>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" id="copy-item-link">复制链接</a>
  </p>
    <p style="border-bottom: 1px solid #eee;"><img  alt="二维码" style="width:114px;height:114px;" src="?s=home/common/qrcode&size=3&url=<?php echo ($share_url); ?>"> </p>
  </div>

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

 <script src="//cdn.staticfile.org/highlight.js/9.0.0/highlight.min.js"></script> 
 <script src="/apidoc/Public/editor.md/lib/marked.min.js"></script>
 <script src="/apidoc/Public/editor.md/lib/prettify.min.js"></script>
 <script src="/apidoc/Public/editor.md/lib/flowchart.min.js"></script>
 <script src="/apidoc/Public/editor.md/lib/raphael.min.js"></script>
 <script src="/apidoc/Public/editor.md/lib/underscore.min.js"></script>
 <script src="/apidoc/Public/editor.md/lib/sequence-diagram.min.js"></script>
 <script src="/apidoc/Public/editor.md/lib/jquery.flowchart.min.js"></script>
 <script src="/apidoc/Public/editor.md/editormd.min.js"></script>
 <script src="/apidoc/Public/js/jquery.goup.min.js"></script>
 <script src="/apidoc/Public/lightbox/js/lightbox.js?a=abc"></script>
<script src="/apidoc/Public/jquery.zclip/jquery.zclip.js"></script>
<script src="/apidoc/Public/js/jquery.bootstrap-growl.min.js"></script>
<script src="/apidoc/Public/js/item/show_single_page.js?a=ab"></script>