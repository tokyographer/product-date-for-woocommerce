jQuery( document ).ready( function( $ ) {
    var wp_inline_edit = inlineEditPost.edit;
    inlineEditPost.edit = function( post_id ) {
        wp_inline_edit.apply( this, arguments );

        var id = 0;
        if ( typeof( post_id ) == 'object' ) {
            id = parseInt( this.getId( post_id ) );
        }

        if ( id > 0 ) {
            var specific_post_inline_data = $( '#wcpd_inline_' + id );
            var retreat_start_date = specific_post_inline_data.find( '.retreat_start_date' ).text();

            $( ':input[name="_retreat_start_date"]', '.inline-edit-row' ).val( retreat_start_date );
        }
    };
} );