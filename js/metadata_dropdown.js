function DropDown(el) {
	this.dd = el;
	this.initEvents();
}

DropDown.prototype = {
		initEvents : function() {
				var obj = this;

				obj.dd.on('click', function(event){
						$(this).toggleClass('active');
						event.stopPropagation();
				});
		}
}

jQuery( document ).ready(function() {

	$(".metadata-dropdown").on('click', function(event){
		$(this).toggleClass('active');
		return false;
	});

	var dd = new DropDown( $('.metadata-dropdown') );

	$(document).click(function() {
		$('.metadata-dropdown').removeClass('active');
	});

});
