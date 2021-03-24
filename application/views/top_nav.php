<div class="col-md-3 left_col">
                    <div class="left_col scroll-view">
                        <div class="navbar nav_title" style="border: 0;">
                            <a href="#" class="site_title"><img height="51" src="<?php echo base_url()."assets/reports/images/bajaj-logo.svg"; ?>"/>
                                <span>EPC</span></a>
                        </div>

                        <div class="clearfix"></div>

                        <!-- menu profile quick info -->
                        <div class="profile clearfix">
                            <div class="profile_pic">
              <!--                <img src="images/img.jpg" alt="..." class="img-circle profile_img">-->
                            </div>
                            <div class="profile_info"></div>
                        </div>
                        <!-- /menu profile quick info -->

                        <br />

                        <!-- sidebar menu -->
                        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                            <div class="menu_section">
                                <h3>Reports</h3>
                                <ul class="nav side-menu">
                    <?php 
                    $rol = $this->session->userdata('role');
                    if($rol[0]['role_name'] == "Distributor" || $rol[0]['role_name'] == "Dealer"){ ?>
                       <li>
                            <a href="<?php echo base_url() . "Sa_vin_search_dealers/Vindetails"; ?>"><i class="fa fa-file"></i>Vin Search Details</a>
                                </li>
              <?php  } else { ?>
                       <li>
                        <a href="<?php echo base_url() . "epc_reports/Vindetails"; ?>"><i class="fa fa-file"></i>VIN Details</a>
                        </li>
                        <li>
                                        <a href="<?php echo base_url() . "index.php/sa_epc_sbomb"; ?>"><i class="fa fa-file"></i>SBOM REPORT (Stand Alone)</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo base_url() . "sa_epc_reports_summary/Vindetails"; ?>"><i class="fa fa-file"></i> VIN SUMMARY REPORT (Stand Alone Report)</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo base_url() . "index.php/sa_epc_sbomb_summary"; ?>"><i class="fa fa-file"></i>SBOM SUMMARY (Stand Alone)</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo base_url() . "index.php/Sa_material_servisibility_history"; ?>"><i class="fa fa-file"></i>Material Serviceability Report</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo base_url() . "Sa_vin_search/Vindetails"; ?>"><i class="fa fa-file"></i>Vin Search Details</a>
                                    </li>
                 <?php   } ?>
                                    
<!--                                    <li>
                                        <a href="<?php echo base_url() . "epc_reports_summary/Vindetails"; ?>"><i class="fa fa-file"></i>VIN Report Summary</a>
                                    </li>
                                    
                                    <li>
                                        <a href="<?php echo base_url() . "index.php/epc_sbomb"; ?>"><i class="fa fa-file"></i>SBOM Report</a>
                                    </li>
                                    
                                    <li>
                                        <a href="<?php echo base_url() . "index.php/epc_sbomb_summary"; ?>"><i class="fa fa-file"></i>SBOM Summary Report</a>
                                    </li>
                                    <li>-----------</li>-->
                                    
                                </ul>
                            </div>


                        </div>
                        <!-- /sidebar menu -->

                        <!-- /menu footer buttons -->
                        <div class="sidebar-footer hidden-small">
                            <a data-toggle="tooltip" data-placement="top" title="Settings">
                                <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
                            </a>
                            <a data-toggle="tooltip" data-placement="top" title="FullScreen">
                                <span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
                            </a>
                            <a data-toggle="tooltip" data-placement="top" title="Lock">
                                <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
                            </a>
                            <a data-toggle="tooltip" data-placement="top" title="Logout" href="<?php echo base_url()."user/logout"; ?>">
                                <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
                            </a>
                        </div>
                        <!-- /menu footer buttons -->
                    </div>
                </div>
<div class="top_nav">
                    <div class="nav_menu">
                        <nav>
                            <div class="nav toggle">
                                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                            </div>
<?php 
                                $url="first_name=".$this->session->userdata('first_name');
                                $url .="&last_name=".$this->session->userdata('last_name');
                                $url .="&product_name=EPC";
                                $url .="&model_name=BajajEpc";
                                $url .="&model_number=epc".date('Y');
                                $url .="&serial_number=epc".$this->session->userdata('phone_number');
                                $url .="&email=".$this->session->userdata('email');
                                $url .="&mobile=".$this->session->userdata('phone_number');
                                ?>
                            <ul class="nav navbar-nav navbar-right">
 | <li><a href="<?php echo "//afterbuy.care/Thirdparty/support?".$url; ?>"> Support </a></li>
                                <li class="">
                                    <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                        <img src="images/img.jpg" alt="">
                                        <span class=" fa fa-angle-down"></span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-usermenu pull-right">
<!--                                        <li><a href="javascript:;"> Profile</a></li>
                                        <li>
                                            <a href="javascript:;">
                                                <span class="badge bg-red pull-right">50%</span>
                                                <span>Settings</span>
                                            </a>
                                        </li>
                                        <li><a href="javascript:;">Help</a></li>-->
                                        <li><a href="<?php echo base_url()."user/logout"; ?>"><i class="fa fa-sign-out pull-right"></i> Log Out</a></li>
                                    </ul>
                                </li>

                                
                            </ul>
                        </nav>
                    </div>
                </div>
