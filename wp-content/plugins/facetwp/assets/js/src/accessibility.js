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

            $('.facetwp-page, .facetwp-toggle, .facetwp-selection-value, .facetwp-link').each(function() {
                $(this).attr('role', 'link');
                $(this).attr('aria-label', $(this).text());
                $(this).attr('tabindex', 0);
            });

            $('.facetwp-dropdown').each(function() {
                $(this).attr('aria-label', $(this).find('option:selected').text());
            });

            $('.facetwp-search').each(function() {
                $(this).attr('aria-label', $(this).attr('placeholder'));
            });

            if (null != last_checked) {
                var $el = $('.facetwp-facet [data-value="' + last_checked + '"]');
                if ($el.len()) {
                    $el.nodes[0].focus();
                }
                last_checked = null;
            }
        }, 999);
    }

    $().on('keydown', '.facetwp-checkbox, .facetwp-radio, .facetwp-link', function(e) {
        if (32 == e.keyCode || 13 == e.keyCode) {
            last_checked = $(this).attr('data-value');
            e.preventDefault();
            this.click();
        }
    });

    $().on('keydown', '.facetwp-page, .facetwp-toggle, .facetwp-selection-value', function(e) {
        if (32 == e.keyCode || 13 == e.keyCode) {
            e.preventDefault();
            this.click();
        }
    });
})(fUtil);