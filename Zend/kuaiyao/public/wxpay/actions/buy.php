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
$url = INDEX_HTTP . '/commodity/orderStat.html';
$data = urlExec($url);
$orderStat = 100 + (int)$data;
?>
<div class="btn">
		<ul>
			<li>
				<input type="button" class="submit" value="立刻支付" onclick="pay()">
			</li>
		</ul>
</div>

</body>
<script type="text/javascript">
var open_id;
var order_sn;


<?php
//$openId = "kkkkkkkkkk";
$openId=$_GET['openId'];
$order_sn=$_GET['order_sn'];
/*echo "user_id = '".$user_id."';";*/
echo "open_id = '".$openId."';";
echo "order_sn = '".$order_sn."';";
/*echo "good_num = '".$good_num."';";
echo "code_num = '".$code_num."';";*/
?>


//调用微信JS api 支付
function jsApiCall()
{
	var url = "<?php echo WXPAY_HTTP . 'jsapi.php'?>";
	// 获取jsApiParameters
	$.post(url,{open_id:open_id,order_sn : order_sn,total:total},function(data) {
		WeixinJSBridge.invoke(
		'getBrandWCPayRequest',
		data,
		function(res){
			WeixinJSBridge.log(res.err_msg);
			// alert(res.err_code+res.err_desc+res.err_msg);
			if (res.err_msg.indexOf("ok") != -1) {
				window.location.href = "<?php echo WXPAY_HTTP . 'PaySuccess.php'?>";
			}else{
				window.location.href = "<?php echo WXPAY_HTTP . 'PaymentFailed.php'?>";
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

function pay(){
	if (! order_sn) {
		return false;
	}
	var url = "<?php echo INDEX_HTTPs . '/commodity/orderInfo.html'?>";
	$.post(url,{order_sn:order_sn}, function (data) {
		if (data.status ==0) {
			alert('请重试');
			return false;
		}
		else if (data.status == 1) {
			alert('order error');
			return false;
		}
		else {
			total = data.total;
			order_sn=data.order_sn;
			callpay();
			return true;
		}
	},"json");

}

</script>
</html>