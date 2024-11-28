jQuery( function( $ ) {
	// Quick Edit functionality for Retreat Start Date.
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
			var retreat_start_date = $post_row.find( '.column-retreat_start_date' ).text().trim();

			// Handle 'N/A' value.
			if ( 'N/A' === retreat_start_date ) {
				retreat_start_date = '';
			}

			$quick_edit_row.find( 'input[name="_retreat_start_date"]' ).val( retreat_start_date );
		}
	};
} );