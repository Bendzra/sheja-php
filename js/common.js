$(function ()
{
	function initScroll() {
		var vewable = $('.vewable');
		if(vewable.length === 0) return;

		var offset = vewable.offset();
		
		offset.left -= 20;
		offset.top -= $(window).height() / 3;

		$('html, body').animate({
			scrollTop: offset.top,
			scrollLeft: offset.left
		}, 700);
	}
	
	initScroll();

});
