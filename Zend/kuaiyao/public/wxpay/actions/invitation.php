
<!doctype html>
<html lang="zh-cn">
<head>
<meta charset="UTF-8">
<meta name="Keywords" content="购买快摇名片" />
<meta name="Description" content="购买快摇名片" />
<title>推荐码列表</title>
<meta name="viewport"
	content="width=device-width, initial-scale=1, maximum-scale=1,user-scalable=ture">
<link rel="stylesheet" type="text/css" href="css/style.css" />
<script src="js/jquery.min.js"></script>
<script src="js/jquery.lightbox_me.js"></script>
<style type="text/css">
	.kaweb{
		width: 100%;
		height: 100px;
	}
	.kaweb_main{
		padding: 10% 1% 2% 1%;
	}
	.kaweb_main h4{
		text-align: center;
	}
	.kaweb_box{
		width: 100%;
		margin: 0px auto;
		padding-top: 33px;
	}
	.kaweb_box h6{
		padding-bottom: 12px;
	}
	.kaweb_box ul{
		clear: both;

	}
	.kaweb_box ul li{
		float: left;
		list-style: none;
		margin-left: 3%;
		margin-bottom: 3%
	}
	.kaweb_box ul li p{
		font-size: 12px;
        width: 100px;
        height: 30px;
        border: 1px solid blue;
        line-height: 28px;
        text-align: center;
        color: #666;

	}
	.kaweb_box ul li p span{
		/*color: #2eaffe;*/
	}
	.wei{
		color: #2eaffe;
	}
	.yi{
		color: #ccc;
	}
</style>
<?php /*
<script src="err/isWX.js"></script>
*/?>
</head>
<body>
<?php 
require_once 'config.php';
?>
    <div class="kaweb">
        <div class="kaweb_main">
		<h4>推荐码列表</h4>
		<div class="kaweb_box">
		<h6>我的推荐码</h6>
       <!--  <div class="kaweb_main">
		<h4>推荐码列表</h4>
		<div class="kaweb_box"> -->
			<ul class="lists">
				
			</ul>
		</div>
		</div>

	</div>
	
	
	<div id="user_id" class="sign_k">
		<h2>绑定手机</h2>
		<p>
			<input id="mobile" name="mobile" type="text" class="text"
				placeholder="手机号码" /> <br />
			<input id="mobile_code" name="mobile_code" type="text" class="text" style="margin:4px 0 0 10%; width:35%;float:left;display:inline-block;"
				placeholder="验证码" /><input style="height:40px;line-height:40px;width:33%;boder:1px solid #999;background:#ccc;color:#333;margin:4px 10% 0 0;float:right;display:inline-block;" type="button" class="submit hq_code_cur" onclick="smscode(1)" value="获取验证码" id='button_code' />
		</p>
		<dl>
			<dd style="border-right: 1px solid #b2b2b6">
				<input type="button" class="submit close" value="确认"
					onclick="smscode(2)" />
			</dd>
			<dd>
				<input type="button" class="close" value="取消" onclick="close(this)" />
			</dd>
		</dl>
    </div>
</body>
<script type="text/javascript">
var open_id;
var user_id;
<?php 
require_once "WxPay.JsApiPay.php";
$tools = new JsApiPay();
//$openId = "dfskfjdskljfkldsjfkl";
$openId = $tools->GetOpenid();

// 检查用户是否已经绑定手机
$url = INDEX_HTTP . '/weixin/checkOpenId.html';
$params = array(
    'open_id' => $openId
);
$user_id = urlExec($url, $params, 'post');

if (! $user_id) {
    echo "showBindMobile();";
}
else {
    echo "user_id = '".$user_id."';";
    echo 'showInvitation();';
}
echo "open_id = '".$openId."';";
?>

function showBindMobile() {
	$("#user_id").lightbox_me({centered: true, preventScroll: true, onLoad: function() {
		//$("#user_id").find("input:first").focus();
	}});
}

function bindMobile() {
	var url = "<?php echo INDEX_HTTP;?>/weixin/bindMobile.html";
	var mobile = $("#mobile").val();
	$.post(url, {user_id : user_id} ,function(data) {
		user_id = data;
		showInvitation();
		}, "text");
}

function showInvitation()
{
	var url = "<?php echo INDEX_HTTP;?>/weixin/showInvitation.html";
	$.post(url, {user_id : user_id,} ,function(data){
    		//for(idata.data.length
    		for(i in data.data) {
    			var item = data.data[i];
    			if(item.status==0){
    			    $(".lists").append('<li><p>'+item.code+'<span class="wei">(未使用)</span></p></li>');
    			}else if(item.status==1){
    				$(".lists").append('<li><p>'+item.code+'<span class="yi">(已使用)</span></p></li>');    
    			}
    		}
		}, "json");
}

function smscode(action)
{
	if (action != 1 && action != 2) {
		alert("请求参数不完整");
		return false;
	}
	// 短信验证码
	var mobile = $("#mobile").val();
	var code = $("#mobile_code").val();
	if(mobile=="") {
	    alert("手机号码不能为空");
	    return false;
	}
	if (mobile.length != 11) {
		alert("输入正确的手机号码");
		return false;
	}
	$.post("<?php echo INDEX_HTTP . '/weixin/smscode.html'?>", {mobile : mobile, action : action, code : code},
			function(data){
		if (action == 1) {
			if(data.status==0) {
			    setTimeout("startCounts(60)", 1000 );
			}
			else {
				alert(data.msg);
				return false;
			}
		}
		else if (action == 2) {
			if(data.status==0) {
			    bindMobile()
			}
			else {
				showBindMobile();
				alert(data.msg);
				return false;
			}
		}
	},"json");
	return true;
}

var time1;
function startCounts(num) {
    document.getElementById('button_code').value= num + '秒';
    document.getElementById('button_code').disabled=true;
    num=num-1;
    if(num<=0)
    {
    	document.getElementById('button_code').value='重新获取验证码';
 	    document.getElementById('button_code').disabled=false;
  	    clearTimeout(i1);
  	    return false;
    }
    time1 = setTimeout("startCounts("+num+")",1000);
}
</script>
</html>

