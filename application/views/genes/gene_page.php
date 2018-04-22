<h1><i><?php echo $display_name ?></i></h1>

<div id="mutation-tables">
        	
    <fieldset>
        <legend class="genename" id="<?php echo $gene ?>">
	    	<strong><i>
	        	Variant List
	        </i></strong>
	        <span>click this row to expand list</span>
        </legend>

        <span>
        	<!-- Commented out the below line for PR to cordova/dev. uncomment it for PV stuff -->
        	<!-- <?php if(is_dir("assets/public/pdb/dvd-structures/$gene")){ ?><a href="<?php echo site_url('viewer/'.$gene); ?>">PV</a><?php } ?>	-->
			
			Download variant list:
        	<a href="<?php echo gene_link_to_api($gene, 'csv', 'download') ?>">CSV</a> 
    		<a href="<?php echo gene_link_to_api($gene, 'tab', 'download') ?>">Tab</a>
		</span>
		         
        <div id="table-<?php echo $gene ?>" class="variant-list-container">
        
        </div>
    </fieldset>

</div>
