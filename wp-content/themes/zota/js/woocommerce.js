'use strict';

class MiniCart {
  miniCartAll() {
    var _this = this;

    jQuery(".dropdown-toggle").dropdown();

    _this.remove_click_Outside();

    jQuery("#tbay-offcanvas-main .btn-toggle-canvas").on("click", function () {
      jQuery('#tbay-offcanvas-main').removeClass('active');
    });
    jQuery(".mini-cart").click(function (e) {
      if (!(jQuery(e.currentTarget).parents('.tbay-topcart').length > 0)) return;
      jQuery('.tbay-dropdown-cart').addClass('active');
      jQuery(document.body).trigger('tbay_refreshed_mini_cart_top');
    });
    jQuery(".offcanvas-close").click(function (e) {
      e.preventDefault();
      jQuery('.tbay-dropdown-cart').removeClass('active');
      jQuery('#wrapper-container').removeClass('offcanvas-right');
    });
    jQuery(".mini-cart.v2").click(function (e) {
      e.preventDefault();
      jQuery('#wrapper-container').toggleClass(e.currentTarget.dataset.offcanvas);
    });
  }

  remove_click_Outside() {
    let win = jQuery(window);
    win.on("click.Bst,click touchstart tap", function (event) {
      let box_popup = jQuery('.tbay_header-template .tbay-element-mini-cart .dropdown-content .widget_shopping_cart_content, .tbay-element-mini-cart .widget-header-cart > span, .tbay-element-mini-cart .heading-title, .topbar-device-mobile .tbay-element-mini-cart .dropdown-content .widget_shopping_cart_content, .tbay-element-mini-cart .dropdown-content, a.ajax_add_to_cart, .tbay-element-mini-cart .cart_list a.remove');
      let cart_right_active = !jQuery('.tbay-dropdown-cart').hasClass('active');
      if (cart_right_active) return;

      if (box_popup.has(event.target).length == 0 && !box_popup.is(event.target)) {
        jQuery('#wrapper-container').removeClass('offcanvas-right');
        jQuery('#wrapper-container').removeClass('offcanvas-left');
        jQuery('.tbay-dropdown-cart').removeClass('active');
        jQuery('#tbay-offcanvas-main,.tbay-offcanvas').removeClass('active');
        jQuery("#tbay-dropdown-cart").hide(500);
        return;
      }
    });
  }

  minicartTopContent() {
    let cart_content = jQuery('.tbay_header-template .tbay-element-mini-cart .dropdown-content .widget_shopping_cart_content'),
        cart_top = cart_content.parent().find('.widget-header-cart').outerHeight(),
        list_cart = jQuery('.tbay_header-template .tbay-element-mini-cart .dropdown-content .widget_shopping_cart_content .mcart-border ul.product_list_widget'),
        group_button = list_cart.next().outerHeight();
    cart_content.css("top", cart_top);
    list_cart.css("bottom", group_button + 10);
  }

  minicartTopContentmobile() {
    let cart_content = jQuery('.topbar-device-mobile .tbay-element-mini-cart .dropdown-content .widget_shopping_cart_content'),
        cart_top = cart_content.parent().find('.widget-header-cart').outerHeight(),
        list_cart = jQuery('.topbar-device-mobile .tbay-element-mini-cart .dropdown-content .widget_shopping_cart_content .mcart-border ul.product_list_widget'),
        group_button = list_cart.next().outerHeight();
    cart_content.css("top", cart_top);
    list_cart.css("bottom", group_button + 10);
  }

}

const ADDED_TO_CART_EVENT = "added_to_cart";
const LOADMORE_AJAX_HOME_PAGE = "tbay_more_post_ajax";
const LOADMORE_AJAX_SHOP_PAGE = "tbay_pagination_more_post_ajax";
const LIST_POST_AJAX_SHOP_PAGE = "tbay_list_post_ajax";
const GRID_POST_AJAX_SHOP_PAGE = "tbay_grid_post_ajax";

class AjaxCart {
  constructor() {
    if (typeof zota_settings === "undefined") return;

    let _this = this;

    _this.ajaxCartPosition = zota_settings.cart_position;

    switch (_this.ajaxCartPosition) {
      case "popup":
        _this._initAjaxPopup();

        break;

      case "left":
        _this._initAjaxCartLeftOrRight("left");

        break;

      case "right":
        _this._initAjaxCartLeftOrRight("right");

        break;
    }

    MiniCart.prototype.miniCartAll();

    _this._initEventRemoveProduct();

    _this._initEventMiniCartAjaxQuantity();

    jQuery(window).on("resize", () => {
      if (zota_settings.mobile && jQuery(window).width() < 992) {
        MiniCart.prototype.minicartTopContentmobile();
      } else {
        MiniCart.prototype.minicartTopContent();
      }
    });
    jQuery(document.body).on('wc_fragments_refreshed', function () {
      if (zota_settings.mobile && jQuery(window).width() < 992) {
        MiniCart.prototype.minicartTopContentmobile();
      } else {
        MiniCart.prototype.minicartTopContent();
      }
    });
    jQuery(document.body).on('wc_fragments_loaded', function () {
      if (zota_settings.mobile && jQuery(window).width() < 992) {
        MiniCart.prototype.minicartTopContentmobile();
      } else {
        MiniCart.prototype.minicartTopContent();
      }
    });
    jQuery(document.body).on('tbay_refreshed_mini_cart_top', function () {
      if (zota_settings.mobile && jQuery(window).width() < 992) {
        MiniCart.prototype.minicartTopContentmobile();
      } else {
        MiniCart.prototype.minicartTopContent();
      }
    });
  }

  _initAjaxPopupContent(button) {
    let cart_modal = jQuery('#tbay-cart-modal'),
        cart_modal_content = jQuery('#tbay-cart-modal').find('.modal-body-content');
        zota_settings.popup_cart_noti;
        let string = '';
    cart_modal.addClass('active');
    jQuery(`.ajax_cart_popup`).trigger('active_ajax_cart_popup');
    let title = button.attr('aria-label');
    if (typeof title === "undefined") return;
    string += `<div class="popup-cart">`;
    string += `<div class="main-content">`;
    string += `<i class="tb-icon tb-icon-check-circle"></i>`;
    string += `<p class="cart-notification">${title}</p>`;

    if (!wc_add_to_cart_params.is_cart) {
      string += `<a class="button view-cart" href="${wc_add_to_cart_params.cart_url}" title="${wc_add_to_cart_params.i18n_view_cart}">${wc_add_to_cart_params.i18n_view_cart}</a>`;
    }

    string += `</div>`;
    string += `</div>`;

    if (typeof string !== "undefined") {
      cart_modal_content.append(string);
    }
  }

  _initAjaxPopup() {
    var _this = this;

    if (typeof wc_add_to_cart_params === 'undefined') {
      return false;
    }

    if (zota_settings.ajax_popup_quick) {
      jQuery(`.ajax_cart_popup`).on('click', '.ajax_add_to_cart', function (e) {
        let button = jQuery(this);

        _this._initAjaxPopupContent(button);
      });
    } else {
      jQuery(`.ajax_cart_popup`).on(ADDED_TO_CART_EVENT, function (ev, fragmentsJSON, cart_hash, button) {
        if (typeof fragmentsJSON == 'undefined') fragmentsJSON = jQuery.parseJSON(sessionStorage.getItem(wc_cart_fragments_params.fragment_name));

        _this._initAjaxPopupContent(button);
      });
    }

    jQuery(`.ajax_cart_popup`).on('active_ajax_cart_popup', () => {
      if (jQuery('#tbay-cart-modal').hasClass('active')) {
        jQuery('#tbay-cart-modal').off().on('click', function () {
          let _this = jQuery(this);

          jQuery(this).closest('#tbay-cart-modal').removeClass('active');
          setTimeout(function () {
            _this.closest('#tbay-cart-modal').find('.modal-body .modal-body-content').empty();
          }, 300);
        });
      }
    });
  }

  _initAjaxCartLeftOrRight(position) {
    jQuery(`.ajax_cart_${position}`).on(ADDED_TO_CART_EVENT, function () {
      jQuery('.tbay-dropdown-cart').addClass('active');
      jQuery(document.body).trigger('wc_fragments_refreshed');
    });
  }

  _initEventRemoveProduct() {
    if (typeof wc_add_to_cart_params === 'undefined') {
      return false;
    }

    jQuery(document).on('click', '.mini_cart_content a.remove', event => {
      this._onclickRemoveProduct(event);

      event.stopPropagation();
    });
  }

  _onclickRemoveProduct(event) {
    event.preventDefault();
    var product_id = jQuery(event.currentTarget).attr("data-product_id"),
        cart_item_key = jQuery(event.currentTarget).attr("data-cart_item_key"),
        product_container = jQuery(event.currentTarget).parents('.mini_cart_item'),
        thisItem = jQuery(event.currentTarget).closest('.widget_shopping_cart_content');
    product_container.block({
      message: null,
      overlayCSS: {
        cursor: 'none'
      }
    });

    this._callRemoveProductAjax(product_id, cart_item_key, thisItem, event);
  }

  _callRemoveProductAjax(product_id, cart_item_key, thisItem, event) {
    jQuery.ajax({
      type: 'POST',
      dataType: 'json',
      url: wc_add_to_cart_params.ajax_url,
      data: {
        action: "product_remove",
        product_id: product_id,
        nonce: zota_settings.wp_product_remove_nonce,
        cart_item_key: cart_item_key
      },
      beforeSend: function () {
        thisItem.find('.mini_cart_content').append('<div class="ajax-loader-wapper"><div class="ajax-loader"></div></div>').fadeTo("slow", 0.3);
        event.stopPropagation();
      },
      success: response => {
        this._onRemoveSuccess(response, product_id);

        jQuery(document.body).trigger('wc_fragments_refreshed');
      }
    });
  }

  _onRemoveSuccess(response, product_id) {
    if (!response || response.error) return;
    var fragments = response.fragments;

    if (fragments) {
      jQuery.each(fragments, function (key, value) {
        jQuery(key).replaceWith(value);
      });
    }

    jQuery('.add_to_cart_button.added[data-product_id="' + product_id + '"]').removeClass("added").next('.wc-forward').remove();
  }

  _initEventMiniCartAjaxQuantity() {
    jQuery('body').on('change', '.mini_cart_content .qty', function (event) {
      event.preventDefault();
      var urlAjax = zota_settings.wc_ajax_url.toString().replace('%%endpoint%%', 'zota_quantity_mini_cart'),
          input = jQuery(this),
          wrap = jQuery(input).parents('.mini_cart_content'),
          hash = jQuery(input).attr('name').replace(/cart\[([\w]+)\]\[qty\]/g, "$1"),
          max = parseFloat(jQuery(input).attr('max'));

      if (!max) {
        max = false;
      }

      var quantity = parseFloat(jQuery(input).val());

      if (max > 0 && quantity > max) {
        jQuery(input).val(max);
        quantity = max;
      }

      jQuery.ajax({
        url: urlAjax,
        type: 'POST',
        dataType: 'json',
        cache: false,
        data: {
          hash: hash,
          quantity: quantity
        },
        beforeSend: function () {
          wrap.append('<div class="ajax-loader-wapper"><div class="ajax-loader"></div></div>').fadeTo("slow", 0.3);
          event.stopPropagation();
        },
        success: function (data) {
          if (data && data.fragments) {
            jQuery.each(data.fragments, function (key, value) {
              if (jQuery(key).length) {
                jQuery(key).replaceWith(value);
              }
            });

            if (typeof $supports_html5_storage !== 'undefined' && $supports_html5_storage) {
              sessionStorage.setItem(wc_cart_fragments_params.fragment_name, JSON.stringify(data.fragments));
              set_cart_hash(data.cart_hash);

              if (data.cart_hash) {
                set_cart_creation_timestamp();
              }
            }

            jQuery(document.body).trigger('wc_fragments_refreshed');
          }
        }
      });
    });
  }

}

class WishList {
  constructor() {
    this._onChangeWishListItem();
  }

  _onChangeWishListItem() {
    jQuery(document).on('added_to_wishlist removed_from_wishlist', () => {
      var counter = jQuery('.count_wishlist');
      if (counter.length === 0) return;
      jQuery.ajax({
        url: yith_wcwl_l10n.ajax_url,
        data: {
          action: 'yith_wcwl_update_wishlist_count'
        },
        dataType: 'json',
        success: function (data) {
          counter.html(data.count);
        },
        beforeSend: function () {
          counter.block();
        },
        complete: function () {
          counter.unblock();
        }
      });
    });
  }

}

class ProductItem {
  initAddButtonQuantity() {
    let input = jQuery('.quantity input');
    input.each(function () {
      if (jQuery(this).parent('.box').length) return;
      jQuery(this).wrap('<span class="box"></span>');

      if (jQuery(this).attr('type') == 'hidden') {
        jQuery(this).parents('.quantity').addClass('hidden');
      } else {
        jQuery(`<button class="minus" type="button" value="&nbsp;">${zota_settings.quantity_minus}</button>`).insertBefore(jQuery(this));
        jQuery(`<button class="plus" type="button" value="&nbsp;">${zota_settings.quantity_plus}</button>`).insertAfter(jQuery(this));
      }
    });
  }

  initOnChangeQuantity(callback) {
    if (typeof zota_settings === "undefined") return;
    this.initAddButtonQuantity();
    jQuery(document).off('click', '.plus, .minus').on('click', '.plus, .minus', function (event) {
      event.preventDefault();
      var qty = jQuery(this).closest('.quantity').find('.qty'),
          currentVal = parseFloat(qty.val()),
          max = jQuery(qty).attr("max"),
          min = jQuery(qty).attr("min"),
          step = jQuery(qty).attr("step");
      currentVal = !currentVal || currentVal === '' || currentVal === 'NaN' ? 0 : currentVal;
      max = max === '' || max === 'NaN' ? '' : max;
      min = min === '' || min === 'NaN' ? 0 : min;
      step = step === 'any' || step === '' || step === undefined || parseFloat(step) === NaN ? 1 : step;

      if (jQuery(this).is('.plus')) {
        if (max && (max == currentVal || currentVal > max)) {
          qty.val(max);
        } else {
          qty.val(currentVal + parseFloat(step));
        }
      } else {
        if (min && (min == currentVal || currentVal < min)) {
          qty.val(min);
        } else if (currentVal > 0) {
          qty.val(currentVal - parseFloat(step));
        }
      }

      if (callback && typeof callback == "function") {
        jQuery(this).parent().find('input').trigger("change");
        callback();

        if (jQuery(event.target).parents('.mini_cart_content').length > 0) {
          event.stopPropagation();
        }
      }
    });
  }

  _initSwatches() {
    if (jQuery('.tbay-swatches-wrapper li a').length === 0) return;
    jQuery('body').on('click', '.tbay-swatches-wrapper li a', function (event) {
      event.preventDefault();
      let $active = false;
      let $parent = jQuery(this).closest('.product-block');

      if ($parent.find('.tbay-product-slider-gallery').hasClass('slick-initialized')) {
        var $image = $parent.find('.image .slick-current img:eq(0)');
      } else {
        var $image = $parent.find('.image img:eq(0)');
      }

      if (!jQuery(this).closest('ul').hasClass('active')) {
        jQuery(this).closest('ul').addClass('active');
        $image.attr('data-old', $image.attr('src'));
      }

      if (!jQuery(this).hasClass('selected')) {
        jQuery(this).closest('ul').find('li a').each(function () {
          if (jQuery(this).hasClass('selected')) {
            jQuery(this).removeClass('selected');
          }
        });
        jQuery(this).addClass('selected');
        $parent.addClass('product-swatched');
        $active = true;
      } else {
        $image.attr('src', $image.data('old'));
        jQuery(this).removeClass('selected');
        $parent.removeClass('product-swatched');
      }

      if (!$active) return;

      if (typeof jQuery(this).data('imageSrc') !== 'undefined') {
        $image.attr('src', jQuery(this).data('imageSrc'));
      }

      if (typeof jQuery(this).data('imageSrcset') !== 'undefined') {
        $image.attr('srcset', jQuery(this).data('imageSrcset'));
      }

      if (typeof jQuery(this).data('imageSizes') !== 'undefined') {
        $image.attr('sizes', jQuery(this).data('imageSizes'));
      }
    });
  }

  _initQuantityMode() {
    if (typeof zota_settings === "undefined" || !zota_settings.quantity_mode) return;
    jQuery(".woocommerce .products").on("click", ".quantity .qty", function () {
      return false;
    });
    jQuery(".woocommerce .products").on("change input", ".quantity .qty", function () {
      var add_to_cart_button = jQuery(this).parents(".product").find(".add_to_cart_button");
      add_to_cart_button.attr("data-quantity", jQuery(this).val());
    });
    jQuery(".woocommerce .products").on("keypress", ".quantity .qty", function (e) {
      if ((e.which || e.keyCode) === 13) {
        jQuery(this).parents(".product").find(".add_to_cart_button").trigger("click");
      }
    });
  }

}

class Cart {
  constructor() {
    this._initEventChangeQuantity();

    jQuery(document.body).on('updated_wc_div', () => {
      this._initEventChangeQuantity();

      if (typeof wc_add_to_cart_variation_params !== 'undefined') {
        jQuery('.variations_form').each(function () {
          jQuery(this).wc_variation_form();
        });
      }
    });
    jQuery(document.body).on('cart_page_refreshed', () => {
      this._initEventChangeQuantity();
    });
    jQuery(document.body).on('tbay_display_mode', () => {
      this._initEventChangeQuantity();
    });
  }

  _initEventChangeQuantity() {
    if (jQuery("body.woocommerce-cart [name='update_cart']").length === 0) {
      new ProductItem().initOnChangeQuantity(() => {});
    } else {
      new ProductItem().initOnChangeQuantity(() => {
        jQuery('.woocommerce-cart-form :input[name="update_cart"]').prop('disabled', false);

        if (typeof zota_settings !== "undefined" && zota_settings.ajax_update_quantity) {
          jQuery("[name='update_cart']").trigger('click');
        }
      });
    }
  }

}

class Checkout {
  constructor() {
    this._toogleWoocommerceIcon();
  }

  _toogleWoocommerceIcon() {
    if (jQuery('.woocommerce-info a').length < 1) {
      return;
    }

    jQuery('.woocommerce-info a').click(function () {
      jQuery(this).find('.icons').toggleClass('icon-arrow-down').toggleClass('icon-arrow-up');
    });
  }

}

class LoadMore {
  constructor() {
    if (typeof zota_settings === "undefined") return;

    this._initLoadMoreOnHomePage();

    this._initLoadMoreOnShopPage();

    this._int_berocket_lmp_end();
  }

  _initLoadMoreOnHomePage() {
    var _this = this;

    jQuery('.more_products').each(function () {
      var id = jQuery(this).data('id');
      jQuery(`#more_products_${id} a[data-loadmore="true"]`).click(function () {
        var event = jQuery(this);

        _this._callAjaxLoadMore({
          data: {
            action: LOADMORE_AJAX_HOME_PAGE,
            paged: jQuery(this).data('paged') + 1,
            number: jQuery(this).data('number'),
            columns: jQuery(this).data('columns'),
            layout: jQuery(this).data('layout'),
            type: jQuery(this).data('type'),
            category: jQuery(this).data('category'),
            screen_desktop: jQuery(this).data('desktop'),
            screen_desktopsmall: jQuery(this).data('desktopsmall'),
            screen_tablet: jQuery(this).data('tablet'),
            screen_mobile: jQuery(this).data('mobile')
          },
          event: event,
          id: id,
          thisItem: jQuery(this).parent().parent()
        });

        return false;
      });
    });
  }

  _initLoadMoreOnShopPage() {

    jQuery('.tbay-pagination-load-more').each(function (index) {
      jQuery(this).data('id');
      jQuery('.tbay-pagination-load-more a[data-loadmore="true"]').click(function () {
        var event = jQuery(this),
            data = {
          'action': LOADMORE_AJAX_SHOP_PAGE,
          'query': zota_settings.posts,
          'page': zota_settings.current_page
        };
        jQuery.ajax({
          url: zota_settings.ajaxurl,
          data: data,
          type: 'POST',
          beforeSend: function (xhr) {
            event.addClass('active');
          },
          success: function (data) {
            if (data) {
              event.closest('#main').find('.display-products > div').append(data);
              zota_settings.current_page++;
              event.removeClass('active');
              if (zota_settings.current_page == zota_settings.max_page) event.remove();
            } else {
              event.remove();
            }
          }
        });
        return false;
      });
    });
  }

  _callAjaxLoadMore(params) {
    var _this = this;

    var data = params.data;
    var event = params.event;
    jQuery.ajax({
      type: "POST",
      dataType: "JSON",
      url: woocommerce_params.ajax_url,
      data: data,
      beforeSend: function () {
        event.addClass('active');
      },
      success: function (response) {
        _this._onAjaxSuccess(response, params);
      }
    });
  }

  _onAjaxSuccess(response, params) {
    var data = params.data;
    var event = params.event;

    if (response.check == false) {
      event.remove();
    }

    event.data('paged', data.paged);
    event.data('number', data.number + data.columns * (params.data.action === LOADMORE_AJAX_HOME_PAGE ? 3 : 2));
    var $element = params.data.action === LOADMORE_AJAX_HOME_PAGE ? jQuery(`.widget_products_${params.id} .products>.row`) : jQuery('.archive-shop .products >.row');
    $element.append(response.posts);

    if (typeof wc_add_to_cart_variation_params !== 'undefined') {
      jQuery('.variations_form').each(function () {
        jQuery(this).wc_variation_form().find('.variations select:eq(0)').trigger('change');
      });
    }

    jQuery('.woocommerce-product-gallery').each(function () {
      jQuery(this).wc_product_gallery();
    });
    jQuery(`.variable-load-more-${data.paged}`).tawcvs_variation_swatches_form();

    if (typeof tawcvs_variation_swatches_form !== 'undefined') {
      jQuery('.variations_form').tawcvs_variation_swatches_form();
      jQuery(document.body).trigger('tawcvs_initialized');
    }

    event.find('.loadding').remove();
    event.removeClass('active');
    event.button('reset');
    params.thisItem.removeAttr("style");
  }

  _int_berocket_lmp_end() {
    jQuery(document).on('berocket_lmp_end', () => {
      jQuery('.woocommerce-product-gallery').each(function () {
        jQuery(this).wc_product_gallery();
      });

      if (typeof wc_add_to_cart_variation_params !== 'undefined') {
        jQuery('.variations_form').each(function () {
          jQuery(this).wc_variation_form().find('.variations select:eq(0)').trigger('change');
        });
      }

      if (typeof tawcvs_variation_swatches_form !== 'undefined') {
        jQuery('.variations_form').tawcvs_variation_swatches_form();
        jQuery(document.body).trigger('tawcvs_initialized');
      }
    });
  }

}

class ModalVideo {
  constructor($el, options = {
    classBtn: '.tbay-modalButton',
    defaultW: 640,
    defaultH: 360
  }) {
    this.$el = $el;
    this.options = options;

    this._initVideoIframe();
  }

  _initVideoIframe() {
    jQuery(`${this.options.classBtn}[data-target='${this.$el}']`).on('click', this._onClickModalBtn);
    jQuery(this.$el).on('hidden.bs.modal', () => {
      jQuery(this.$el).find('iframe').html("").attr("src", "");
    });
  }

  _onClickModalBtn(event) {
    let html = jQuery(event.currentTarget).data('target');
    var allowFullscreen = jQuery(event.currentTarget).attr('data-tbayVideoFullscreen') || false;
    var dataVideo = {
      'src': jQuery(event.currentTarget).attr('data-tbaySrc'),
      'height': jQuery(event.currentTarget).attr('data-tbayHeight') || this.options.defaultH,
      'width': jQuery(event.currentTarget).attr('data-tbayWidth') || this.options.defaultW
    };
    if (allowFullscreen) dataVideo.allowfullscreen = "";
    jQuery(html).find("iframe").attr(dataVideo);
    event.preventDefault();
  }

}

class WooCommon {
  constructor() {
    this._tbayFixRemove();

    jQuery(document.body).on('tbayFixRemove', () => {
      this._tbayFixRemove();
    });
  }

  _tbayFixRemove() {
    jQuery('.tbay-gallery-varible .woocommerce-product-gallery__trigger').remove();
  }

}

/*! Magnific Popup - v1.1.0 - 2016-02-20
* http://dimsemenov.com/plugins/magnific-popup/
* Copyright (c) 2016 Dmitry Semenov; */
(function (factory) {
if (typeof define === 'function' && define.amd) {
 define(['jquery'], factory);
 } else if (typeof exports === 'object') {
 factory(require('jquery'));
 } else {
 factory(window.jQuery || window.Zepto);
 }
 }(function($) {
var CLOSE_EVENT = 'Close',
	BEFORE_CLOSE_EVENT = 'BeforeClose',
	AFTER_CLOSE_EVENT = 'AfterClose',
	BEFORE_APPEND_EVENT = 'BeforeAppend',
	MARKUP_PARSE_EVENT = 'MarkupParse',
	OPEN_EVENT = 'Open',
	CHANGE_EVENT = 'Change',
	NS = 'mfp',
	EVENT_NS = '.' + NS,
	READY_CLASS = 'mfp-ready',
	REMOVING_CLASS = 'mfp-removing',
	PREVENT_CLOSE_CLASS = 'mfp-prevent-close';
var mfp,
	MagnificPopup = function(){},
	_isJQ = !!(window.jQuery),
	_prevStatus,
	_window = $(window),
	_document,
	_prevContentType,
	_wrapClasses,
	_currPopupType;
var _mfpOn = function(name, f) {
		mfp.ev.on(NS + name + EVENT_NS, f);
	},
	_getEl = function(className, appendTo, html, raw) {
		var el = document.createElement('div');
		el.className = 'mfp-'+className;
		if(html) {
			el.innerHTML = html;
		}
		if(!raw) {
			el = $(el);
			if(appendTo) {
				el.appendTo(appendTo);
			}
		} else if(appendTo) {
			appendTo.appendChild(el);
		}
		return el;
	},
	_mfpTrigger = function(e, data) {
		mfp.ev.triggerHandler(NS + e, data);
		if(mfp.st.callbacks) {
			e = e.charAt(0).toLowerCase() + e.slice(1);
			if(mfp.st.callbacks[e]) {
				mfp.st.callbacks[e].apply(mfp, $.isArray(data) ? data : [data]);
			}
		}
	},
	_getCloseBtn = function(type) {
		if(type !== _currPopupType || !mfp.currTemplate.closeBtn) {
			mfp.currTemplate.closeBtn = $( mfp.st.closeMarkup.replace('%title%', mfp.st.tClose ) );
			_currPopupType = type;
		}
		return mfp.currTemplate.closeBtn;
	},
	_checkInstance = function() {
		if(!$.magnificPopup.instance) {
			mfp = new MagnificPopup();
			mfp.init();
			$.magnificPopup.instance = mfp;
		}
	},
	supportsTransitions = function() {
		var s = document.createElement('p').style,
			v = ['ms','O','Moz','Webkit'];
		if( s['transition'] !== undefined ) {
			return true;
		}
		while( v.length ) {
			if( v.pop() + 'Transition' in s ) {
				return true;
			}
		}
		return false;
	};
MagnificPopup.prototype = {
	constructor: MagnificPopup,
	init: function() {
		var appVersion = navigator.appVersion;
		mfp.isLowIE = mfp.isIE8 = document.all && !document.addEventListener;
		mfp.isAndroid = (/android/gi).test(appVersion);
		mfp.isIOS = (/iphone|ipad|ipod/gi).test(appVersion);
		mfp.supportsTransition = supportsTransitions();
		mfp.probablyMobile = (mfp.isAndroid || mfp.isIOS || /(Opera Mini)|Kindle|webOS|BlackBerry|(Opera Mobi)|(Windows Phone)|IEMobile/i.test(navigator.userAgent) );
		_document = $(document);
		mfp.popupsCache = {};
	},
	open: function(data) {
		var i;
		if(data.isObj === false) {
			mfp.items = data.items.toArray();
			mfp.index = 0;
			var items = data.items,
				item;
			for(i = 0; i < items.length; i++) {
				item = items[i];
				if(item.parsed) {
					item = item.el[0];
				}
				if(item === data.el[0]) {
					mfp.index = i;
					break;
				}
			}
		} else {
			mfp.items = $.isArray(data.items) ? data.items : [data.items];
			mfp.index = data.index || 0;
		}
		if(mfp.isOpen) {
			mfp.updateItemHTML();
			return;
		}
		mfp.types = [];
		_wrapClasses = '';
		if(data.mainEl && data.mainEl.length) {
			mfp.ev = data.mainEl.eq(0);
		} else {
			mfp.ev = _document;
		}
		if(data.key) {
			if(!mfp.popupsCache[data.key]) {
				mfp.popupsCache[data.key] = {};
			}
			mfp.currTemplate = mfp.popupsCache[data.key];
		} else {
			mfp.currTemplate = {};
		}
		mfp.st = $.extend(true, {}, $.magnificPopup.defaults, data );
		mfp.fixedContentPos = mfp.st.fixedContentPos === 'auto' ? !mfp.probablyMobile : mfp.st.fixedContentPos;
		if(mfp.st.modal) {
			mfp.st.closeOnContentClick = false;
			mfp.st.closeOnBgClick = false;
			mfp.st.showCloseBtn = false;
			mfp.st.enableEscapeKey = false;
		}
		if(!mfp.bgOverlay) {
			mfp.bgOverlay = _getEl('bg').on('click'+EVENT_NS, function() {
				mfp.close();
			});
			mfp.wrap = _getEl('wrap').attr('tabindex', -1).on('click'+EVENT_NS, function(e) {
				if(mfp._checkIfClose(e.target)) {
					mfp.close();
				}
			});
			mfp.container = _getEl('container', mfp.wrap);
		}
		mfp.contentContainer = _getEl('content');
		if(mfp.st.preloader) {
			mfp.preloader = _getEl('preloader', mfp.container, mfp.st.tLoading);
		}
		var modules = $.magnificPopup.modules;
		for(i = 0; i < modules.length; i++) {
			var n = modules[i];
			n = n.charAt(0).toUpperCase() + n.slice(1);
			mfp['init'+n].call(mfp);
		}
		_mfpTrigger('BeforeOpen');
		if(mfp.st.showCloseBtn) {
			if(!mfp.st.closeBtnInside) {
				mfp.wrap.append( _getCloseBtn() );
			} else {
				_mfpOn(MARKUP_PARSE_EVENT, function(e, template, values, item) {
					values.close_replaceWith = _getCloseBtn(item.type);
				});
				_wrapClasses += ' mfp-close-btn-in';
			}
		}
		if(mfp.st.alignTop) {
			_wrapClasses += ' mfp-align-top';
		}
		if(mfp.fixedContentPos) {
			mfp.wrap.css({
				overflow: mfp.st.overflowY,
				overflowX: 'hidden',
				overflowY: mfp.st.overflowY
			});
		} else {
			mfp.wrap.css({
				top: _window.scrollTop(),
				position: 'absolute'
			});
		}
		if( mfp.st.fixedBgPos === false || (mfp.st.fixedBgPos === 'auto' && !mfp.fixedContentPos) ) {
			mfp.bgOverlay.css({
				height: _document.height(),
				position: 'absolute'
			});
		}
		if(mfp.st.enableEscapeKey) {
			_document.on('keyup' + EVENT_NS, function(e) {
				if(e.keyCode === 27) {
					mfp.close();
				}
			});
		}
		_window.on('resize' + EVENT_NS, function() {
			mfp.updateSize();
		});
		if(!mfp.st.closeOnContentClick) {
			_wrapClasses += ' mfp-auto-cursor';
		}
		if(_wrapClasses)
			mfp.wrap.addClass(_wrapClasses);
		var windowHeight = mfp.wH = _window.height();
		var windowStyles = {};
		if( mfp.fixedContentPos ) {
            if(mfp._hasScrollBar(windowHeight)){
                var s = mfp._getScrollbarSize();
                if(s) {
                    windowStyles.marginRight = s;
                }
            }
        }
		if(mfp.fixedContentPos) {
			if(!mfp.isIE7) {
				windowStyles.overflow = 'hidden';
			} else {
				$('body, html').css('overflow', 'hidden');
			}
		}
		var classesToadd = mfp.st.mainClass;
		if(mfp.isIE7) {
			classesToadd += ' mfp-ie7';
		}
		if(classesToadd) {
			mfp._addClassToMFP( classesToadd );
		}
		mfp.updateItemHTML();
		_mfpTrigger('BuildControls');
		$('html').css(windowStyles);
		mfp.bgOverlay.add(mfp.wrap).prependTo( mfp.st.prependTo || $(document.body) );
		mfp._lastFocusedEl = document.activeElement;
		setTimeout(function() {
			if(mfp.content) {
				mfp._addClassToMFP(READY_CLASS);
				mfp._setFocus();
			} else {
				mfp.bgOverlay.addClass(READY_CLASS);
			}
			_document.on('focusin' + EVENT_NS, mfp._onFocusIn);
		}, 16);
		mfp.isOpen = true;
		mfp.updateSize(windowHeight);
		_mfpTrigger(OPEN_EVENT);
		return data;
	},
	close: function() {
		if(!mfp.isOpen) return;
		_mfpTrigger(BEFORE_CLOSE_EVENT);
		mfp.isOpen = false;
		if(mfp.st.removalDelay && !mfp.isLowIE && mfp.supportsTransition )  {
			mfp._addClassToMFP(REMOVING_CLASS);
			setTimeout(function() {
				mfp._close();
			}, mfp.st.removalDelay);
		} else {
			mfp._close();
		}
	},
	_close: function() {
		_mfpTrigger(CLOSE_EVENT);
		var classesToRemove = REMOVING_CLASS + ' ' + READY_CLASS + ' ';
		mfp.bgOverlay.detach();
		mfp.wrap.detach();
		mfp.container.empty();
		if(mfp.st.mainClass) {
			classesToRemove += mfp.st.mainClass + ' ';
		}
		mfp._removeClassFromMFP(classesToRemove);
		if(mfp.fixedContentPos) {
			var windowStyles = {marginRight: ''};
			if(mfp.isIE7) {
				$('body, html').css('overflow', '');
			} else {
				windowStyles.overflow = '';
			}
			$('html').css(windowStyles);
		}
		_document.off('keyup' + EVENT_NS + ' focusin' + EVENT_NS);
		mfp.ev.off(EVENT_NS);
		mfp.wrap.attr('class', 'mfp-wrap').removeAttr('style');
		mfp.bgOverlay.attr('class', 'mfp-bg');
		mfp.container.attr('class', 'mfp-container');
		if(mfp.st.showCloseBtn &&
		(!mfp.st.closeBtnInside || mfp.currTemplate[mfp.currItem.type] === true)) {
			if(mfp.currTemplate.closeBtn)
				mfp.currTemplate.closeBtn.detach();
		}
		if(mfp.st.autoFocusLast && mfp._lastFocusedEl) {
			$(mfp._lastFocusedEl).focus();
		}
		mfp.currItem = null;
		mfp.content = null;
		mfp.currTemplate = null;
		mfp.prevHeight = 0;
		_mfpTrigger(AFTER_CLOSE_EVENT);
	},
	updateSize: function(winHeight) {
		if(mfp.isIOS) {
			var zoomLevel = document.documentElement.clientWidth / window.innerWidth;
			var height = window.innerHeight * zoomLevel;
			mfp.wrap.css('height', height);
			mfp.wH = height;
		} else {
			mfp.wH = winHeight || _window.height();
		}
		if(!mfp.fixedContentPos) {
			mfp.wrap.css('height', mfp.wH);
		}
		_mfpTrigger('Resize');
	},
	updateItemHTML: function() {
		var item = mfp.items[mfp.index];
		mfp.contentContainer.detach();
		if(mfp.content)
			mfp.content.detach();
		if(!item.parsed) {
			item = mfp.parseEl( mfp.index );
		}
		var type = item.type;
		_mfpTrigger('BeforeChange', [mfp.currItem ? mfp.currItem.type : '', type]);
		mfp.currItem = item;
		if(!mfp.currTemplate[type]) {
			var markup = mfp.st[type] ? mfp.st[type].markup : false;
			_mfpTrigger('FirstMarkupParse', markup);
			if(markup) {
				mfp.currTemplate[type] = $(markup);
			} else {
				mfp.currTemplate[type] = true;
			}
		}
		if(_prevContentType && _prevContentType !== item.type) {
			mfp.container.removeClass('mfp-'+_prevContentType+'-holder');
		}
		var newContent = mfp['get' + type.charAt(0).toUpperCase() + type.slice(1)](item, mfp.currTemplate[type]);
		mfp.appendContent(newContent, type);
		item.preloaded = true;
		_mfpTrigger(CHANGE_EVENT, item);
		_prevContentType = item.type;
		mfp.container.prepend(mfp.contentContainer);
		_mfpTrigger('AfterChange');
	},
	appendContent: function(newContent, type) {
		mfp.content = newContent;
		if(newContent) {
			if(mfp.st.showCloseBtn && mfp.st.closeBtnInside &&
				mfp.currTemplate[type] === true) {
				if(!mfp.content.find('.mfp-close').length) {
					mfp.content.append(_getCloseBtn());
				}
			} else {
				mfp.content = newContent;
			}
		} else {
			mfp.content = '';
		}
		_mfpTrigger(BEFORE_APPEND_EVENT);
		mfp.container.addClass('mfp-'+type+'-holder');
		mfp.contentContainer.append(mfp.content);
	},
	parseEl: function(index) {
		var item = mfp.items[index],
			type;
		if(item.tagName) {
			item = { el: $(item) };
		} else {
			type = item.type;
			item = { data: item, src: item.src };
		}
		if(item.el) {
			var types = mfp.types;
			for(var i = 0; i < types.length; i++) {
				if( item.el.hasClass('mfp-'+types[i]) ) {
					type = types[i];
					break;
				}
			}
			item.src = item.el.attr('data-mfp-src');
			if(!item.src) {
				item.src = item.el.attr('href');
			}
		}
		item.type = type || mfp.st.type || 'inline';
		item.index = index;
		item.parsed = true;
		mfp.items[index] = item;
		_mfpTrigger('ElementParse', item);
		return mfp.items[index];
	},
	addGroup: function(el, options) {
		var eHandler = function(e) {
			e.mfpEl = this;
			mfp._openClick(e, el, options);
		};
		if(!options) {
			options = {};
		}
		var eName = 'click.magnificPopup';
		options.mainEl = el;
		if(options.items) {
			options.isObj = true;
			el.off(eName).on(eName, eHandler);
		} else {
			options.isObj = false;
			if(options.delegate) {
				el.off(eName).on(eName, options.delegate , eHandler);
			} else {
				options.items = el;
				el.off(eName).on(eName, eHandler);
			}
		}
	},
	_openClick: function(e, el, options) {
		var midClick = options.midClick !== undefined ? options.midClick : $.magnificPopup.defaults.midClick;
		if(!midClick && ( e.which === 2 || e.ctrlKey || e.metaKey || e.altKey || e.shiftKey ) ) {
			return;
		}
		var disableOn = options.disableOn !== undefined ? options.disableOn : $.magnificPopup.defaults.disableOn;
		if(disableOn) {
			if($.isFunction(disableOn)) {
				if( !disableOn.call(mfp) ) {
					return true;
				}
			} else {
				if( _window.width() < disableOn ) {
					return true;
				}
			}
		}
		if(e.type) {
			e.preventDefault();
			if(mfp.isOpen) {
				e.stopPropagation();
			}
		}
		options.el = $(e.mfpEl);
		if(options.delegate) {
			options.items = el.find(options.delegate);
		}
		mfp.open(options);
	},
	updateStatus: function(status, text) {
		if(mfp.preloader) {
			if(_prevStatus !== status) {
				mfp.container.removeClass('mfp-s-'+_prevStatus);
			}
			if(!text && status === 'loading') {
				text = mfp.st.tLoading;
			}
			var data = {
				status: status,
				text: text
			};
			_mfpTrigger('UpdateStatus', data);
			status = data.status;
			text = data.text;
			mfp.preloader.html(text);
			mfp.preloader.find('a').on('click', function(e) {
				e.stopImmediatePropagation();
			});
			mfp.container.addClass('mfp-s-'+status);
			_prevStatus = status;
		}
	},
	_checkIfClose: function(target) {
		if($(target).hasClass(PREVENT_CLOSE_CLASS)) {
			return;
		}
		var closeOnContent = mfp.st.closeOnContentClick;
		var closeOnBg = mfp.st.closeOnBgClick;
		if(closeOnContent && closeOnBg) {
			return true;
		} else {
			if(!mfp.content || $(target).hasClass('mfp-close') || (mfp.preloader && target === mfp.preloader[0]) ) {
				return true;
			}
			if(  (target !== mfp.content[0] && !$.contains(mfp.content[0], target))  ) {
				if(closeOnBg) {
					if( $.contains(document, target) ) {
						return true;
					}
				}
			} else if(closeOnContent) {
				return true;
			}
		}
		return false;
	},
	_addClassToMFP: function(cName) {
		mfp.bgOverlay.addClass(cName);
		mfp.wrap.addClass(cName);
	},
	_removeClassFromMFP: function(cName) {
		this.bgOverlay.removeClass(cName);
		mfp.wrap.removeClass(cName);
	},
	_hasScrollBar: function(winHeight) {
		return (  (mfp.isIE7 ? _document.height() : document.body.scrollHeight) > (winHeight || _window.height()) );
	},
	_setFocus: function() {
		(mfp.st.focus ? mfp.content.find(mfp.st.focus).eq(0) : mfp.wrap).focus();
	},
	_onFocusIn: function(e) {
		if( e.target !== mfp.wrap[0] && !$.contains(mfp.wrap[0], e.target) ) {
			mfp._setFocus();
			return false;
		}
	},
	_parseMarkup: function(template, values, item) {
		var arr;
		if(item.data) {
			values = $.extend(item.data, values);
		}
		_mfpTrigger(MARKUP_PARSE_EVENT, [template, values, item] );
		$.each(values, function(key, value) {
			if(value === undefined || value === false) {
				return true;
			}
			arr = key.split('_');
			if(arr.length > 1) {
				var el = template.find(EVENT_NS + '-'+arr[0]);
				if(el.length > 0) {
					var attr = arr[1];
					if(attr === 'replaceWith') {
						if(el[0] !== value[0]) {
							el.replaceWith(value);
						}
					} else if(attr === 'img') {
						if(el.is('img')) {
							el.attr('src', value);
						} else {
							el.replaceWith( $('<img>').attr('src', value).attr('class', el.attr('class')) );
						}
					} else {
						el.attr(arr[1], value);
					}
				}
			} else {
				template.find(EVENT_NS + '-'+key).html(value);
			}
		});
	},
	_getScrollbarSize: function() {
		if(mfp.scrollbarSize === undefined) {
			var scrollDiv = document.createElement("div");
			scrollDiv.style.cssText = 'width: 99px; height: 99px; overflow: scroll; position: absolute; top: -9999px;';
			document.body.appendChild(scrollDiv);
			mfp.scrollbarSize = scrollDiv.offsetWidth - scrollDiv.clientWidth;
			document.body.removeChild(scrollDiv);
		}
		return mfp.scrollbarSize;
	}
};
$.magnificPopup = {
	instance: null,
	proto: MagnificPopup.prototype,
	modules: [],
	open: function(options, index) {
		_checkInstance();
		if(!options) {
			options = {};
		} else {
			options = $.extend(true, {}, options);
		}
		options.isObj = true;
		options.index = index || 0;
		return this.instance.open(options);
	},
	close: function() {
		return $.magnificPopup.instance && $.magnificPopup.instance.close();
	},
	registerModule: function(name, module) {
		if(module.options) {
			$.magnificPopup.defaults[name] = module.options;
		}
		$.extend(this.proto, module.proto);
		this.modules.push(name);
	},
	defaults: {
		disableOn: 0,
		key: null,
		midClick: false,
		mainClass: '',
		preloader: true,
		focus: '',
		closeOnContentClick: false,
		closeOnBgClick: true,
		closeBtnInside: true,
		showCloseBtn: true,
		enableEscapeKey: true,
		modal: false,
		alignTop: false,
		removalDelay: 0,
		prependTo: null,
		fixedContentPos: 'auto',
		fixedBgPos: 'auto',
		overflowY: 'auto',
		closeMarkup: '<button title="%title%" type="button" class="mfp-close">&#215;</button>',
		tClose: 'Close (Esc)',
		tLoading: 'Loading...',
		autoFocusLast: true
	}
};
$.fn.magnificPopup = function(options) {
	_checkInstance();
	var jqEl = $(this);
	if (typeof options === "string" ) {
		if(options === 'open') {
			var items,
				itemOpts = _isJQ ? jqEl.data('magnificPopup') : jqEl[0].magnificPopup,
				index = parseInt(arguments[1], 10) || 0;
			if(itemOpts.items) {
				items = itemOpts.items[index];
			} else {
				items = jqEl;
				if(itemOpts.delegate) {
					items = items.find(itemOpts.delegate);
				}
				items = items.eq( index );
			}
			mfp._openClick({mfpEl:items}, jqEl, itemOpts);
		} else {
			if(mfp.isOpen)
				mfp[options].apply(mfp, Array.prototype.slice.call(arguments, 1));
		}
	} else {
		options = $.extend(true, {}, options);
		if(_isJQ) {
			jqEl.data('magnificPopup', options);
		} else {
			jqEl[0].magnificPopup = options;
		}
		mfp.addGroup(jqEl, options);
	}
	return jqEl;
};
var INLINE_NS = 'inline',
	_hiddenClass,
	_inlinePlaceholder,
	_lastInlineElement,
	_putInlineElementsBack = function() {
		if(_lastInlineElement) {
			_inlinePlaceholder.after( _lastInlineElement.addClass(_hiddenClass) ).detach();
			_lastInlineElement = null;
		}
	};
$.magnificPopup.registerModule(INLINE_NS, {
	options: {
		hiddenClass: 'hide',
		markup: '',
		tNotFound: 'Content not found'
	},
	proto: {
		initInline: function() {
			mfp.types.push(INLINE_NS);
			_mfpOn(CLOSE_EVENT+'.'+INLINE_NS, function() {
				_putInlineElementsBack();
			});
		},
		getInline: function(item, template) {
			_putInlineElementsBack();
			if(item.src) {
				var inlineSt = mfp.st.inline,
					el = $(item.src);
				if(el.length) {
					var parent = el[0].parentNode;
					if(parent && parent.tagName) {
						if(!_inlinePlaceholder) {
							_hiddenClass = inlineSt.hiddenClass;
							_inlinePlaceholder = _getEl(_hiddenClass);
							_hiddenClass = 'mfp-'+_hiddenClass;
						}
						_lastInlineElement = el.after(_inlinePlaceholder).detach().removeClass(_hiddenClass);
					}
					mfp.updateStatus('ready');
				} else {
					mfp.updateStatus('error', inlineSt.tNotFound);
					el = $('<div>');
				}
				item.inlineElement = el;
				return el;
			}
			mfp.updateStatus('ready');
			mfp._parseMarkup(template, {}, item);
			return template;
		}
	}
});
var AJAX_NS = 'ajax',
	_ajaxCur,
	_removeAjaxCursor = function() {
		if(_ajaxCur) {
			$(document.body).removeClass(_ajaxCur);
		}
	},
	_destroyAjaxRequest = function() {
		_removeAjaxCursor();
		if(mfp.req) {
			mfp.req.abort();
		}
	};
$.magnificPopup.registerModule(AJAX_NS, {
	options: {
		settings: null,
		cursor: 'mfp-ajax-cur',
		tError: '<a href="%url%">The content</a> could not be loaded.'
	},
	proto: {
		initAjax: function() {
			mfp.types.push(AJAX_NS);
			_ajaxCur = mfp.st.ajax.cursor;
			_mfpOn(CLOSE_EVENT+'.'+AJAX_NS, _destroyAjaxRequest);
			_mfpOn('BeforeChange.' + AJAX_NS, _destroyAjaxRequest);
		},
		getAjax: function(item) {
			if(_ajaxCur) {
				$(document.body).addClass(_ajaxCur);
			}
			mfp.updateStatus('loading');
			var opts = $.extend({
				url: item.src,
				success: function(data, textStatus, jqXHR) {
					var temp = {
						data:data,
						xhr:jqXHR
					};
					_mfpTrigger('ParseAjax', temp);
					mfp.appendContent( $(temp.data), AJAX_NS );
					item.finished = true;
					_removeAjaxCursor();
					mfp._setFocus();
					setTimeout(function() {
						mfp.wrap.addClass(READY_CLASS);
					}, 16);
					mfp.updateStatus('ready');
					_mfpTrigger('AjaxContentAdded');
				},
				error: function() {
					_removeAjaxCursor();
					item.finished = item.loadError = true;
					mfp.updateStatus('error', mfp.st.ajax.tError.replace('%url%', item.src));
				}
			}, mfp.st.ajax.settings);
			mfp.req = $.ajax(opts);
			return '';
		}
	}
});
var _imgInterval,
	_getTitle = function(item) {
		if(item.data && item.data.title !== undefined)
			return item.data.title;
		var src = mfp.st.image.titleSrc;
		if(src) {
			if($.isFunction(src)) {
				return src.call(mfp, item);
			} else if(item.el) {
				return item.el.attr(src) || '';
			}
		}
		return '';
	};
$.magnificPopup.registerModule('image', {
	options: {
		markup: '<div class="mfp-figure">'+
					'<div class="mfp-close"></div>'+
					'<figure>'+
						'<div class="mfp-img"></div>'+
						'<figcaption>'+
							'<div class="mfp-bottom-bar">'+
								'<div class="mfp-title"></div>'+
								'<div class="mfp-counter"></div>'+
							'</div>'+
						'</figcaption>'+
					'</figure>'+
				'</div>',
		cursor: 'mfp-zoom-out-cur',
		titleSrc: 'title',
		verticalFit: true,
		tError: '<a href="%url%">The image</a> could not be loaded.'
	},
	proto: {
		initImage: function() {
			var imgSt = mfp.st.image,
				ns = '.image';
			mfp.types.push('image');
			_mfpOn(OPEN_EVENT+ns, function() {
				if(mfp.currItem.type === 'image' && imgSt.cursor) {
					$(document.body).addClass(imgSt.cursor);
				}
			});
			_mfpOn(CLOSE_EVENT+ns, function() {
				if(imgSt.cursor) {
					$(document.body).removeClass(imgSt.cursor);
				}
				_window.off('resize' + EVENT_NS);
			});
			_mfpOn('Resize'+ns, mfp.resizeImage);
			if(mfp.isLowIE) {
				_mfpOn('AfterChange', mfp.resizeImage);
			}
		},
		resizeImage: function() {
			var item = mfp.currItem;
			if(!item || !item.img) return;
			if(mfp.st.image.verticalFit) {
				var decr = 0;
				if(mfp.isLowIE) {
					decr = parseInt(item.img.css('padding-top'), 10) + parseInt(item.img.css('padding-bottom'),10);
				}
				item.img.css('max-height', mfp.wH-decr);
			}
		},
		_onImageHasSize: function(item) {
			if(item.img) {
				item.hasSize = true;
				if(_imgInterval) {
					clearInterval(_imgInterval);
				}
				item.isCheckingImgSize = false;
				_mfpTrigger('ImageHasSize', item);
				if(item.imgHidden) {
					if(mfp.content)
						mfp.content.removeClass('mfp-loading');
					item.imgHidden = false;
				}
			}
		},
		findImageSize: function(item) {
			var counter = 0,
				img = item.img[0],
				mfpSetInterval = function(delay) {
					if(_imgInterval) {
						clearInterval(_imgInterval);
					}
					_imgInterval = setInterval(function() {
						if(img.naturalWidth > 0) {
							mfp._onImageHasSize(item);
							return;
						}
						if(counter > 200) {
							clearInterval(_imgInterval);
						}
						counter++;
						if(counter === 3) {
							mfpSetInterval(10);
						} else if(counter === 40) {
							mfpSetInterval(50);
						} else if(counter === 100) {
							mfpSetInterval(500);
						}
					}, delay);
				};
			mfpSetInterval(1);
		},
		getImage: function(item, template) {
			var guard = 0,
				onLoadComplete = function() {
					if(item) {
						if (item.img[0].complete) {
							item.img.off('.mfploader');
							if(item === mfp.currItem){
								mfp._onImageHasSize(item);
								mfp.updateStatus('ready');
							}
							item.hasSize = true;
							item.loaded = true;
							_mfpTrigger('ImageLoadComplete');
						}
						else {
							guard++;
							if(guard < 200) {
								setTimeout(onLoadComplete,100);
							} else {
								onLoadError();
							}
						}
					}
				},
				onLoadError = function() {
					if(item) {
						item.img.off('.mfploader');
						if(item === mfp.currItem){
							mfp._onImageHasSize(item);
							mfp.updateStatus('error', imgSt.tError.replace('%url%', item.src) );
						}
						item.hasSize = true;
						item.loaded = true;
						item.loadError = true;
					}
				},
				imgSt = mfp.st.image;
			var el = template.find('.mfp-img');
			if(el.length) {
				var img = document.createElement('img');
				img.className = 'mfp-img';
				if(item.el && item.el.find('img').length) {
					img.alt = item.el.find('img').attr('alt');
				}
				item.img = $(img).on('load.mfploader', onLoadComplete).on('error.mfploader', onLoadError);
				img.src = item.src;
				if(el.is('img')) {
					item.img = item.img.clone();
				}
				img = item.img[0];
				if(img.naturalWidth > 0) {
					item.hasSize = true;
				} else if(!img.width) {
					item.hasSize = false;
				}
			}
			mfp._parseMarkup(template, {
				title: _getTitle(item),
				img_replaceWith: item.img
			}, item);
			mfp.resizeImage();
			if(item.hasSize) {
				if(_imgInterval) clearInterval(_imgInterval);
				if(item.loadError) {
					template.addClass('mfp-loading');
					mfp.updateStatus('error', imgSt.tError.replace('%url%', item.src) );
				} else {
					template.removeClass('mfp-loading');
					mfp.updateStatus('ready');
				}
				return template;
			}
			mfp.updateStatus('loading');
			item.loading = true;
			if(!item.hasSize) {
				item.imgHidden = true;
				template.addClass('mfp-loading');
				mfp.findImageSize(item);
			}
			return template;
		}
	}
});
var hasMozTransform,
	getHasMozTransform = function() {
		if(hasMozTransform === undefined) {
			hasMozTransform = document.createElement('p').style.MozTransform !== undefined;
		}
		return hasMozTransform;
	};
$.magnificPopup.registerModule('zoom', {
	options: {
		enabled: false,
		easing: 'ease-in-out',
		duration: 300,
		opener: function(element) {
			return element.is('img') ? element : element.find('img');
		}
	},
	proto: {
		initZoom: function() {
			var zoomSt = mfp.st.zoom,
				ns = '.zoom',
				image;
			if(!zoomSt.enabled || !mfp.supportsTransition) {
				return;
			}
			var duration = zoomSt.duration,
				getElToAnimate = function(image) {
					var newImg = image.clone().removeAttr('style').removeAttr('class').addClass('mfp-animated-image'),
						transition = 'all '+(zoomSt.duration/1000)+'s ' + zoomSt.easing,
						cssObj = {
							position: 'fixed',
							zIndex: 9999,
							left: 0,
							top: 0,
							'-webkit-backface-visibility': 'hidden'
						},
						t = 'transition';
					cssObj['-webkit-'+t] = cssObj['-moz-'+t] = cssObj['-o-'+t] = cssObj[t] = transition;
					newImg.css(cssObj);
					return newImg;
				},
				showMainContent = function() {
					mfp.content.css('visibility', 'visible');
				},
				openTimeout,
				animatedImg;
			_mfpOn('BuildControls'+ns, function() {
				if(mfp._allowZoom()) {
					clearTimeout(openTimeout);
					mfp.content.css('visibility', 'hidden');
					image = mfp._getItemToZoom();
					if(!image) {
						showMainContent();
						return;
					}
					animatedImg = getElToAnimate(image);
					animatedImg.css( mfp._getOffset() );
					mfp.wrap.append(animatedImg);
					openTimeout = setTimeout(function() {
						animatedImg.css( mfp._getOffset( true ) );
						openTimeout = setTimeout(function() {
							showMainContent();
							setTimeout(function() {
								animatedImg.remove();
								image = animatedImg = null;
								_mfpTrigger('ZoomAnimationEnded');
							}, 16);
						}, duration);
					}, 16);
				}
			});
			_mfpOn(BEFORE_CLOSE_EVENT+ns, function() {
				if(mfp._allowZoom()) {
					clearTimeout(openTimeout);
					mfp.st.removalDelay = duration;
					if(!image) {
						image = mfp._getItemToZoom();
						if(!image) {
							return;
						}
						animatedImg = getElToAnimate(image);
					}
					animatedImg.css( mfp._getOffset(true) );
					mfp.wrap.append(animatedImg);
					mfp.content.css('visibility', 'hidden');
					setTimeout(function() {
						animatedImg.css( mfp._getOffset() );
					}, 16);
				}
			});
			_mfpOn(CLOSE_EVENT+ns, function() {
				if(mfp._allowZoom()) {
					showMainContent();
					if(animatedImg) {
						animatedImg.remove();
					}
					image = null;
				}
			});
		},
		_allowZoom: function() {
			return mfp.currItem.type === 'image';
		},
		_getItemToZoom: function() {
			if(mfp.currItem.hasSize) {
				return mfp.currItem.img;
			} else {
				return false;
			}
		},
		_getOffset: function(isLarge) {
			var el;
			if(isLarge) {
				el = mfp.currItem.img;
			} else {
				el = mfp.st.zoom.opener(mfp.currItem.el || mfp.currItem);
			}
			var offset = el.offset();
			var paddingTop = parseInt(el.css('padding-top'),10);
			var paddingBottom = parseInt(el.css('padding-bottom'),10);
			offset.top -= ( $(window).scrollTop() - paddingTop );
			var obj = {
				width: el.width(),
				height: (_isJQ ? el.innerHeight() : el[0].offsetHeight) - paddingBottom - paddingTop
			};
			if( getHasMozTransform() ) {
				obj['-moz-transform'] = obj['transform'] = 'translate(' + offset.left + 'px,' + offset.top + 'px)';
			} else {
				obj.left = offset.left;
				obj.top = offset.top;
			}
			return obj;
		}
	}
});
var IFRAME_NS = 'iframe',
	_emptyPage = '//about:blank',
	_fixIframeBugs = function(isShowing) {
		if(mfp.currTemplate[IFRAME_NS]) {
			var el = mfp.currTemplate[IFRAME_NS].find('iframe');
			if(el.length) {
				if(!isShowing) {
					el[0].src = _emptyPage;
				}
				if(mfp.isIE8) {
					el.css('display', isShowing ? 'block' : 'none');
				}
			}
		}
	};
$.magnificPopup.registerModule(IFRAME_NS, {
	options: {
		markup: '<div class="mfp-iframe-scaler">'+
					'<div class="mfp-close"></div>'+
					'<iframe class="mfp-iframe" src="//about:blank" frameborder="0" allowfullscreen></iframe>'+
				'</div>',
		srcAction: 'iframe_src',
		patterns: {
			youtube: {
				index: 'youtube.com',
				id: 'v=',
				src: '//www.youtube.com/embed/%id%?autoplay=1'
			},
			vimeo: {
				index: 'vimeo.com/',
				id: '/',
				src: '//player.vimeo.com/video/%id%?autoplay=1'
			},
			gmaps: {
				index: '//maps.google.',
				src: '%id%&output=embed'
			}
		}
	},
	proto: {
		initIframe: function() {
			mfp.types.push(IFRAME_NS);
			_mfpOn('BeforeChange', function(e, prevType, newType) {
				if(prevType !== newType) {
					if(prevType === IFRAME_NS) {
						_fixIframeBugs();
					} else if(newType === IFRAME_NS) {
						_fixIframeBugs(true);
					}
				}
			});
			_mfpOn(CLOSE_EVENT + '.' + IFRAME_NS, function() {
				_fixIframeBugs();
			});
		},
		getIframe: function(item, template) {
			var embedSrc = item.src;
			var iframeSt = mfp.st.iframe;
			$.each(iframeSt.patterns, function() {
				if(embedSrc.indexOf( this.index ) > -1) {
					if(this.id) {
						if(typeof this.id === 'string') {
							embedSrc = embedSrc.substr(embedSrc.lastIndexOf(this.id)+this.id.length, embedSrc.length);
						} else {
							embedSrc = this.id.call( this, embedSrc );
						}
					}
					embedSrc = this.src.replace('%id%', embedSrc );
					return false;
				}
			});
			var dataObj = {};
			if(iframeSt.srcAction) {
				dataObj[iframeSt.srcAction] = embedSrc;
			}
			mfp._parseMarkup(template, dataObj, item);
			mfp.updateStatus('ready');
			return template;
		}
	}
});
var _getLoopedId = function(index) {
		var numSlides = mfp.items.length;
		if(index > numSlides - 1) {
			return index - numSlides;
		} else  if(index < 0) {
			return numSlides + index;
		}
		return index;
	},
	_replaceCurrTotal = function(text, curr, total) {
		return text.replace(/%curr%/gi, curr + 1).replace(/%total%/gi, total);
	};
$.magnificPopup.registerModule('gallery', {
	options: {
		enabled: false,
		arrowMarkup: '<button title="%title%" type="button" class="mfp-arrow mfp-arrow-%dir%"></button>',
		preload: [0,2],
		navigateByImgClick: true,
		arrows: true,
		tPrev: 'Previous (Left arrow key)',
		tNext: 'Next (Right arrow key)',
		tCounter: '%curr% of %total%'
	},
	proto: {
		initGallery: function() {
			var gSt = mfp.st.gallery,
				ns = '.mfp-gallery';
			mfp.direction = true;
			if(!gSt || !gSt.enabled ) return false;
			_wrapClasses += ' mfp-gallery';
			_mfpOn(OPEN_EVENT+ns, function() {
				if(gSt.navigateByImgClick) {
					mfp.wrap.on('click'+ns, '.mfp-img', function() {
						if(mfp.items.length > 1) {
							mfp.next();
							return false;
						}
					});
				}
				_document.on('keydown'+ns, function(e) {
					if (e.keyCode === 37) {
						mfp.prev();
					} else if (e.keyCode === 39) {
						mfp.next();
					}
				});
			});
			_mfpOn('UpdateStatus'+ns, function(e, data) {
				if(data.text) {
					data.text = _replaceCurrTotal(data.text, mfp.currItem.index, mfp.items.length);
				}
			});
			_mfpOn(MARKUP_PARSE_EVENT+ns, function(e, element, values, item) {
				var l = mfp.items.length;
				values.counter = l > 1 ? _replaceCurrTotal(gSt.tCounter, item.index, l) : '';
			});
			_mfpOn('BuildControls' + ns, function() {
				if(mfp.items.length > 1 && gSt.arrows && !mfp.arrowLeft) {
					var markup = gSt.arrowMarkup,
						arrowLeft = mfp.arrowLeft = $( markup.replace(/%title%/gi, gSt.tPrev).replace(/%dir%/gi, 'left') ).addClass(PREVENT_CLOSE_CLASS),
						arrowRight = mfp.arrowRight = $( markup.replace(/%title%/gi, gSt.tNext).replace(/%dir%/gi, 'right') ).addClass(PREVENT_CLOSE_CLASS);
					arrowLeft.click(function() {
						mfp.prev();
					});
					arrowRight.click(function() {
						mfp.next();
					});
					mfp.container.append(arrowLeft.add(arrowRight));
				}
			});
			_mfpOn(CHANGE_EVENT+ns, function() {
				if(mfp._preloadTimeout) clearTimeout(mfp._preloadTimeout);
				mfp._preloadTimeout = setTimeout(function() {
					mfp.preloadNearbyImages();
					mfp._preloadTimeout = null;
				}, 16);
			});
			_mfpOn(CLOSE_EVENT+ns, function() {
				_document.off(ns);
				mfp.wrap.off('click'+ns);
				mfp.arrowRight = mfp.arrowLeft = null;
			});
		},
		next: function() {
			mfp.direction = true;
			mfp.index = _getLoopedId(mfp.index + 1);
			mfp.updateItemHTML();
		},
		prev: function() {
			mfp.direction = false;
			mfp.index = _getLoopedId(mfp.index - 1);
			mfp.updateItemHTML();
		},
		goTo: function(newIndex) {
			mfp.direction = (newIndex >= mfp.index);
			mfp.index = newIndex;
			mfp.updateItemHTML();
		},
		preloadNearbyImages: function() {
			var p = mfp.st.gallery.preload,
				preloadBefore = Math.min(p[0], mfp.items.length),
				preloadAfter = Math.min(p[1], mfp.items.length),
				i;
			for(i = 1; i <= (mfp.direction ? preloadAfter : preloadBefore); i++) {
				mfp._preloadItem(mfp.index+i);
			}
			for(i = 1; i <= (mfp.direction ? preloadBefore : preloadAfter); i++) {
				mfp._preloadItem(mfp.index-i);
			}
		},
		_preloadItem: function(index) {
			index = _getLoopedId(index);
			if(mfp.items[index].preloaded) {
				return;
			}
			var item = mfp.items[index];
			if(!item.parsed) {
				item = mfp.parseEl( index );
			}
			_mfpTrigger('LazyLoad', item);
			if(item.type === 'image') {
				item.img = $('<img class="mfp-img" />').on('load.mfploader', function() {
					item.hasSize = true;
				}).on('error.mfploader', function() {
					item.hasSize = true;
					item.loadError = true;
					_mfpTrigger('LazyLoadError', item);
				}).attr('src', item.src);
			}
			item.preloaded = true;
		}
	}
});
var RETINA_NS = 'retina';
$.magnificPopup.registerModule(RETINA_NS, {
	options: {
		replaceSrc: function(item) {
			return item.src.replace(/\.\w+$/, function(m) { return '@2x' + m; });
		},
		ratio: 1
	},
	proto: {
		initRetina: function() {
			if(window.devicePixelRatio > 1) {
				var st = mfp.st.retina,
					ratio = st.ratio;
				ratio = !isNaN(ratio) ? ratio : ratio();
				if(ratio > 1) {
					_mfpOn('ImageHasSize' + '.' + RETINA_NS, function(e, item) {
						item.img.css({
							'max-width': item.img[0].naturalWidth / ratio,
							'width': '100%'
						});
					});
					_mfpOn('ElementParse' + '.' + RETINA_NS, function(e, item) {
						item.src = st.replaceSrc(item, ratio);
					});
				}
			}
		}
	}
});
 _checkInstance(); }));

class QuickView {
  constructor() {
    if (typeof zota_settings === "undefined") return;

    this._init_tbay_quick_view();
  }

  _init_tbay_quick_view() {
    var _this = this;

    jQuery(document).off('click', 'a.qview-button').on('click', 'a.qview-button', function (e) {
      e.preventDefault();
      let self = jQuery(this);
      self.parent().addClass('loading');
      let mainClass = self.attr('data-effect');
      let is_blocked = false,
          product_id = jQuery(this).data('product_id'),
          url = zota_settings.ajaxurl + '?action=zota_quickview_product&product_id=' + product_id;

      if (typeof zota_settings.loader !== 'undefined') {
        is_blocked = true;
        self.block({
          message: null,
          overlayCSS: {
            background: '#fff url(' + zota_settings.loader + ') no-repeat center',
            opacity: 0.5,
            cursor: 'none'
          }
        });
      }

      _this._ajax_call(self, url, is_blocked, mainClass);

      e.stopPropagation();
    });
  }

  _ajax_call(self, url, is_blocked, mainClass) {
    jQuery.get(url, function (data, status) {
      jQuery.magnificPopup.open({
        removalDelay: 100,
        closeMarkup: '<button title="%title%" type="button" class="mfp-close"> ' + zota_settings.close + '</button>',
        callbacks: {
          beforeOpen: function () {
            this.st.mainClass = mainClass + ' zota-quickview';
          }
        },
        items: {
          src: data,
          type: 'inline'
        }
      });
      let qv_content = jQuery("#tbay-quick-view-content");
      let form_variation = qv_content.find('.variations_form');

      if (typeof wc_add_to_cart_variation_params !== 'undefined') {
        form_variation.each(function () {
          jQuery(this).wc_variation_form();
        });
      }

      jQuery(document.body).trigger('updated_wc_div');
      self.parent().removeClass('loading');

      if (is_blocked) {
        self.unblock();
      }

      jQuery(document.body).trigger('tbay_quick_view');
    });
  }

}

class DisplayMode {
  constructor() {
    if (typeof zota_settings === "undefined") return;

    this._initModeListShopPage();

    this._initModeGridShopPage();

    jQuery(document.body).on('displayMode', () => {
      this._initModeListShopPage();

      this._initModeGridShopPage();
    });
  }

  _initModeListShopPage() {

    jQuery('#display-mode-list').each(function (index) {
      jQuery(this).click(function () {
        if (jQuery(this).hasClass('active')) return;
        var event = jQuery(this),
            data = {
          'action': LIST_POST_AJAX_SHOP_PAGE,
          'query': zota_settings.posts
        };
        jQuery.ajax({
          url: zota_settings.ajaxurl,
          data: data,
          type: 'POST',
          beforeSend: function (xhr) {
            event.closest('#main').find('.display-products').addClass('load-ajax');
          },
          success: function (data) {
            if (data) {
              event.parent().children().removeClass('active');
              event.addClass('active');
              event.closest('#main').find('.display-products > div').html(data);
              event.closest('#main').find('.display-products').fadeOut(0, function () {
                jQuery(this).addClass('products-list').removeClass('products-grid grid').fadeIn(300);
              });

              if (typeof wc_add_to_cart_variation_params !== 'undefined') {
                jQuery('.variations_form').each(function () {
                  jQuery(this).wc_variation_form().find('.variations select:eq(0)').trigger('change');
                });
              }

              jQuery(document.body).trigger('tbay_display_mode');
              event.closest('#main').find('.display-products').removeClass('load-ajax');
              Cookies.set('zota_display_mode', 'list', {
                expires: 0.1,
                path: '/'
              });
            }
          }
        });
        return false;
      });
    });
  }

  _initModeGridShopPage() {

    jQuery('#display-mode-grid').each(function (index) {
      jQuery(this).click(function () {
        if (jQuery(this).hasClass('active')) return;
        var event = jQuery(this),
            data = {
          'action': GRID_POST_AJAX_SHOP_PAGE,
          'query': zota_settings.posts
        };
        event.closest('#main').find('div.display-products');
        jQuery.ajax({
          url: zota_settings.ajaxurl,
          data: data,
          type: 'POST',
          beforeSend: function (xhr) {
            event.closest('#main').find('.display-products').addClass('load-ajax');
          },
          success: function (data) {
            if (data) {
              event.parent().children().removeClass('active');
              event.addClass('active');
              event.closest('#main').find('.display-products > div').html(data);
              let products = event.closest('#main').find('div.display-products');
              products.fadeOut(0, function () {
                jQuery(this).addClass('products-grid').removeClass('products-list').fadeIn(300);
              });

              if (typeof wc_add_to_cart_variation_params !== 'undefined') {
                jQuery('.variations_form').each(function () {
                  jQuery(this).wc_variation_form().find('.variations select:eq(0)').trigger('change');
                });
              }

              jQuery(document.body).trigger('tbay_display_mode');
              event.closest('#main').find('.display-products').removeClass('load-ajax');
              Cookies.set('zota_display_mode', 'grid', {
                expires: 0.1,
                path: '/'
              });
            }
          }
        });
        return false;
      });
    });
  }

}

class AjaxFilter {
  constructor() {
    this._intAjaxFilter();
  }

  _intAjaxFilter() {
    jQuery(document).on("woof_ajax_done", woof_ajax_done_handler);

    function woof_ajax_done_handler(e) {
      jQuery('.woocommerce-product-gallery').each(function () {
        jQuery(this).wc_product_gallery();
      });
      jQuery(document.body).trigger('tbayFixRemove');
      jQuery(document.body).trigger('displayMode');
      jQuery(document.body).trigger('ajax_sidebar_shop_mobile');

      if (jQuery('body').hasClass('filter-mobile-active')) {
        jQuery("body").removeClass('filter-mobile-active');
      }

      if (typeof tawcvs_variation_swatches_form !== 'undefined') {
        jQuery('.variations_form').tawcvs_variation_swatches_form();
        jQuery(document.body).trigger('tawcvs_initialized');
      }

      jQuery('.variations_form').each(function () {
        jQuery(this).wc_variation_form();
      });
    }
  }

}

class ShopProduct {
  constructor() {
    var _this = this;

    _this._SidebarShopMobile();

    _this._removeProductCategory();

    jQuery(document.body).on('ajax_sidebar_shop_mobile', () => {
      _this._SidebarShopMobile();

      jQuery('.filter-btn-wrapper').removeClass('active');
      jQuery("body").removeClass('filter-mobile-active');
    });
  }

  _SidebarShopMobile() {
    let btn_filter = jQuery("#button-filter-btn"),
        btn_close = jQuery("#filter-close,.close-side-widget");
    btn_filter.on("click", function (e) {
      jQuery('.filter-btn-wrapper').addClass('active');
      jQuery("body").addClass('filter-mobile-active');
    });
    btn_close.on("click", function (e) {
      jQuery('.filter-btn-wrapper').removeClass('active');
      jQuery("body").removeClass('filter-mobile-active');
    });
  }

  _removeProductCategory() {
    let category = jQuery('.archive-shop .display-products .product-category');
    if (category.length === 0) return;
    category.remove();
  }

}

class SingleProduct {
  constructor() {
    var _this = this;

    _this._intStickyMenuBar();

    _this._intNavImage();

    _this._intReviewPopup();

    _this._intShareMobile();

    _this._intTabsMobile();

    _this._initBuyNow();

    _this._initMoveTopSingle(false);

    _this._initChangeImageVarible();

    _this._initOpenAttributeMobile();

    _this._initCloseAttributeMobile();

    _this._initCloseAttributeMobileWrapper();

    _this._initAddToCartClickMobile();

    _this._initBuyNowwClickMobile();

    setTimeout(function () {
      jQuery(document.body).on('tbay_quick_view', () => {
        _this._initBuyNow();
      });
    }, 2000);
  }

  _intStickyMenuBar() {
    if (jQuery('#sticky-custom-add-to-cart').length === 0) return;
    jQuery('body').on('click', '#sticky-custom-add-to-cart', function (event) {
      jQuery('#shop-now .single_add_to_cart_button').click();
      event.stopPropagation();
    });
  }

  _intNavImage() {
    jQuery(window).scroll(function () {
      let isActive = jQuery(this).scrollTop() > 400;
      jQuery('.product-nav').toggleClass('active', isActive);
    });
  }

  _intReviewPopup() {
    if (jQuery('#list-review-images').length === 0) return;
    var container = [];
    jQuery('#list-review-images').find('.review-item').each(function () {
      var $link = jQuery(this).find('.review-link'),
          item = {
        src: $link.attr('href'),
        w: $link.data('width'),
        h: $link.data('height'),
        title: $link.children('.caption').html()
      };
      container.push(item);
    });
    jQuery('#list-review-images > ul> li a').off('click').on('click', function (event) {
      event.preventDefault();
      var $pswp = jQuery('.pswp')[0],
          options = {
        index: jQuery(this).parents('.review-item').index(),
        showHideOpacity: true,
        closeOnVerticalDrag: false,
        mainClass: 'pswp-review-images'
      };
      var gallery = new PhotoSwipe($pswp, PhotoSwipeUI_Default, container, options);
      gallery.init();
      event.stopPropagation();
    });
  }

  _intShareMobile() {
    let share = jQuery('.woo-share-mobile'),
        close = jQuery('.image-mains .show-mobile .woo-share-mobile .share-content .share-header .share-close i');
    share.find('button').click(function () {
      jQuery(event.target).parents('.woo-share-mobile').toggleClass("open");
      jQuery('body').toggleClass("overflow-y");
    });
    let win_share = jQuery(window);
    let forcusshare = jQuery('.woo-share-mobile button, .woo-share-mobile button i, .woo-share-mobile .content, .woo-share-mobile .share-title, .woo-share-mobile .share-close');
    win_share.on("click.Bst", function (event) {
      if (!share.hasClass('open')) return;

      if (forcusshare.has(event.target).length == 0 && !forcusshare.is(event.target)) {
        share.removeClass("open");
        jQuery('body').removeClass("overflow-y");
      }
    });
    close.click(function () {
      share.removeClass("open");
      jQuery('body').removeClass("overflow-y");
    });
  }

  _intTabsMobile() {
    let tabs = jQuery('.woocommerce-tabs-sidebar'),
        click = tabs.find('.tabs-sidebar a'),
        close = tabs.find('.close-tab, #tab-sidebar-close'),
        body = jQuery('body'),
        sidebar = jQuery('.tabs-sidebar'),
        screen = window.matchMedia("(max-width: 1199px)");
    if (tabs.length === 0) return;
    click.click(function (e) {
      e.preventDefault();
      let tabid = jQuery(this).data('tabid');
      sidebar.addClass('open');
      tabs.find('.wc-tab-sidebar').removeClass('open');
      jQuery('#' + tabid).addClass('open');

      if (screen.matches) {
        body.addClass('overflow-y');
      }
    });
    close.click(function (e) {
      e.preventDefault();
      sidebar.removeClass('open');
      jQuery(this).closest('.woocommerce-tabs-sidebar').find('.wc-tab-sidebar').removeClass('open');

      if (screen.matches) {
        body.removeClass('overflow-y');
      }
    });
  }

  _initBuyNow() {
    if (jQuery('.tbay-buy-now').length === 0) return;
    jQuery('body').on('click', '.tbay-buy-now', function (e) {
      e.preventDefault();
      let productform = jQuery(this).closest('form.cart'),
          submit_btn = productform.find('[type="submit"]'),
          buy_now = productform.find('input[name="zota_buy_now"]'),
          is_disabled = submit_btn.is('.disabled');
      if (!is_disabled) buy_now.val('1');
      submit_btn.trigger('click');
    });
    jQuery(document.body).on('show_variation', (event, variation, purchasable) => {
      if (purchasable) {
        jQuery(event.target).parents('form.variations_form').find('.tbay-buy-now').removeClass('disabled');
      } else {
        jQuery(event.target).parents('form.variations_form').find('.tbay-buy-now').addClass('disabled');
      }
    });
    jQuery(document.body).on('hide_variation', (event, variation, purchasable) => {
      jQuery(event.target).parents('form.variations_form').find('.tbay-buy-now').addClass('disabled');
    });
  }

  _initFeatureVideo() {
    if (typeof zota_settings === "undefined") return;
    let featured = jQuery(document).find(zota_settings.img_class_container + '.tbay_featured_content');
    if (featured.length === 0) return;
    let featured_index = featured.index(),
        featured_gallery_thumbnail = jQuery(zota_settings.thumbnail_gallery_class_element).get(featured_index);
    jQuery(featured_gallery_thumbnail).addClass('tbay_featured_thumbnail');
  }

  _initMoveTopSingle($resize) {
    let top_single = jQuery('.top-single-product');
    if (top_single.length === 0) return;
    let size = jQuery(window).width();

    if (size < 991) {
      if (top_single.length > 0) {
        top_single.prependTo(jQuery(".single-main-content > .row > .information"));
      }
    } else if ($resize) {
      top_single.prependTo(jQuery(".singular-shop .single-main-content"));
    }
  }

  _initChangeImageVarible() {
    let form = jQuery("form.variations_form");
    if (form.length === 0) return;
    form.on('change', function () {
      var _this = jQuery(this);

      var attribute_label = [];

      _this.find('.variations tr').each(function () {
        if (typeof jQuery(this).find('select').val() !== "undefined") {
          attribute_label.push(jQuery(this).find('select option:selected').text());
        }
      });

      _this.parent().find('.mobile-attribute-list .value').empty().append(attribute_label.join('/ '));

      jQuery(document.body).on('show_variation', () => {
        form.find('.mobile-infor-wrapper .infor-body').empty().append(form.find('.single_variation_wrap .single_variation').html());
      });
    });
    setTimeout(function () {
      jQuery(document.body).on('reset_data', () => {
        form.find('.mobile-infor-wrapper .infor-body .woocommerce-variation-availability').empty();
        form.find('.mobile-infor-wrapper .infor-body').empty().append(form.parent().children('.price').html());
        return;
      });
      jQuery(document.body).on('woocommerce_gallery_init_zoom', () => {
        let src_image = jQuery(".flex-control-thumbs").find('.flex-active').attr('src');
        jQuery('.mobile-infor-wrapper img').attr('src', src_image);
      });
      jQuery(document.body).on('mobile_attribute_open', () => {
        if (form.find('.single_variation_wrap .single_variation').is(':empty')) {
          form.find('.mobile-infor-wrapper .infor-body').empty().append(form.parent().children('.price').html());
        } else if (!form.find('.single_variation_wrap .single_variation .woocommerce-variation-price').is(':empty')) {
          form.find('.mobile-infor-wrapper .infor-body').empty().append(form.find('.single_variation_wrap .single_variation').html());
        } else {
          form.find('.mobile-infor-wrapper .infor-body').empty().append(form.find('.single_variation_wrap .single_variation').html());
          form.find('.mobile-infor-wrapper .infor-body .woocommerce-variation-price').empty().append(form.parent().children('.price').html());
        }
      });
    }, 1000);
  }

  _initOpenAttributeMobile() {
    let attribute = jQuery("#attribute-open");
    if (attribute.length === 0) return;
    attribute.off().on('click', function () {
      jQuery(this).parent().parent().find('form.cart').addClass('open open-btn-all');
      jQuery(document.body).trigger('mobile_attribute_open');
    });
  }

  _initAddToCartClickMobile() {
    let addtocart = jQuery("#tbay-click-addtocart");
    if (addtocart.length === 0) return;
    addtocart.off().on('click', function () {
      jQuery(this).parent().parent().find('form.cart').addClass('open open-btn-addtocart');
    });
  }

  _initBuyNowwClickMobile() {
    let buy_now = jQuery("#tbay-click-buy-now");
    if (buy_now.length === 0) return;
    buy_now.off().on('click', function () {
      jQuery(this).parent().parent().find('form.cart').addClass('open open-btn-buynow');
    });
  }

  _initCloseAttributeMobile() {
    let close = jQuery("#mobile-close-infor");
    if (close.length === 0) return;
    close.off().on('click', function () {
      jQuery(this).parents('form.cart').removeClass('open');

      if (jQuery(this).parents('form.cart').hasClass('open-btn-all')) {
        jQuery(this).parents('form.cart').removeClass('open-btn-all');
      }

      if (jQuery(this).parents('form.cart').hasClass('open-btn-buynow')) {
        jQuery(this).parents('form.cart').removeClass('open-btn-buynow');
      }

      if (jQuery(this).parents('form.cart').hasClass('open-btn-addtocart')) {
        jQuery(this).parents('form.cart').removeClass('open-btn-addtocart');
      }
    });
  }

  _initCloseAttributeMobileWrapper() {
    let close = jQuery("#mobile-close-infor-wrapper");
    if (close.length === 0) return;
    close.off().on('click', function () {
      jQuery(this).parent().find('form.cart').removeClass('open');

      if (jQuery(this).parent().find('form.cart').hasClass('open-btn-all')) {
        jQuery(this).parent().find('form.cart').removeClass('open-btn-all');
      }

      if (jQuery(this).parent().find('form.cart').hasClass('open-btn-buynow')) {
        jQuery(this).parent().find('form.cart').removeClass('open-btn-buynow');
      }

      if (jQuery(this).parent().find('form.cart').hasClass('open-btn-addtocart')) {
        jQuery(this).parent().find('form.cart').removeClass('open-btn-addtocart');
      }
    });
  }

}
jQuery(window).on("resize", () => {
  var singleproduct = new SingleProduct();

  singleproduct._initMoveTopSingle(true);
});

class ProductTabs {
  constructor() {
    if (typeof zota_settings === "undefined") return;

    this._initProductTabsAjax();

    this._initProductTabs();
  }

  _initProductTabs() {
    jQuery(".tbay-element-product-tabs").each(function (index, element) {
      var $this = jQuery(element);
      if ($this.hasClass("ajax-active") || $this.data("isInitialized")) return;
      $this.data("isInitialized", true);
      $this.find('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        jQuery(document.body).trigger("tbay_carousel_slick");
      });
    });
  }

  _initProductTabsAjax() {
    var process = false;
    jQuery('.tbay-element-product-tabs.ajax-active').each(function (index, element) {
      var $this = jQuery(element);
      $this.find('.product-tabs-title li a').off('click').on('click', function (e) {
        e.preventDefault();
        var $this = jQuery(this),
            atts = $this.parent().parent().data('atts'),
            value = $this.data('value'),
            id = $this.attr('href');

        if (process || jQuery(id).hasClass('active-content')) {
          return;
        }

        process = true;
        jQuery.ajax({
          url: zota_settings.ajaxurl,
          data: {
            atts: atts,
            value: value,
            action: 'zota_get_products_tab_shortcode'
          },
          dataType: 'json',
          method: 'POST',
          beforeSend: function (xhr) {
            jQuery(id).parent().addClass('load-ajax');
          },
          success: function (data) {
            jQuery(id).html(data.html);
            jQuery(id).parent().find('.current').removeClass('current');
            jQuery(id).parent().removeClass('load-ajax');
            jQuery(id).addClass('active-content');
            jQuery(id).addClass('current');
            jQuery(document.body).trigger('tbay_carousel_slick');
            jQuery(document.body).trigger('tbay_ajax_tabs_products');
          },
          error: function () {
            console.log('ajax error');
          },
          complete: function () {
            process = false;
          }
        });
      });
    });
  }

}

class ProductCategoriesTabs {
  constructor() {
    if (typeof zota_settings === "undefined") return;

    this._initProductCategoriesTabsAjax();

    this._initProductCategoriesTabs();
  }

  _initProductCategoriesTabs() {
    jQuery(".tbay-element-product-categories-tabs").each(function (index, element) {
      var $this = jQuery(element);
      if ($this.hasClass("ajax-active") || $this.data("isInitialized")) return;
      $this.data("isInitialized", true);
      $this.find('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        jQuery(document.body).trigger("tbay_carousel_slick");
      });
    });
  }

  _initProductCategoriesTabsAjax() {
    var process = false;
    jQuery('.tbay-element-product-categories-tabs.ajax-active').each(function (index, element) {
      var $this = jQuery(element);
      $this.find('.product-categories-tabs-title li a').off('click').on('click', function (e) {
        e.preventDefault();
        var $this = jQuery(this),
            atts = $this.parent().parent().data('atts'),
            value = $this.data('value'),
            id = $this.attr('href');

        if (process || jQuery(id).hasClass('active-content')) {
          return;
        }

        process = true;
        jQuery.ajax({
          url: zota_settings.ajaxurl,
          data: {
            atts: atts,
            value: value,
            action: 'zota_get_products_categories_tab_shortcode'
          },
          dataType: 'json',
          method: 'POST',
          beforeSend: function (xhr) {
            jQuery(id).parent().addClass('load-ajax');
          },
          success: function (data) {
            jQuery(id).html(data.html);
            jQuery(id).parent().find('.current').removeClass('current');
            jQuery(id).parent().removeClass('load-ajax');
            jQuery(id).addClass('active-content');
            jQuery(id).addClass('current');
            jQuery(document.body).trigger('tbay_carousel_slick');
            jQuery(document.body).trigger('tbay_ajax_tabs_products');
          },
          error: function () {
            console.log('ajax error');
          },
          complete: function () {
            process = false;
          }
        });
      });
    });
  }

}

jQuery(document).ready(() => {
  var product_item = new ProductItem();

  product_item._initSwatches();

  product_item._initQuantityMode();

  jQuery(document.body).trigger('tawcvs_initialized');
  new AjaxCart(), new WishList(), new Cart(), new Checkout(), new WooCommon(), new LoadMore(), new ModalVideo("#productvideo"), new QuickView(), new DisplayMode(), new ShopProduct(), new AjaxFilter(), new SingleProduct(), new ProductTabs(), new ProductCategoriesTabs();
});
setTimeout(function () {
  jQuery(document.body).on('wc_fragments_refreshed wc_fragments_loaded removed_from_cart', function () {
    new ProductItem().initOnChangeQuantity(() => {});
  });
}, 30);
jQuery(document).ready(function ($) {
  var singleproduct = new SingleProduct();

  singleproduct._initFeatureVideo();
});
setTimeout(function () {
  jQuery(document.body).on('tbay_ajax_tabs_products', () => {
    var product_item = new ProductItem();
    product_item.initAddButtonQuantity();

    product_item._initQuantityMode();
  });
}, 2000);

var AddButtonQuantity = function ($scope, $) {
  var product_item = new ProductItem();
  product_item.initAddButtonQuantity();
};

var AjaxProductTabs = function ($scope, $) {
  new ProductTabs(), new ProductCategoriesTabs();
};

jQuery(window).on('elementor/frontend/init', function () {
  if (typeof zota_settings !== "undefined" && elementorFrontend.isEditMode() && Array.isArray(zota_settings.elements_ready.products)) {
    jQuery.each(zota_settings.elements_ready.products, function (index, value) {
      elementorFrontend.hooks.addAction('frontend/element_ready/zota-' + value + '.default', AddButtonQuantity);
    });
  }
});
jQuery(window).on('elementor/frontend/init', function () {
  if (typeof zota_settings !== "undefined" && elementorFrontend.isEditMode() && Array.isArray(zota_settings.elements_ready.ajax_tabs)) {
    jQuery.each(zota_settings.elements_ready.ajax_tabs, function (index, value) {
      elementorFrontend.hooks.addAction('frontend/element_ready/tbay-' + value + '.default', AjaxProductTabs);
    });
  }
});
