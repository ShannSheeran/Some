/* 
* @Author: Marte
* @Date:   2016-06-26 22:40:40
* @Last Modified by:   Marte
* @Last Modified time: 2016-06-30 14:22:28
*/

$(document).ready(function(){
    // banner轮播图开始
        var num = 0;
        var timer = null;
       $('.banner ol li').click(function(event) {
            if(!$(this).hasClass('current')){
                $(this).addClass('current').siblings('').removeClass('current');
                $('.banner ul').stop().animate({'left':-$(this).index()*100+'%'});
                num=$(this).index();
            }
           
       });

       function autoPlay(){
            num++;
            if(num>2){num=0;}
            $('.banner ol li').eq(num).addClass('current').siblings('').removeClass('current');
            $('.banner ul').animate({'left':-num*100+'%'});
       }
       timer=setInterval(autoPlay,5000)

       $('.banner').mouseenter(function(event) {
           clearInterval(timer);
       }).mouseleave(function(event) {
           timer=setInterval(autoPlay,5000);
       });
       // banner轮播图结束；
       
       // 供应商、经销商登陆切换
       $('.login_box .txt h2').click(function(event) {
           $(this).addClass('current').siblings('').removeClass('current');
           $('.login_box .account_bd').eq($(this).index()).addClass('current').siblings('').removeClass('current');
            });
           // 热销产品无缝滚动

        (function(){
            var num = 0;
            var timer = null;
            function autoPlay(){
            num--;
            if(num<-664){num=0}
            $('.product ul').css('left',num)
        }
            timer = setInterval(autoPlay,20)
            $('.product ul li').mouseenter(function(event) {
            clearInterval(timer);
        }).mouseleave(function(event) {
            timer = setInterval(autoPlay,20)
        });
        })();
});