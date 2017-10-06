/* ========================================================================
 * Tutorial specific Javascript
 * 
 * ========================================================================
 * Copyright 2015 Bootbites.com (unless otherwise stated)
 * For license information see: http://bootbites.com/license
 * ======================================================================== */

// Sticky navbar
// =========================
$(document).ready(function() {
  // Custom function which toggles between sticky class (is-sticky)
  var stickyToggle = function(sticky, stickyWrapper, scrollElement) {
    var stickyHeight = sticky.outerHeight();
    var stickyTop = stickyWrapper.offset().top;
    var sticky_row = sticky.children('.container').children('.row');
    if (scrollElement.scrollTop() >= stickyTop){
      stickyWrapper.height(stickyHeight);
      sticky.addClass("is-sticky");
      sticky_row.children('.col-lg-3').removeClass('col-lg-3').addClass('col-lg-1');
      sticky_row.children('.col-lg-5').removeClass('col-lg-5').addClass('col-lg-7');
      sticky_row.children('.col-md-7').removeClass('col-md-7').addClass('col-md-1');
      sticky_row.children('.col-md-12').removeClass('col-md-12').addClass('col-md-6');
      sticky_row.children('#logo.col-sm-12').removeClass('col-sm-12').addClass('col-sm-2');
      sticky_row.children('.col-sm-12').removeClass('col-sm-12').addClass('col-sm-5');
      sticky_row.children('#logo.col-12').removeClass('col-12').addClass('col-3');
      sticky_row.children('#search_widget.col-12').removeClass('col-12').addClass('col-9');
    }
    else{
      sticky.removeClass("is-sticky");
      sticky_row.children('.col-lg-1').removeClass('col-lg-1').addClass('col-lg-3');
      sticky_row.children('.col-lg-7').removeClass('col-lg-7').addClass('col-lg-5');
      sticky_row.children('.col-md-1').removeClass('col-md-1').addClass('col-md-7');
      sticky_row.children('.col-md-6').removeClass('col-md-6').addClass('col-md-12');
      sticky_row.children('#logo.col-sm-2').removeClass('col-sm-2').addClass('col-sm-12');
      sticky_row.children('.col-sm-2').removeClass('col-sm-2').addClass('col-sm-7');
      sticky_row.children('.col-sm-5').removeClass('col-sm-5').addClass('col-sm-12');
      sticky_row.children('.col-9').removeClass('col-9').addClass('col-12');
      sticky_row.children('.col-3').removeClass('col-3').addClass('col-12');
      stickyWrapper.height('auto');
    }
  };
  
  // Find all data-toggle="sticky-onscroll" elements
  $('[data-toggle="sticky-onscroll"]').each(function() {
    var sticky = $(this);
    var stickyWrapper = $('<div>').addClass('sticky-wrapper'); // insert hidden element to maintain actual top offset on page
    sticky.before(stickyWrapper);
    sticky.addClass('sticky');
    
    // Scroll & resize events
    $(window).bind('scroll.sticky-onscroll resize.sticky-onscroll', function() {
      stickyToggle(sticky, stickyWrapper, $(this));
    });
    
    // On page load
    stickyToggle(sticky, stickyWrapper, $(window));
  });
});