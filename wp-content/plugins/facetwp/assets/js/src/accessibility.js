(function($) {
    var last_checked = null;

    if ('undefined' !== typeof FWP.hooks) {
        FWP.hooks.addAction('facetwp/loaded', function() {
            $('.facetwp-checkbox, .facetwp-radio').each(function() {
                $(this).attr('role', 'checkbox');
                $(this).attr('aria-checked', $(this).hasClass('checked') ? 'true' : 'false');
                $(this).attr('aria-label', $(this).text());
                $(this).attr('tabindex', 0);
            });

            $('.facetwp-page, .facetwp-toggle, .facetwp-selection-value').each(function() {
                $(this).attr('role', 'link');
                $(this).attr('aria-label', $(this).text());
                $(this).attr('tabindex', 0);
            });

            $('.facetwp-search').each(function() {
                $(this).attr('aria-label', $(this).attr('placeholder'));
            });

            if ( null != last_checked ) {
                $('.facetwp-facet [data-value="' + last_checked + '"]').focus();
                last_checked = null;
            }
        }, 999);
    }

    $(document).on('keydown', '.facetwp-checkbox, .facetwp-radio', function(e) {
        var keyCode = e.originalEvent.keyCode;
        if ( 32 == keyCode || 13 == keyCode ) {
            last_checked = $(this).attr('data-value');
            e.preventDefault();
            $(this).click();
        }
    });

    $(document).on('keydown', '.facetwp-page, .facetwp-toggle, .facetwp-selection-value', function(e) {
        var keyCode = e.originalEvent.keyCode;
        if ( 32 == keyCode || 13 == keyCode ) {
            e.preventDefault();
            $(this).click();
        }
    });
})(jQuery);