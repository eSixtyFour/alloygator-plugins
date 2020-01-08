// Create a new color picker instance
// https://iro.js.org/guide.html#getting-started
var carSelector = new iro.ColorPicker(".e64-car-wheel", {
  // color picker options
  // Option guide: https://iro.js.org/guide.html#color-picker-options
  width: 180,
  color: php_vars.colour,
  borderWidth: 1,
  borderColor: "#fff",
});

// https://iro.js.org/guide.html#color-picker-events
carSelector.on(["color:init", "color:change"], function(color){
  // Show the current color in different formats
  // Using the selected color: https://iro.js.org/guide.html#selected-color-api
  $(".e64-car-colour").css("background-color", color.hexString);
  
});


var alloySelector = new iro.ColorPicker(".e64-alloy-wheel", {
  // Create the colour selector colour wheel graphic display
  width: 180,
  color: php_vars.colour,
  borderWidth: 1,
  borderColor: "#828282",
});

alloySelector.on(["color:init", "color:change"], function(color){
  $(".e64-alloy-colour").css("background-color", color.hexString);
  
});

var $e64CarColour, $e64CarColourImg, $e64OverlayBox, $e64CsContainer, $e64ColourSelector, $canvas

$(function(){
	
	$(".e64-box").each(function(){
		$this = $(this);
		clr=$this.data("color");
		$this.css("background-color", clr);
	});
	
	
	$(".e64-colour-selector").on("click", ".e64-alloy-colours .e64-box", function(e){
		e.preventDefault();
		
		bgCol = $(this).css("background-color");
		
		// Uncomment this line to set the Colour Wheel to the colour chosen by the single-click colur boxes
		// alloySelector.color.set(bgCol);
		
		collapsePanel();
	});
	
	
	$(".e64-colour-selector").on("click", ".e64-alloy-colours a", function(e){
		e.preventDefault();
		
		collapsePanel();
		
		imgNme = $(this).data("img");
		bgCol = $(this).css("background-color");
		
		$(".e64-alloy-colour").css("background-color", bgCol);
		
	});
	
	$(".e64-colour-selector").on("click", ".e64-gator-colours a", function(e){
		e.preventDefault();
		
		collapsePanel();
		
		attr=$(this).data("name");
		$(".e64-product-attribute").val(attr);
		$(".e64-buyColourButton").removeClass("disabled");
		
		bgCol = $(this).css("background-color");
		$(".e64-gator-colour").css("background-color", bgCol);
	});
	
	$(".e64-colour-selector").on("click", ".e64-buyColourButton", function(e) {
		e.preventDefault();
		attr = $(".e64-product-attribute").val();
		if (""!=attr) {
			amp = (php_vars.bookUrl.indexOf("?")<=0) ? "?" : "&";
			document.location.href=php_vars.bookUrl+amp+"attribute_pa_colour="+attr;
		}
	});
	
	$(".e64-accordion").on("click", function(e) {
		crnt = $(".e64-active").data("target");
		
		collapsePanel();
		
		pnl = $(this).data("target");
		hgt = $(this).data("height");
		if (crnt != pnl) {
			$(this).addClass("e64-active")
			$(pnl).addClass("e64-active-pnl").css("max-height", hgt+"px");
		}
	});
	
	
	$(".e64-select").on("click", function(e) {
		collapsePanel();
	});


	$('.e64-take-photo').click(function(e) {
		e.preventDefault();
		
		cameraFlash();
		collapsePanel();
		
		togglePhotoButton();

		var car = $(".e64-car-colour").get(0)
		
		html2canvas(car).then(function(canvas) {
			
			$canvas = canvas;
			
			var canvasWidth = canvas.width;
			var canvasHeight = canvas.height;
			
			var img = Canvas2Image.convertToImage(canvas, canvasWidth, canvasHeight);
			$(".e64-show-image").html(img).fadeIn(500);
			
		});
		
	});
	
	$('.e64-save-image').click(function(e) {
		e.preventDefault();
		type = $('#sel').val(); // image type
		w = $('#imgW').val(); // image width
		h = $('#imgH').val(); // image height
		f = 'My-AlloyGators'; // file name
		w = (w === '') ? $canvas.width : w;
		h = (h === '') ? $canvas.height : h;
		// save as image
		Canvas2Image.saveAsImage($canvas, w, h, type, f);
	});
	
	
	$('.e64-close-photo').click (function(e) {
		e.preventDefault();
		$('.e64-show-image').fadeOut(500, function(){
			$('.e64-show-image').html("");
			togglePhotoButton();
		});
	});
	
	$e64ColourSelector = $(".e64-colour-choice");
	$e64CarColour = $(".e64-car-colour");
	$e64CarColourImg = $(".e64-overlay-img img");
	$e64MblColourImg = $(".e64-mbl-overlay-img img");
	$e64OverlayBox = $(".e64-overlay-box");
	$e64CsContainer = $(".e64-cs-container");
	
	resizeWindow();
	
	$(window).resize(resizeWindow);

});

var resizeWindow = function() {
	var newHgt = $e64CarColourImg.height();
	if (newHgt<=0) newHgt = $e64MblColourImg.height();
	var addHgt = $e64ColourSelector.height();
	
	$e64CsContainer.css("height", (newHgt + addHgt + 10) + "px");
	$e64OverlayBox.css("height", (newHgt - 10) + "px");
	$e64CarColour.css("height", newHgt + "px");
}


var collapsePanel = function() {
	$(".e64-active").removeClass("e64-active");
	$(".e64-active-pnl").removeClass("e64-active-pnl").css("max-height", "");
}

var cameraFlash = function() {
	hgt = parseInt($(".e64-overlay-img").height());
	$(".e64-camera-flash").fadeIn(120, function(e) {
		$(".e64-camera-flash").fadeOut(50)
	});
}

var togglePhotoButton = function() {
	if ($('.e64-photo-button').is(":visible")) {
		$('.e64-photo-button').hide();
		$('.e64-close-button').show();
	}
	else {
		$('.e64-photo-button').show();
		$('.e64-close-button').hide();
	}
}


