/*!
 * Cndk.BeforeAfter.js v 0.0.2 (https://github.com/ilkerccom/cndkbeforeafter)
 * Ilker Cindik
 * Licensed under the MIT license
 */

jQuery.fn.cndkbeforeafter = function(options) {

    // Default settings
    var settings = jQuery.extend({
        mode: "hover", /* hover,drag */
        showText: true,
        beforeText: "Before",
        beforeTextPosition: "top-left", /* top-left, top-right, bottom-left, bottom-right */
        afterText: "After",
        afterTextPosition: "top-right", /* top-left, top-right, bottom-left, bottom-right */
        seperatorWidth: "4px",
        seperatorOpacity: "0.8",
        theme: "light", /* light,dark  */ 
        autoSliding: false,
        autoSlidingStopOnHover: true,
        hoverEffect: true,
        enterAnimation: false
    }, options);

    // This
    var element = this;

    if( element.length === 0 ) return; 

    // Wait for image(s) loading
    var img = new Image();
        
    jQuery(img).on('load', function() {
        runCndkBeforeAfter(element);
    });

    img.src = jQuery(this).find(">div").eq(0).find('div[data-type="before"] img').attr("src");   

    // Run Plugin
    function runCndkBeforeAfter(element)
    {
        element.each(function() { 

            // Get contents
            var count = jQuery(this).find(">div>div").length;
            if(count <= 1)
            {
                // No images
                console.log("(cndk.beforeafter.js) Error -> No before-after images found.");
            }

            // Add theme class
            element.addClass("cndkbeforeafter-theme-"+settings.theme);

            // Continue
            var root = jQuery(this);
            root.addClass("cndkbeforeafter cndkbeforeafter-root");
            root.append("<div class='cndkbeforeafter-seperator' style='width:"+settings.seperatorWidth+";opacity:"+settings.seperatorOpacity+"'></div>");

            // Container
            root.append("<div class='cndkbeforeafter-container'></div>");

            // Hover Effect
            if(settings.hoverEffect == true)
            {
                root.addClass("cndkbeforeafter-hover");
            }

            // Before-After text
            if(settings.showText == true)
            {
                var dataBeforeTitle = jQuery(this).find(">div").eq(0).find('div[data-type="before"]').attr("data-title") == undefined ? settings.beforeText : jQuery(this).find(">div").eq(0).find('div[data-type="before"]').attr("data-title");
                var dataAfterTitle = jQuery(this).find(">div").eq(0).find('div[data-type="after"]').attr("data-title") == undefined ? settings.afterText : jQuery(this).find(">div").eq(0).find('div[data-type="after"]').attr("data-title");
                root.append("<div class='cndkbeforeafter-item-before-text cndkbeforeafter-"+settings.beforeTextPosition+"'>"+dataBeforeTitle+"</div>");
                root.append("<div class='cndkbeforeafter-item-after-text cndkbeforeafter-"+settings.afterTextPosition+"'>"+dataAfterTitle+"</div>");
            }

            for(var i=0; i<count; i++)   
            {
                // Before
                var div1 = jQuery(this).find(">div").eq(i).find('div[data-type="before"]');
                var img1 = jQuery(this).find(">div").eq(i).find('div[data-type="before"] img');
                img1.addClass("cndkbeforeafter-item-before");
                div1.addClass("cndkbeforeafter-item-before-c");
                div1.css("overflow","hidden");
                div1.css("z-index","2");

                // After
                var div2 = jQuery(this).find(">div").eq(i).find('div[data-type="after"]');
                var img2 = jQuery(this).find(">div").eq(i).find('div[data-type="after"] img');
                img2.addClass("cndkbeforeafter-item-after");
                div2.addClass("cndkbeforeafter-item-after-c");
                div2.css("z-index","1");

                // Image-Item width/height
                var itemwidth = img1.width();
                var itemheight = img1.height();

                // Screen width
                var screenWidth = jQuery(this).parent().width();
                if(screenWidth < itemwidth)
                {
                    itemheight = itemheight/(itemwidth/screenWidth);
                    itemwidth = screenWidth;
                    img1.css("width", itemwidth + "px");
                    img2.css("width", itemwidth + "px");
                }

                // Item
                jQuery(this).find(">div").eq(0).addClass("cndkbeforeafter-item");
                jQuery(this).find(">div").eq(0).css("height",itemheight + "px");

                // Small Before-After text
                if(itemwidth < 200)
                {
                    jQuery(this).find(".cndkbeforeafter-item-after-text").addClass("cndkbeforeafter-extra-small-text cndkbeforeafter-extra-small-text-after");
                    jQuery(this).find(".cndkbeforeafter-item-before-text").addClass("cndkbeforeafter-extra-small-text cndkbeforeafter-extra-small-text-before");
                }

                // Start position
                div1.css("width","50%");
                div2.css("width","50%");
                jQuery(".cndkbeforeafter-seperator").css("left","50%");

                // Root inline
                root.css("width",itemwidth + "px");
                root.css("height",itemheight + "px");
            }

            // Modes
            if(settings.mode == "hover")
            {
                // Hover mode
                jQuery(root).find(".cndkbeforeafter-seperator, .cndkbeforeafter-item > div").addClass("cndkbeforeafter-hover-transition");
                jQuery(root).mousemove(function(e){
                    var parentOffset = jQuery(this).offset();
                    var mouseX = parseInt((e.pageX - parentOffset.left));
                    var mousePercent = (mouseX*100)/parseInt(root.width());
                    jQuery(this).find(".cndkbeforeafter-item-before-c").css("width",mousePercent+"%");
                    jQuery(this).find(".cndkbeforeafter-item-after-c").css("width",(100-mousePercent)+"%");
                    jQuery(this).find(".cndkbeforeafter-seperator").css("left",mousePercent+"%");
                }).mouseleave(function(){
                    jQuery(this).find(".cndkbeforeafter-item-after-c").css("width","50%");
                    jQuery(this).find(".cndkbeforeafter-item-before-c").css("width","50%");
                    jQuery(this).find(".cndkbeforeafter-seperator").css("left","50%");
                });
            }
            else if(settings.mode == "drag")
            {
                // Drag mode
                jQuery(root).find(".cndkbeforeafter-seperator, .cndkbeforeafter-item > div").addClass("cndkbeforeafter-drag-transition");
                jQuery(root).click(function(e){
                    var parentOffset = jQuery(this).offset();
                    var mouseX = parseInt((e.pageX - parentOffset.left));
                    var mousePercent = (mouseX*100)/parseInt(root.width());
                    jQuery(this).find(".cndkbeforeafter-item-before-c").css("width",mousePercent+"%");
                    jQuery(this).find(".cndkbeforeafter-item-after-c").css("width",(100-mousePercent)+"%");
                    jQuery(this).find(".cndkbeforeafter-seperator").css("left",mousePercent+"%");
                });

                // Draggable seperator
                var isSliding = false;
                var currentElement = (root);
                currentElement.find(".cndkbeforeafter-seperator").on("mousedown",function(e){
                    isSliding = true;
                    currentElement.find(".cndkbeforeafter-seperator, .cndkbeforeafter-item > div").removeClass("cndkbeforeafter-drag-transition");
                    currentElement.mousemove(function(e){
                        if(isSliding) {
                            var parentOffset = currentElement.offset();
                            var mouseX = parseInt((e.pageX - parentOffset.left));
                            var mousePercent = (mouseX*100)/parseInt(root.width());
                            currentElement.find(".cndkbeforeafter-item-before-c").css("width",mousePercent+"%");
                            currentElement.find(".cndkbeforeafter-item-after-c").css("width",(100-mousePercent)+"%");
                            currentElement.find(".cndkbeforeafter-seperator").css("left",mousePercent+"%");
                        }
                    });
                });

                // Release
                currentElement.find(".cndkbeforeafter-seperator").on("mouseup",function(e){
                    isSliding = false;
                    currentElement.find(".cndkbeforeafter-seperator, .cndkbeforeafter-item > div").addClass("cndkbeforeafter-drag-transition");
                });

                // Mobile touch-support
                currentElement.find(".cndkbeforeafter-seperator").on("touchstart",function(e){
                    isSliding = true;
                    currentElement.find(".cndkbeforeafter-seperator, .cndkbeforeafter-item > div").removeClass("cndkbeforeafter-drag-transition");
                    currentElement.on("touchmove",function(e){
                        var parentOffset = currentElement.offset();
                        var mouseX = parseInt((e.originalEvent.touches[0].pageX - parentOffset.left));
                        var mousePercent = (mouseX*100)/parseInt(root.width());
                        currentElement.find(".cndkbeforeafter-item-before-c").css("width",mousePercent+"%");
                        currentElement.find(".cndkbeforeafter-item-after-c").css("width",(100-mousePercent)+"%");
                        currentElement.find(".cndkbeforeafter-seperator").css("left",mousePercent+"%");
                    });
                });

                // Add visual to seperator
                currentElement.find(".cndkbeforeafter-seperator").append("<div><span></span></div>");
            }

            // Start Animation
            if(settings.enterAnimation)
            {
                jQuery(this).addClass("cndkbeforeafter-animation");
            }

            // Auto-Sliding
            if(settings.autoSliding)
            {
                jQuery(this).attr("auto-sliding","true");
                jQuery(this).find(".cndkbeforeafter-item-before-c").addClass("cndkbeforeafter-animation-item-1");
                jQuery(this).find(".cndkbeforeafter-item-after-c").addClass("cndkbeforeafter-animation-item-2");
                jQuery(this).find(".cndkbeforeafter-seperator").addClass("cndkbeforeafter-animation-seperator");

                if(settings.autoSlidingStopOnHover)
                {
                    // Stop On Enter
                    jQuery(this).on("mouseenter", function(){
                        jQuery(this).find(".cndkbeforeafter-item-before-c").removeClass("cndkbeforeafter-animation-item-1");
                        jQuery(this).find(".cndkbeforeafter-item-after-c").removeClass("cndkbeforeafter-animation-item-2");
                        jQuery(this).find(".cndkbeforeafter-seperator").removeClass("cndkbeforeafter-animation-seperator");
                    })

                    // Start On Exit
                    jQuery(this).on("mouseleave", function(){
                        jQuery(this).find(".cndkbeforeafter-item-before-c").addClass("cndkbeforeafter-animation-item-1");
                        jQuery(this).find(".cndkbeforeafter-item-after-c").addClass("cndkbeforeafter-animation-item-2");
                        jQuery(this).find(".cndkbeforeafter-seperator").addClass("cndkbeforeafter-animation-seperator");
                    })
                }
            }

            

            // On window resize
            jQuery( window ).resize(function() {
                
            });
        });
    }
};