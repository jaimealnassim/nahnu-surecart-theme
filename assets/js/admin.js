/* Nahnu SureCart Theme — Admin JS */
( function () {
	'use strict';

	// ── Tab switching ───────────────────────────────────────
	document.querySelectorAll( '.nst-tab' ).forEach( function ( tab ) {
		tab.addEventListener( 'click', function () {
			document.querySelectorAll( '.nst-tab' ).forEach( function(t) { t.classList.remove( 'active' ); } );
			document.querySelectorAll( '.nst-panel' ).forEach( function(p) { p.classList.remove( 'active' ); } );
			this.classList.add( 'active' );
			var panel = document.getElementById( 'nst-tab-' + this.dataset.tab );
			if ( panel ) panel.classList.add( 'active' );
		} );
	} );

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

	var nonce   = ( typeof nstAvatar !== 'undefined' ) ? nstAvatar.nonce   : '';
	var ajaxUrl = ( typeof nstAvatar !== 'undefined' ) ? nstAvatar.ajaxUrl : ( typeof ajaxurl !== 'undefined' ? ajaxurl : '' );

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
				'action=nst_save_avatar&attachment_id=' + encodeURIComponent( att.id ),
				function( data ) { setPreview( data.preview_url || url ); }
			);
		} );
		frame.open();
	} );

	// ── Remove ───────────────────────────────────────────────
	if ( removeBtn ) {
		removeBtn.addEventListener( 'click', function() {
			if ( ! confirm( 'Remove the default avatar?' ) ) return;
			doAjax( 'action=nst_remove_avatar', function() {
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
			presetBtns.forEach( function(b) { b.style.borderColor = '#e0e0e0'; b.style.boxShadow = ''; } );
			this.style.borderColor = '#1a1a1a';
			this.style.boxShadow = '0 0 0 3px rgba(26,26,26,0.15)';
			doAjax(
				'action=nst_save_preset&preset=' + encodeURIComponent( key ),
				function( data ) { setPreview( data.preview_url || url ); }
			);
		} );
	} );

} )();
