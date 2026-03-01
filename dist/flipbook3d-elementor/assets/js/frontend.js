/**
 * FlipBook3D — Elementor Frontend
 *
 * Uses Elementor's `frontend/element_ready/{widget}.default` hook which fires
 * for every instance of the widget, both on the live site and in the Elementor
 * preview panel — no handler class required.
 */
( function ( $ ) {
	'use strict';

	/* ------------------------------------------------------------------
	   Store instances so we can destroy them on re-render (editor)
	------------------------------------------------------------------ */
	var instances = {};

	/* ------------------------------------------------------------------
	   Bootstrap one widget instance
	   $scope — the Elementor widget jQuery wrapper element
	------------------------------------------------------------------ */
	function initWidget( $scope ) {
		var el = $scope.find( '.fb3d-widget' )[ 0 ];
		if ( ! el ) return;

		var id = el.id;

		// Tear down any prior instance (Elementor can re-render on settings change)
		if ( instances[ id ] ) {
			instances[ id ].destroy();
			delete instances[ id ];
		}
		if ( el._fb3d ) {
			el._fb3d.destroy();
			el._fb3d = null;
		}

		var d = el.dataset;

		/* ---- Parse data attributes ---- */
		var bookWidth    = parseInt( d.width, 10 )        || Math.min( 900, window.innerWidth - 40 );
		var aspectRatio  = parseFloat( d.aspectRatio )     || 1.414;
		var flipDuration = parseInt( d.flipDuration, 10 )  || 700;
		var scale        = parseFloat( d.scale )           || 1.5;
		var pageBg       = d.pageBg                        || '#ffffff';

		var muted           = 'yes' === d.mute;
		var showControls    = 'yes' === d.showControls;
		var showPageNumbers = 'yes' === d.showPageNumbers;
		var showFullscreen  = 'yes' === d.showFullscreen;
		var clickToFlip     = 'yes' === d.clickToFlip;
		var startPage       = Math.max( 1, parseInt( d.startPage, 10 ) || 1 );

		var sourceType = d.sourceType || 'pdf_url';
		var pdfUrl     = d.pdfUrl     || '';
		var images     = [];
		try { images = JSON.parse( d.images || '[]' ); } catch ( e ) {}

		/* ---- Guard: nothing configured ---- */
		if ( ! pdfUrl && ! images.length ) {
			showPlaceholder( el, 'No PDF or images configured yet.' );
			return;
		}

		/* ---- Cap width to container width to prevent overflow ---- */
		var containerW = $( el ).parent().width();
		if ( containerW > 0 && bookWidth > containerW ) {
			bookWidth = containerW;
		}

		/* ---- Instantiate FlipBook3D ---- */
		var fb;
		try {
			fb = new FlipBook3D( el, {
				width:          bookWidth,
				aspectRatio:    aspectRatio,
				flipDuration:   flipDuration,
				scale:          scale,
				pageBackground: pageBg,
			} );
		} catch ( err ) {
			console.error( '[FlipBook3D] Init error:', err );
			showPlaceholder( el, 'Could not initialise — see browser console.' );
			return;
		}

		fb.muted     = muted;
		el._fb3d     = fb;
		instances[id]= fb;

		/* ---- Visibility toggles (must run after _build() creates the DOM) ---- */
		if ( ! showControls ) {
			var bar = el.querySelector( '.flipbook3d-controls' );
			if ( bar ) bar.style.display = 'none';
		}

		if ( ! showPageNumbers ) {
			el.querySelectorAll( '.flipbook3d-page-num' ).forEach( function ( n ) {
				n.style.display = 'none';
			} );
		}

		if ( ! showFullscreen ) {
			var fsBtn = el.querySelector( '#fb-fs' );
			if ( fsBtn ) fsBtn.style.display = 'none';
		}

		if ( ! clickToFlip ) {
			[ '.flipbook3d-click-left', '.flipbook3d-click-right' ].forEach( function ( sel ) {
				var zone = el.querySelector( sel );
				if ( zone ) zone.style.display = 'none';
			} );
		}

		/* ---- Load content, then seek to start page ---- */
		function onLoaded() {
			if ( startPage > 1 ) { fb.goTo( startPage - 1 ); }
		}

		if ( 'images' === sourceType && images.length ) {
			fb.loadImages( images ).then( onLoaded );
		} else if ( pdfUrl ) {
			fb.loadPDF( pdfUrl ).then( onLoaded );
		}

		/* ---- Responsive: re-size book when container changes ---- */
		var origWidth = bookWidth;
		$( window ).on( 'resize.fb3d-' + id, function () {
			var cw = $( el ).parent().width();
			if ( ! cw || cw <= 0 ) return;
			var nw = Math.min( origWidth, cw );
			if ( nw !== fb.totalW ) { fb._resize( nw ); }
		} );

	}

	/* ------------------------------------------------------------------
	   Placeholder shown before any source is configured
	------------------------------------------------------------------ */
	function showPlaceholder( el, msg ) {
		el.innerHTML =
			'<div style="display:flex;align-items:center;justify-content:center;' +
			'flex-direction:column;gap:12px;background:#f0ede8;' +
			'border:2px dashed #c8a96e;border-radius:8px;padding:40px 32px;font-family:Georgia,serif;">' +
				'<span style="font-size:40px;">📖</span>' +
				'<span style="font-size:14px;color:#8b6914;font-weight:bold;">FlipBook3D</span>' +
				'<span style="font-size:12px;color:#aaa;">' + msg + '</span>' +
			'</div>';
	}

	/* ------------------------------------------------------------------
	   Register with Elementor once the frontend is ready
	------------------------------------------------------------------ */
	$( window ).on( 'elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction(
			'frontend/element_ready/flipbook3d.default',
			initWidget
		);
	} );

} )( jQuery );
