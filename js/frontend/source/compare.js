/**
 * Premise Split View plugin main JS file
 */
( function($) {

	// decalere our global variables
	var psvWrapper;

	// On document ready
	$(document).ready(function(){

		// reference our wrapper
		psvWrapper = $('.psv-compare-inner');

		// initiate the split view
		psvDragCompareInit();
	});

	// initiate the drag compare functionlality
	function psvDragCompareInit() {
		if ( psvWrapper.length > 0 ) {
			psvWrapper.each( function() {
				var compare = $( this );
				compareThis( compare );
				$( window ).resize( function() {
					compareThis( compare );
				} );
			});
		}
		return false;
	}

	// Start the split view. This is what creates the actual functionality.
	function compareThis(container, makeDraggable, sectionsClickable) {
		// if ( $( window ).width() <= 750 ) return false;

		container         = container         || null;
		makeDraggable     = 'boolean' === typeof makeDraggable     ? makeDraggable     : true;
		sectionsClickable = 'boolean' === typeof sectionsClickable ? sectionsClickable : false;

		// Check if the container exists first
		if ( ! container ) {
			return false;
		}

		// reference our variables
		var front  = container.find('.psv-compare-front'),
		handle     = container.find('.psv-compare-handle'),
		content    = container.find('.psv-content'),
		left       = handle.find('.psv-slide-left'),
		right      = handle.find('.psv-slide-right'),
		isResizing = false,
		rightOffset = 10;

		// set the content width to the same width of the container
		content.width( container.width() );
		$(window).resize( function(){
			content.width( container.width() );
			slideCenter(false);
		});

		left.click(slideRight);

		right.click(slideLeft);

		// Bind the handle behaviour based on the params submitted
		// by default this is set to be drggable
		if ( makeDraggable ) {
			doDraggable();
		}
		else {
			handle.click(slideCenter);
		}

		// if left and right sections shoudl be clickable
		// Adds the class used to bind that event
		// Currently not supported if handle is draggable
		if ( sectionsClickable && ! makeDraggable ) {
			doClickableSections();
		}

		// bind the draggable functionality
		function doDraggable() {
			handle.on('mousedown', function() {
				// start resizing
				$(this).addClass('psv-dragging');
				isResizing = true;
				$(document).on('mousemove', function (e) {
					// we don't want to do anything if we aren't resizing.
					if (!isResizing)
						return;
					var offsetRight = container.width() - (e.clientX - container.offset().left);

					if ( offsetRight > rightOffset && offsetRight < ( container.width() - rightOffset ) ) {
						front.css('width', offsetRight);
					}
				}).
				on('mouseup', function (e) {
					// stop resizing
					handle.removeClass('psv-dragging');
					isResizing = false;
					return false;
				});
				return false;
			});
			return false;
		}

		// bnd the clickable section
		function doClickableSections() {
			$('.psv-compare-left, .psv-compare-right').addClass('psv-compare-clickable');

			// bind comparing sections
			// allows clicking on the left or right switch
			$('.psv-compare-left.psv-compare-clickable').click(function() {
				slideRight();
				return false;
			});
			$('.psv-compare-right.psv-compare-clickable').click(function() {
				slideLeft();
				return false;
			});
			return false;
		}

		// slide left
		function slideLeft() {
			front.removeClass('psv-slided-left');
			front.addClass('psv-slided-right');
			front.animate({
				width: rightOffset
			}, 400);
			return false;
		}

		// slide right
		function slideRight() {
			front.removeClass('psv-slided-right');
			front.addClass('psv-slided-left');
			var width = container.width() - rightOffset;
			front.animate({
				width: width
			}, 400);
			return false;
		}

		// slide to cnenter
		function slideCenter(animate) {
			animate = 'boolean' === typeof animate ? animate : true;
			front.removeClass('psv-slided-left psv-slided-right');
			if (animate) {
				front.animate({
					width: '50%'
				}, 400);
			} else {
				front.css('width', '50%');
			}
			return false;
		}
	}

})(jQuery);
