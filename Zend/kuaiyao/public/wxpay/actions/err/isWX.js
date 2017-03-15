window.onload = function(){
    if(!isWeiXin()){
        window.location.href="err/index.htm";
    }
}
function isWeiXin(){
    var ua = window.navigator.userAgent.toLowerCase();
    if(ua.match(/MicroMessenger/i) == 'micromessenger'){
        return true;
    }else{
        return false;
    }
}