/*!
 * plugin-lib (https://github.com/felixarntz/plugin-lib)
 * By Felix Arntz (https://leaves-and-love.net)
 * Licensed under GPL-3.0
 */
( function( $, pluginLibData ) {

	$( '.nav-tab' ).on( 'click', function( e ) {
		var $this = $( this );
		var $all  = $this.parent().children( '.nav-tab' );

		e.preventDefault();

		if ( 'true' === $this.attr( 'aria-selected' ) ) {
			return;
		}

		$all.each( function() {
			$( this ).attr( 'aria-selected', 'false' );
			$( $( this ).attr( 'href' ) ).attr( 'aria-hidden', 'true' );
		});

		$this.attr( 'aria-selected', 'true' );
		$( $this.attr( 'href' ) ).attr( 'aria-hidden', 'false' );
	});

	//TODO: handle slug

}( jQuery, pluginLibEditModelData ) );
