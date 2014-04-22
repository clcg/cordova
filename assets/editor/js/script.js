/* 	Variation Database Editor -- JavaScript methods
    by Sean Ephraim
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
$("#expand-all").click(function() {
  expandAllAccordions(true);
});
/* Expand all accordions + change plus/minus icons */
function expandAllAccordions(expand_by_click) {
  expand_by_url_param = getParameterByName('expand');
  if (expand_by_click || expand_by_url_param == 'true') {
    $(".accordion-body").addClass("in");
    $(".accordion-toggle").children(".icon-plus").addClass("icon-minus");
    $(".accordion-toggle").children(".icon-minus").removeClass("icon-plus");
  }
}
/* Toggle accordian plus/minus icons */
$(".accordion-toggle").click(function() {
  $(this).children(".icon-plus, .icon-minus").toggleClass("icon-minus icon-plus");
});
/* Toggle variant confirmation icons + save button */
$(".variant-change-confirm").each(function() {
  toggleConfLabel($(this));
});
$(".variant-change-confirm").click(function() {
  toggleConfLabel($(this));
  displaySaveButton();
});
function toggleConfLabel(e) {
  if ($(e).is(':checked')) { 
    $(e).parent().addClass("hold-change");
    $(e).parent().removeClass("release-change");
  }
  else {
    $(e).parent().addClass("release-change");
    $(e).parent().removeClass("hold-change");
  }
}
function displaySaveButton() {
  $("#affixed-save-wrapper").show();
}
/* Confirm/unconfirm all */
$("#confirm-all").click(function() {
  $(".variant-change-confirm").prop("checked", false);
  $(".variant-change-confirm").each(function() {
    toggleConfLabel($(this));
  });
  displaySaveButton();
});
$("#unconfirm-all").click(function() {
  $(".variant-change-confirm").prop("checked", true);
  $(".variant-change-confirm").each(function() {
    toggleConfLabel($(this));
  });
  displaySaveButton();
});
/* Force release -- default should be "None" */
$(document).ready(function() { 
  $("#special-release-none").prop("checked", true);
  $("#force-add-variant").prop("checked", false);
});
/* Force add -- only available when input isn't changed */
$("#variation").on('input', function() {
 $("#force-add-variant").prop("checked", false);
 $("#force-add-variant-wrapper").hide();
});
$(function () { 
  expandAllAccordions(false);
  $("#expand-all").on('click', function(e) {e.preventDefault(); return true;});
  $("[data-toggle='popover']").on('click', function(e) {e.preventDefault(); return true;});
  $("[data-toggle='popover']").popover(); // init. popovers
});
/* Add sortable tables */
$(document).ready(function() { 
  $(".tablesorter").tablesorter(); 
}); 
/* Progress bar for adding a variant */
$("#add-variant-submit").click(function() {
  $("#add-variant-progress").show();
  $("#form-add-variant").hide();
  $(".error, .success").hide();
});
/* Scroll to top */
$("a[href='#top']").click(function() {
  $("html, body").animate({ scrollTop: 0 }, "slow");
  return false;
});
/* Scroll to bottom */
$("a[href='#bottom']").click(function() {
  $("html, body").animate({ scrollTop: $(document).height() }, "slow");
  return false;
});
