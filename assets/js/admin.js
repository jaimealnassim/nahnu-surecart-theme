/* Nahnu Custom Theme for SureCart — Admin JS */
( function () {
	'use strict';

	// ── Tab switching ───────────────────────────────────────
	document.querySelectorAll( '.nst-tab' ).forEach( function ( tab ) {
		tab.addEventListener( 'click', function () {
			document.querySelectorAll( '.nst-tab' ).forEach( function(t) { t.classList.remove( 'active' ); } );
			document.querySelectorAll( '.nst-panel' ).forEach( function(p) { p.classList.remove( 'active' ); } );
			this.classList.add( 'active' );
			var key = this.dataset.tab;
			document.querySelectorAll( '.nst-panel' ).forEach( function(p) {
				if ( p.id === 'nst-tab-' + key || p.id.indexOf( 'nst-tab-' + key + '-' ) === 0 ) p.classList.add( 'active' );
			} );
			var hiddenTab = document.getElementById( 'nst_tab' );
			if ( hiddenTab ) hiddenTab.value = key;
			var app = document.querySelector( '.nst-app' );
			if ( app ) app.dataset.tab = key;
			try { history.replaceState( null, '', location.pathname + location.search.replace( /([?&])tab=[^&]*/, '$1' ).replace( /[?&]$/, '' ) + ( location.search.indexOf('?') === -1 ? '?' : '&' ) + 'tab=' + key ); } catch (e) {}
			window.scrollTo( { top: 0 } );
		} );
	} );

	// ── Unsaved-changes indicator (top bar) ─────────────────
	var form  = document.getElementById( 'nst-form' );
	var state = document.getElementById( 'nst-save-state' );
	var stateText = state ? state.querySelector( '.nst-save-state-text' ) : null;
	var dirty = false;
	function markDirty() {
		if ( dirty || ! state ) return;
		dirty = true;
		state.dataset.state = 'dirty';
		if ( stateText ) stateText.textContent = ( window.nahnuSctTools && nahnuSctTools.i18n && nahnuSctTools.i18n.unsaved ) || 'Unsaved changes';
	}
	if ( form ) {
		form.addEventListener( 'input',  markDirty );
		form.addEventListener( 'change', markDirty );
		form.addEventListener( 'submit', function () { dirty = false; } );
		window.addEventListener( 'beforeunload', function ( e ) {
			if ( dirty ) { e.preventDefault(); e.returnValue = ''; }
		} );
	}
	// Mark the hex field so the accent picker's hex box also dirties the form.
	var nstHexField = document.getElementById( 'accent_color_hex' );
	if ( nstHexField ) nstHexField.addEventListener( 'input', markDirty );

	// ── Accent color sync ───────────────────────────────────
	var picker  = document.getElementById( 'accent_color' );
	var hexInp  = document.getElementById( 'accent_color_hex' );
	var swatches = document.querySelectorAll( '.nst-swatch' );

	function setAccent( color ) {
		if ( picker )  picker.value  = color;
		if ( hexInp )  hexInp.value  = color;
		swatches.forEach( function(s) {
			s.classList.toggle( 'active', s.dataset.color.toLowerCase() === color.toLowerCase() );
		} );
	}

	if ( picker ) {
		picker.addEventListener( 'input', function() { setAccent( this.value ); } );
	}

	if ( hexInp ) {
		hexInp.addEventListener( 'input', function() {
			var v = this.value.trim();
			if ( ! v.startsWith('#') ) v = '#' + v;
			if ( /^#[0-9a-fA-F]{6}$/.test( v ) ) setAccent( v );
		} );
	}

	swatches.forEach( function( s ) {
		s.addEventListener( 'click', function() { setAccent( this.dataset.color ); } );
	} );

	// ── Range sliders ───────────────────────────────────────
	document.querySelectorAll( '.nst-range' ).forEach( function( range ) {
		var valEl = range.nextElementSibling;
		if ( valEl && valEl.classList.contains( 'nst-range-val' ) ) {
			range.addEventListener( 'input', function() { valEl.textContent = this.value; } );
		}
	} );

	// ── Toggle accent on checked state ──────────────────────
	function updateToggleColors( color ) {
		document.querySelectorAll( '.nst-toggle input:checked + .nst-toggle-track' ).forEach( function(t) {
			t.style.background = color;
		} );
	}
	if ( picker ) updateToggleColors( picker.value );

	document.querySelectorAll( '.nst-toggle input[type="checkbox"]' ).forEach( function( cb ) {
		cb.addEventListener( 'change', function() {
			this.nextElementSibling.style.background = this.checked ? ( picker ? picker.value : '#1a1a1a' ) : '';
		} );
	} );

} )();

// ── Avatar ──────────────────────────────────────────────────
( function () {

	var pickBtn    = document.getElementById( 'nst-avatar-pick' );
	var removeBtn  = document.getElementById( 'nst-avatar-remove' );
	var msgEl      = document.getElementById( 'nst-avatar-msg' );
	var circle     = document.getElementById( 'nst-av-circle' );
	var presetBtns = document.querySelectorAll( '.nst-av-preset' );

	if ( ! pickBtn ) return;

	var nonce   = ( typeof nahnuSctAvatar !== 'undefined' ) ? nahnuSctAvatar.nonce   : '';
	var ajaxUrl = ( typeof nahnuSctAvatar !== 'undefined' ) ? nahnuSctAvatar.ajaxUrl : ( typeof ajaxurl !== 'undefined' ? ajaxurl : '' );

	// ── Helpers ─────────────────────────────────────────────
	function msg( text, ok ) {
		if ( ! msgEl ) return;
		msgEl.textContent = text;
		msgEl.className   = 'nst-avatar-msg nst-avatar-msg--' + ( ok ? 'ok' : 'err' );
		msgEl.removeAttribute( 'hidden' );
		setTimeout( function() { msgEl.setAttribute( 'hidden', '' ); }, 4000 );
	}

	function setPreview( url ) {
		if ( ! circle ) return;
		circle.innerHTML = '<img src="' + url + '" alt="" id="nst-av-img">';
		if ( removeBtn ) removeBtn.removeAttribute( 'hidden' );
		pickBtn.lastChild.textContent = ' Change photo';
	}

	function clearPreview() {
		if ( ! circle ) return;
		circle.innerHTML = '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
		if ( removeBtn ) removeBtn.setAttribute( 'hidden', '' );
	}

	function doAjax( body, onSuccess ) {
		fetch( ajaxUrl, {
			method:  'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body:    body + '&nonce=' + encodeURIComponent( nonce ),
		} )
		.then( function(r) { return r.json(); } )
		.then( function(res) {
			if ( res.success ) {
				onSuccess( res.data || {} );
				msg( '✓ Saved — reload the customer dashboard to see it.', true );
			} else {
				msg( '✗ ' + ( res.data || 'Save failed.' ), false );
			}
		} )
		.catch( function() { msg( '✗ Request failed.', false ); } );
	}

	// ── Media library ────────────────────────────────────────
	var frame;
	pickBtn.addEventListener( 'click', function() {
		if ( typeof wp === 'undefined' || ! wp.media ) {
			msg( '✗ Media library not available. Try refreshing.', false );
			return;
		}
		if ( frame ) { frame.open(); return; }
		frame = wp.media( {
			title:   'Choose a default profile photo',
			button:  { text: 'Use as default avatar' },
			library: { type: 'image' },
			multiple: false,
		} );
		frame.on( 'select', function() {
			var att = frame.state().get( 'selection' ).first().toJSON();
			var url = att.sizes
				? ( att.sizes.thumbnail || att.sizes.medium || att.sizes.full || att ).url
				: att.url;
			doAjax(
				'action=nahnu_sct_save_avatar&attachment_id=' + encodeURIComponent( att.id ),
				function( data ) { setPreview( data.preview_url || url ); }
			);
		} );
		frame.open();
	} );

	// ── Remove ───────────────────────────────────────────────
	if ( removeBtn ) {
		removeBtn.addEventListener( 'click', function() {
			if ( ! confirm( 'Remove the default avatar?' ) ) return;
			doAjax( 'action=nahnu_sct_remove_avatar', function() {
				clearPreview();
				presetBtns.forEach( function(b) { b.classList.remove( 'selected' ); } );
			} );
		} );
	}

	// ── Presets ───────────────────────────────────────────────
	presetBtns.forEach( function( btn ) {
		btn.addEventListener( 'click', function() {
			var key = this.dataset.preset;
			var img = this.querySelector( 'img' );
			var url = img ? img.src : '';
			presetBtns.forEach( function(b) { b.classList.remove( 'is-selected' ); } );
			this.classList.add( 'is-selected' );
			doAjax(
				'action=nahnu_sct_save_preset&preset=' + encodeURIComponent( key ),
				function( data ) { setPreview( data.preview_url || url ); }
			);
		} );
	} );

} )();

// ── Live preview, presets, export, import ───────────────────
( function () {
	'use strict';

	var form    = document.getElementById( 'nst-form' );
	var frame   = document.getElementById( 'nst-preview' );
	var status  = document.getElementById( 'nst-preview-status' );
	if ( ! form || ! frame ) return;

	var cfg     = ( typeof nahnuSctTools !== 'undefined' ) ? nahnuSctTools : { nonce: '', ajaxUrl: ajaxurl, presets: {} };
	var timer   = null;
	var inflight = null;

	function setStatus( text, cls ) {
		if ( ! status ) return;
		status.textContent = text;
		status.className   = 'nst-preview-status' + ( cls ? ' is-' + cls : '' );
	}

	function liveStyle() {
		var doc = frame.contentDocument;
		return doc ? doc.getElementById( 'nahnu-sct-preview-inline-css' ) : null;
	}

	// POST the current form state and inject the generated CSS into the iframe.
	function refresh() {
		var el = liveStyle();
		if ( ! el ) return;
		if ( inflight ) inflight.abort();
		inflight = new AbortController();
		setStatus( 'Updating…', 'busy' );

		var body = new URLSearchParams();
		body.set( 'action', 'nahnu_sct_preview_css' );
		body.set( 'nonce',  cfg.nonce );
		new FormData( form ).forEach( function ( value, key ) {
			if ( key.indexOf( '_wp' ) === 0 || key.indexOf( 'nst_' ) === 0 ) return; // nonce / routing fields
			body.set( 'settings[' + key + ']', value );
		} );

		fetch( cfg.ajaxUrl, { method: 'POST', body: body, signal: inflight.signal } )
			.then( function(r){ return r.json(); } )
			.then( function(res) {
				if ( res && res.success ) {
					el.textContent = res.data.css;
					setStatus( 'Live preview', '' );
				} else {
					setStatus( 'Preview failed', 'err' );
				}
			} )
			.catch( function(e) { if ( e.name !== 'AbortError' ) setStatus( 'Preview failed', 'err' ); } );
	}

	function schedule() {
		clearTimeout( timer );
		timer = setTimeout( refresh, 220 );
	}

	form.addEventListener( 'input',  schedule );
	form.addEventListener( 'change', schedule );
	frame.addEventListener( 'load', refresh );
	if ( frame.contentDocument && frame.contentDocument.readyState === 'complete' ) refresh();

	// ── View switcher (Cart / Checkout / Dashboard) ──
	document.querySelectorAll( '.nst-pv-btn' ).forEach( function( btn ) {
		btn.addEventListener( 'click', function() {
			document.querySelectorAll( '.nst-pv-btn' ).forEach( function(b){ b.classList.remove( 'active' ); } );
			this.classList.add( 'active' );
			var doc = frame.contentDocument; if ( ! doc ) return;
			var view = this.dataset.view;
			doc.querySelectorAll( '.pv-view' ).forEach( function(v) {
				v.classList.toggle( 'is-active', v.id === 'pv-' + view );
			} );
		} );
	} );

	// Auto-switch the preview to the tab being edited.
	var tabToView = { cart: 'cart', checkout: 'checkout', account: 'dashboard' };
	document.querySelectorAll( '.nst-tab' ).forEach( function( tab ) {
		tab.addEventListener( 'click', function() {
			var v = tabToView[ this.dataset.tab ];
			if ( ! v ) return;
			var btn = document.querySelector( '.nst-pv-btn[data-view="' + v + '"]' );
			if ( btn ) btn.click();
		} );
	} );
	// …and on load, when the page opened on a deep-linked tab.
	frame.addEventListener( 'load', function () {
		var active = document.querySelector( '.nst-tab.active' );
		var v = active && tabToView[ active.dataset.tab ];
		var btn = v && document.querySelector( '.nst-pv-btn[data-view="' + v + '"]' );
		if ( btn && ! btn.classList.contains( 'active' ) ) btn.click();
	} );

	// ── Presets: fill the form, do not save ──
	function setField( name, value ) {
		var els = form.querySelectorAll( '[name="' + name + '"]' );
		if ( ! els.length ) return;
		els.forEach( function( el ) {
			if ( el.type === 'checkbox' ) {
				el.checked = !! value;
				el.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			} else if ( el.type === 'range' ) {
				el.value = value;
				el.dispatchEvent( new Event( 'input', { bubbles: true } ) );
			} else {
				el.value = value;
			}
		} );
	}

	document.querySelectorAll( '.nst-preset' ).forEach( function( card ) {
		function apply() {
			var key = card.dataset.preset;
			var values = cfg.presets[ key ];
			if ( ! values ) return;
			Object.keys( values ).forEach( function( k ) { setField( k, values[ k ] ); } );

			// Keep the accent hex twin + swatches in sync.
			var hex = document.getElementById( 'accent_color_hex' );
			if ( hex && values.accent_color ) { hex.value = values.accent_color; hex.dispatchEvent( new Event( 'input', { bubbles: true } ) ); }

			document.querySelectorAll( '.nst-preset' ).forEach( function(c){ c.classList.remove( 'is-selected' ); } );
			card.classList.add( 'is-selected' );
			schedule();
		}
		card.addEventListener( 'click', apply );
		card.addEventListener( 'keydown', function(e){ if ( e.key === 'Enter' || e.key === ' ' ) { e.preventDefault(); apply(); } } );
	} );

	// ── Export ──
	var exportBtn = document.getElementById( 'nst-export' );
	if ( exportBtn ) {
		exportBtn.addEventListener( 'click', function() {
			var body = new URLSearchParams( { action: 'nahnu_sct_export', nonce: cfg.nonce } );
			fetch( cfg.ajaxUrl, { method: 'POST', body: body } )
				.then( function(r){ return r.json(); } )
				.then( function(res) {
					if ( ! res || ! res.success ) return;
					var blob = new Blob( [ JSON.stringify( res.data.json, null, 2 ) ], { type: 'application/json' } );
					var a = document.createElement( 'a' );
					a.href = URL.createObjectURL( blob );
					a.download = res.data.filename;
					document.body.appendChild( a ); a.click(); a.remove();
					setTimeout( function(){ URL.revokeObjectURL( a.href ); }, 2000 );
				} );
		} );
	}

	// ── Import: show chosen filename ──
	var fileInp = document.getElementById( 'nst_import_file' );
	var fileLbl = document.getElementById( 'nst-import-filename' );
	if ( fileInp && fileLbl ) {
		fileInp.addEventListener( 'change', function() {
			fileLbl.textContent = this.files && this.files[0] ? this.files[0].name : 'No file chosen';
		} );
	}
} )();
