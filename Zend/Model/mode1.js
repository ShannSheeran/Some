
var model1 = (function(){
    var m1 = function(){
        console.log(count);
    }
    var m2 = function(){

    }
    var m3=function(int)
    {
        setInterval(function(){
            var day = parseInt(int/86400)+'天';
            var h = parseInt(int/3600)+'小时';
            var m = parseInt(int/60)+'分钟';
            var s = parseInt(int)+'秒';
            var text = day+h+m+s;
            int--;
            console.log(text);
            if(int<0)
            {
                int=0;
            }
        },1000);
    }

    var m4= function()
    {

    }

    return {
        m1:m1,
        m2:m2,
        m3:m3,
        m4:m4,
    };
})();