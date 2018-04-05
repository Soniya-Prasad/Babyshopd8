(function($) {
  /* $(document).ajaxStop(function() {
       $('#views-exposed-form-blogs-page-1 .select-nav').insertAfter('.container h1');
    });   */
    
	Drupal.behaviors.selectNav = {
    attach: function (context, settings) {

      if($('.select-nav_mobile').hasClass('mobileNavapplied')){}else{
        $('.select-nav_mobile').addClass('mobileNavapplied');
        $(".select-nav_mobile a.sub-opener").click(function(){
          $(".select-nav_mobile").toggleClass("sub-active");
        });
        $(".select-nav_mobile ul li .radio").click(function(){
          $(".select-nav_mobile").removeClass("sub-active")
        });
      }
      $('#views-exposed-form-blogs-page-1 .select-nav').insertAfter('.container h1');
      $( "#select-nav ul li div input" ).each(function( index ) {
        if($(this).attr('checked') === 'checked'){
          $(this).parent().addClass('blogactive');
        }
      });
      $( ".path-inspiration .select-nav_mobile ul li div input" ).each(function( index ) {
        if($(this).attr('checked') === 'checked'){
         $(this).parents('li').addClass('blog_active');
        }
      });

      if ($('#select-nav ul li input#edit-type-product').length > 0 && $('#select-nav ul li:eq(1)').hasClass('blog_active') == false) {
        var product_html = $('#select-nav ul li input#edit-type-product').parents('li').html();
        $('#select-nav ul li input#edit-type-product').parents('li').remove();
        $('#select-nav ul li:eq(0)').after('<li>'+product_html+'</li>');
      }

      if ($('.select-nav.select-nav_mobile ul li input#edit-type-product').length > 0 && $('.select-nav.select-nav_mobile ul li:eq(1)').hasClass('blog_active') == false) {
        var product_html = $('.select-nav.select-nav_mobile ul li input#edit-type-product').parents('li').html();
        $('.select-nav.select-nav_mobile ul li input#edit-type-product').parents('li').remove();
        $('.select-nav.select-nav_mobile ul li:eq(0)').after('<li>'+product_html+'</li>');
      }

      $('#views-exposed-form-search-page-1 #edit-type .select-nav ul li label').on("click", function() {
        $('#views-exposed-form-search-page-1 #edit-type ul li').removeClass('blog_active');
        $('#views-exposed-form-search-page-1 #edit-type .select-nav_mobile').removeClass('sub-active');
        $(this).find( "input" ).prop('checked','checked');
        $(this).parents('li').addClass('blog_active');
        $('#views-exposed-form-search-page-1 button:submit').click();
      });

      $( "ul.search-results li" ).each(function(key) {
        var type = $('ul.search-results li:eq('+key+') .type').html();
        if(type== 'Product' || type== 'FAQ' || type== 'Offer') {

          //var link_url = document.location.href.replace('search' , '' );
          var comp_url = document.location.href.split('search');
          var link_url = comp_url[0];
          if($('ul.search-results li:eq('+key+') .reference_url a').length > 0) {
            link_url = $('ul.search-results li:eq('+key+') .reference_url a').prop('href');
          }

          var prev_url = $('ul.search-results li:eq('+key+') h2 a').prop('href');
          var comp_url = prev_url.split(document.location.origin + document.location.pathname);
          if(comp_url.length > 1) {
            prev_url = comp_url[1];
          }

          if (prev_url.indexOf(link_url) == '-1'){
            $('ul.search-results li:eq('+key+') h2 a').prop('href',link_url+prev_url);
          }
        }
      });

      $(".select-nav_mobile ul li a").click(function(){
        $(".select-nav_mobile").removeClass("sub-active");
      });

      if ($('.subscribe-popup').length>0) {
        var popup_top = ($(window).height() - $('.subscribe-popup').height())/2;
        $('.subscribe-popup').css("top", popup_top + "px");

        if($(window).width()<767) {
          $('.subscribe-popup #edit-email').focusin(function() {
            $('.subscribe-popup').css("top","0");
          });

          $('.subscribe-popup #edit-email').focusout(function() {
            $('.subscribe-popup').css("top", popup_top + "px");
          });
        }
      }

      if ($('.youtube a img').length>0) {
        $('.youtube a img').after('<span class="bg-play"></span>');
      }
      

    }
  };

  $('#views-exposed-form-search-page-1 #edit-type #select-nav ul li:eq(0),#views-exposed-form-search-page-1 #edit-type .select-nav_mobile ul li:eq(0)').addClass('blog_active');

	$('.alert-success').insertAfter('.contact-message-form #edit-actions');
	$('.alert-danger').insertBefore('.contact-message-form #edit-field-feed-wrapper');
	$('.loader').hide();
	$('.contact-message-form #edit-submit').click(function(){
		var feed = $("textare[name='field_field_tell_us_your_feedbac[0][value]']").val();
		var name = $("input[name='field_name[0][value]']").val();
		var email = $("input[name='field_email[0][value]").val();
		var phone = $("input[name='field__phone_number[0][value]']").val();
		if(feed != '' && name != '' && email != '' && phone != '') {
			$('.loader').show();
		}
	});


  // Hide the apply button.
  $('.views-exposed-form button:submit').hide();
  $('#block-views-block-related-article-block-1').insertAfter('.single-post');
 /* $('.contact-message-contact-us-form #edit-submit').on("click", function() {
    alert('fgdfg');
      $('.contact-essage-contact-us-form #edit-submit').after().addClass('ajax-throbber glyphicon-spin icon glyphicon glyphicon-refresh');


});*/


//$('#views-exposed-form-blogs-page-1 .category-nav').hide();
$('.view-media-center .pager-nav').insertAfter('.media-center .column-left ul');
$('#sidebar_second').insertAfter('.column-left');
$("#edit-field-field-tell-us-your-feedbac-0-format").hide();

if ($('.category-nav ul').length > 0) {

    var query_string = getUrlVars();
    if(query_string.hasOwnProperty('tag')) {
      $('.category-nav ul li input[value='+query_string.tag+']').parent('label').addClass('active');
    }

    if(query_string.hasOwnProperty('brand')) {
      $('.category-nav ul li input[value='+query_string.brand+']').parent('label').addClass('active');
    }

    var max = 11;
    if($('.category-nav ul:eq(1) li').length > max) {
      $('.category-nav ul:eq(1)')
      .find('li:gt('+max+')')
      .hide()
      .end()
      .append(
        $('<li><a class="show_more" href="javascript:void(0)">'+Drupal.t('Show more')+'</a></li>').click( function(){
          $(this).siblings(':hidden').show().end().remove();
        })
      );
    }

    $('#edit-tag .category-nav ul li label').on("click", function() {

      var tid = $(this).prop('value');
      $('#edit-tag .category-nav select').val(tid);

      if (history.pushState) {
        var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?tag=' + tid;
        window.history.pushState({path:newurl},'',newurl);
      }

    });

    $('#edit-brand .category-nav ul li label').on("click", function() {

      var tid = $(this).prop('value');
      $('#edit-brand .category-nav select').val(tid);

      if (history.pushState) {
        var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?brand=' + tid;
        window.history.pushState({path:newurl},'',newurl);
      }

    });

  }

  $('.results-search button.reset').on("click", function() {
    $('.results-search input[type="search"]').val('');
    $('.result-head span').html('""');
    $('#views-exposed-form-search-page-1 input#edit-query').val('');
    $('#select-nav ul li:eq(0) label').click();

    $('.logo').show();
    $('.results-search').addClass('hide-block');
    $('.header-frame').removeClass('hide-block');
    $('.main-nav').removeClass('hide-block');
  });

  if($('.youtube-container iframe').length > 0) {
    var iframe_height = $('.slide .bg-stretch:eq(0)').innerHeight();
    $('.youtube-container iframe').attr('height',iframe_height);
  }


  if($('.search-box .btn-search').length > 0) {
    $('.search-box .btn-search').html('<i class="icon-search"></i>').click(function(){
      return searchLocations();
    });
  }

  if($('.search #edit-search-button').length > 0) {
    $('.search #edit-search-button').html('<span class="tablet-hidden">'+Drupal.t('Find Stores')+'</span><i class="icon-search tablet-visible"></i>').click(function(){
      return searchLocations();
    });
  }

  $('.nav-opener').on("click", function() {
    $('.nav-container ul:eq(0)').addClass('accordion');
    $('.nav-container ul:eq(1)').addClass('sub-nav');
    //alert($(window).width());
    if($(window).width() < 767) {
      $('.nav-container ul:eq(0)').show();
    }
    else {
      $('.nav-container ul:eq(0)').hide();
    }
  });

  $(window).load(function() {

    if($('.path-faq').length > 0) {
      var hash = window.location.hash;
      if(hash.length > 0) {
        $( ".select-nav ul li a" ).each(function(key) {
          var a_selector = $(".select-nav ul li a:eq("+key+")");
          var url = a_selector.prop('href');
          var comp_url = url.split(document.location.origin + document.location.pathname);
          var url = comp_url[1];
          if(url==hash) {
            a_selector.parent().addClass('active');
            a_selector.click();
          }
        });
      }
      else {
        $(".select-nav ul li:eq(0)").addClass('active');
        $(".select-nav ul li:eq(0) a").click();
      }
    }

  });



})(jQuery);

function getUrlVars() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}
