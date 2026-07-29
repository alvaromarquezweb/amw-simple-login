/**
 * AMW Simple Login — settings page scripts.
 * Enqueued only on Settings → AMW Login. Translated strings and config
 * arrive from PHP via wp_localize_script as window.amwLoginAdmin.
 */
( function( $ ) {
	'use strict';

	var L = window.amwLoginAdmin || { logo: {}, bg: {} };

	$( function() {

		// ── Colour fields: WordPress Iris picker (type or paste a hex) ──
		$( '.amw-color-field' ).wpColorPicker();

		// ── Tabs (ARIA tab pattern with keyboard support) ──
		var $tabs   = $( '.amw-tabs .nav-tab' );
		var $panels = $( '.amw-tab-panel' );
		var $submit = $( '.amw-submit' );

		function activateTab( $tab, setFocus ) {
			var tab = $tab.data( 'amwTab' );
			$tabs.removeClass( 'nav-tab-active' ).attr( { 'aria-selected': 'false', tabindex: '-1' } );
			$tab.addClass( 'nav-tab-active' ).attr( { 'aria-selected': 'true', tabindex: '0' } );
			$panels.removeClass( 'amw-active' )
				.filter( '[data-amw-panel="' + tab + '"]' ).addClass( 'amw-active' );
			// The main Save button only applies to the option-form tabs.
			$submit.toggle( tab !== 'herramientas' );
			if ( setFocus ) { $tab.trigger( 'focus' ); }
		}

		$tabs.on( 'click', function( e ) {
			e.preventDefault();
			activateTab( $( this ), false );
		} );

		$tabs.on( 'keydown', function( e ) {
			var i = $tabs.index( this );
			var last = $tabs.length - 1;
			var target;
			switch ( e.key ) {
				case 'ArrowRight': case 'ArrowDown': target = i < last ? i + 1 : 0; break;
				case 'ArrowLeft':  case 'ArrowUp':   target = i > 0 ? i - 1 : last; break;
				case 'Home': target = 0; break;
				case 'End':  target = last; break;
				default: return;
			}
			e.preventDefault();
			activateTab( $tabs.eq( target ), true );
		} );

		// ── Media picker helper ──
		function mediaPicker( uploadId, hiddenId, previewId, removeId, cfg, previewStyle ) {
			var frame;
			$( uploadId ).on( 'click', function( e ) {
				e.preventDefault();
				if ( frame ) { frame.open(); return; }
				frame = wp.media( {
					title: cfg.frameTitle,
					button: { text: cfg.frameBtn },
					multiple: false,
					library: { type: 'image' }
				} );
				frame.on( 'select', function() {
					var a = frame.state().get( 'selection' ).first().toJSON();
					$( hiddenId ).val( a.id );
					$( previewId ).html( $( '<img>' ).attr( 'src', a.url ).attr( 'style', previewStyle ) );
					$( uploadId ).text( cfg.change );
					if ( ! $( removeId ).length ) {
						$( uploadId ).after(
							'<button type="button" class="button" id="' + removeId.slice( 1 ) +
							'" style="margin-left:6px;">' + cfg.remove + '</button>'
						);
					}
				} );
				frame.open();
			} );
			$( document ).on( 'click', removeId, function( e ) {
				e.preventDefault();
				$( hiddenId ).val( '' );
				$( previewId ).empty();
				$( uploadId ).text( cfg.select );
				$( this ).remove();
			} );
		}

		mediaPicker(
			'#amw-logo-upload', '#amw_logo_id', '#amw-logo-preview', '#amw-logo-remove',
			L.logo, 'max-width:200px;max-height:80px;display:block;background:#111;padding:8px;border-radius:4px;'
		);
		mediaPicker(
			'#amw-bg-upload', '#amw_bg_id', '#amw-bg-preview', '#amw-bg-remove',
			L.bg, 'max-width:280px;max-height:120px;display:block;border-radius:4px;'
		);

		// ── Background type / overlay show-hide ──
		function ovToggle() {
			var img = $( 'input.amw-bg-type:checked' ).val() === 'image';
			var ov  = $( 'input.amw-ov-type:checked' ).val();
			$( '.amw-ov-on' ).toggle( img && ov !== 'none' );
			$( '.amw-ov-grad' ).toggle( img && ov === 'gradient' );
		}
		function bgToggle() {
			var t = $( 'input.amw-bg-type:checked' ).val();
			$( '.amw-bg-fill' ).toggle( t !== 'image' );
			$( '.amw-bg-grad' ).toggle( t === 'gradient' );
			$( '.amw-bg-image' ).toggle( t === 'image' );
			ovToggle();
		}
		$( 'input.amw-bg-type' ).on( 'change', bgToggle );
		$( 'input.amw-ov-type' ).on( 'change', ovToggle );
		if ( $( 'input.amw-bg-type' ).length ) { bgToggle(); }

		// ── Panel section only for left/right positions ──
		function panelToggle() {
			$( '#amw-panel-section' ).toggle( $( 'input.amw-layout:checked' ).val() !== 'center' );
		}
		$( 'input.amw-layout' ).on( 'change', panelToggle );
		if ( $( 'input.amw-layout' ).length ) { panelToggle(); }

		// ── Palette presets ──
		$( '.amw-preset' ).on( 'click', function( e ) {
			e.preventDefault();
			var colors = $( this ).data( 'colors' );
			$.each( colors, function( key, hex ) {
				var $f = $( '#amw_' + key );
				if ( $f.hasClass( 'wp-color-picker' ) ) {
					$f.wpColorPicker( 'color', hex );
				} else {
					$f.val( hex );
				}
			} );
		} );

		// ── Export: copy / download ──
		$( '#amw-export-copy' ).on( 'click', function( e ) {
			e.preventDefault();
			var ta = document.getElementById( 'amw_export_json' );
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( ta.value );
			} else {
				ta.select();
				document.execCommand( 'copy' );
			}
		} );
		$( '#amw-export-download' ).on( 'click', function( e ) {
			e.preventDefault();
			var data = document.getElementById( 'amw_export_json' ).value;
			var blob = new Blob( [ data ], { type: 'application/json' } );
			var a = document.createElement( 'a' );
			a.href = URL.createObjectURL( blob );
			a.download = L.downloadName || 'amw-simple-login-settings.json';
			document.body.appendChild( a );
			a.click();
			document.body.removeChild( a );
		} );
	} );

}( jQuery ) );
