<!doctype html>
<html lang="zh-cn">
<head>
<meta charset="UTF-8">
<meta name="Keywords" content="购买成功" />
<meta name="Description" content="购买成功" />
<title>购买成功</title>
<meta name="viewport"
	content="width=device-width, initial-scale=1, maximum-scale=1,user-scalable=ture">
<link rel="stylesheet" type="text/css" href="css/style.css" />
<script src="js/jquery.min.js"></script>
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
$url = INDEX_HTTP . '/weixin/success.html';
$params = array(
    'open_id' => $openId
);
$data = urlExec($url, $params, 'post');
$list = json_decode($data, true);
?>
<div style="width:80%; margin:10px 0 0 0; padding:8px 10%; background-color: #FFF;">
<div style="background:url(images/img01.png) no-repeat;background-size:20px;display:inline-block;padding:0 0 0 24px;height:20px;line-height:20px;">恭喜您购买成功！</div>
<p>您已免费获得由我们送出的8个K码！凭K码购买产品立省30元，每激活一个K码返现50元！</p>
<?php if ($list) {?>
<br />
<p>K码：</p>
<?php foreach ($list as $value) {?>
<p><?php echo $value['code'];?></p>
<?php }?>
<br />
<p>温馨提示：请保存，您还可以下载【快摇名片】APP查看。</p>
<?php }?>
</div>
</body>
</html>