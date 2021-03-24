                    
					
                    <table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <thead>
                        <tr>                          						 
                          <th>SKU Code</th>						 
                          <th>Plant Code</th>						 
                          <th>Material Code</th>						 
                          <th>Material Description</th>						 
                          <th>SBOM creation dt</th>						 
                          <th>Valid from date</th>						 
                          <th>Valid to date</th>						 
                          <th>Currently valid</th>						 
                          <th>Plate</th>						 
                          						 
                        </tr>
                      </thead>
                      <tbody>
                          <?php if($data_set_dtl){ foreach ($data_set_dtl as $key => $value) {  ?>
                                                   
                        <tr>
                            <td><?php echo $value['sku_code']; ?></td>						
                            <td><?php echo $value['plant']; ?></td>	
                            <td><?php echo $value['material_code']; ?></td>	
                            <td><?php echo $value['material_description']; ?></td>	
                            <td><?php echo $value['sku_created_on']; ?></td>	
                            <td><?php echo $value['valid_from']; ?></td>						
                            <td><?php echo $value['valid_to']; ?></td>	
                            <?php $ans = ($value['valid_to'] != "31-12-9999") ? "NO" : "YES";
                            $color = ($ans== "YES") ? "green": "red";
                            ?>
                            <td style="color: white;" bgcolor="<?php echo $color ?>"><?php echo $ans ?></td>
                            <td><?php echo "<a target='_blank' href='http://qaepc.gladminds.co/plates/".$value['plate_approve_id']."/sbom'>". $value['plate_code']."-go" ?></td>
                            
                            
                        </tr>
                            <?php }} ?>                  
                      </tbody>
                    </table>
					
				<?php echo $this->ajax_pagination->create_links(); ?>	
                  