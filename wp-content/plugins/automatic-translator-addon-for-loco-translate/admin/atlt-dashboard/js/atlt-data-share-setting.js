
jQuery(function($) {

    const $termsLink         = $('.atlt-see-terms');
    const $termsBox          = $('#termsBox');

    $termsLink.on('click', function(e) {

        e.preventDefault();
        
        const isVisible = $termsBox.toggle().is(':visible');
        $(this).html(isVisible ? 'Hide Terms' : 'See terms');
    });


});
