<html>
	<head>
		<script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript"></script>
		<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
		<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
                <style>
                    
select[readonly].select2 + .select2-container {
  pointer-events: none;
  touch-action: none;

  .select2-selection {
    background: #eee;
    box-shadow: none;
  }

  .select2-selection__arrow,
  .select2-selection__clear {
    display: none;
  }
}</style>
		<script type="text/javascript">
			$(document).ready(function() {
                            $("#country").select2({data:[{id:'au',text:"Australia"},{id:'in',text:"India"}]});
                            $('#country').val('in'); 
                            $('#country').trigger('change');                            
                        $('#country').select2({
                   
                    }).on("change", function (e) { 
                        alert($('#country').select2().val());
                    });
//                    $.ajax({
//                    type: 'POST',
//                    url: "<?php echo base_url() . 'Sa_vin_search/plates_sku'; ?>/?plate=aaa",
//                    data: '',
//                     contentType: "application/json",
//                    dataType: "json",
//                    beforeSend: function () {
//                        //start loader 
//                       alert();
//                    },
//                    success: function (sku_codes) {
//                        
//                        $("#country").select2({
//				  data: sku_codes
//				});
//
//                                $("#country").select2({disabled:false});
//                    }
//                });
			});
		</script>
	</head>
	<body>
		<h1>DropDown with Search using jQuery</h1>
		<div>
			<select id="country" style="width:300px;" >
			<!-- Dropdown List Option -->
			</select>
		</div>
	</body>
</html>