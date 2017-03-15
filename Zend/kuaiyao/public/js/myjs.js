// JavaScript Document
$(document).ready(function(){
	$("#txsqjl").click(function(){
	   $("#wdtjm_li").removeClass("pon");
       $(".mdb_bottom_body").hide();
	   $("#txsqjl").css("color", "#2e6ffe"); 
	   $("#wdtjm").css("color", "#666"); 
	   $("#txsqjl_li").addClass("pon1");
	   $(".mdb_bottom_body_two").show();  
});
});
$(document).ready(function(){
	$("#wdtjm").click(function(){
	   $("#txsqjl_li").removeClass("pon1");
       $(".mdb_bottom_body_two").hide();
	   $("#wdtjm").css("color", "#2e6ffe"); 
	   $("#txsqjl").css("color", "#666"); 
	   $("#wdtjm_li").addClass("pon");
	   $(".mdb_bottom_body").show();  
});
});