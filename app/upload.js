(function () {

	if (!window.FormData) {
		alert("No formdata support!");
        return;
    }

    $('#btnx').click(function() {

		$("#response").text ('Uploading . . .');

		formdata = new FormData(document.getElementById('boxsendit'));
		
    	$.ajax({  
	        url: "upload.php",  
	        type: "POST",  
	        data: formdata,  
	        processData: false,  
	        contentType: false,  
	        success: function (res) {  
	            $("#response").html(res);
	        }  
    	});

	});

})();