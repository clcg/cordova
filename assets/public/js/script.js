/* 	Variation Database Javascript methods

    Nikhil Anand <nikhil@mantralay.org>
    Sean Ephraim <sean.ephraim@gmail.com>
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

// Make each table sortable and show a bubble for the "Variant type" column
function tablesort() {
  $('#mutation-tables table').each(function(){
  	$(this).tablesorter({
  	  widgets: ['zebra'],
  	  headers: { 0: { sorter: false } }
  	 });
  });
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

  tablesort();

  // Load variations when gene name is clicked
  $.ajaxSetup ({
    cache: false
  });
	$('.genename').click(function(){
    var variations_table = $(this).parent().children(".variant-list-container");
    $(this).toggleClass('collapsed');
    if (!$(this).hasClass("loaded")) {
      // Load and display variations table for the first time
      $(this).addClass("loaded");
      $("#table-"+$(this).attr('id')).toggle();
      
      //searchPos functionality...preserves legacy/previous functionality
      var parts = window.location.search.substr(1).split("=");
      if(parts[0].localeCompare('searchStr') == 0){
    	  //searchPos
    	  var loading_modal = '' +
	        '<div id="loading-modal">' +
	        '    <div>' +
	        '        <img src="./assets/public/img/loading.gif" alt="Loading icon">' +
	        '    </div>' +
	        '</div>';
    	  
    	  var loadURL = "./geneVariantPos/" + parts[1]; //providing the position searched here
      
      } else {
	      var loading_modal = '' +
	        '<div id="loading-modal">' +
	        '    <div>' +
	        '        <img src="../assets/public/img/loading.gif" alt="Loading icon">' +
	        '    </div>' +
	        '</div>';
  
	      var loadURL = "../gene/"+this.id; //this.id is the gene name
      }
      
      //variations_table.html(loading_modal).load(loadURL);
      variations_table.html(loading_modal).load(loadURL, function(){
        tablesort();
      });
    }
    else if (variations_table.css('display') == 'none') {
      // Show variations table if hidden and already loaded
      variations_table.show();
    }
    else {
      // Hide variations table if already loaded
      variations_table.hide();
    }
	});
	
	// Modal popup for variant data
	$('.showinfo .showinfo-popup').live('click', function(){
		
	  var parts = window.location.search.substr(1).split("=");
      if(parts[0].localeCompare('searchStr') == 0){
    	  //for searchPos page
          var parent_variant = $(this).closest("tr").attr("id").substring(9);
    	  var src='./variant/'+ parent_variant;
      } else {
    	  //for by letter page
    	  var parent_variant = $(this).closest("tr").attr("id").substring(9);
    	  var src='../variant/' + parent_variant;
      }
      
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

});
