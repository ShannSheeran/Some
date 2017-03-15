$(document).ready(function(){
  $(".bt1").click(function(){
  $(".bt1 a").css("color","#3270fe");
  $(".bt2 a").css("color","black");
  $(".bt3 a").css("color","black");
  $(".tag1").css("display","block");
  $(".tag2").css("display","none");
  $(".tag3").css("display","none");
});
  $(".bt2").click(function(){
  $(".bt2 a").css("color","#3270fe");
  $(".bt1 a").css("color","black");
  $(".bt3 a").css("color","black");
  $(".tag1").css("display","none");
  $(".tag2").css("display","block");
  $(".tag3").css("display","none");
});
  $(".bt3").click(function(){
  $(".bt3 a").css("color","#3270fe");
  $(".bt1 a").css("color","black");
  $(".bt2 a").css("color","black");
  $(".tag3").css("display","block");
  $(".tag2").css("display","none");
  $(".tag1").css("display","none");
});
});