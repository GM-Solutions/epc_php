<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!-- Meta, title, CSS, favicons, etc. -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" sizes="16x16" href="//epc.gladminds.co/static/epc/img/favicon/favicon-16x16.png">
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
        <!--<link href="../assets/reports/vendors/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css" rel="stylesheet">-->

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
        <!--<script src="../assets/reports/vendors/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>-->
        <link href="../assets/reports/vendors/pnotify/dist/pnotify.css" rel="stylesheet">
        <script type="text/javascript" src="http://www.google.com/jsapi"></script>
	
	
        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script src="https://code.highcharts.com/modules/exporting.js"></script>
        <script src="https://code.highcharts.com/modules/offline-exporting.js"></script>
        <link href="../assets/css/epccss.css" rel="stylesheet" type="text/css"/>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
		<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
   
               
<!--        <link href="../assets/search_select/selectstyle.css" rel="stylesheet" type="text/css">-->
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/offline-exporting.js"></script>
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
                                                
                                                <div  class="col-md-3" aria-hidden="false">
                                                    <label for="select2-single-input-sm" class="control-label">Material code</label>
                                                    <input id="material_code" class="form-control" type="text" name="material_code" value=""/>
                                                </div>
                                                     <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Select <span id="lbl">Update On</span> Date</label>
                                                        <div class="form-group">
                                                            <div id="reportrange_right" class="pull-left" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                                                            <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                                            <span>December 30, 2014 - January 28, 2015</span> <b class="caret"></b>
                                                          </div>
                                                        </div>
                                                    </div>
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
                                                    <h4>Serviceability History</h4>
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
        
        
        
        <!-- Custom Theme Scripts -->
        <script src="../assets/reports/build/js/custom.js" type="text/javascript"></script>
        <script >
                $(window).load(function() {
     $('#loading').hide();
  });
//        var today = moment();
//                $('#reportrange_right').datetimepicker({
//                        ignoreReadonly: true,
//                        format: 'MM-YYYY',
//                        defaultDate: today
//                });
                    </script>
        <script>            
    
    function searchFilter(page_num) {
         var date = $("#reportrange_right > span").text();
    /*material_code*/
    var e = document.getElementById('material_code');
    var material_code = e.value;
    page_num = page_num?page_num:0;
        $.ajax({
        type: 'POST',
        url: "<?php echo base_url().'Sa_material_servisibility_history/servisibility_ajax'; ?>/"+page_num,
        data:'page='+page_num+'&material_code='+material_code+'&month_year='+date,
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
    var date = $("#reportrange_right > span").text();
    /*material_code*/
    var e = document.getElementById('material_code');
    var material_code = e.value;

    document.location.href = "<?php echo base_url().'Sa_material_servisibility_history/download_sbometails/'; ?>?material_code="+material_code+'&month_year='+date;
                  }
        </script>
        <div id="loading" ><div class="loader"></div></div>
    </body>
</html>
