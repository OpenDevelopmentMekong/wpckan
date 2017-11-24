jQuery( document ).ready(function() {

	jQuery(".metadata-dropdown").on('click', function(event){
			$(this).toggleClass('active');
			event.stopPropagation();
	});

});
