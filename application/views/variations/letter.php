<div id="mutation-tables">
    <?php foreach ($genes as $gene): ?>
    <fieldset>
        <legend class="genename" id="<?php echo $gene ?>"><strong><?php echo $gene ?></strong> <span> <a href="https://github.com/wtollefson/dvd-structures/tree/master/<?php echo $gene ?>">3D Structure</a> <a href="<?php echo gene_link_to_api($gene, 'csv') ?>">CSV</a> <a href="<?php echo gene_link_to_api($gene, 'tab') ?>">Tab</a> <a href="<?php echo gene_link_to_api($gene, 'json') ?>">JSON</a> <a href="<?php echo gene_link_to_api($gene, 'xml') ?>">XML</a></span></legend>
        <div id="table-<?php echo $gene ?>" class="variant-list-container">
        </div>
    </fieldset>
    <?php endforeach; ?>
</div>
