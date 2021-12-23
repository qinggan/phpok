
;(function($) {
	$.browser = {};
	$.browser.mozilla = false;
	$.browser.webkit = false;
	$.browser.opera = false;
	$.browser.msie = false;

	var nAgt = navigator.userAgent;
	$.browser.name = navigator.appName;
	$.browser.fullVersion = '' + parseFloat(navigator.appVersion);
	$.browser.majorVersion = parseInt(navigator.appVersion, 10);
	var nameOffset, verOffset, ix;

	// In Opera, the true version is after "Opera" or after "Version"
	if ((verOffset = nAgt.indexOf("Opera")) != -1) {
		$.browser.opera = true;
		$.browser.name = "Opera";
		$.browser.fullVersion = nAgt.substring(verOffset + 6);
		if ((verOffset = nAgt.indexOf("Version")) != -1)
			$.browser.fullVersion = nAgt.substring(verOffset + 8);
	}
	// In MSIE, the true version is after "MSIE" in userAgent
	else if ((verOffset = nAgt.indexOf("MSIE")) != -1) {
		$.browser.msie = true;
		$.browser.name = "Microsoft Internet Explorer";
		$.browser.fullVersion = nAgt.substring(verOffset + 5);
	}
	// In Chrome, the true version is after "Chrome"
	else if ((verOffset = nAgt.indexOf("Chrome")) != -1) {
		$.browser.webkit = true;
		$.browser.name = "Chrome";
		$.browser.fullVersion = nAgt.substring(verOffset + 7);
	}
	// In Safari, the true version is after "Safari" or after "Version"
	else if ((verOffset = nAgt.indexOf("Safari")) != -1) {
		$.browser.webkit = true;
		$.browser.name = "Safari";
		$.browser.fullVersion = nAgt.substring(verOffset + 7);
		if ((verOffset = nAgt.indexOf("Version")) != -1)
			$.browser.fullVersion = nAgt.substring(verOffset + 8);
	}
	// In Firefox, the true version is after "Firefox"
	else if ((verOffset = nAgt.indexOf("Firefox")) != -1) {
		$.browser.mozilla = true;
		$.browser.name = "Firefox";
		$.browser.fullVersion = nAgt.substring(verOffset + 8);
	}
	// In most other browsers, "name/version" is at the end of userAgent
	else if ((nameOffset = nAgt.lastIndexOf(' ') + 1) <
		(verOffset = nAgt.lastIndexOf('/'))) {
		$.browser.name = nAgt.substring(nameOffset, verOffset);
		$.browser.fullVersion = nAgt.substring(verOffset + 1);
		if ($.browser.name.toLowerCase() == $.browser.name.toUpperCase()) {
			$.browser.name = navigator.appName;
		}
	}
	// trim the fullVersion string at semicolon/space if present
	if ((ix = $.browser.fullVersion.indexOf(";")) != -1)
		$.browser.fullVersion = $.browser.fullVersion.substring(0, ix);
	if ((ix = $.browser.fullVersion.indexOf(" ")) != -1)
		$.browser.fullVersion = $.browser.fullVersion.substring(0, ix);

	$.browser.majorVersion = parseInt('' + $.browser.fullVersion, 10);
	if (isNaN($.browser.majorVersion)) {
		$.browser.fullVersion = '' + parseFloat(navigator.appVersion);
		$.browser.majorVersion = parseInt(navigator.appVersion, 10);
	}
	$.browser.version = $.browser.majorVersion;
})(jQuery);
