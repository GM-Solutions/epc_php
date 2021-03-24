                    
					
                    <table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <thead>
                        <tr>                          						 
                          <th>SKU Code</th>						 
                          <th>SKU Description</th>						 
                          <th>Plant</th>						 
                          <th>Count of Parts</th>						 
                          					 
                          						 
                          						 
                        </tr>
                      </thead>
                      <tbody>
                          <?php if($data_set_dtl){ foreach ($data_set_dtl as $key => $value) {  ?>
                                                   
                        <tr>
                            <td><?php echo $value['sku_code']; ?></td>						
                            <td><?php echo $value['sku_description']; ?></td>						
                            <td><?php echo $value['plant']; ?></td>	
                            <td><?php echo $value['part_count']; ?></td>	
                            
                        </tr>
                            <?php }} ?>                  
                      </tbody>
                    </table>
					
				<?php echo $this->ajax_pagination->create_links(); ?>	
                  