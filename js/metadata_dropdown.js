$(".metadata-dropdown").on('click', function(event){
	$(this).toggleClass('active');
	return false;
});

$(function() {

	var dd = new DropDown( $('.metadata-dropdown') );

	$(document).click(function() {
		// all dropdowns
		$('.metadata-dropdown').removeClass('active');
	});

	obj.dd.on('click', function(event){
		$(this).toggleClass('active');
		return false;
	});

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

});
