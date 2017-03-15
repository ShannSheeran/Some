//遮罩层
function showDialog()
{
	// $('.thickbox').show();
	$('.page_strong').css('display','block');
}
function hideDialog()
{
	// $('.thickbox').hide();
	$('.page_strong').css('display','none');
}

//页面内容不够时仍然使底部居于最下方
// $(function(){
// var footerHeight = 0, 
// footerTop = 0, 
// $footer = $("footer"); 
// positionFooter();
// function positionFooter() { 
// footerHeight = $footer.height();
// footerTop = ($(window).scrollTop()+$(window).height()-footerHeight)+"px";
// if ( $(document.body).height()< $(window).height()) { 
// $footer.css({ 
// position: "fixed" 
// }).stop().css({ 
// bottom: 0 
// }); 
// } else { 
// $footer.css({ 
// position: "static" 
// }); 
// } 
// } 
// $(window).scroll(positionFooter).resize(positionFooter); 
// });

//鼠标经过预览图片函数
function preview(img){
	$("#preview .jqzoom img").attr("src",$(img).attr("src"));
	$("#preview .jqzoom img").attr("jqimg",$(img).attr("bimg"));
}
//图片放大镜效果
$(function(){
	$(".jqzoom").jqueryzoom({xzoom:330,yzoom:330});
	$('.navlia').mouseenter(function(event) {
		$(this).children('.sub-about').stop().slideDown();
	}).mouseleave(function(event) {
		$(this).children('.sub-about').stop().slideUp();
	});

	// $('.navlia').hover(function(event) {
	//    console.log(11);
	//    $(this).children('.sub-about').stop().slideToggle();
	// });

	// $('.sub-about').mouseenter(function(event) {
	//    $(this).parent('.navlia').addClass('current');
	// }).mouseleave(function(event) {
	//    $(this).parent('.navlia').removeClass('current');
	// });
});



//图片预览小图移动效果,页面加载时触发
function scrollDiv()
{
	var tempLength = 0; //临时变量,当前移动的长度
	var viewNum = 5; //设置每次显示图片的个数量
	var moveNum = 1; //每次移动的数量
	var moveTime = 300; //移动速度,毫秒
	var scrollDiv = $(".spec-scroll .items ul"); //进行移动动画的容器
	var scrollItems = $(".spec-scroll .items ul li"); //移动容器里的集合
	var moveLength = scrollItems.eq(0).width() * moveNum; //计算每次移动的长度
	var countLength = (scrollItems.length - viewNum) * scrollItems.eq(0).width(); //计算总长度,总个数*单个长度
	  
	//下一张
	$(".spec-scroll .next").bind("click",function(){
		if(tempLength < countLength){
			if((countLength - tempLength) > moveLength){
				scrollDiv.animate({left:"-=" + moveLength + "px"}, moveTime);
				tempLength += moveLength;
			}else{
				scrollDiv.animate({left:"-=" + (countLength - tempLength) + "px"}, moveTime);
				tempLength += (countLength - tempLength);
			}
		}
	});
	//上一张
	$(".spec-scroll .prev").bind("click",function(){
		if(tempLength > 0){
			if(tempLength > moveLength){
				scrollDiv.animate({left: "+=" + moveLength + "px"}, moveTime);
				tempLength -= moveLength;
			}else{
				scrollDiv.animate({left: "+=" + tempLength + "px"}, moveTime);
				tempLength = 0;
			}
		}
	});
}

function returnImgUrl(url)
{
	if(url=='' || chkFormat(url,'photo') ==true)
	{
		// url='images/logo.png';
		url = '/uploadfiles/no_pic.png';
	}else{
		// url='../uploadfiles/'+url;
		url = '/uploadfiles/'+url;
	}
	return url;
}

function chkFormat(str, ftype) {  
	var Regexs = {  
            email: (/^[0-9a-z][0-9a-z\-\_\.]+@([0-9a-z][0-9a-z\-]*\.)+[a-z]{2,}$/i),//邮箱  
            phone: (/^0[0-9]{2,3}[2-9][0-9]{6,7}$/),//座机手机号码  
            ydphpne: (/^((13[4-9])|(15[012789])|147|182|187|188)[0-9]{8}$/),//移动手机号码  
            allphpne: (/^((13[0-9])|(15[0-9])|(18[0-9]))[0-9]{8}$/),//所有手机号码  
            ltphpne: (/^((13[0-2])|(15[56])|(186)|(145))[0-9]{8}$/),//联通手机号码  
            dxphpne: (/^((133)|(153)|(180)|(189))[0-9]{8}$/),//电信手机号码  
            url: (/^http:\/\/([0-9a-z][0-9a-z\-]*\.)+[a-z]{2,}(:\d+)?\/[0-9a-z%\-_\/\.]+/i),//网址  
            num: (/[^0-9]/),//数字  
            cnum: (/[^0-9a-zA-Z_.-]/),  
            photo: (/\.jpg$|\.jpeg$|\.gif$|\.png$/i),//图片格式  
            row: (/\n/ig)  
        };
    var nReg = Regexs[ftype];  
    if (str == null || str == "") return false; //输入为空，认为是验证通过     
    if (!nReg.test(str)) {  
        return true;  
    } else {  
        return false;  
    }  
};  