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


        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script src="https://code.highcharts.com/modules/exporting.js"></script>
        <script src="https://code.highcharts.com/modules/offline-exporting.js"></script>
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
                                <h3>VIN Count Summary Report</h3>
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


                                        </ul>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content">



                                        <table class="table table-striped table-bordered dt-responsive nowrap" id="example" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>VIN No</th>
                                                    <th>Manufacture date</th>
                                                    <th>SKU Description</th>
                                                    <th>sku_code</th>						 
                                                    <th>Production plant</th>						 
                                                    
                                                    <th>Import Date Details</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if ($data_set_dtl) {
                                                    foreach ($data_set_dtl as $key => $value) {
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $value['product_id'] ?></td>
                                                            <td><?php echo date("d-m-Y", strtotime($value['vehicle_off_line_date'])) ?></td>							

                                                            <td><?php echo $value['sku_description'] ?></td>
                                                            <td><?php echo $value['sku_code'] ?></td>							
                                                            <td><?php echo $value['plant'] ?></td>							
                                                             
                                                            <td><?php echo $value['data_import_month_year'] ?></td> 
                                                        </tr>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>

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
            var today = moment();
            $('#reportrange_right').datetimepicker({
                ignoreReadonly: true,
                format: 'MM-YYYY',
                defaultDate: today
            });</script>
        <script>
            searchFilter(0);
            function searchFilter(page_num) {
                var date = $("#reportrange_right > span").text();
                /*plant*/
                var e = document.getElementById('plant');
                var plant = e.value;
                /*sbomb_exists*/
                var e = document.getElementById('sbomb_exists');
                var sbomb_exists = e.options[e.selectedIndex].value;

                /*sku_code*/
                var e = document.getElementById('sku_code');
                var sku_code = e.value;
                var e = document.getElementById('sku_desc');
                var sku_desc = e.value;
                page_num = page_num ? page_num : 0;
                $.ajax({
                    type: 'POST',
                    url: "<?php echo base_url() . 'epc_reports_summary/Vindetails_ajax'; ?>/" + page_num,
                    data: 'page=' + page_num + '&sku_code=' + sku_code + '&month_year=' + date + '&plant=' + plant + '&sbomb_exists=' + sbomb_exists + '&sku_desc=' + sku_desc,
                    beforeSend: function () {
                        //start loader 
                    },
                    success: function (html) {
                        $('#t12get').html(html);
                        //stop loader 
                    }
                });
            }

            function e7858xport() {
                var date = $("#reportrange_right > span").text();
                /*sbomb_exists*/
                var e = document.getElementById('sbomb_exists');
                var sbomb_exists = e.options[e.selectedIndex].value;
                /*plant*/
                var e = document.getElementById('plant');
                var plant = e.value;

                /*sku_code*/
                var e = document.getElementById('sku_code');
                var sku_code = e.value;

                document.location.href = "<?php echo base_url() . 'epc_reports_summary/download_vindetails/'; ?>?sku_code=" + sku_code + '&month_year=' + date + '&plant=' + plant + '&sbomb_exists=' + sbomb_exists;
            }


        </script>
        <script src="https://code.jquery.com/jquery-3.3.1.js" type="text/javascript"></script>

        <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js" type="text/javascript"></script>
        <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap.min.js" type="text/javascript"></script>
        <script>
            $(document).ready(function () {
                $('#example').DataTable();
            });
        </script>
    </body>
</html>
