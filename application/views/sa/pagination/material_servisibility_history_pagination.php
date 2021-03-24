                    
					
                    <table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <thead>
                        <tr>                          						 
                            <th rowspan="2">Material Number</th>						 
                          <th rowspan="2">Material Description</th>						 
                          <th colspan="3">Tag History</th>						 						 
                            </tr>
                            <tr> 
                            
                          <th>Old Tag</th>						 						 
                          <th>New Tag</th>						 						 
                          <th>Update On</th>						 						 
                        </tr>
                      </thead>
                      <tbody>
                          <?php if($data_set_dtl){ foreach ($data_set_dtl as $key => $value) { 
                              ?>
                                                   
                        <tr>
                            <td ><?php echo $value['material_number']; ?></td>						
                            <td><?php echo $value['material_description']; ?></td>						
                            <td><?php echo $value['old_tag'] ?></td>	
                            <td><?php echo $value['new_tag'] ?></td>	
                            <td><?php echo $value['change_date'] ?></td>	
                                                       
                        </tr>
                            <?php }} ?>                  
                      </tbody>
                    </table>
					
				<?php echo $this->ajax_pagination->create_links(); ?>	
                  