jQuery(document).ready(function($) {
	var POPUP = $('.cc-popup'),
		POPUP_ID = POPUP.attr('id'),
		COOKIE_ID = "" + POPUP_ID + "=0; path=/",
		BODY = document.body,
		HTML = document.documentElement,
		POPUP_CLOSE = $('.cc-popup-close');

	POPUP_CLOSE.on('click', function() {
		document.cookie = COOKIE_ID;
		POPUP.remove();
	});

	$(window).on('load scroll', function() {
		if (POPUP.length > 0) {
			var DOC_HEIGHT = Math.max(BODY.scrollHeight, BODY.offsetHeight, HTML.clientHeight, HTML.scrollHeight, HTML.offsetHeight),
				WINDOW_TOP = BODY.scrollTop,
				WINDOW_HEIGHT = $(window).innerHeight(),
				TOP_ANCHOR = WINDOW_TOP >= DOC_HEIGHT / 2 - WINDOW_HEIGHT / 2;
			if (TOP_ANCHOR) {
				POPUP.addClass('cc-popup-block');
			}
		}
	});
});
