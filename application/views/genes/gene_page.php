<h1><i><?php echo $display_name ?></i></h1>

<!-- <fieldset> -->
<!-- 	<center> -->
<!-- 	<button type="button">Load NGL Protein Structure Viewer Button Here?</button> -->
<!-- 	</center> -->
<!-- </fieldset> -->

<div id="mutation-tables">
    <fieldset>
        <legend class="genename" id="<?php echo $gene ?>">
	    	<strong>
	        	Variant List
	        </strong>
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