                    

<table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
    <thead>
        <tr>
            
            <th>SKU Description</th>
            <th>sku_code</th>						 
            <th>Production plant</th>						 
            <th>Vin Count</th>
            <th>SBOM Exist</th>						 
            <th>Manufacturing Date</th>						 
<!--            <th>Action</th>						 -->
        </tr>
    </thead>
    <tbody>
        <?php if ($data_set_dtl) {
            foreach ($data_set_dtl as $key => $value) { ?>

                <tr>
                    <td><?php echo $value['sku_description'] ?></td>
                    <td><?php echo $value['sku_code'] ?></td>							
                    <td><?php echo $value['plant'] ?></td>							
                    <td><a href="javascript:;"  onclick="show_details('<?php echo $value['sku_code'] ?>','<?php echo $value['plant'] ?>');"><?php echo $value['Vin_count'] ?></a></td>                          
                    <td><?php echo $value['sbomb_exists'] ?></td>                          
                    <td><?php echo $value['data_manufacture_month_year'] ?></td>                          
<!--                    <td><a href="javascript:;"  onclick="show_details('<?php echo $value['sku_code'] ?>','<?php echo $value['plant'] ?>');">More..</a></i>
                    </td>-->
                </tr>
    <?php }
} ?>                  
    </tbody>
</table>					
<?php echo $this->ajax_pagination->create_links(); ?>	