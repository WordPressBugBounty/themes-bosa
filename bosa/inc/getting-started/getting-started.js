//Load Image on Widget
jQuery( document ).ready( function($) {
     function media_upload( button_class ) {
        var _custom_media = false,
        _orig_send_attachment = wp.media.editor.send.attachment;
        $( 'body' ).on( 'click', button_class, function(e) {
            var button_id ='#'+$( this ).attr( 'id' );
            var self = $( button_id );
            var send_attachment_bkp = wp.media.editor.send.attachment;
            var button = $( button_id );
            var id = button.attr( 'id' ).replace( '_button', '' );
            _custom_media = true;
            wp.media.editor.send.attachment = function( props, attachment ){
                if ( _custom_media  ) {
                    $( '.custom_media_id' ).val( attachment.id );
                    $( '.custom_media_url' ).val( attachment.url );
                    $( '.custom_media_image' ).attr( 'src', attachment.url ).css( 'display','block' );
                } else {
                    return _orig_send_attachment.apply( button_id, [props, attachment] );
                }
            }
            wp.media.editor.open( button );
                return false;
        });
     }

     if ( typeof wp !== 'undefined' && wp.media && wp.media.editor ) {
        media_upload( '.custom_media_button' );
     }

    // Installs and activates the required plugins that are still missing, one after another,
    // then opens the starter site importer.
    $( document ).on( 'click', '.bosa-install-plugins', function (e) {
        e.preventDefault();

        if ( typeof bosa_adi_install === 'undefined' || $( this ).hasClass( 'updating-message' ) ) {
            return;
        }

        $( this ).addClass( 'updating-message' );
        $( this ).text( bosa_adi_install.btn_text );

        var plugins = ( bosa_adi_install.plugins || [] ).slice();

        function install_next() {
            if ( ! plugins.length ) {
                window.location.href = bosa_adi_install.redirect;
                return;
            }
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action   : 'bosa_getting_started',
                    security : bosa_adi_install.nonce,
                    slug     : plugins.shift()
                },
                error: function( xhr, ajaxOptions, thrownError ){
                    console.log( thrownError );
                },
                complete: function() {
                    setTimeout( install_next, 500 );
                }
            });
        }
        install_next();
    } );

});
