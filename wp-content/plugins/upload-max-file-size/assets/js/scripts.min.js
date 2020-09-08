( function( $ ){
    $(document).ready(function() {
        $( ".nav-tab" ).click(function() {
            $('#container').imagesLoaded( function() {
                  $("#container1").twentytwenty({
                    before_label: 'Original',
                    after_label: 'Imagified',
                  });
            });
        });
    });
})( jQuery );


( function( $ ){
    'use strict';
    function switch_tab( tab ){
        var active_tab = ( tab )?tab:( window.location.hash )?window.location.hash:'#tab1';
        history.replaceState( null, null, active_tab );
        $( '.nav-tab' ).removeClass( 'nav-tab-active' );
        $( 'a[href^="'+active_tab+'"]' ).addClass( 'nav-tab-active' );
        $( '.tab' ).hide();
        $( '.tab' ).removeClass( 'tab-active' );
        $( active_tab ).show();
        $( active_tab ).addClass( 'tab-active' );
    }
    //initialisation
    $(document).ready(function() {
          $("#container1").twentytwenty({
            before_label: 'Original',
            after_label: 'Imagified',
          });
        $( '.nav-tab' ).click(function(){
            event.preventDefault();
            switch_tab( $( this ).attr('href') );
        });
        switch_tab();
    });


})( jQuery );
