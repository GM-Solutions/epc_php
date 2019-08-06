                    

<table class="table table-striped table-bordered dt-responsive" cellspacing="0" width="100%" style="display: block;overflow: scroll;">
    <thead>
        <tr>

            <th>Part Number</th><!-- part number-->
            <th>Part Description</th><!-- part number-->
            <th>Quantity</th> 
            <th>Valid From</th><!-- part number-->
            <th>Valid To</th><!-- part number-->
            <th>Current Service Tag</th>			 
            <th>Status</th>				 
            <th>Plate</th>				 
        </tr>
    </thead>
    <tbody>
        <?php if ($data_set_dtl) { $catlog_url = $this->config->item('catlog');
            foreach ($data_set_dtl as $key => $value) { ?>

                <tr>
        <!--                          <td><?php //echo date("d-m-Y", strtotime($value['manufacturing_date'])); ?></td>-->

        <!--                          <td><?php // echo $value['sku_code'] ?></td>							-->
        <!--                          <td><?php //echo $value['sku_description'] ?></td>							-->
        <!--                          <td><?php //echo $value['node_id'] ?></td>                          -->
                    <td><?php echo $value['part_number'] ?></td>                          
                    <td><?php echo $value['material_description'] ?></td>                          
                                  <td><?php echo $value['quantity'] ?></td>                          
                    <td><?php echo $value['valid_from'] ?></td>                          
                    <td><?php echo $value['valid_to'] ?></td>                          
<!--                    <td><?php // echo $value['serial_number'] ?></td>                          
                    <td><?php // echo!empty($value['locators_description']) ? $value['locators_description'] : $value['serial_number'] ?></td>                          
                    <td><?php // echo $value['old_tag'] ?></td>                          -->
                    <td><?php echo $value['new_tag'] ?></td>                          
<!--                    <td><?php // echo $value['change_date'] ?></td>                          -->


                    <td><?php if ($value['status'] == 'DELETED') { ?> NON CURRENT <?php } else {
                    echo $value['status'];
                } ?></td>                                                  

                    <td><?php echo!empty($value['plate_approve_id']) ? "<a target='_blank' href='".$catlog_url['url']."/plates/" . $value['plate_approve_id'] . "/sbom'>" . $value['plate_txt'] . "</a>" : "---" ?></td>                     

                </tr>
    <?php }
} ?>                  
    </tbody>
</table>					
<?php echo $this->ajax_pagination->create_links(); ?>	
