jQuery( document ).ready(function() {

	var dd = new DropDown( $('.metadata-dropdown') );

	$(document).click(function() {
		$('.metadata-dropdown').removeClass('active');
	});

	$(".metadata-dropdown").on('click', function(event){
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
