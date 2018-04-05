(function($) {
  $(".slick-slider").slick({
    infinite: true,
    slidesToShow: 1,
    centerPadding: 0,
    variableWidth: true,
    centerMode: true,
    prevArrow: "<span class='js-carousel-prev icon-arrow-left slick-arrow'></span>",
    nextArrow: "<span class='js-carousel-next icon-arrow-right slick-arrow'></span>",
    autoplay: true,
    autoplaySpeed: 5000,
    responsive: [{
      breakpoint: 767,
      settings: {
        centerMode: true,
        variableWidth: true,
        lazyLoad: "ondemand"
      }
    }]
  });

  if($(window).width() > 767) {
    $(".slick-current img").load(function() {
      var a = ($(".slick-slider").width() - $(".slick-current").width()) / 2;
      $(".js-carousel-prev").width(a);
      var b = ($(".slick-slider").width() - $(".slick-current").width()) / 2;
      $(".js-carousel-next").width(b)
    });
  }

  $("#play-video").on("click", function() {
    $(this).next().show();
    $(this).hide();
    url = $("#row8 iframe").attr("src") + "&autoplay=1";
    $("#row8 iframe").attr("src", url);
  })
  $("#play-video2").on("click", function() {
    $(this).next().show();
    $(this).hide();
    url = $("#row9 iframe").attr("src") + "&autoplay=1";
    $("#row9 iframe").attr("src", url);
  })

})(jQuery);