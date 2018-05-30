$(function(){

	$(".top-menu-button").on("click", function(){
		if($("#left_side").hasClass('active')){
			$("#left_side").removeClass('active');
			$(".shadow-background").css({"display": "none"});
			$("#content").removeClass("no-scroll");
		}else{
			$("#left_side").addClass('active');
			$(".shadow-background").css({"display": "block"});
			$("#content").addClass("no-scroll");
		}
	});
	$(".shadow-background").on("click", function(){
		if($("#left_side").hasClass('active')){
			$("#left_side").removeClass('active');
			$(".shadow-background").css({"display": "none"});
			$("#content").removeClass("no-scroll");
		}
	});
});