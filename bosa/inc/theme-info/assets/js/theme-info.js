jQuery(document).ready(function ($) {

    /* If there are required actions, add an icon with the number of required actions in the About kt-theme-info page -> Actions recommended tab */
    var kt_theme_info_count_actions_recommended = kt_theme_info_object.count_actions_recommended;

    if ( (typeof kt_theme_info_count_actions_recommended !== 'undefined') && (kt_theme_info_count_actions_recommended != '0') ) {
        jQuery('li.kt-theme-info-w-red-tab a').append('<span class="kt-theme-info-actions-count">' + kt_theme_info_count_actions_recommended + '</span>');
    }

    /* Dismiss required actions */
    jQuery(".kt-theme-info-recommended-action-button,.reset-all").click(function() {

        var id = jQuery(this).attr('id'),
            action = jQuery(this).attr('data-action');

        jQuery.ajax({
            type      : "GET",
            data      : {
                action: 'kt_theme_info_update_recommended_action',
                id: id,
                todo: action
            },
            dataType  : "html",
            url       : kt_theme_info_object.ajaxurl,
            beforeSend: function (data, settings) {
                jQuery('.kt-theme-info-tab-pane#actions_required h1').append('<div id="temp_load" style="text-align:center"><img src="' + kt_theme_info_object.template_directory + '../loader.gif" /></div>');
            },
            success   : function (data) {
                location.reload();
                jQuery("#temp_load").remove();
                /* Remove loading gif */
            },
            error     : function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR + " :: " + textStatus + " :: " + errorThrown);
            }
        });
    });

    /*faq*/
    jQuery(".faq .faq-title").click(function(e) {

        //Close all <div> but the <div> right after the clicked <a>
        $(e.target).next('div').siblings('div').slideUp();

        //Toggle open/close on the <div> after the <h3>, opening it if not open.
        $(e.target).next('div').slideToggle();
    });

});

/* Theme dashboard tabs: switch panels in place and keep ?tab= in the URL, without reloading. */
( function () {
    var nav = document.querySelector( '.info-wrap .nav-tab-wrapper' );
    var panels = document.querySelectorAll( '.info-wrap .theme-info-panel' );
    if ( ! nav || ! panels.length || ! window.history || ! window.URL ) {
        return;
    }

    function show( tab, push ) {
        var found = false;
        panels.forEach( function ( p ) {
            var active = p.getAttribute( 'data-tab' ) === tab;
            p.hidden = ! active;
            found = found || active;
        } );
        if ( ! found ) {
            return false;
        }
        nav.querySelectorAll( '.nav-tab' ).forEach( function ( a ) {
            a.classList.toggle( 'nav-tab-active', a.getAttribute( 'data-tab' ) === tab );
        } );
        if ( push ) {
            var u = new URL( window.location.href );
            u.searchParams.set( 'tab', tab );
            window.history.pushState( { themeInfoTab: tab }, '', u.toString() );
        }
        return true;
    }

    nav.addEventListener( 'click', function ( e ) {
        var a = e.target.closest( '.nav-tab[data-tab]' );
        // Let modified clicks (new tab / window) behave like normal links.
        if ( ! a || e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0 ) {
            return;
        }
        if ( show( a.getAttribute( 'data-tab' ), true ) ) {
            e.preventDefault();
        }
    } );

    window.addEventListener( 'popstate', function () {
        show( new URL( window.location.href ).searchParams.get( 'tab' ) || 'getting_started', false );
    } );
} )();
