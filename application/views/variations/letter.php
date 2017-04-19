<div id="mutation-tables">
    <?php foreach ($genes as $gene => $alias): ?>
    <fieldset>
        <legend class="genename" id="<?php echo $gene ?>">
        	<strong><?php echo $display_names[$gene] ?></strong> 
        	<span>
        	<a href="<?php echo site_url('controller/function/uri') ?>">Link</a>
        		<a href="<?php echo site_url('variations/viwer/$gene')?>">Protein Viewer</a>
        		<a href="<?php echo gene_link_to_api($gene, 'csv') ?>">CSV</a> 
        		<a href="<?php echo gene_link_to_api($gene, 'tab') ?>">Tab</a> 
        		<a href="<?php echo gene_link_to_api($gene, 'json') ?>">JSON</a> 
        		<a href="<?php echo gene_link_to_api($gene, 'xml') ?>">XML</a>
        	</span>
        </legend>
        <div id="table-<?php echo $gene ?>" class="variant-list-container">
        </div>
    </fieldset>
    <?php endforeach; ?>
</div>
