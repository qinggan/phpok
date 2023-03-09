(function ($) {
	var defaults = {
		url: false,
		callback: false,
		target: false,
		duration: 120,
		on: 'mouseover' // other options: 'grab', 'click', 'toggle'
	};

	
	$.zoombie = function(target, source, img) {
		var outerWidth,
			outerHeight,
			xRatio,
			yRatio,
			offset,
			position = $(target).css('position');

		
		$(target).css({
			position: /(absolute|fixed)/.test() ? position : 'relative',
			overflow: 'hidden'
		});

		$(img)
			.addClass('zoomImg')
			.css({
				position: 'absolute',
				top: 0,
				left: 0,
				opacity: 0,
				width: img.width,
				height: img.height,
				border: 'none',
				maxWidth: 'none'
			})
			.appendTo(target);

		return {
			init: function() {
				outerWidth = $(target).outerWidth();
				outerHeight = $(target).outerHeight();
				xRatio = (img.width - outerWidth) / $(source).outerWidth();
				yRatio = (img.height - outerHeight) / $(source).outerHeight();
				offset = $(source).offset();
			},
			move: function (e) {
				var left = (e.pageX - offset.left),
					top = (e.pageY - offset.top);

				top = Math.max(Math.min(top, outerHeight), 0);
				left = Math.max(Math.min(left, outerWidth), 0);

				img.style.left = (left * -xRatio) + 'px';
				img.style.top = (top * -yRatio) + 'px';
			}
		};
	};

	$.fn.zoombie = function (options) {
		return this.each(function () {
			var
			settings = $.extend({}, defaults, options || {}),
			//target will display the zoomed iamge
			target = settings.target || this,
			//source will provide zoom location info (thumbnail)
			source = this,
			img = new Image(),
			$img = $(img),
			mousemove = 'mousemove',
			clicked = false;

			// If a url wasn't specified, look for an image element.
			if (!settings.url) {
				settings.url = $(source).find('img').attr('_src');
				if (!settings.url) {
					return;
				}
			}

			img.onload = function () {
				var zoombie = $.zoombie(target, source, img);

				function start(e) {
					zoombie.init();
					zoombie.move(e);

					// Skip the fade-in for IE8 and lower since it chokes on fading-in
					// and changing position based on mousemovement at the same time.
					$img.stop()
					.fadeTo($.support.opacity ? settings.duration : 0, 1);
				}

				function stop() {
					$img.stop()
					.fadeTo(settings.duration, 0);
				}

				if (settings.on === 'grab') {
					$(source).mousedown(
						function (e) {
							$(document).one('mouseup',
								function () {
									stop();

									$(document).unbind(mousemove, zoombie.move);
								}
							);

							start(e);

							$(document)[mousemove](zoombie.move);

							e.preventDefault();
						}
					);
				} else if (settings.on === 'click') {
					$(source).click(
						function (e) {
							if (clicked) {
								// bubble the event up to the document to trigger the unbind.
								return;
							} else {
								clicked = true;
								start(e);
								$(document)[mousemove](zoombie.move);
								$(document).one('click',
									function () {
										stop();
										clicked = false;
										$(document).unbind(mousemove, zoombie.move);
									}
								);
								return false;
							}
						}
					);
				} else if (settings.on === 'toggle') {
					$(source).click(
						function (e) {
							if (clicked) {
								stop();
							} else {
								start(e);
							}
							clicked = !clicked;
						}
					);
				} else {
					zoombie.init(); 

					$(source).hover(
						start,
						stop
					)[mousemove](zoombie.move);
				}

				if ($.isFunction(settings.callback)) {
					settings.callback.call(img);
				}
			};

			img.src = settings.url;
		});
	};

	$.fn.zoombie.defaults = defaults;
}(window.jQuery));