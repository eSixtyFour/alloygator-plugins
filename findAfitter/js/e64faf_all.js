(function($) {
	
	if (window.location.href.indexOf("product") > -1) {
		var fittingVal = "buy-with-fitting";
		
		$(".e64-faf-grey-overlay .e64-closeOverlay").click(function(e){
			e.preventDefault();
			e64_closeOverlayWindow();
		});
		
		$(".e64-faf-grey-overlay").on("click", ".fitterSearchForm .e64_bookFitter", function(e){
			e.preventDefault();
			
			title = $(this).closest(".e64_infowin").find(".e64-faf-title").text();
			text = $(".e64-fitting-company").data("template");
			if (text) text = text.replace('@companyName@', title);
			
			$(".e64-fitting-company p").html(text);
			$(".e64-fitting-company").removeClass("e64-display-none");
			$("#e64-fitterid").val($(this).data("fitterid"));
			
			e64_closeOverlayWindow();
			// Now submit the form, as at this point the customer has already clicked the 'Add to cart' button
			$("form.variations_form").submit();
			
		});
		
		$("form.variations_form").on("submit", function(e){
			
			fitterId = parseInt($("#e64-fitterid").val());
			buyOption = $("select[name='attribute_pa_fitting-option']").val();
			
			if ((isNaN(fitterId)||fitterId==0) && buyOption == fittingVal) {
				e.preventDefault();
				e64_openOverlayWindow();
			}
			
		});
		
		$("#pa_fitting-option").on("change", function(e) {
			if ($(this).val()!= fittingVal && $(this).val()!="") {
				$(".e64-fitting-company").addClass("e64-display-none");
				$("#e64-fitterid").val("");
			}
		});
		
		$(window).resize(resizeWindow);
		
	}
	
	if (window.location.href.indexOf("checkout") > -1) {
		setCheckoutFittersDelivery();
		
	}
	
})(jQuery);


function resizeWindow() {
	e64_repositionMapContainer();
}

function e64_openOverlayWindow() {
	$(".e64-faf-grey-overlay").fadeIn(400, function(){
		e64_repositionMapContainer();
		$(".e64-faf-grey-overlay .e64-container").show(function(){e64_repositionClosebutton();});
	});
}

function e64_closeOverlayWindow() {
	$(".e64-faf-grey-overlay .e64-close-button").hide();
	$(".e64-faf-grey-overlay .e64-container").hide(function() {
		$(".e64-faf-grey-overlay").fadeOut(400);
	});
}

function e64_repositionMapContainer() {
	if ($(".e64-faf-grey-overlay").length>0) {
		ovrHgt = $("body").height()/2;
		ovrWdt = $("body").width()/2;
		cntTop = ovrHgt - ($(".e64-faf-grey-overlay .e64-container").height()/2);
		cntLft = ovrWdt - ($(".e64-faf-grey-overlay .e64-container").width()/2) - 50;
		cntTop = (cntTop<=0) ? 0 : cntTop;
		cntLft = (cntLft<=0) ? 0 : cntLft;
		$(".e64-faf-grey-overlay .e64-container").css({"top":cntTop+"px", "left":cntLft+"px"});
		e64_repositionClosebutton();
	}
}

function e64_repositionClosebutton() {
	mapRight = $(".e64-faf-grey-overlay .e64-container").position().left+$(".e64-container").width();
	mapTop = $(".e64-faf-grey-overlay .e64-container").position().top;
	$(".e64-faf-grey-overlay .e64-close-button").css({"top": mapTop+"px","left": mapRight+"px"}).show();
}

function setCheckoutFittersDelivery() {
	if ($("#shipping_company").length>0) {
		
		$("#address_book_field").remove();
		
		$("#ship-to-different-address-checkbox").parent().find("span").html("Ship directly to fitter");
		
		$('#ship-to-different-address-checkbox').on('click keyup keypress keydown', function(e) {
			if($(this).is('[readonly]')) { return false; }
		});
		
		$(".e64-default-value input").each(function(){
			val = $(this).data('e64value');
			if (val) {
				$(this).val(val);
				$(this).attr("readonly", true);
			}
		});
	
		$(".e64-default-value select").each(function(){
			val = $(this).data('e64value');
			if (val) {
				$selOpt = $(this).find("option:contains("+val+")");
				if ($selOpt.length>0) {
					$selOpt.attr('selected', 'selected');
					$(this).val($selOpt.val());
					$(this).parent().find(".select2-selection__rendered").html(val);
					$(this).attr("readonly", true);
					
				}
			}
		});
		
		if (!$("#ship-to-different-address-checkbox").is(":checked")) $("#ship-to-different-address-checkbox").trigger("click").hide();
	}
}
