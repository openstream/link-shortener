( function () {
	'use strict';

	document.addEventListener( 'click', function ( e ) {
		var button = e.target.closest( '.openstream-copy-btn' );
		if ( ! button ) {
			return;
		}

		var url = button.getAttribute( 'data-url' );
		if ( ! url ) {
			return;
		}

		navigator.clipboard.writeText( url ).then( function () {
			var originalText = button.textContent;
			button.textContent = 'Copied!';
			button.classList.add( 'copied' );

			setTimeout( function () {
				button.textContent = originalText;
				button.classList.remove( 'copied' );
			}, 2000 );
		} );
	} );
} )();
