var remoji_id;
var remoji_type;

document.addEventListener( 'DOMContentLoaded', function() { jQuery( document ).ready( function( $ ) {

	/**
	 * Count the view
	 */
	function remoji_postview() {
		$.ajax( {
			url: remoji.postview_url,
			type: 'POST',
			data: { post_id: remoji.postview_id },
			dataType: 'json',
			success: function( res ) {
				var counter_ele = '[data-remoji_counter=' + remoji.postview_id + ']';
				if ( res._res !== 'ok' ) {
					if ( res._msg ) {
						$( counter_ele ).append( res._msg );
					}
				} else {
					$( counter_ele ).html( res.num );
				}
			}
		} );
	}

	/**
	 * Postview counter
	 */
	if ( remoji.hasOwnProperty( 'postview_delay' ) ) {
		setTimeout( remoji_postview, remoji.postview_delay * 1000 );
	}

	/**
	 * Show large version preview of emoji
	 */
	function remoji_show_large() {
		var remoji_name = $( this ).data( 'remoji-name' );
		var large_img = $( this ).children().attr( 'src' );
		$( '#remoji_preview' ).html( '<img src="' + large_img + '" />' );
		$( '#remoji_preview_text' ).html( remoji_name );
	}

	function remoji_hide_large() {
		$( '#remoji_preview' ).html( '' );
		$( '#remoji_preview_text' ).html( '' );
	}

	/**
	 * Display the emoji picker panel
	 */
	function remoji_load_panel( e ) {
		remoji_id = $( this ).data( 'remoji-id' );
		remoji_type = $( this ).data( 'remoji-type' );

		if ( $( '#remoji_panel' ).length ) {
			remoji_locate_panel( this );
		}
		else {
			remoji_fetch_reaction_panel( this );
		}
	}

	/**
	 * Locate the panel based on current add button
	 */
	function remoji_locate_panel( that ) {
		var position = $( that ).position();
		$( '#remoji_panel' ).insertAfter( that ).css( 'left', position.left ).show();
	}

	/**
	 * REST fetch the emoji panel
	 */
	function remoji_fetch_reaction_panel( that ) {
		$.get( remoji.show_reaction_panel_url,
			function( res ) {
				if ( res._res !== 'ok' ) {
					$( '.remoji_error_bar[data-remoji-id=' + remoji_id + '][data-remoji-type=' + remoji_type + ']' ).css( 'display', 'inline-block' ).show().fadeOut( 2000 );
					return;
				}

				if ( $( '#remoji_panel' ).length ) {
					return;
				}

				$( 'body' ).append( res.data );

				$( '.remoji_picker_item' ).click( remoji_submit_reaction );
				$( '.remoji_picker_item' ).hover( remoji_show_large, remoji_hide_large );

				remoji_locate_panel( that );
			} );
	}

	/**
	 * Emoji react submission
	 */
	function remoji_submit_reaction( e ) {
		var that = this;

		var this_remoji_id = $( this ).data( 'remoji-id' ) ? $( this ).data( 'remoji-id' ) : remoji_id;
		var this_remoji_type = $( this ).data( 'remoji-type' ) ? $( this ).data( 'remoji-type' ) : remoji_type;
		var this_remoji_name = $( this ).data( 'remoji-name' );

		$.ajax( {
			url: remoji.reaction_submit_url,
			type: 'POST',
			data: { emoji: this_remoji_name, related_id: this_remoji_id, related_type: this_remoji_type },
			dataType: 'json',
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', remoji.nonce ) ;
			},
			success: function( res ) {
				if ( res._res !== 'ok' ) {
					if ( res._msg ) {
						$( '.remoji_error_bar[data-remoji-id=' + this_remoji_id + '][data-remoji-type=' + this_remoji_type + ']' ).html( res._msg );
					}
					$( '.remoji_error_bar[data-remoji-id=' + this_remoji_id + '][data-remoji-type=' + this_remoji_type + ']' ).css( 'display', 'inline-block' ).show().fadeOut( 2000 );
				} else {
					// Add the emoji to emoji bar
					var this_selector = '.remoji_container[data-remoji-id=' + this_remoji_id + '][data-remoji-type="' + this_remoji_type + '"][data-remoji-name="' + this_remoji_name + '"]';
					if ( $( this_selector ).length > 0 ) {
						$( this_selector + ' .remoji_count' ).html( $( this_selector + ' .remoji_count' ).text() * 1 + 1 );
					}
					else {
						// Insert a new emoji
						var new_ele = $( '<div />' ).addClass( 'remoji_container' ).attr( 'data-remoji-id', this_remoji_id ).attr( 'data-remoji-type', this_remoji_type ).attr( 'data-remoji-name', this_remoji_name )
							.append( $( '<img />' ).attr( 'src', res.src ) )
							.append( $( '<span />' ).addClass( 'remoji_count' ).html( '1' ) );
						new_ele.insertBefore( $( '.remoji_add_container[data-remoji-id=' + this_remoji_id + '][data-remoji-type=' + this_remoji_type + ']' ) ).click( remoji_submit_reaction );
					}
				}

			}
		} );
	}

	$( '.remoji_add_container' ).click( remoji_load_panel );
	$( '.remoji_container' ).click( remoji_submit_reaction );

	/**
	 * Hide the panel after clicked anywhere other than the add btn
	 */
	$( document ).mouseup( function(e) {
		var container = $( "#remoji_panel" );
		var remoji_picker_item = $( '.remoji_picker_item' );
		var remoji_picker_item_img = $( '.remoji_picker_item img' );

		if ( container.is( ':hidden' ) ) {
			return;
		}

		// If the target of the click isn't the container
		if ( ! container.is( e.target ) && container.has( e.target ).length === 0 ) {
			container.hide();
			return;
		}

		if ( remoji_picker_item.is( e.target ) || remoji_picker_item_img.is( e.target ) ) {
			container.hide();
		}
	} );

} ); } );