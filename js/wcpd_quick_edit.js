jQuery( function( $ ) {
	// Quick Edit functionality for Event Start Date.
	var wp_inline_edit = inlineEditPost.edit;
	inlineEditPost.edit = function( post_id ) {
		wp_inline_edit.apply( this, arguments );

		var id = 0;
		if ( typeof( post_id ) === 'object' ) {
			id = parseInt( this.getId( post_id ) );
		}

		if ( id > 0 ) {
			var $post_row      = $( '#post-' + id );
			var $quick_edit_row = $( '#edit-' + id );
			var event_start_date = $post_row.find( '.column-event_start_date' ).text().trim();

			// Handle 'N/A' value.
			if ( 'N/A' === event_start_date ) {
				event_start_date = '';
			}

			$quick_edit_row.find( 'input[name="_event_start_date"]' ).val( event_start_date );
		}
	};
} );