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


        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script src="https://code.highcharts.com/modules/exporting.js"></script>
        <script src="https://code.highcharts.com/modules/offline-exporting.js"></script>
        
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
		<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
        <style>
            .makereadonly {
    pointer-events: none;
  touch-action: none;
   background: #eee !important;
    box-shadow: none;}

  .select2-selection {
    background: #eee !important;
    box-shadow: none;
            }           
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
                    
            
            #loading {
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
                        <div class="page-title">
                            <div class="title_left">
                                <h3>VIN Search</h3>
                            </div>
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
<!--                                                    <label for="filter_selection" class="control-label">Serviceable tag</label>
                                                    <br/>
                                                    <input type="radio" name="serviceable" value="serviceable" checked> Serviceable<br>
                                                    <input type="radio" name="serviceable" value="all" > All<br>-->
                                                </div>

                                            </div>
                                            <div class="row">
                                                <div  class="col-md-5" aria-hidden="false">
                                                    <label for="select2-single-input-sm" class="control-label">Plant</label><br/>
<!--                                                    <input id="plant" class="form-control" type="text" name="plant" value=""/>-->
<!--                                                    <select  id="plant" name="plant"  placeholder="Plant Code" class="selectpicker form-control " >	
                                                        <option></option>

                                                        <?php foreach ($vin_codes as $key => $value) {
                                                        echo "<option value='".$value['plant_id']."' >".$value['description']." ( ".$value['plant_id']." )"."</option>";
                                                         } ?>
                                                </select>-->
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

                                            </div>
<div class="row">
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        
                                                    <label>Select <span id="lbl">Manufacture</span> Date From</label>
                                                    <input type="hidden" id="hidden_mfdt" />                                                        
<input type="text" name="value_from_start_date" class="form-control dtrng" data-datepicker="separateRange"/>

                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label><span id="lbl">Manufacture</span> Date To</label>
<input type="text" name="value_from_end_date" class="form-control dtrng" data-datepicker="separateRange"/>

                                                    </div>
                                                </div>
                                                <div  class="col-md-2" aria-hidden="false"><br/>
                                                    <input id="component" class="form-control" type="text" name="component" value="" placeholder="Component"/>
                                                </div>
                                                <div  class="col-md-3" aria-hidden="false"><br/>
                                                    <input id="description" class="form-control" type="text" name="description" value="" placeholder="Description"/>
                                                </div>
</div>
                                            <div class="row">
                                                <div  class="col-md-3" aria-hidden="false">
<label>First Vehicle <span id="lbl">Manufacture</span> Date On:</label>
<input type="text" id="first_manufacturing_date" class="form-control" placeholder="First Vehicle Manufacturing Date" name="first_manufacturing_date" value="" readonly="" /></div>
                                                <div  class="col-md-3" aria-hidden="false"><br/>
                                                    <button type="button"  onclick="searchFilter(0)" class="btn btn-primary btn-block" name="get_rpt" >Get BOM Details</button>
                                                    
                                                </div>
                                                <div  class="col-md-3" aria-hidden="false"><br/>
                                                    
                                                    <button type="button" onclick="e7858xport()" class="btn btn-dark btn-block" name="get_rpt" >Download BOM Details</button>
                                                </div>
                                                
                                                
                                            
                                            </div>


                                            <div class="row">
                                                <div  class="col-md-12">                                                    
                                                    <h4>Vin Data</h4>
                                                    <div id="t12get"></div>
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


        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.css">
        <!--        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.7.14/css/bootstrap-datetimepicker.min.css">-->
        <!-- Custom Theme Scripts -->
        <script>    var separator = ' - ', dateFormat = 'DD/MM/YYYY';
    var options = {
        showDropdowns: true,
        autoUpdateInput: false,
        autoApply: true,
        locale: {
            format: dateFormat,
            separator: separator,
            applyLabel: 'apply',
            cancelLabel: 'caancle'
        },
       // minDate: moment().add(10, 'days'),
        maxDate: moment().add(359, 'days'),
        opens: "right"
    };


        $('[data-datepicker=separateRange]')
            .daterangepicker(options)
            .on('apply.daterangepicker' ,function(ev, picker) {
                var boolStart = this.name.match(/value_from_start_/g) == null ? false : true;
                var boolEnd = this.name.match(/value_from_end_/g) == null ? false : true;

                var mainName = this.name.replace('value_from_start_', '');
                if(boolEnd) {
                    mainName = this.name.replace('value_from_end_', '');
                    $(this).closest('form').find('[name=value_from_end_'+ mainName +']').blur();
                }

                $(this).closest('form').find('[name=value_from_start_'+ mainName +']').val(picker.startDate.format(dateFormat));
                $(this).closest('form').find('[name=value_from_end_'+ mainName +']').val(picker.endDate.format(dateFormat));

                $(this).trigger('change').trigger('keyup');
                
            })
            .on('show.daterangepicker', function(ev, picker) {
                var boolStart = this.name.match(/value_from_start_/g) == null ? false : true;
                var boolEnd = this.name.match(/value_from_end_/g) == null ? false : true;
                var mainName = this.name.replace('value_from_start_', '');
                if(boolEnd) {
                    mainName = this.name.replace('value_from_end_', '');
                }

                var startDate = $(this).closest('form').find('[name=value_from_start_'+ mainName +']').val();
                var endDate = $(this).closest('form').find('[name=value_from_end_'+ mainName +']').val();

                $('[name=daterangepicker_start]').val(startDate).trigger('change').trigger('keyup');
                $('[name=daterangepicker_end]').val(endDate).trigger('change').trigger('keyup');
                
                if(boolEnd) {
                    $('[name=daterangepicker_end]').focus();
                }
            });</script>
        <script >
$(window).load(function () {
    <?php if($select_type == "other"){ ?>
        $('#rrpo77hx').removeClass('hide_me');
        document.getElementById("vin_no").readOnly = true;
        $('#filter_selection').val('other');
        
         $("#plant").select2({disabled:false});
                    
        
   <?php } else { ?>
       
       // $('#rrpo77hx').removeClass('hide_me');
        document.getElementById("vin_no").readOnly = false;
        $('#filter_selection').val('with_vin');
        
       

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
                    $('.dtrng').removeClass('makereadonly');
                     //document.getElementById("vin_no").value = "";

                    $('#rrpo77hx').removeClass('hide_me');
      
                    
                    document.getElementById("vin_no").readOnly = true;

                    $('#plant').prop("disabled", false);
//                    $('#plant').val('');
                    $('#plant').trigger('change');
                    $("#skucodes").select2({disabled:false});
                     $("#skucodes").empty(); 
//                    $("#skucodes").select2({data:''});
//                    $('#skucodes').val('');
                    $('#skucodes').trigger('change');
//                    $('#plant').val('');
                    $('#skucodes').trigger('change');
                    
                $('#reportrange_right').show();
                    
                } else { 
                    $('.dtrng').addClass('makereadonly');
                    document.getElementById("vin_no").value = "";


 $("#skucodes").select2({disabled:true});
//                    $("#skucodes").select2({data:''});
                     $("#skucodes").empty(); 
                     
                     $('#plant').prop("disabled", true);                    
                    document.getElementById("vin_no").readOnly = false;
                    document.getElementById("plant").readOnly = true;                    
                    $('#reportrange_right').hide();
                    $(".date_hider").show();

                }

            }
           
            function getVinDetails() {
                var e = document.getElementById('vin_no');
                var vin_no = e.value;

                $.ajax({
                    type: 'POST',
                    url: "<?php echo base_url() . 'Sa_vin_search_dealers/get_manufacturing_details'; ?>/",
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
                            
                            var start = moment(returnedData.data.vehicle_off_line_date).format('ll');
                            var end = moment(returnedData.data.vehicle_off_line_date).format('ll');
                            
                            $('#hidden_mfdt').val(start+' - '+end);
//                            cb(start, end);
                            //alert(returnedData.data.sku_code.id);
                            $("#skucodes").select2({data:returnedData.data.sku_code});
                            $('#skucodes').val(returnedData.data.sku_code[0].id);
                            $('#skucodes').trigger('change');  
                            $("#skucodes").select2({disabled:true})
                            
                            $('#plant').val(returnedData.data.plant);
                            $('#plant').trigger('change');   
                            $("#plant").select2({disabled:true})
                            $("#first_manufacturing_date").val(moment(returnedData.data.first_manufacturing_date).format('MMMM YYYY'));

                        } else {
                            alert("No VIN detail found.");
                        }
                        $('#loading').hide();
                        //stop loader 
                    }
                });
            }

            function searchFilter(page_num) {
            var date = '';
            
                var filter =$('#filter_selection').find(":selected").val();
                if(filter == "with_vin"){
                    date =  $('#hidden_mfdt').val();
                } else {                    
                    var dateParts = ($('[name=value_from_end_date]').val()).split("/");
                    var end_dt = new Date(+dateParts[2], dateParts[1] - 1, +dateParts[0]); 
                    var dateParts = ($('[name=value_from_start_date]').val()).split("/");
                    var start_dt = new Date(+dateParts[2], dateParts[1] - 1, +dateParts[0]);                    
                    date = moment(start_dt).format('ll')+" - "+moment(end_dt).format('ll');
                }
            var serviceable = "all";
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
                /*date_filer*/

                page_num = page_num ? page_num : 0;
                $.ajax({
                    type: 'POST',
                    url: "<?php echo base_url() . 'Sa_vin_search_dealers/Vindetails_ajax'; ?>/" + page_num,
                    data: 'page=' + page_num + '&sku_code=' + sku_code + '&vin_no=' + vin_no + '&month_year=' + date + '&plant=' + plant + '&component=' + component + '&description=' + description+'&serviceable='+serviceable,
                    beforeSend: function () {
                        //start loader 
                        $('#loading').show();
                    },
                    success: function (html) {
                        $('#t12get').html(html);
                        //stop loader 
                        $('#loading').hide();
                    }
                });
            }

            function e7858xport() {


            var date = $("#rrpo77hx > span").text();
//            var serviceable = $("input[name='serviceable']:checked").val();
            var serviceable = "all";
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
                
                document.location.href = "<?php echo base_url() . 'Sa_vin_search_dealers/download_vindetails/'; ?>?sku_code=" + sku_code + '&vin_no=' + vin_no + '&month_year=' + date + '&plant=' + plant;
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
                    url: "<?php echo base_url() . 'Sa_vin_search_dealers/plates_sku'; ?>/?plant="+plant,
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
                    url: "<?php echo base_url() . 'Sa_vin_search_dealers/sku_manufacturing_date'; ?>/?sku_code="+aa+"&plant="+plant,
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
    
    	</script>
        
                    <script src="../assets/reports/build/js/custom.js" type="text/javascript"></script>
        <div id="loading" ><div class="loader"></div></div>
    </body>
</html>
