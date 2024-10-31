/**
 * Script for post like plugin
 * Author : Aamer Shahzad
 */
(function ( $ ) {

    // post like
    $( 'a.post--like' ).on( 'click', function () {
        // define $this var
        var $this = $( this );
        // return false if already liked
        if ( $this.hasClass( 'liked' ) ) {
            alert( pl_vars.already_liked_msg );
            return false;
        }

        var post_id = $this.data( 'post-id' );
        var user_id = $this.data( 'user-id' );

        var post_data = {
            action : 'post_like',
            post_id : post_id,
            user_id : user_id,
            post_like_nonce : pl_vars.nonce_like
        };

        $.post( pl_vars.ajax_url, post_data, function ( response ) {
            if ( response === 'liked' ) {
                $this.addClass( 'liked' );
                var count_wrap = $('.post--like-count');
                var count = count_wrap.text();
                count_wrap.text( parseInt( count ) + 1 );
                $this.parent( '.post--like-wrap' ).addClass('post--liked');
            } else {
                alert( pl_vars.error_msg );
            }
        } );

        return false;
    } );

    // post unlike
    $('a.post--unlike').on( 'click', function () {
        // define $this var
        var $this = $( this );
        // return false if already unliked
        if ( $this.hasClass( 'unliked' ) ) {
            alert( pl_vars.already_unliked_msg );
            return false;
        }

        // get data from link
        var post_id = $this.data( 'post-id' );
        var user_id = $this.data( 'user-id' );

        var post_data = {
            action : 'post_unlike',
            post_id : post_id,
            user_id : user_id,
            post_unlike_nonce : pl_vars.nonce_unlike
        };

        $.post( pl_vars.ajax_url, post_data, function ( response ) {
            if ( response === 'unliked' ) {
                $this.addClass( 'unliked' );
                var count_wrap = $('.post--like-count');
                var count = count_wrap.text();
                count_wrap.text( parseInt( count ) - 1 );
                $this.parent( '.post--like-wrap' ).removeClass('post--liked');
            } else {
                alert( pl_vars.error_msg );
            }
        } );

        return false;
    } );

})( jQuery );