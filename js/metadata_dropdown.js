jQuery( document ).ready(function() {

	$(".metadata-dropdown").on('click', function(event){
			$(this).toggleClass('active');
			event.stopPropagation();
	});

});
