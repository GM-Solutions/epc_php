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
        <style>
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
        <link href="../assets/search_select/selectstyle.css" rel="stylesheet" type="text/css">
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

                                        <form >
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
                                                    <label for="filter_selection" class="control-label">Serviceable tag</label>
                                                    <br/>
                                                    <input type="radio" name="serviceable" value="serviceable" checked> Serviceable<br>
                                                    <input type="radio" name="serviceable" value="all" > All<br>
                                                </div>

                                            </div>
                                            <div class="row">
                                                <div  class="col-md-5" aria-hidden="false">
                                                    <label for="select2-single-input-sm" class="control-label">Plant</label><br/>
<!--                                                    <input id="plant" class="form-control" type="text" name="plant" value=""/>-->
                                                    <select  id="plant" name="plant"  placeholder="Plant Code" class="selectpicker form-control " >	
                                                        <option></option>
                                                        <?php foreach ($vin_codes as $key => $value) {
                                                        echo "<option value='".$value['plant_id']."' >".$value['description']." ( ".$value['plant_id']." )"."</option>";
                                                         } ?>
                                                </select>
                                                    
                                                </div>
    <div  class="col-md-6" aria-hidden="false">
    <label for="select2-single-input-sm" class="control-label">SKU code / Description</label><br/>
    <div class="search_sku"><select  id="s74ku_desc"  theme="google" width="400"  placeholder="SKU Code" data-search="true" readonly>	
	<?php foreach ($sku_codes as $key => $value) {
        echo "<option value='".$value['sku_code']."' selected>".$value['sku_description']." ( ".$value['sku_code']." )"."</option>";
         } ?>
</select>
    </div>
    <input id="sku_code" class="form-control sho_sku" type="text" name="sku_code" value=""/>
    <input id="sku777_code" class="form-control" type="hidden" name="sku777_code" value="" />
    </div>
<!--                                                <div  class="col-md-3" aria-hidden="false">
                                                    <label for="select2-single-input-sm" class="control-label">SKU code</label>
                                                    <input id="sku_code" class="form-control" type="text" name="sku_code" value=""/>
                                                </div>-->
                                            </div>
<div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        
<!--                                                        <div class="form-group">                                                            
                                                            <div class='input-group date' id='datetimepicker1'>
                                                                <input type='text' class="form-control" />
                                                                <span class="input-group-addon">
                                                                    <span class="glyphicon glyphicon-calendar"></span>
                                                                </span>
                                                            </div></div>-->
                                                    <label>Select <span id="lbl">Manufacture</span> Date</label>
<!--                                                            <div class="form-group">
                                                            <div id="reportrange_right" class="pull-left" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                                                            <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                                            <span></span> <b class="caret"></b>
                                                          </div>
                                                                <input type="text" class="date_hider form-control" readonly="">
                                                        </div>-->
                                                        
                                                        <div id="rrpo77hx" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
    <i class="fa fa-calendar"></i>&nbsp;
    <span></span> <i class="fa fa-caret-down"></i>
</div>
                                                    </div>
                                                </div>
                                                <!--                                                <div class="col-md-3"><label for="select2-single-input-sm" class="control-label">SKU Description</label></div>-->

<!--                                                <div  class="col-md-3" aria-hidden="false">
                                                   <label for="filter_selection" class="control-label">SKU Description</label> 
                                                   <input id="s74ku_desc" class="form-control" type="text" name="s74ku_desc" value="" readonly=""/>
                                                   
                                                </div>-->
                                                <div  class="col-md-2" aria-hidden="false"><br/>
                                                    <input id="component" class="form-control" type="text" name="component" value="" placeholder="Component"/>
                                                </div>
                                                <div  class="col-md-3" aria-hidden="false"><br/>
                                                    <input id="description" class="form-control" type="text" name="description" value="" placeholder="Description"/>
                                                </div>
                                                <div  class="col-md-3" aria-hidden="false"><br/>
                                                    <button type="button"  onclick="searchFilter(0)" class="btn btn-primary btn-block" name="get_rpt" >Get BOM Details</button>
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
        
        <script >
$(window).load(function () {
    $('#loading').hide();

    $('.sho_sku').show();
    $(".search_sku").hide();
    $('#reportrange_right').hide();
    $(".date_hider").show();
});
                                                   
  $('#plant').prop("disabled", true);
    document.getElementById("sku_code").readOnly = true;

        </script>
        <script>
 function cb(start, end) {
        $('#rrpo77hx span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }
            function  change_filter(e) {
                if (e.value == "other") {
                    document.getElementById("vin_no").readOnly = true;

                    $('#plant').prop("disabled", false);
                    document.getElementById("sku_code").readOnly = false;
                    $('#reportrange_right').show();
                    $(".date_hider").hide();
                    $(".sho_sku").hide();
                    $(".search_sku").show();
                    document.getElementById("vin_no").value = "";
                    document.getElementById("plant").value = "";
                    document.getElementById("sku_code").value = "";
                    
                    

                var start = moment().subtract(29, 'days');
                var end = moment();


                $('#rrpo77hx').daterangepicker({
                    showDropdowns: true,
                    minYear: 2004,
                    maxYear: 2022,
                    autoApply: true,
                    startDate: start,
                    endDate: end,
                    ranges: {
                       'Today': [moment(), moment()],
                       'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                       'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                       'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                       'This Month': [moment().startOf('month'), moment().endOf('month')],
                       'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    }
                }, cb);
                cb(start, end);

                } else {
                     $('#plant').prop("disabled", true);
                    $(".sho_sku").show();
                    $(".search_sku").hide();
                    document.getElementById("vin_no").readOnly = false;

                    document.getElementById("plant").readOnly = true;
                    document.getElementById("sku_code").readOnly = true;
                    $('#reportrange_right').hide();
                    $(".date_hider").show();

                    document.getElementById("vin_no").value = "";
                    document.getElementById("plant").value = "";
                    document.getElementById("sku_code").value = "";
                    
                    

    var start = moment().subtract(29, 'days');
    var end = moment();

    
    $('#rrpo77hx').daterangepicker({
        
        startDate: start,
        endDate: end,
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);
    cb(start, end);


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
                            
                            var start = moment(returnedData.data.vehicle_off_line_date);
                            var end = moment(returnedData.data.vehicle_off_line_date);
                            $('#rrpo77hx').daterangepicker({
                                startDate: start,
                                endDate: end,
                                ranges: {
                                   'Today': [moment(), moment()],
                                   'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                                   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                                   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                                   'This Month': [moment().startOf('month'), moment().endOf('month')],
                                   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                                }
                            }, cb);
                            cb(start, end);

                            document.getElementById("sku777_code").value = returnedData.data.sku_code;
                            document.getElementById("sku_code").value = returnedData.data.sku_code_details+"("+returnedData.data.sku_code+")";
                            //document.getElementById("plant").value = returnedData.data.plant;

                          $('#plant option[value='+returnedData.data.plant+']').attr('selected','selected');
                        } else {
                            alert("No VIN detail found.");
                        }
                        $('#loading').hide();
                        //stop loader 
                    }
                });
            }

            function searchFilter(page_num) {
                var date = $("#rrpo77hx > span").text();
            var serviceable = $("input[name='serviceable']:checked").val();
           var plant = $('#plant :selected').val();
                /*vin_no*/
                var e = document.getElementById('vin_no');
                var vin_no = e.value;
                /*sku_code*/
                var e = document.getElementById('sku777_code');
                var sku_code = e.value;
                
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
                    url: "<?php echo base_url() . 'Sa_vin_search/Vindetails_ajax'; ?>/" + page_num,
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
                /*plant*/
               var plant = $('#plant :selected').val();
                /*vin_no*/
                var e = document.getElementById('vin_no');
                var vin_no = e.value;
                /*sku_code*/
                var e = document.getElementById('sku777_code');
                var sku_code = e.value;
                /*date_filer*/

                document.location.href = "<?php echo base_url() . 'Sa_vin_search/download_vindetails/'; ?>?sku_code=" + sku_code + '&vin_no=' + vin_no + '&month_year=' + date + '&plant=' + plant;
            }
        </script>
        <script src="../assets/search_select/selectstyle.js"></script>
       
<script>
    
jQuery(document).ready(function($) {
	$('select').selectstyle({
		width  : 400,
		height : 300,
		theme  : 'light',
		onchange : function(val){
                  
                }
	});
});
</script>


 
<script type="text/javascript">
                   
$(function() {

    var start = moment().subtract(29, 'days');
    var end = moment();

    
    $('#rrpo77hx').daterangepicker({
        showDropdowns: true,
        minYear: 2004,
        maxYear: 2022,
        autoApply: true,
        startDate: start,
        endDate: end,
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);
    cb(start, end);
});


</script>
                    <script src="../assets/reports/build/js/custom.js" type="text/javascript"></script>
        <div id="loading" ><div class="loader"></div></div>
    </body>
</html>
