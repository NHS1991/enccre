/*!
	Wheelzoom 3.0.0
	license: MIT
	http://www.jacklmoore.com/wheelzoom
*/
window.wheelzoom = (function(){
	var defaults = {
		zoom: 0.10
	};

	var canvas = document.createElement('canvas');

	function setSrcToBackground(img) {
		img.style.backgroundImage = "url('"+img.src+"')";
		img.style.backgroundRepeat = 'no-repeat';
		canvas.width = img.naturalWidth;
		canvas.height = img.naturalHeight;
		img.src = canvas.toDataURL();
        img.style.cursor = 'pointer';

	}

	main = function(img, options){
		if (!img || !img.nodeName || img.nodeName !== 'IMG') { return; }

		var settings = {};
		var width;
		var height;
		var bgWidth;
		var bgHeight;
		var bgPosX;
		var bgPosY;
		var previousEvent;

		function updateBgStyle() {
			if (bgPosX > 0) {
				bgPosX = 0;
			} else if (bgPosX < width - bgWidth) {
				bgPosX = width - bgWidth;
			}

			if (bgPosY > 0) {
				bgPosY = 0;
			} else if (bgPosY < height - bgHeight) {
				bgPosY = height - bgHeight;
			}

			img.style.backgroundSize = bgWidth+'px '+bgHeight+'px';
			img.style.backgroundPosition = bgPosX+'px '+bgPosY+'px';
		}

		function reset() {
            img.addEventListener('wheel',onwheel);
            img.addEventListener('clickzoom',onclickzoom);
			bgWidth = width;
			bgHeight = height;
			bgPosX = bgPosY = 0;
            img.style.cursor = 'pointer';
			updateBgStyle();
		}

		function onwheel(e) {
			var deltaY = 0;
            img.removeEventListener('clickzoom',onclickzoom);
			e.preventDefault();
			if (e.deltaY) { // FireFox 17+ (IE9+, Chrome 31+?)
				deltaY = e.deltaY;
			} else if (e.wheelDelta) {
				deltaY = -e.wheelDelta;
			}

			// As far as I know, there is no good cross-browser way to get the cursor position relative to the event target.
			// We have to calculate the target element's position relative to the document, and subtrack that from the
			// cursor's position relative to the document.
			var rect = img.getBoundingClientRect();
			var offsetX = e.pageX - rect.left - document.body.scrollLeft;
			var offsetY = e.pageY - rect.top - document.body.scrollTop;

			// Record the offset between the bg edge and cursor:
			var bgCursorX = offsetX - bgPosX;
			var bgCursorY = offsetY - bgPosY;
			
			// Use the previous offset to get the percent offset between the bg edge and cursor:
			var bgRatioX = bgCursorX/bgWidth;
			var bgRatioY = bgCursorY/bgHeight;

			// Update the bg size:
			if (deltaY < 0) {
				bgWidth += bgWidth*settings.zoom;
				bgHeight += bgHeight*settings.zoom;
                img.style.cursor = "zoom-in";
				img.style.cursor = "-webkit-zoom-in";
				img.style.cursor = "-moz-zoom-in";
			} else {
				bgWidth -= bgWidth*settings.zoom;
				bgHeight -= bgHeight*settings.zoom;
                img.style.cursor = "zoom-out";
				img.style.cursor = "-webkit-zoom-out";
				img.style.cursor = "-moz-zoom-out";
			}

			// Take the percent offset and apply it to the new size:
			bgPosX = offsetX - (bgWidth * bgRatioX);
			bgPosY = offsetY - (bgHeight * bgRatioY);

			// Prevent zooming out beyond the starting size
			if (bgWidth <= width || bgHeight <= height) {
				reset();
			} else {
				updateBgStyle();
			}
		}

		function drag(e) {
			e.preventDefault();
			bgPosX += (e.pageX - previousEvent.pageX);
			bgPosY += (e.pageY - previousEvent.pageY);
			previousEvent = e;
            img.style.cursor = 'pointer';
			updateBgStyle();
		}

		function removeDrag() {
			img.removeEventListener('mouseup', removeDrag);
			img.removeEventListener('mousemove', drag);
            img.removeEventListener('touchend', removeDrag);
            img.removeEventListener('touchmove', drag);
		}

		// Make the background draggable
		function draggable(e) {
			e.preventDefault();
			previousEvent = e;
			img.addEventListener('mousemove', drag);
            img.addEventListener('touchmove', drag);
			img.addEventListener('mouseup', removeDrag);
            img.addEventListener('touchend', removeDrag);
		}
        function onclickzoom(){
            img.removeEventListener('wheel', onwheel);
            bgHeight = img.getAttribute("height_img");
            bgWidth = img.getAttribute("width_img");
            // Prevent zooming out beyond the starting size
            if (bgWidth <= width || bgHeight <= height) {
                reset();
            } else {
                updateBgStyle();
            }
        }

		function loaded() {
			var computedStyle = window.getComputedStyle(img, null);

			width = parseInt(computedStyle.width, 10);
			height = parseInt(computedStyle.height, 10);
			bgWidth = width;
			bgHeight = height;
			bgPosX = 0;
			bgPosY = 0;

			setSrcToBackground(img);

			img.style.backgroundSize =  width+'px '+height+'px';
			img.style.backgroundPosition = '0 0';
			img.addEventListener('wheelzoom.reset', reset);
            img.addEventListener('clickzoom',onclickzoom);
			img.addEventListener('wheel', onwheel);
			img.addEventListener('mousedown', draggable);
            img.addEventListener('touchstart',draggable);
		}

		img.addEventListener('wheelzoom.destroy', function (originalProperties) {
			console.log(originalProperties);
			img.removeEventListener('wheelzoom.destroy');
			img.removeEventListener('wheelzoom.reset', reset);
			img.removeEventListener('load', onload);
			img.removeEventListener('mouseup', removeDrag);
            img.removeEventListener('touchend', removeDrag);
			img.removeEventListener('mousemove', drag);
            img.removeEventListener('touchmove', drag);
			img.removeEventListener('mousedown', draggable);
            img.removeEventListener('touchstart', draggable);
			img.removeEventListener('wheel', onwheel);
            img.removeEventListener('clickzoom',onclickzoom);

            img.style.cursor = 'pointer';
			img.style.backgroundImage = originalProperties.backgroundImage;
			img.style.backgroundRepeat = originalProperties.backgroundRepeat;
			img.src = originalProperties.src;
		}.bind(null, {
			backgroundImage: img.style.backgroundImage,
			backgroundRepeat: img.style.backgroundRepeat,
			src: img.src
		}));

		options = options || {};
		Object.keys(defaults).forEach(function(key){
			settings[key] = options[key] !== undefined ? options[key] : defaults[key];
        });

		if (img.complete) {
			loaded();
		} else {
			function onload() {
				img.removeEventListener('load', onload);
				loaded();
			}
			img.addEventListener('load', onload);
		}
	};

	// Do nothing in IE8
	if (typeof window.getComputedStyle !== 'function') {
		return function(elements) {
			return elements;
		}
	} else {
		return function(elements,options) {
			if (elements && elements.length) {
				Array.prototype.forEach.call(elements, main, options);
			} else if (elements && elements.nodeName) {
				main(elements,options);
			}
			return elements;
		}
	}
}());