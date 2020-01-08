// JavaScript Document
$(function() {
	$(".fg-tag-list a.fg-tag-link").each(function(e){
		tag = $(this).attr("href");
		tag = tag.replace('#tag-','');
		if (""==tag) tag="all";
		$(this).css("background-image", "url('"+php_vars.adUrl+"assets/"+tag+".png')");
	});
});
