$(document).ready(function() {	

	//changed 'parent()' to 'parents()' so the code will still work with devel themer enabled
	var hsubmenu = $("#help-menu-item").parents("li").find("ul.menu");
	var wsubmenu = $("#welcome-message").parents("li").find("ul");
		
	//prepare welcome submenu		
	wsubmenu.css('margin-left', '163px');
	wsubmenu.css('width', '150px');
	wsubmenu.find("li, a").css('float', 'left');
	wsubmenu.find("li, a").css('display', 'block');
	wsubmenu.find("li, a").css('clear', 'both');
		
	//prepare help sub-menu
	hsubmenu.append('<li class="contact-info"> <h3>Call Us</h3> <p><strong>USA</strong><br />'+ $('#us-phone').text() +'</p><p><strong>International</strong><br />'+ $('#intl-phone').text() +'</p></li>');
	
	
	$("#help-menu-item").click(function(){
		wsubmenu.slideUp();
		$('#utility-map-modal').slideUp(); 
		hsubmenu.slideToggle(); 
		return false;
	});
	hsubmenu.mouseleave(function(){
		hsubmenu.slideUp();
	});
	
	$("#welcome-message").click(function(){	
		hsubmenu.slideUp();
		$('#utility-map-modal').slideUp();	
		wsubmenu.slideToggle(); 
		return false;
	});
	wsubmenu.mouseleave(function(){
		wsubmenu.slideUp();
	});
	
	
	
});

$(function(evt){	
	$("#utility-search-label").overlabel();
	
	$("#map-menu-item").bind("click",function(){
		$("#help-menu-item").parents("li").find("ul.menu").slideUp();
		$("#welcome-message").parents("li").find("ul").slideUp();
		if ($('#utility-map-modal').length == 0){
            $("#utility-bar").append('<div id="utility-map-modal"></div>');
            $("<iframe frameborder=0 scrolling='no' id='utility-map-iframe'" + ($.browser.msie ? " allowtransparency='true'" : '') + "/>")
            .attr({src: "http://www.andrewharper.com/regions/worldwide?map=true", name: "utility-map-iframe"})
            .appendTo("#utility-map-modal");
            $("#utility-map-modal").slideDown(function(){
                $(this).block(block_settings);
                $('iframe#utility-map-iframe').load(function(){
                    $("#utility-map-modal").unblock();  
                });
            });
        }else{
            $('#utility-map-modal').slideToggle();
        }
        return false;
	});	
	
	// Attach hover behavior to main navigation 
	$("#navigation > .section > ul.menu > li > a").bind("mouseenter focusin", function(){
		$("#navigation > .section > ul.menu > li").removeClass("active-trail");
		$("#navigation > .section > ul.menu > li > a").removeClass("active");
		$(this).addClass("active").parent().addClass("active-trail");
	});	
	
    
	// MPC - Open blog in new window
	$('#dhtml_menu-429').attr('target', '_blank');
	
    // HR slider - rbanh
    try {
        $('.views_slideshow_thumbnailhover_div_breakout_teaser').hover(
            function() { 
                HR_slider_reset();
                $(this).addClass('views_slideshow_thumbnailhover_active_teaser');
            },
            function() { 
            }
        )
		
		t = setTimeout("HR_slider_auto_rotate()",2000);
		
		$('#fromdate').datepicker({
			showOn: "button",
			buttonImage: "/sites/all/themes/andrew_harper/images/AH/date-picker.png",
			buttonImageOnly: true,
			minDate: +3
		});
		$('#todate').datepicker({
			showOn: "button",
			buttonImage: "/sites/all/themes/andrew_harper/images/AH/date-picker.png",
			buttonImageOnly: true
		});
		
		rbanh_tooltip();
        
        // init special offer links in hotel pages
        $('#hotels-special-offers').find('.views-field-title a').bind('click', function(e){
            e.preventDefault();
            e.stopPropagation();
            window.location = this.href;
            window.location.reload(true);
        });
        
        // locked articles go to /user
        $('#hideaway-reports .locked').bind('click', function(e){
            window.location = "/user";
        }).css('cursor', 'pointer');

		
    } catch(e) {}
 
    $('#request-rate').submit(function(){

		var fromDate, toDate, plus3Date;
		fromDate = new Date($('#fromdate').val());
		toDate = new Date($('#todate').val());
		
		// Stupid Javascript date math...
		plus3Date = new Date();
		plus3Date = new Date(plus3Date.getFullYear(), plus3Date.getMonth(), plus3Date.getDate());
		plus3Date = new Date(plus3Date.getTime() + (1000 * 60 * 60 * 24 * 3));
		
		if(fromDate > toDate)
		{
			alert("The Start Date may not be greater than the End Date");
			return false;
		}
		if(fromDate < plus3Date)
		{
			alert("Online booking requests must be made at least 48 hours prior to your desired travel date.");
			return false;
		}
	
		window.open($(this).find('#params').val() + "&roomcount=" +$(this).find('#roomcount').val() 
				+ "&fromdate=" +$(this).find('#fromdate').val() + "&todate=" +$(this).find('#todate').val() 
    			+ "&Adults=" +$(this).find('#Adults').val() + "&Children=" +$(this).find('#Children').val() 
				+ "&MemberID=" +$(this).find('#MemberId').val());
    	
	return false;
	});   
});

function HR_slider_auto_rotate()
{
	var hr_count = 0;
	$('#views_slideshow_thumbnailhover_teaser_section_hideaway_report-panel_pane_1 > div').each(function(){
		if ($(this).is(':visible'))
		{
			HR_slider_reset();
			
			var hr_count_inner = 0;
			$('#views_slideshow_thumbnailhover_breakout_teasers_hideaway_report-panel_pane_1 > div').each(function(){
				if (hr_count_inner == hr_count)
					$(this).addClass('views_slideshow_thumbnailhover_active_teaser');
				hr_count_inner++;
			});
		}
		hr_count++;
	})
	t=setTimeout("HR_slider_auto_rotate()",2000);
}

function HR_slider_reset()
{
    $('.views_slideshow_thumbnailhover_div_breakout_teaser').removeClass('views_slideshow_thumbnailhover_active_teaser');
}

// Initialize UI Block settings
var block_settings = {
	message: '<p><img src="/sites/all/themes/andrew_harper/images/layout/ajax-loader.gif" /></p>', 
    css: { border: '0 none', background: 'transparent none' },
    overlayCSS:  { backgroundColor: '#333', opacity: .9 }
};
Drupal.behaviors.clickrule = function (context) {
	if(Drupal.jsEnabled){
		var enabled = 'true';
	}
	if(Drupal.settings.andrewharper != undefined){
		$("#page-wrapper a").bind("click", function(){
			 url = $(this).href;
            
			  $.ajax({
			         type:     "GET",
			         url:      '/click_rule_ajax/' + enabled,
			         dataType: "html",
			         async: false,
			         success: function(x){
				  		var result = Drupal.parseJson(x);
				  		if(result.url){
				  			location.href = result.url;
					  		return false;
				  		}
				  		else {
                            
				  			//location.href = document.URL;
                            
					  		return false;
				  		}
			  		}
			      });		
		});
	}

};
var ahrender = function (response){
	var result = Drupal.parseJson(response);
	$('div.ajax-login').html(result.data);
};

this.rbanh_tooltip = function(){
	xOffset = 30;
	yOffset = 15;
	$(".tooltip").hover(function(e){
		var tt_data = $(this).attr('data');
		$("body").append("<p id='tooltip'>"+tt_data+"</p>");
		$("#tooltip")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px")
			.fadeIn("fast");		
		},
		function(){	
			$("#tooltip").remove();
	});	
	$(".tooltip").mousemove(function(e){
		$("#tooltip")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px");
	});			
};
