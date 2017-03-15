<!doctype html>
<html lang="zh-cn">
<head>
<meta charset="UTF-8">
<meta name="Keywords" content="购买记录" />
<meta name="Description" content="购买记录" />
<title>购买记录</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1,user-scalable=ture">
<link rel="stylesheet" type="text/css" href="css/style.css" />
<script src="js/jquery.min.js"></script>
<script src="js/jquery.lightbox_me.js"></script>
<script src="err/isWX.js"></script>
</head>
<body>
<?php 
// ini_set('display_errors','On');
// error_reporting(E_ALL);
include_once 'config.php';
//①、获取用户openid
$openId = isset($_GET['open_id']) ? trim($_GET['open_id']) : '';
if (! $openId) {
    require_once "WxPay.JsApiPay.php";
    $tools = new JsApiPay();
    $openId = $tools->GetOpenid();
}

$url = INDEX_HTTP . '/weixin/order.html';
$params = array(
    'open_id' => $openId
);
$data = urlExec($url, $params, 'post');
$list = json_decode($data, true);
?>

<?php $status = array(1=>'待付款', 2=>'待发货', 3=>'已发货',4 =>'已收货');?>
<?php if ($list) {?>
<?php foreach ($list as $value) {?>
<div class="list">
  <p class="date"><span><?php echo $status[$value['status']];?></span>下单日期　<?php echo $value['timestamp'];?><br>订单编号　<?php echo $value['order_sn'];?></p>
  <dl>
     <dt><img src="images/pic02.png" width="80"></dt>
     <dd>
       <h2>快摇名片随身设备</h2>
       <p>价格：<?php echo $value['total'];?></p>
     </dd>
  </dl>
  <p class="num">总金额：<font><?php echo $value['total'];?></font> 元</p>
</div>
<?php }?>
<?php }?>
</body>
</html>
