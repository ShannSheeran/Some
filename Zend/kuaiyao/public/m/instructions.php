<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="UTF-8">
<meta name="Keywords" content="快摇名片" />
<meta name="Description" content="快摇名片" />
<title>快摇名片</title>
<meta name="viewport" content="width=device-width,inital-scale=1.0,minimum-scale=1.0,maximum-scale=1.0" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-status-bar-style" content="black" />
<meta name="format-detection" content="telephone=no" />
<link rel="stylesheet" type="text/css" href="css/style.css" />
<script type="text/javascript" src="js/jquery.min.js"></script>
<style type="text/css">
.product {
	position:fixed;
	top:50px;
	left: 0;
	display: inline-block;
	width: 100%;
	padding:10px 0;
	border-top:1px solid #eee;
	border-bottom:1px solid #eee;
	background: rgba(255,255,255,0.7);
	z-index: 100;
}
.product li{
	float: left;
	width: 20%;
	margin:0 6.666%;
	height: 20px;
	text-align: center;	
}
.product_current{
	color: #05a3d9;
	border-bottom: 1px solid #05a3d9;
}

.product_details{
	margin:100px 10px 10px 10px;
}
.product_details img{
	width: 100%;
}
.product_details h2{
	color: #333;
	text-align: center;
	font-size: 1.5rem;
	margin-top: 15px;
	margin-bottom: 5px;
}
.product_details  h4{
	color: #333;
	font-size: 1.1rem;
	margin-bottom: 10px;
	margin-top: 25px;
}
.product_details  p{
	color:#555;
}

.product_details  .instructions img{
	margin:20px 0;
	width: 80%;
	margin-left: 10%;
}
.product_details ul{
	margin-top: 25px;
}
.product_details  ul li{
	margin-bottom: 5px;
}
.product_details .p_center{
	text-align: center;
}
.product_details .warranty p{
	text-indent: 2em;
}
.product_details .binding p{
	margin-top:30px;
}
.product_details .binding img{
	width:80%;
	margin:20px 10%;
}
.product_details .binding .small{
	width:40%;
	margin:20px 30%;
}
.hint{
	position: fixed;
	background: rgba(000,000,000,0.5);
	top:0;
	left: 0;
	right: 0;
	bottom: 0;
	display: none;
}
.hint div{
	position:fixed;
	width:76%;
	padding:4%;
	top:35%;
	left: 10%;
	background: #fff;
	border-radius:5px;
}
</style>
</head>

<body>
	<div class="header">
		<div class="logo"><a href="home.html"><img  src="images/logo.png" alt=""/></a></div>
		<span>产品说明</span>
	</div>
	<ul class="product">
		<li class="product_current">使用说明</li>
		<li>绑定指南</li>
		<li>保修协议</li>
	</ul>
	<div class="product_details">	
		<!-- 使用说明 -->
		<div class="instructions">
			<h2>快摇名片™使用说明书</h2>
			<p class="p_center">说明书仅适用于快摇名片™，说明中的图片可能和您手中的实物有些不同</p>
			<h4>准备使用：</h4>
			<p>在使用前，请先花几分钟时间熟悉一下您的设备。</p>
			<img src="images/2_03.jpg" alt="">
			<h4>为设备充电：</h4>
			<p>请使用输出电压为5V的充电器为设备充电，充电式您只需将充电线插入充电口，通电后充电指示灯将变成蓝色长亮。充电完毕后（大约3小时），指示灯熄灭，此时请取下充电器。</p>
			<h4>如何使用：</h4>
			<p>1、启动设备：轻触开关按钮，此时LED状态指示灯亮3S后熄灭，进入开机状态。</p>
			<p>2、开启手机蓝牙</p>
			<p>3、微信摇一摇：进入摇一摇页面，等待几秒，当检测到设备时，会出现临时“周边”，此时摇晃手机，将摇出快摇名片中保存的微名片信息。</p>
			<img src="images/2_06.png" alt="">
			<p>4、浏览名片信息：点击摇到的名片可浏览详细的名片信息，在信息最后面可以将名片信息保存在手机通讯录中，也可关注名片人的微信。</p>
			<p>5、关闭设备：再次轻触开关按钮，此时LED状态指示灯亮0.5S后熄灭，进入关机状态。</p>
			<h4>适用人群：</h4>
			<p>本产品适用于中小企业主、个体户、媒体、金融产品从业者、保险从业者、房地产中介、代理商、经销商、销售人员及有需要的商务人士。</p>
			<h4>注意事项：</h4>
			<p>如果打开了蓝牙，微信摇一摇还没有看到“摇一摇周边”入口，请检查：</p>
			<ul>
				<li>A.可能是微信版本不支持。请确认iOS微信版本是中国大陆5.4及以上版本，Android微信版本是6.0及以上版本，微信繁体中文6.1版本也支持“摇一摇周边”；</li>
				<li>B.可能是地域设置有问题。请确认用户的地区设置在中国（设置-通用-多语言环境-区域格式），海外版暂时没有；</li>
				<li>C.可能是系统版本不支持。请确认iOS手机系统在7.0以上（7.1.2以上会比较稳定，7.1.1偶尔会有bug，需要在手机的“设置-隐私-定位功能”中开关几次微信定位功能，或重启手机）；Android手机系统在4.3以上；</li>
				<li>D.可能是蓝牙被占用。如果正在使用蓝牙耳机、智能手表或其他可穿戴设备，请先关闭。</li>
			</ul>
			<h4>产品参数：</h4>
			<img src="images/2_05.jpg" alt="">
		</div>
		<!-- 绑定指南 -->
		<div class="binding" style="display:none;">
			<h2>快摇名片自助绑定步骤</h2>
			<p>第一步：打开“快摇名片APP”，在“我的”页面【设备状态】，显示：设备未绑定。</p>
			<img class="small" src="images/01.jpg"/>
			<p>第二步：点击“设备未绑定”进入扫二维码绑定界面，扫描设备包装盒附带的二维码后，点击确认绑定</p>
			<img  src="images/02.png"/>
			<p>第三步：绑定成功后跳回“我的”页面，同时在设备状态栏显示未连接，此时绑定已成功；打开设备并处于开启状态，保证设备在十米以内，手机打开蓝牙，在App点击“立即连接”</p>
			<img  src="images/03.png"/>
			<p>最后会提示“连接成功”，在设备状态栏下方显示设备电量</p>
			<img class="small" src="images/04.png"/>
		</div>
		<!-- 保修协议 -->
		<div class="warranty" style="display:none;">
			<h2>快摇名片™ 一（1）年有限保证</h2>
			<p class="p_center">仅适用于快摇品牌产品</p>

			<h4>消费者权益保护法与本保证的关系</h4>

			<p>针对中华人民共和国的消费者：本保证中所述权利作为您根据《中华人民共和国消费者权益保护法》及其他相关消费者保护法律和法规（“适用法律”）可享有的法定权利的补充。您有权就重大故障取得更换或退款，以及就任何其他可合理遇见的损失或损害得到赔偿。如果货品未能达到可以接受的质量，而情况不构成重大故障的，您也有权要求修理或者更换货品。修理货品或会导致数据丢失。提交修理的货品可能以同一类型的翻新货品更换，而非予以修理。修理货品时可能使用翻新零部件。</p>
			<br/>
			<p>为充分理解您的权利，您应当查询所有适用法律。</p>

			<h4>受限于消费者法律的保证限制</h4>

			<p>除非本保证另有明确规定，在适用法律允许的范围内，本保证及上述救济均为排他，且取代所有其他保证和救济，无论是口头、书面、明示或默示。在适用法律允许的范围内，快摇明确拒绝承担任何以及所有法定或默示保证，包括但不限于，对于适销性及特定适用性的保证，以及针对隐蔽或潜在缺陷的保证。如果根据适用法律,快摇不能放弃默示保证或适用法律规定的保证，则所有该等保证应当限于默示保证或适用法律规定保证的范围内，并应根据适用法律强制适用。快摇经销商、代理人或员工均未被授权对本保证做出任何修改、延期或增添。如果本保证任何条款被裁定为违法或不可执行，剩余条款的合法性和可执行性不受影响或损害。</p>
			<br />
			<p>除非本保证明确规定，在适用法律要求的最大范围内，对于因购买或使用产品和/或其附件所造成的直接、特殊、偶发性或衍生性损失，包括但不限于使用损失、收入损失、实际或预期收益损失（包括合同的收益损失）、金钱使用损失、预期节余损失、业务损失、机会损失、商誉损失、名誉损失、数据损坏的损失、损害，或因任何原因造成的间接或衍生性损失或损害（包括设备和财产的更换，恢复、编写或复制快摇产品中存储或使用的任何程序或数据所产生的任何费用，以及未能维持产品中存储信息的保密性），快摇均不承担责任。上述限制不适用于死亡或人身伤害索赔，也不适用于法律规定的因故意或重大过失行为和/或疏忽导致的任何财产损害责任。快摇不做以下声明：其能够维修本保证项下的任何产品或能够更换产品，且不会对程序或数据造成危险或丢失。</p>

			<h4>本保证涵盖的范围是哪些？</h4>

			<p>快摇保证：若按照快摇公布的指南正常使用，自最终使用者购买日期起一（1）年内（“保证期”），原包装中包含的快摇品牌硬件产品及配件（“快摇产品”）不存在材料和工艺缺陷。快摇公布的指南包括但不限于技术说明书、用户手册及服务信息中包含的信息。</p>

			<h4>本保证不涵盖哪些内容？</h4>

			<p>本保证不适用于任何非快摇品牌硬件产品或任何软件（即便与快摇硬件一同包装或出售）。快摇以外的生产商、供应商或出版商可向您提供其各自的保证，请您与其联系以获得进一步的信息。由快摇分销的快摇品牌或非快摇品牌软件（包括但不限于系统软件）不在本保证内，除非该等软件影响快摇产品的正常运行。请阅读软件中所附的许可协议，以详细了解您对软件的使用权利。快摇不保证快摇产品操作时不受干扰或没有错误。对于因未能遵守有关快摇产品使用说明而造成的损害，快摇不承担责任。</p>

			<h4>本保证不适用于：</h4>
			<ul>
				<li>(a)消耗零部件，如电池等随时间推移而耗损的零部件，除非是因材料或工艺缺陷而发生的故障；</li>
				<li>(b)表面损坏，包括但不限于刮痕、凹痕及端口破胶；</li>
				<li>(c)因与其他产品共同使用导致的损害；</li>
				<li>(d)因事故、滥用、误用、接触液体、火灾、地震或其他外部原因导致的损害；</li>
				<li>(e)因未遵守快摇公布的指南操作快摇产品造成的损害；</li>
				<li>(f)因任何快摇代表或快摇授权服务商以外之人提供服务（包括升级和扩展）而造成的损害；</li>
				<li>(g)未经快摇书面许可而进行修改以更改其功能或性能的快摇产品；</li>
				<li>(h)因快摇产品正常磨损或正常老化导致的缺陷.</li>
			</ul>
			<h4>有关快摇维修的重大限制</h4>

			<p>快摇可能仅在快摇或其授权分销商最初销售硬件产品的国家/地区提供针对这些设备的保修服务。</p>

			<h4>您的责任</h4>

			<p>您应该定期备份快摇产品存储媒介中包含的信息，以保护该内容并预防可能的操作故障。</p>
			<br/>
			<p>在接受保修服务前，快摇或其代理人可能要求您提供在零售购买之日向终端用户客户销售时与快摇产品一同提供的保修凭证，回答旨在帮助诊断潜在问题的提问并遵循快摇程序，以获得保修服务。在您提交需要保修服务的快摇产品前，您应该制作一份该设备存储媒介内容的单独备份副本，清除所有您希望保护的个人信息。</p>
			<br/>
			<p>在保修服务期间，存储媒介中的内容将被删除并格式化。对于被维修快摇产品的存储媒介或任何其他部分中包含的任何数据或其他信息的丢失，快摇及其代理人概不负责。</p>
			<br/>
			<p>在保修服务之后，您的快摇产品或替换设备将按照您最初购买快摇产品时的配置返还给您，并可能做出相关更新。</p>
			<br/>
			<p>重要事项：请勿拆开快摇产品。拆开快摇产品可能导致本保证未涵盖的损害。仅快摇或快摇授权服务商方可为本快摇产品提供服务。</p>
			<h4>发生违反本保证情形时快摇会怎样做？</h4>
			<br/>
			<p>受限于适用法律规定的保证和救济，如果您在保修期内向快摇或快摇授权服务商提交有效索赔，快摇将：（i）维修快摇产品，或（ii）更换快摇产品，或（iii）退还您的购买价款，换回此快摇产品。</p>
			<br/>
			<p>快摇可能要求您自己更换用户可自行安装的某些零部件或快摇产品。零部件或快摇产品的更换品，包括用户依照快摇指示已安装好的用户可自行安装的零部件，其保修期为下列期限中的较长者：（i）本保证的剩余期限，（ii）更换或维修之日起九十（90）日，（iii）适用法律规定的期限。在快摇产品或零部件获得更换或退款时，任何更换物品成为您的财产，被更换或退款的物品成为快摇的财产。</p>


			<h4>保修服务选项</h4>

			<p>快摇将通过下列一个或多个选项提供保修服务：</p>
			<ul>
				<li>
			(i) 送修服务。您可以向提供送修服务的快摇授权服务商站点返还您的快摇产品。服务将在该地点进行，或者快摇授权服务商可能将您的快摇产品发送至快摇的维修服务站点进行维修。一旦通知您该服务完成，您将及时从快摇授权服务商站点取回快摇产品，或快摇产品将直接从快摇授权服务商站点发送到您的所在地。</li>
				<li>(ii)直接邮寄服务。如果快摇确定您的快摇产品符合直接邮寄服务的条件，快摇将向您提供预付邮资的运货单及包装材料（如适用），您须依照快摇的指示将您的快摇产品寄送至快摇授权服务商站点。完成维修服务后，快摇授权服务商站点会将快摇产品返还给您。如果您遵照所有指示，快摇将支付快摇产品往返您所在地的运费。</li>
				<li>(iii) 自行维修零部件服务。自行维修零部件服务可让您自行维修您的快摇产品。如果情况允许自行维修零部件服务，您须遵守下列流程。</li>
				<li>(a)快摇要求返还被更换的快摇产品或零部件。快摇可能要求提供信用卡授权作为快摇产品或零部件的更换品之零售价及可能产生运费的担保。如果您无法提供信用卡授权，快摇可能无法向您提供自行维修零部件服务，而只能安排其他替代服务。快摇将向您寄送快摇产品或零部件的更换件，同时提供安装指示（如适用），以及对于被更换的快摇产品及零部件的任何返还要求。如果您依照指示，快摇将取消信用卡授权，您将不需要支付快摇产品或零部件的更换品以及往返您所在地的运费。如果您未能依照指示返还被更换的快摇产品或零部件，或返还不符合服务资格的被更换的快摇产品或零部件，快摇将收取您的信用卡授权金额。</li>
				<li>(b)快摇不要求返还被更换的快摇产品或零部件。快摇将向您免费寄送快摇产品或零部件的更换品及安装指示（如适用），以及对于被更换的快摇产品或零部件的任何处理要求。</li>
				<li>(c)对于您产生的与自行维修零部件服务相关的任何人工费用，快摇不承担责任。如果您需要进一步的协助，请联系快摇代表以获得帮助。</li>
			</ul>

			<p>快摇有权变更快摇向您提供保修服务的方式，以及您的快摇产品获得特定服务方式的资格。服务将受限于服务需求国提供的可选项目。根据国家的不同，服务选项、零部件的获得及响应时间可能有所不同。如果快摇产品所在国不能提供保修服务，您可能需要负责运费和手续费。如果您在非原购买国寻求服务，您须遵守所有适用的进出口法律和法规，并负责所有关税、增值税及其他相关税收和费用。如果可以提供国际服务，快摇公司将使用符合当地标准的同等快摇产品和零部件，维修或更换快摇产品和零部件。</p>


			<h4>有限责任</h4>

			<p>除本保证规定以外以及在法律允许的最大范围内，任何情况下快摇均无须承担因违反保证或条件、或根据任何其他法学理论所引致的直接的、特殊的、附带的或后果性损害，包括但不限于使用损失；收入损失；实际或预期利润损失（包括合同利润损失）；资金运用损失；未能如期节省的损失；业务损失；机会损失；商誉损失；声誉损失；数据丢失；损坏或毁损；或不论如何导致的任何间接或后果性损失或损害（包括更换设备和财产）、因修复、编写或复制储存于快摇或连同快摇使用的任何程序或数据、或因未能为快摇产品储存的资料保密而产生的任何费用等。</p>

			<p>前述限制不适用于死亡或人身伤害的申索，或对于故意和严重疏忽行为和/或遗漏的任何法定责任。快摇否认曾经陈述其可以修理本保证下任何快摇产品，或在对快摇产品储存资讯毫无风险或丢失的情况下更换快摇产品。</p>
			<br />
			<p>有些国家和省不允许排除或限制附带的或后果性损害，因此上述限制或排除可能不适用于您。</p>

			<h4>隐私</h4>

			<p>快摇将根据快摇客户隐私政策维护和使用客户信息。</p>

			<h4>一般条款</h4>

			<p>快摇经销商、代理人或员工均未被授权对本保证做出任何修改、延期或增添。若任何条款被裁定为违法或不可执行，剩余条款的合法性和可执行性不受影响或损害。本保证适用快摇产品购买地国家的法律并据此进行解释。快摇或其权益继受人是本保证项下的保证人。</p>

			<h4>版权所有</h4>
			<p>© 2015快摇名片™</p>
		</div>
	</div>
	
	<div class="hint">
		<div>
			<p>感谢购买快摇名片设备，若在使用期间有不解之处可直接联系快摇App的快摇小助手或拨打快摇服务电话：</p>
			<p>400-8383-765</p>
			<p>感谢您的支持，祝您使用愉快！</p>
		</div>
	</div>
	<!-- 横屏幕提示 -->
	<div id="orientLayer" class="mod-orient-layer">
	<div class="mod-orient-layer__content"> <i class="icon mod-orient-layer__icon-orient"></i>
	  <div class="mod-orient-layer__desc">为了更好的体验，请使用竖屏浏览</div>
	</div>
	</div>
	

	<script>
		$(".product li").click(function(){
	    	var i = $(this).index();
	    	$(".product li").removeClass("product_current");
	    	$(this).addClass("product_current");
	    	$('.product_details div').hide().eq(i).show();
	    })

		var show = <?php echo $take=isset($_GET['a'])?$_GET['a']:0;?>;
		if(show==2){
			$(".hint").hide();
		}else{
			$(".hint").show();
		}
		$(".hint").click(function(){
			$(this).hide();
		});


	</script>
</body>
</html>