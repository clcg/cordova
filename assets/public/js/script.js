/* 	Variation Database Javascript methods
    by Nikhil Anand <nikhil@mantralay.org>
    and Sean Ephraim
    Tue Sept 24 2013
*/

function getParameterByName(name)
{
  name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
  var regexS = "[\\?&]" + name + "=([^&#]*)";
  var regex = new RegExp(regexS);
  var results = regex.exec(window.location.search);
  if(results == null)
    return "";
  else
    return decodeURIComponent(results[1].replace(/\+/g, " "));
}

function _otoscope_db_set_column_cookie($index,$colcount) {
	var cookieval = '', i = 0;
	for (i=1; i <= $colcount; i++) {
		cookieval += ( i == $index ) ? 0 : 1;
	};
}

$(document).ready(function(){
    
  // If this is a first-time visit, and NOT a gene page, collapse the glossary
  var URL = $(location).attr('href');
  if($.cookie("mdb_visited") == null && (URL.indexOf("letter") == -1)) {
      $("#sidebar-sorters-glossary").hide();
      $.cookie("mdb_visited", "yes", {expires: 7});
  } else {
      $("#sidebar-sorters-glossary").show();
  }

  /* S I D E B A R */
    
	// Check sidebar collapsed state
	if($.cookie("sidebar-sorters-information")) {$("#sidebar-sorters-information").hide();}
	if($.cookie("sidebar-sorters-glossary"))    {$("#sidebar-sorters-glossary").hide();}
	
	// Collapse fieldsets and remember their collapsed state
	$('.sidebar-collapsible legend').click(function(){
		var sidebar_id = $(this).closest('fieldset').children('div').attr('id');
		if($.cookie(sidebar_id) == null) {
			$(this).closest('fieldset').children('div').hide();
			$.cookie(sidebar_id, "1");
		} else {
			$(this).closest('fieldset').children('div').show();
			$.cookie(sidebar_id, null);			
		}
	});

  /* V I E W */

	// Apply persistence to fieldset view
	$("#mutation-tables fieldset div").each(function(){
		if ($.cookie($(this).attr('id'))) {
			$(this).siblings('legend').toggleClass('collapsed');
      $(this).toggle();
		}
	});
	
    // Make each table sortable and show a bubble for the "Variant type" column
	$('#mutation-tables table').each(function(){
		$(this).tablesorter({
		  widgets: ['zebra'],
		  headers: { 0: { sorter: false } }
		 });
	});
	
	// Make cookies for each fieldset that is collapsed 
	// Eat cookies for each fieldset that is expanded
	$('.genename').click(function(){
		$(this).toggleClass('collapsed');
		$("#table-"+$(this).attr('id')).toggle();
		
		if ($.cookie("table-"+$(this).attr('id'))) {
			$.cookie("table-"+$(this).attr('id'),null);
		} else {      
			$.cookie("table-"+$(this).attr('id'),'1');
		};
	});
	
	// Modal popup for variant data
	$('.showinfo .showinfo-popup').click(function(){
	  var parent_id = $(this).closest("tr").attr("id").substring(9); // 9 = "mutation-"
    var src='../variant/' + parent_id; 
    var viewport_height =  $(window).height();
		$.modal('<iframe id="variant-pane" src="' + src + '" height="650" width="670" style="border:0">', {
    	containerCss:{
    	  containerId: 'info-container',
    		backgroundColor:"#fff",
    		borderColor:"#fff",
    		height:650,
    		padding:0,
        width:670
    	},
      top:10,
    	opacity:90,
    	overlayClose:true
    });
    return false;
	});

	// Contact popup
	$('.contact-popup').click(function(){ 
		$("#section-contact").modal({opacity:90,overlayClose:true});
	});
  // Contact CBCB is unchecked by default
  $("#contact-cbcb").prop("checked", false);
  // Automatically Contact CBCB on click
	$('.contact-cbcb').click(function(){ 
    $("#contact-cbcb").prop("checked", true);
	});
	
	// Handle contact form submission
	$('#contact-submit').click(function(){
		var hasError = false;
		var filter = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;
		
		// Check name
		if (!$("#contact-name").val()) {
			hasError = true;
			$("#contact-name-error").text("Please enter your name");
			$("#contact-name-error").slideDown();
		} else if($("#contact-name").val().length < 3){
			hasError = true;
			$("#contact-name-error").text("We really need a longer name...");
			$("#contact-name-error").slideDown();
		} else {
			$("#contact-name-error").slideUp();
		}
		
		// Check email
		if (!filter.test($("#contact-email").val())) {
			hasError = true;
			$("#contact-email-error").text("Please enter a valid email address");
			$("#contact-email-error").slideDown();
		} else {	
			$("#contact-email-error").slideUp();
		}
		
		// Check comments
		if (!$("#contact-comments").val()) {
			hasError = true;
			$("#contact-comments-error").text("Please enter your questions or comments");
			$("#contact-comments-error").slideDown();
		} else if($("#contact-comments").val().length < 10){
			hasError = true;
			$("#contact-comments-error").text("You surely have something more to add...");
			$("#contact-comments-error").slideDown();
		} else {
			$("#contact-comments-error").slideUp();
		}
		
		// Submit form
		if (hasError == false) {
      document.getElementById("section-contact").submit();
		}
	});
	
	
	/* Help page */
  $('#content-help .float-left' ).shadow('raised');
  $('#content-help .float-right').shadow('raised');

  /* Variant view (small) */
  // Show tooltips
  $("#frequency-small div").each(function(){
      $(this).tipsy({
        fallback: $(this).children('strong').text(),
        gravity:'s'
      });
  });

  // Add description to paragraph above on click
  $("#frequency-small table td div").click(function(){
      $('#frequency-description').text($(this).children('strong').text());
  });

  /* Close dialoge boxes */
  $(".close").click(function(){
    $(".close").parent().hide();
  });

  /* Show email success message */
  if (getParameterByName('success') == 1) {
    $(".success").show();
  }

  /* Scroll long text */
  
});
