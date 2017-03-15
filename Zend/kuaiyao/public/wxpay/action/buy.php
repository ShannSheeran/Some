<!doctype html>
<html lang="zh-cn">
<head>
<meta charset="UTF-8">
<meta name="Keywords" content="购买快摇名片" />
<meta name="Description" content="购买快摇名片" />
<title>购买快摇名片</title>
<meta name="viewport"
	content="width=device-width, initial-scale=1, maximum-scale=1,user-scalable=ture">
<link rel="stylesheet" type="text/css" href="css/style.css" />
<script src="js/jquery.min.js"></script>
<script src="js/jquery.lightbox_me.js"></script>
<script src="js/TouchSlide.1.1.js"></script>
<style type="text/css">
	/* 本例子css -------------------------------------- */
	.focus{ width:100%;  margin:0 auto; position:relative; overflow:hidden;   }
	.focus .hd{ width:100%; position:absolute; z-index:1; bottom:5px; text-align:center;  }
	.focus .hd ul{ display:inline-block; height:5px; padding:3px 5px; /*background-color:rgba(255,255,255,0.7)*/; 
		-webkit-border-radius:5px; -moz-border-radius:5px; border-radius:5px; font-size:0; vertical-align:top;
	}
	.focus .hd ul li{ display:inline-block; width:5px;  height:5px; -webkit-border-radius:5px; -moz-border-radius:5px; border-radius:5px; background:#8C8C8C; margin:0 5px;  vertical-align:top; overflow:hidden;   }
	.focus .hd ul .on{ background:#FE6C9C;  }

	.focus .bd{ position:relative; z-index:0; }
	.focus .bd li img{ width:100%; background:url(images/loading.gif) center center no-repeat;  }
	.focus .bd li a{ -webkit-tap-highlight-color:rgba(0, 0, 0, 0); /* 取消链接高亮 */  }
</style>
<?php /*
<script src="err/isWX.js"></script>
*/?>
</head>
<body>
<?php 
require_once 'config.php';
$url = INDEX_HTTP . '/weixin/orderStat.html';
$data = urlExec($url);
$orderStat = 100 + (int)$data;
?>
<div class="banner">
		<!-- <img src="images/banner01.jpg" width="100%"> -->
			<div id="focus" class="focus">
				<div class="hd">
					<ul></ul>
				</div>
				<div class="bd">
					<ul>
							<li><a href="#"><img _src="../../images/app/02.png" src="../../images/app/02.png" /></a></li>
							<li><a href="#"><img _src="../../images/app/03.png" src="../../images/app/03.png"/></a></li>
							<li><a href="#"><img _src="../../images/app/06.png" src="../../images/app/06.png"/></a></li>
							<li><a href="#"><img _src="../../images/app/15.png" src="../../images/app/15.png"/></a></li>
							<li><a href="#"><img _src="../../images/app/16.png" src="../../images/app/16.png"/></a></li>
					</ul>
				</div>
			</div>
			<script type="text/javascript">
				TouchSlide({ 
					slideCell:"#focus",
					titCell:".hd ul", //开启自动分页 autoPage:true ，此时设置 titCell 为导航元素包裹层
					mainCell:".bd ul", 
					effect:"left", 
					autoPlay:true,//自动播放
					autoPage:true, //自动分页
					switchLoad:"_src" //切换加载，真实图片路径为"_src" 
				});
			</script>
	</div>
	<div class="info">
		<dl>
			<dt>快摇名片随身设备</dt>
			<dd>
				原价：
				<font>1688</font>
				元
			</dd>
			<dd>
				<span>销量：<?php echo $orderStat;?>件</span>
				K码价：
				<font>1388</font>
				元
			</dd>
		</dl>
	</div>
	<div class="btn">
		<ul>
			<li>
				<input type="button" class="submit" value="立即购买" onclick="orderSubmit(1)">
			</li>
			<li>
				<input type="button" class="submit submit_k" value="使用K码购买">
			</li>
		</ul>
	</div>
	<div class="txt">
		<dl>
			<dt>怎样获取k码？</dt>
			<dd>每购买一款快摇名片，就会赠送8个k码，您可以通过已购买的朋友中获取。</dd>
		</dl>
	</div>

	<div id="sign_k" class="sign_k">
		<h2>验证k码</h2>
		<p>
			<input id="code" name="code" type="text" class="text"
				placeholder="请输入K码" />
		    <span class="text" id="code_msg" style="color:red;display:block;width:100%;text-align:center;"></span>
		</p>
		<dl>
			<dd style="border-right: 1px solid #b2b2b6">
				<input type="button" class="submit" value="确认" onclick="orderSubmit(2)" />
			</dd>
			<dd>
				<input type="submit" class="close" value="取消" />
			</dd>
		</dl>
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
var price;
var order_sn;
var page_id;
$(function(){
	$('.submit_k').click(function(e) {
		$("#sign_k").lightbox_me({centered: true, preventScroll: true, onLoad: function() {
			//$("#sign_k").find("input:first").focus();
		}});
		e.preventDefault();
	});

<?php
require_once "WxPay.JsApiPay.php";
$tools = new JsApiPay();
//$openId = "kkkkkkkkkk";
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
}
echo "open_id = '".$openId."';";
?>
});

//调用微信JS api 支付
function jsApiCall()
{
	var url = "<?php echo WXPAY_HTTP . 'jsapi.php'?>";
	// 获取jsApiParameters
	$.post(url,{open_id:open_id, price : price, order_sn : order_sn},function(data) {
		WeixinJSBridge.invoke(
		'getBrandWCPayRequest',
		data,
		function(res){
			WeixinJSBridge.log(res.err_msg);
			// alert(res.err_code+res.err_desc+res.err_msg);
			if (res.err_msg.indexOf("ok") != -1) {
				if (page_id) {
					// 跳已完成页面
					window.location.href = "<?php echo WXPAY_HTTP . 'success.php'?>" + "?open_id="+open_id;
				}
				else {
					// 跳填写名片页面
					window.location.href = "<?php echo INDEX_HTTP . '/admin/usera/add'?>";
				}
			}
		}
	);
	},"json");
}

function callpay()
{
	if (typeof WeixinJSBridge == "undefined"){
	    if( document.addEventListener ){
	        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
	    }else if (document.attachEvent){
	        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
	        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
	    }
	}else{
	    jsApiCall();
	}
}

function orderSubmit(type)
{
	if (! user_id) {
		showBindMobile();
		return false;
	}
	
	var code = "";
	if (type == 2) {
		code = $("#code").val();
		if (! code) {
			$("#code_msg").html("请输入K码");
			return false;
		}
	}
	var url = "<?php echo INDEX_HTTP . '/weixin/orderSubmit.html'?>";
	$.post(url,{user_id:user_id, code:code}, function (data) {
		if (data.status == 1) {
			showBindMobile();
			return false;
		}
		else if (data.status == 2) {
			$("#code_msg").html(data.msg);
			return false;
		}
		else {
			$("#code_msg").html("");
			price = data.price;
			order_sn = data.order_sn;
			page_id = data.page_id;
			callpay();
			return true;
		}
	},"json");
	return false;
}

function showBindMobile() {
	$("#user_id").lightbox_me({centered: true, preventScroll: true, onLoad: function() {
		//$("#user_id").find("input:first").focus();
	}});
}

function bindMobile() {
	var url = "<?php echo INDEX_HTTP;?>/weixin/bindMobile.html";
	var mobile = $("#mobile").val();
	$.post(url, {open_id : open_id, mobile: mobile} ,function(data) {
		user_id = data;
		}, "text");
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