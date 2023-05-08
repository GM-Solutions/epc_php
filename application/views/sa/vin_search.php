<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!-- Meta, title, CSS, favicons, etc. -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>EPC Reports</title>

        <!-- Bootstrap -->
        <link href="../assets/reports/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Font Awesome -->
        <link href="../assets/reports/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
        <!-- NProgress -->
        <link href="../assets/reports/vendors/nprogress/nprogress.css" rel="stylesheet">

        <!-- bootstrap-daterangepicker -->
        <link href="../assets/reports/vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">
        <!-- bootstrap-datetimepicker -->
        <link href="../assets/reports/vendors/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css" rel="stylesheet">

        <!-- Custom Theme Style -->
        <link href="../assets/reports/build/css/custom.css" rel="stylesheet" type="text/css"/>


        <!-- jQuery -->
        <script src="../assets/reports/vendors/jquery/dist/jquery.min.js" type="text/javascript"></script>
        <!-- Bootstrap -->
        <script src="../assets/reports/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
        <!-- FastClick -->
        <script src="../assets/reports/vendors/fastclick/lib/fastclick.js"></script>

<!--        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>-->
<!--        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.7.14/js/bootstrap-datetimepicker.min.js"></script>-->
<!--        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.js"></script>-->
        <!--         bootstrap-daterangepicker -->                
        <script src="../assets/reports/vendors/moment/min/moment.min.js"></script>
                <script src="../assets/reports/vendors/bootstrap-daterangepicker/daterangepicker.js"></script>
<!--                 bootstrap-datetimepicker  -->
            <script src="../assets/reports/vendors/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
                <link href="../assets/reports/vendors/pnotify/dist/pnotify.css" rel="stylesheet">
        <script type="text/javascript" src="http://www.google.com/jsapi"></script>

         <!-- Datatables -->
    <link href="../assets/reports/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="../assets/reports/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <link href="../assets/reports/vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css" rel="stylesheet">
    <link href="../assets/reports/vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
    <link href="../assets/reports/vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css" rel="stylesheet">

        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script src="https://code.highcharts.com/modules/exporting.js"></script>
        <script src="https://code.highcharts.com/modules/offline-exporting.js"></script>
        
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
                <style>
            
            .hide_me{
                        pointer-events: none;
                        touch-action: none;
                        background: #eee !important;
                        box-shadow: none;
                        
                    }
                    
            
            #load, #loading {
                width: 100%;
                height: 100%;
                top: 0;
                left: 0;
                position: fixed;
                display: block;
                opacity: 0.7;
                background-color: #fff;
                z-index: 99;
                text-align: center;
            }

            .loader {
                position: absolute;
                top: 30%;
                left: 48%;
                z-index: 100;
                border: 16px solid #f3f3f3;
                border-radius: 50%;
                border-top: 16px solid blue;
                border-bottom: 16px solid blue;
                width: 120px;
                height: 120px;
                -webkit-animation: spin 2s linear infinite;
                animation: spin 2s linear infinite;
            }

            @-webkit-keyframes spin {
                0% { -webkit-transform: rotate(0deg); }
                100% { -webkit-transform: rotate(360deg); }
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
<!--        <link href="../assets/search_select/selectstyle.css" rel="stylesheet" type="text/css">-->
    </head>

    <body class="nav-md">
        <div class="container body">
            <div class="main_container">


                <!-- top navigation -->
                <?php $this->load->view('top_nav'); ?>
                <!-- /top navigation -->

                <!-- page content -->
                <div class="right_col" role="main">
                    <div class="">
                        <div class="page-title row">
                            <div class=" col-md-3">
                                <h3>VIN Search</h3>
                            </div>
                            <div id="alrt" class="col-md-8"></div>
                        </div>

                        <div class="clearfix"></div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="x_panel">
                                    <div class="x_title">

                                        <ul class="nav navbar-right panel_toolbox">
                                            <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                            </li>

                                            <li><a class="close-link"><i class="fa fa-close"></i></a>
                                            </li>
                                        </ul>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content">

                                        <form action="javascript: void(0);">
                                            <div class="row">
                                                <div  class="col-md-3" aria-hidden="false">
                                                    <label for="vin_no" class="control-label">VIN</label>
                                                    <input id="vin_no" class="form-control" type="text" name="vin_no" value=""/>
                                                </div>
                                                <div class="col-sm-2">
                                                    <br/>
                                                    <button type="button" onclick="getVinDetails()" class="btn btn-primary btn-block" name="get_report" >Get VIN Details</button>

                                                </div>

                                                <div  class="col-md-3" aria-hidden="false">
                                                    <label for="filter_selection" class="control-label">Filter By</label>
                                                    <select theme="google" width="200"  placeholder="With VIN" data-search="false" id="filter_selection" onchange="change_filter(this)" name="sbomb_exists" class="selectpicker form-control " >                                                        
                                                        <option value="with_vin"  aria-hidden="true" style="" selected> With VIN </option>                                                                                                                                  
                                                        <option value="other" aria-hidden="true" style=""> Other </option>                                                                                                                                  
                                                    </select>
                                                </div>
                                                <div  class="col-md-3" aria-hidden="false">
                                                    <label for="filter_selection" class="control-label">Serviceable or Not</label>
                                                    <br/>
                                                    <input type="radio" name="serviceable" value="serviceable" checked> Serviceable<br>
                                                    <input type="radio" name="serviceable" value="all" > All<br>
                                                </div>

                                            </div>
                                            <div class="row">
                                                <div  class="col-md-5" aria-hidden="false">
                                                    <label for="select2-single-input-sm" class="control-label">Plant</label><br/>
                                                <select id="plant" style="width:400px;" >
                                                    <!-- Dropdown List Option -->
                                                    </select>
                                                </div>
    <div  class="col-md-6" aria-hidden="false">
    <label for="select2-single-input-sm" class="control-label">SKU code / Description</label><br/>

    <select id="skucodes" style="width:400px;" >
			<!-- Dropdown List Option -->
    </select>
    </div>
<!--                                                <div  class="col-md-3" aria-hidden="false">
                                                    <label for="select2-single-input-sm" class="control-label">SKU code</label>
                                                    <input id="sku_code" class="form-control" type="text" name="sku_code" value=""/>
                                                </div>-->
                                            </div>
<div class="row">
    <div class="col-sm-3">
                                                    
        <div class="form-group">
            <label>Select <span id="lbl">Manufacture</span> Date From</label>
                        <div class='input-group date' id='myDatepickerfrom'>
                            <input type='text' class="form-control" />
                            <span class="input-group-addon">
                               <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    
                                                    <div class="form-group">
                                                        <label><span id="lbl">Manufacture</span> Date To</label>
                        <div class='input-group date' id='myDatepickerto'>
                            <input type='text' class="form-control" />
                            <span class="input-group-addon">
                               <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                                                </div>

                                                <div  class="col-md-2" aria-hidden="false"><br/>
                                                    <input id="component" class="form-control" type="text" name="component" value="" placeholder="Part Number"/>
                                                </div>
                                                <div  class="col-md-3" aria-hidden="false"><br/>
                                                    <input id="description" class="form-control" type="text" name="description" value="" placeholder="Part Description"/>
                                                </div>
</div>
<div class="row">
                                                <div  class="col-md-3" aria-hidden="false">
<label>First Vehicle <span id="lbl">Manufacture</span> Date On:</label>
<input type="text" id="first_manufacturing_date" class="form-control" placeholder="First Vehicle Manufacturing Date" name="first_manufacturing_date" value="" readonly="" /></div>
                                                <div  class="col-md-3" aria-hidden="false"><br/>
                                                    <button type="button"  onclick="searchFilter(0)" class="btn btn-primary btn-block" name="get_rpt" >Get Spare Parts</button>
                                                    
                                                </div>
                                                <div  class="col-md-3" aria-hidden="false"><br/>
                                                    
                                                    <button type="button" onclick="e7858xport()" class="btn btn-dark btn-block" name="get_rpt" >Download Spare Parts</button>
                                                </div>     
                                            </div>
                                            <div class="row">
                                                <div  class="col-md-12">                                                    
                                                    <h4>Vin Data</h4>
                                                    <div id="t12get">
                                                        
                                                        
<!--                                                        <table id="datatable" class="table table-striped table-bordered"></table>-->
                                                        <table id="example" class="display table table-striped table-bordered " style="width:100%">
        <thead>
            <tr>
            <th>Part Number</th><!-- part number-->
            <th>Part Description</th><!-- part number-->
            <th>Quantity</th> 
            <th>Valid From</th><!-- part number-->
            <th>Valid To</th><!-- part number-->
            <th>Locator code</th><!-- serial number-->
            <th>Locator Description</th><!-- serial Description-->
            <th>Service Tag</th><!-- Service Tag-->
            <th>Current Service Tag</th><!-- Service tag current -->				 
            <th>Change Date</th><!-- Change Date -->				 
            <th>Status</th>
            <th>Plate</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
            <th>Part Number</th><!-- part number-->
            <th>Part Description</th><!-- part number-->
            <th>Quantity</th> 
            <th>Valid From</th><!-- part number-->
            <th>Valid To</th><!-- part number-->
            <th>Locator code</th><!-- serial number-->
            <th>Locator Description</th><!-- serial Description-->
            <th>Service Tag</th><!-- Service Tag-->
            <th>Current Service Tag</th><!-- Service tag current -->				 
            <th>Change Date</th><!-- Change Date -->				 
            <th>Status</th>	
            <th>Plate</th>
            </tr>
        </tfoot>
    </table>
                                                        
                                                    </div>
                                                </div>
                                            </div>

                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /page content -->

                <!-- footer content -->
                <footer>
                    <div class="pull-right">
                        Power by  <a href="https://gladminds.co">Gladminds</a>
                    </div>
                    <div class="clearfix"></div>
                </footer>
                <!-- /footer content -->
            </div>
        </div>
        <script src="../assets/reports/vendors/nprogress/nprogress.js"></script>
        <script src="../assets/reports/vendors/pnotify/dist/pnotify.js"></script>


<!--        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.css">-->
        <!--        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.7.14/css/bootstrap-datetimepicker.min.css">-->
        <!-- Custom Theme Scripts -->
        
        <script >
$(window).load(function () {
    <?php if($select_type == "other"){ ?>
//        $('.dtrng').removeClass('hide_me');  
        document.getElementById("vin_no").readOnly = true;
        $('#filter_selection').val('other');   
//        $('#myDatepickerto > .form-control').prop('disabled', false);
//        $('#myDatepickerfrom > .form-control').prop('disabled', false);

        $('#myDatepickerfrom').data("DateTimePicker").enable();
        $('#myDatepickerto').data("DateTimePicker").enable();
   <?php } else { ?>       

document.getElementById("vin_no").readOnly = false;
$('#filter_selection').val('with_vin');
//$('#myDatepickerfrom > .form-control').prop('disabled', true);
//$('#myDatepickerto > .form-control').prop('disabled', true);
$('#myDatepickerfrom').data("DateTimePicker").disable();
$('#myDatepickerto').data("DateTimePicker").disable();
$("#skucodes").empty(); 
$("#skucodes").select2({disabled:true});
$('#plant').prop("disabled", true);
  <?php } ?>
$('#loading').hide();
});
                                                   
  

</script>
<script>
            function  change_filter(e) {
                if (e.value == "other") {  
                    $('#myDatepickerfrom').data("DateTimePicker").enable();
                    $('#myDatepickerto').data("DateTimePicker").enable();
                    document.getElementById("vin_no").readOnly = true;
                    $('#plant').prop("disabled", false);
                    $('#plant').trigger('change');
                    $("#skucodes").select2({disabled:false});
                    $('#skucodes').trigger('change');
                } else { 
                    $('#myDatepickerfrom').data("DateTimePicker").disable();
                    $('#myDatepickerto').data("DateTimePicker").disable();
                    $('#myDatepickerfrom').data("DateTimePicker").clear();
                    $('#myDatepickerto').data("DateTimePicker").clear();
                    document.getElementById("vin_no").value = "";
                    /*plant change*/
                    $('#plant').val('');
                    $('#plant').trigger('change');
                    $('#plant').prop("disabled", true);

                    $("#skucodes").select2({disabled:true});
                    $("#skucodes").empty(); 
                    $('#skucodes').trigger('change');

                    document.getElementById("vin_no").readOnly = false;
                }

            }
           
            function getVinDetails() {
                var e = document.getElementById('vin_no');
                var vin_no = e.value;

                $.ajax({
                    type: 'POST',
                    url: "<?php echo base_url() . 'Sa_vin_search/get_manufacturing_details'; ?>/",
                    data: 'vin_no=' + vin_no,
                    beforeSend: function () {
                        //start loader 
                        $('#loading').show();
                    },
                    success: function (data) {
                        var returnedData = JSON.parse(data);

                        if (returnedData.data.status == true) {
                            
                            $(".search_sku").hide();
                            $(".sho_sku").show();
                            
                            var start = moment(returnedData.data.vehicle_off_line_date).format("DD/MM/YYYY");
                            var end = moment(returnedData.data.vehicle_off_line_date).format("DD/MM/YYYY");
                            
                            $('#myDatepickerfrom').data("DateTimePicker").date(start);
                            $('#myDatepickerto').data("DateTimePicker").date(end);
                         

                            $("#skucodes").select2({data:returnedData.data.sku_code});
                            $('#skucodes').val(returnedData.data.sku_code[0].id);
                            $('#skucodes').trigger('change');  
                            $("#skucodes").select2({disabled:true})
                            
                            $('#plant').val(returnedData.data.plant);
                            $('#plant').trigger('change');   
                            $("#plant").select2({disabled:true})
                            $("#first_manufacturing_date").val(moment(returnedData.data.first_manufacturing_date).format('LL'));

                        } else {
                            alert("No VIN detail found.");
                        }
                        $('#loading').hide();
                        //stop loader 
                    }
                });
            }

            function searchFilter(page_num) {
                var  fdate = $('#myDatepickerfrom').data('date');
                var  tdate = $('#myDatepickerto').data('date');
                if(typeof fdate === "undefined" || typeof tdate === "undefined"){
                    $('#alrt').html('<div class="alert alert-danger alert-dismissible"> <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>  <strong>Error!</strong> Please fill dates.</div>');        
                    return  true;}
                var dateParts = (tdate).split("/");
                var end_dt = new Date(+dateParts[2], dateParts[1] - 1, +dateParts[0]); 
                var dateParts = (fdate).split("/");
                var start_dt = new Date(+dateParts[2], dateParts[1] - 1, +dateParts[0]);                    
                var date = moment(start_dt).format('ll')+" - "+moment(end_dt).format('ll');
                var serviceable = $("input[name='serviceable']:checked").val();
                var plant = $('#plant').select2().val();
                /*vin_no*/
                var e = document.getElementById('vin_no');
                var vin_no = e.value;
                /*sku_code*/
                
                var sku_code = $('#skucodes').select2().val();
                if(typeof sku_code === "undefined" || sku_code == ""){$('#alrt').html('<div class="alert alert-danger alert-dismissible"> <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>  <strong>Error!</strong> Please Select SKU code / Description.</div>');        
                    return  true}
                /*component*/
                var e = document.getElementById('component');
                var component = e.value;
                
                /*sku_code*/
                var e = document.getElementById('description');
                var description = e.value;
                /*date_filer*/
                $('.alert').hide();
                $('#example').DataTable( {
                "ajax":{
                "url" : "<?php echo base_url() . 'Sa_vin_search/Vindetails_ajax'; ?>/",
                "type" : "POST",
                data:function(d){ $('#loading').show();
                    d.applications = {"page":"0","sku_code" : sku_code,"vin_no":vin_no,"month_year":date,"plant":plant,"component":component,"description":description,"serviceable":serviceable};
                }},
                "columns": [
                    { "data": "part_number" },
                    { "data": "part_description" },
                    { "data": "quantity" },
                    { "data": "validity_from" },
                    { "data": "validity_to" },
                    { "data": "locater_code" },
                    { "data": "locater_description" },
                    { "data": "service_tag" },
                    { "data": "current_service_tag" },
                    { "data": "change_date" },
                    { "data": "status" },
                    { "data": "plate",
                        "render": function(data, type, row, meta){
			var data1='';
                            if(type === 'display'){
                                
                                data1 = '<a  target="_blank" href="' + data.pl_id + '">' + data.desc + '</a>';
				data1 += '<br><a  target="_blank" href="' + data.multiplate + '">More Plates</a>';
                                
                            }

                            return data1;
                         }
                    }
                ],
                    "bDestroy": true
    } );
     $('#loading').hide();
                
              
            }
           

            function e7858xport() {
                var  fdate = $('#myDatepickerfrom').data('date');
                var  tdate = $('#myDatepickerto').data('date');
                if(typeof fdate === "undefined" || typeof tdate === "undefined"){
                    $('#alrt').html('<div class="alert alert-danger alert-dismissible"> <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>  <strong>Error!</strong> Please fill dates.</div>');        
                    return  true;}
                var dateParts = (tdate).split("/");
                var end_dt = new Date(+dateParts[2], dateParts[1] - 1, +dateParts[0]); 
                var dateParts = (fdate).split("/");
                var start_dt = new Date(+dateParts[2], dateParts[1] - 1, +dateParts[0]);                    
                var date = moment(start_dt).format('ll')+" - "+moment(end_dt).format('ll');
                
                var serviceable = $("input[name='serviceable']:checked").val();
                var plant = $('#plant').select2().val();
                /*vin_no*/
                var e = document.getElementById('vin_no');
                var vin_no = e.value;
                /*sku_code*/
                
                
                var sku_code = $('#skucodes').select2().val();
                
                /*component*/
                var e = document.getElementById('component');
                var component = e.value;
                
                /*sku_code*/
                var e = document.getElementById('description');
                var description = e.value;
                $('.alert').hide();
                document.location.href = "<?php echo base_url() . 'Sa_vin_search/download_vindetails/'; ?>?sku_code=" + sku_code + '&vin_no=' + vin_no + '&month_year=' + date + '&plant=' + plant+ '&component=' + component + '&description=' + description+'&serviceable='+serviceable;
            }
        </script>
 

<script type="text/javascript">
			$(document).ready(function() {
                            <?php 
                             $dt[]= "{id:'all',text:'All'}";
                            foreach ($vin_codes as $key => $value) {
                            $dt[]= "{id:'".$value['plant_id']."',text:'".$value['description']." (".$value['plant_id'].")   '}";
                        } ?>


                        $('#plant').select2({data:[<?php echo implode(",",$dt ) ?>]}).on("change", function (e) { 
                             var filter_selection = $('#filter_selection :selected').val();
                             if(filter_selection == "with_vin"){ return true;}
//                        alert($('#country').select2().val());
                    var  plant= $('#plant').select2().val();
                   call_sku_codes(plant);
                    });
                    <?php if($select_type == "other"){ ?>
       
        call_sku_codes('all');
   <?php } ?>
function call_sku_codes(plant){
     $.ajax({
                    type: 'POST',
                    url: "<?php echo base_url() . 'Sa_vin_search/plates_sku'; ?>/?plant="+plant,
                    data: '',
                     contentType: "application/json",
                    dataType: "json",
                    beforeSend: function () {
                        //start loader 
                         $('#loading').show();
                    },
                    success: function (sku_codes) {
                        if(sku_codes.status == true){
                            $("#skucodes").select2({disabled:false});
                            $("#skucodes").select2({
				  data: sku_codes.sku
				});
                            } else{
                                
//                                $("#skucodes").select2({data:''});
                                $("#skucodes").empty();                                
                                $('#skucodes').val('').trigger('change')
                                $('#skucodes').trigger('change');   
                                
                                
//                                $("#skucodes").select2({disabled:true});
                            }
                                 $('#loading').hide();
                    }
                });
}
			});
                        
                        
                        
                        function isEmpty(val){
    return (val === undefined || val == null || val.length <= 0) ? true : false;
}
	
$('#skucodes').select2().on("change", function (e) { 
var filter_selection = $('#filter_selection :selected').val();
if(filter_selection == "with_vin"){ return true;}
var aa=$('#skucodes').select2().val();
var plant=$('#plant').select2().val();

         $.ajax({
                    type: 'POST',
                    url: "<?php echo base_url() . 'Sa_vin_search/sku_manufacturing_date'; ?>/?sku_code="+aa+"&plant="+plant,
                    data: '',
                     contentType: "application/json",
                    dataType: "json",
                    beforeSend: function () {
                        //start loader 
                         $('#loading').show();
                    },
                    success: function (sku_codes) {
                        if(sku_codes.status == true){
                             $("#first_manufacturing_date").val(moment(sku_codes.sku_manufacturing_date).format('LL'));
                        } else{
                             $("#first_manufacturing_date").val("");
                        }
                                $('#loading').hide();
                    }
                });
    });
    
    $('#myDatepickerfrom').datetimepicker({
        format: 'DD/MM/YYYY',
        ignoreReadonly: false
    });
    
    $('#myDatepickerto').datetimepicker({
        format: 'DD/MM/YYYY'
    });
    

    	</script>
        <!-- Datatables -->
    <script src="../assets/reports/vendors/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/reports/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="../assets/reports/vendors/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="../assets/reports/vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
    <script src="../assets/reports/vendors/datatables.net-buttons/js/buttons.flash.min.js"></script>
    <script src="../assets/reports/vendors/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="../assets/reports/vendors/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="../assets/reports/vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js"></script>
    <script src="../assets/reports/vendors/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
    <script src="../assets/reports/vendors/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="../assets/reports/vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script>
    <script src="../assets/reports/vendors/datatables.net-scroller/js/dataTables.scroller.min.js"></script>
                    <script src="../assets/reports/build/js/custom.js" type="text/javascript"></script>
        <div id="loading" ><div class="loader"></div></div>
    </body>
</html>