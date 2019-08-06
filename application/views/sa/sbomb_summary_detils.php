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
        <!-- bootstrap-daterangepicker -->
        <script src="../assets/reports/vendors/moment/min/moment.min.js"></script>
        <script src="../assets/reports/vendors/bootstrap-daterangepicker/daterangepicker.js"></script>
        <!-- bootstrap-datetimepicker -->    
        <script src="../assets/reports/vendors/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
        <link href="../assets/reports/vendors/pnotify/dist/pnotify.css" rel="stylesheet">
        <script type="text/javascript" src="http://www.google.com/jsapi"></script>
	<link href="../assets/search_select/selectstyle.css" rel="stylesheet" type="text/css">
	
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
    </head>

    <body class="nav-md">
        <div class="container body">
            <div class="main_container">
                

                <!-- top navigation -->
                <?php  $this->load->view('top_nav'); ?>
                <!-- /top navigation -->

                <!-- page content -->
                <div class="right_col" role="main">
                    <div class="">
                        <div class="page-title">
                            <div class="title_left">
                                <h3>SBOM Details</h3>
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
                                                
                                                <div  class="col-md-6" aria-hidden="false">
                                                    <label for="select2-single-input-sm" class="control-label">SKU code/ Description</label>
                                                    <div class="search_sku"><select  id="s74ku_desc"  theme="google" width="400"  placeholder="SKU Code" data-search="true" readonly>	
	<?php foreach ($sku_codes as $key => $value) {
        echo "<option value='".$value['sku_code']."' selected>".$value['sku_description']." ( ".$value['sku_code']." )"."</option>";
         } ?>
</select>
    </div>
    
    <input id="sku777_code" class="form-control" type="hidden" name="sku777_code" value="" />
                                                </div>
                                                                                              
                                                <div class="col-sm-2">
                                                    <br/>
                                                    <button type="button" onclick="searchFilter()" class="btn btn-primary btn-block" name="get_report" >Get Report</button>
                                                    
                                                </div>
                                                <div class="col-sm-2">
                                                    <br/>
                                                    
                                                    <button type="button" onclick="e7858xport()" class="btn btn-success btn-block" name="get_report" >Export Report</button>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div  class="col-md-12">                                                    
                                                    <h4>SBOM Data</h4>
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
        <!-- Custom Theme Scripts -->
        <script src="../assets/reports/build/js/custom.js" type="text/javascript"></script>
        <script >
            $(window).load(function() {
     $('#loading').hide();
  });
        var today = moment();
                $('#reportrange_right').datetimepicker({
                        ignoreReadonly: true,
                        format: 'MM-YYYY',
                        defaultDate: today
                });</script>
        <script>            
    
    function searchFilter(page_num) {
    /*sku_code*/
    var e = document.getElementById('sku777_code');
    var sku_code = e.value;
    page_num = page_num?page_num:0;
        $.ajax({
        type: 'POST',
        url: "<?php echo base_url().'/sa_epc_sbomb_summary/sbomb_ajax'; ?>/"+page_num,
        data:'page='+page_num+'&sku_code='+sku_code,
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

function e7858xport(){
    
    /*sku_code*/
    var e = document.getElementById('sku_code');
    var sku_code = e.value;

    document.location.href = "<?php echo base_url().'sa_epc_sbomb_summary/download_sbometails/'; ?>?sku_code="+sku_code;
                  }
        </script>
        <div id="loading" ><div class="loader"></div></div>
    </body>
</html>
