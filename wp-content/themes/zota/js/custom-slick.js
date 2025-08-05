'use strict';

class Carousel {
  CarouselSlick() {
    var _this = this;

    if (jQuery(".owl-carousel[data-carousel=owl]:visible").length === 0) return;
    jQuery('.owl-carousel[data-carousel=owl]:visible:not(.scroll-init)').each(function () {
      _this._initCarouselSlick(jQuery(this));
    });
    jQuery('.owl-carousel[data-carousel=owl]:visible.scroll-init').waypoint(function () {
      let _this2 = jQuery(jQuery(this)[0].element);

      _this._initCarouselSlick(_this2);
    }, {
      offset: '100%'
    });
  }

  CarouselSlickQuickView() {
    jQuery('#tbay-quick-view-content .woocommerce-product-gallery__wrapper').each(function () {
      let _this = jQuery(this);

      if (_this.children().length == 0 || _this.hasClass("slick-initialized")) {
        return;
      }

      var _config = {};
      _config.slidesToShow = 1;
      _config.infinite = false;
      _config.focusOnSelect = true;
      _config.dots = true;
      _config.arrows = false;
      _config.adaptiveHeight = true;
      _config.mobileFirst = true;
      _config.vertical = false;
      _config.cssEase = 'ease';
      _config.settings = "unslick";
      _config.rtl = _this.parent('.woocommerce-product-gallery').data('rtl') === 'yes';
      jQuery(".variations_form").on("woocommerce_variation_select_change", function () {
        _this.slick("slickGoTo", 0);
      });

      _this.slick(_config);
    });
  }

  _initCarouselSlick(_this2) {
    var _this = this;

    if (_this2.hasClass("slick-initialized")) {
      return;
    }

    if (!jQuery.browser.mobile) {
      _this2.slick(_this._getSlickConfigOption(_this2));
    } else if (!_this2.data('unslick')) {
      _this2.slick(_this._getSlickConfigOption(_this2));
    }
  }

  _getSlickConfigOption(el) {
    var slidesToShow = jQuery(el).data('items'),
        rows = jQuery(el).data('rows') ? parseInt(jQuery(el).data('rows')) : 1,
        desktop = jQuery(el).data('desktopslick') ? jQuery(el).data('desktopslick') : slidesToShow,
        desktopsmall = jQuery(el).data('desktopsmallslick') ? jQuery(el).data('desktopsmallslick') : slidesToShow,
        tablet = jQuery(el).data('tabletslick') ? jQuery(el).data('tabletslick') : slidesToShow,
        landscape = jQuery(el).data('landscapeslick') ? jQuery(el).data('landscapeslick') : 2,
        mobile = jQuery(el).data('mobileslick') ? jQuery(el).data('mobileslick') : 2;
    let enonumber = slidesToShow < jQuery(el).children().length ? true : false,
        enonumber_mobile = 2 < jQuery(el).children().length ? true : false;
    let pagination = enonumber ? Boolean(jQuery(el).data('pagination')) : false,
        nav = enonumber ? Boolean(jQuery(el).data('nav')) : false,
        loop = enonumber ? Boolean(jQuery(el).data('loop')) : false,
        auto = enonumber ? Boolean(jQuery(el).data('auto')) : false;
    var _config = {};
    _config.dots = pagination;
    _config.arrows = nav;
    _config.infinite = loop;
    _config.speed = jQuery(el).data('speed') ? jQuery(el).data('speed') : 500;
    _config.autoplay = auto;
    _config.autoplaySpeed = jQuery(el).data('autospeed') ? jQuery(el).data('autospeed') : 2000;
    _config.cssEase = 'ease';
    _config.slidesToShow = slidesToShow;
    _config.slidesToScroll = slidesToShow;
    _config.mobileFirst = true;
    _config.vertical = false;
    _config.prevArrow = '<button type="button" class="slick-prev"><i class="tb-icon tb-icon-arrow-left1"></i></button>';
    _config.nextArrow = '<button type="button" class="slick-next"><i class="tb-icon tb-icon-arrow-right1"></i></button>';
    _config.rtl = jQuery('html').attr('dir') == 'rtl';

    if (rows > 1) {
      _config.slidesToShow = 1;
      _config.slidesToScroll = 1;
      _config.rows = rows;
      _config.slidesPerRow = slidesToShow;
      var settingsFull = {
        slidesPerRow: slidesToShow
      },
          settingsDesktop = {
        slidesPerRow: desktop
      },
          settingsDesktopsmall = {
        slidesPerRow: desktopsmall
      },
          settingsTablet = {
        slidesPerRow: tablet,
        infinite: false
      },
          settingsLandscape = jQuery(el).data('unslick') ? "unslick" : {
        slidesPerRow: landscape,
        infinite: false
      },
          settingsMobile = jQuery(el).data('unslick') ? "unslick" : {
        slidesPerRow: mobile,
        infinite: false
      };
    } else {
      var settingsFull = {
        slidesToShow: slidesToShow,
        slidesToScroll: slidesToShow
      },
          settingsDesktop = {
        slidesToShow: desktop,
        slidesToScroll: desktop
      },
          settingsDesktopsmall = {
        slidesToShow: desktopsmall,
        slidesToScroll: desktopsmall
      },
          settingsTablet = {
        slidesToShow: tablet,
        slidesToScroll: tablet,
        infinite: false
      },
          settingsLandscape = jQuery(el).data('unslick') ? "unslick" : {
        slidesToShow: landscape,
        slidesToScroll: landscape,
        infinite: false
      },
          settingsMobile = jQuery(el).data('unslick') ? "unslick" : {
        slidesToShow: mobile,
        slidesToScroll: mobile,
        infinite: false
      };
    }

    var settingsArrows = jQuery(el).data('nav') ? {
      arrows: false,
      dots: enonumber_mobile
    } : '';
    settingsLandscape = jQuery(el).data('unslick') ? settingsLandscape : jQuery.extend(true, settingsLandscape, settingsArrows);
    settingsMobile = jQuery(el).data('unslick') ? settingsMobile : jQuery.extend(true, settingsMobile, settingsArrows);
    _config.responsive = [{
      breakpoint: 1600,
      settings: settingsFull
    }, {
      breakpoint: 1199,
      settings: settingsDesktop
    }, {
      breakpoint: 991,
      settings: settingsDesktopsmall
    }, {
      breakpoint: 767,
      settings: settingsTablet
    }, {
      breakpoint: 575,
      settings: settingsLandscape
    }, {
      breakpoint: 0,
      settings: settingsMobile
    }];
    return _config;
  }

  getSlickTabs() {
    var _this = this;

    jQuery('ul.nav-tabs li a').on('shown.bs.tab', event => {
      let carouselItemTab = jQuery(jQuery(event.target).attr("href")).find(".owl-carousel[data-carousel=owl]:visible");
      let carouselItemDestroy = jQuery(jQuery(event.relatedTarget).attr("href")).find(".owl-carousel[data-carousel=owl]");

      if (carouselItemDestroy.hasClass("slick-initialized")) {
        carouselItemDestroy.slick('unslick');
      }

      if (!carouselItemTab.hasClass("slick-initialized")) {
        carouselItemTab.slick(_this._getSlickConfigOption(carouselItemTab));
      }
    });
  }

}

class Slider {
  tbaySlickSlider() {
    jQuery('.flex-control-thumbs').each(function () {
      if (jQuery(this).children().length == 0) {
        return;
      }

      var _config = {};
      _config.vertical = jQuery(this).parent('.woocommerce-product-gallery').data('layout') === 'vertical';
      _config.slidesToShow = jQuery(this).parent('.woocommerce-product-gallery').data('columns');
      _config.infinite = false;
      _config.focusOnSelect = true;
      _config.settings = "unslick";
      _config.prevArrow = '<span class="owl-prev"></span>';
      _config.nextArrow = '<span class="owl-next"></span>';
      _config.rtl = jQuery(this).parent('.woocommerce-product-gallery').data('rtl') === 'yes' && jQuery(this).parent('.woocommerce-product-gallery').data('layout') !== 'vertical';
      _config.responsive = [{
        breakpoint: 1200,
        settings: {
          vertical: false,
          slidesToShow: 4
        }
      }];
      jQuery(this).slick(_config);
    });
  }

}

jQuery(document).ready(function () {
  var carousel = new Carousel();
  carousel.CarouselSlick();
  carousel.getSlickTabs();
});
jQuery(document.body).on('wc-product-gallery-after-init', () => {
  var slider = new Slider();
  slider.tbaySlickSlider();
});
setTimeout(function () {
  jQuery(document.body).on('tbay_quick_view', () => {
    var carousel = new Carousel();
    carousel.CarouselSlickQuickView();
  });
  jQuery(document.body).on('tbay_carousel_slick', () => {
    var carousel = new Carousel();
    carousel.CarouselSlick();
  });
}, 2000);

var CustomSlickHandler = function (jQueryscope, jQuery) {
  var carousel = new Carousel();
  carousel.CarouselSlick();
};

jQuery(window).on('elementor/frontend/init', function () {
  if (typeof zota_settings !== "undefined" && elementorFrontend.isEditMode() && Array.isArray(zota_settings.elements_ready.slick)) {
    jQuery.each(zota_settings.elements_ready.slick, function (index, value) {
      elementorFrontend.hooks.addAction('frontend/element_ready/tbay-' + value + '.default', CustomSlickHandler);
    });
  }
});
