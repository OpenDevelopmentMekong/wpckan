jQuery( document ).ready(function() {
	$( ".template-selector" ).change(function() {
		var value = $(this).val();
		if (value == "dataset-grid"){
			$(".template-dependent-options").hide();
		}else{
			$(".template-dependent-options").show();
		}
	});
});
