                    
					
                    <table class="table table-striped table-bordered dt-responsive" cellspacing="0" width="100%" style="display: block;overflow: scroll;">
                      <thead>
                        <tr>
<!--                          <th>Manufacture date</th>
                          <th>SKU Code</th>
                          <th>SKU Code Description</th>	-->
                          					 
                          <th>Node ID</th>
                          <th>Component</th><!-- part number-->
                          <th>Component Description</th><!-- part number-->
<!--                          <th>plant</th> part number-->
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
                      <tbody>
                          <?php if($data_set_dtl){ foreach ($data_set_dtl as $key => $value) { ?>
                                                   
                        <tr>
<!--                          <td><?php echo date("d-m-Y", strtotime($value['manufacturing_date'])); ?></td>-->
                          						
<!--                          <td><?php echo $value['sku_code'] ?></td>							-->
<!--                          <td><?php echo $value['sku_description'] ?></td>							-->
                          <td><?php echo $value['node_id'] ?></td>                          
                          <td><?php echo $value['part_number'] ?></td>                          
                          <td><?php echo $value['material_description'] ?></td>                          
<!--                          <td><?php echo $value['plant'] ?></td>                          -->
                          <td><?php echo $value['valid_from'] ?></td>                          
                          <td><?php echo $value['valid_to'] ?></td>                          
                          <td><?php echo $value['serial_number'] ?></td>                          
                          <td><?php echo !empty($value['locators_description'])? $value['locators_description'] : $value['serial_number'] ?></td>                          
                          <td><?php echo $value['old_tag'] ?></td>                          
                          <td><?php echo $value['new_tag'] ?></td>                          
                          <td><?php echo $value['change_date'] ?></td>                          
                          <td><?php echo $value['status'] ?></td>                          
                                                   
                          <td><?php echo !empty($value['plate_approve_id']) ? "<a target='_blank' href='http://qaepc.gladminds.co/plates/".$value['plate_approve_id']."/sbom'>". $value['plate_txt'].""  : "---" ?></td>                     
                          
                        </tr>
                            <?php }} ?>                  
                      </tbody>
                    </table>					
<?php echo $this->ajax_pagination->create_links(); ?>	
                  