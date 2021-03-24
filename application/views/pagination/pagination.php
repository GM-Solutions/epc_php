                    
					
                    <table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <thead>
                        <tr>
                          <th>VIN No</th>
                          <th>Manufacture date</th>						 
                          <th>Production plant</th>						 
                          <th>SKU Code</th>						 
                          <th>SKU Code Description</th>						 
                          <th>SBOM Exist</th>						 
                          <th>Data Import Date</th>						 
                        </tr>
                      </thead>
                      <tbody>
                          <?php if($data_set_dtl){ foreach ($data_set_dtl as $key => $value) { ?>
                                                   
                        <tr>
                          <td><?php echo $value['product_id'] ?></td>
                          <td><?php echo date("d-m-Y", strtotime($value['vehicle_off_line_date'])) ?></td>							
                          <td><?php echo $value['plant'] ?></td>							
                          <td><?php echo $value['sku_code'] ?></td>							
                          <td><?php echo $value['sku_description'] ?></td>                          
                          <?php 
                            $color = ($value['sbomb_exists']== "YES") ? "green": "red";
                            ?>
                            <td style="color: white;" bgcolor="<?php echo $color ?>"><?php echo $value['sbomb_exists'] ?></td>
                            <td><?php echo ($value['data_import_date'] != NULL) ? date("d-m-Y", strtotime($value['data_import_date'])) : "" ?></td>                          				
                        </tr>
                            <?php }} ?>                  
                      </tbody>
                    </table>					
<?php echo $this->ajax_pagination->create_links(); ?>	
                  