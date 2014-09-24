<div id="loading-modal-container">
   <div id="show-unknown">
       <a href="<?php print $display_all_url ?>"><?php print $display_all_text ?></a>
   </div>
   <div id="loading-overlay">
       <p id="loading-modal">
           Loading annotations...
           <img src="<?php print site_url('assets/public/img/loading.gif'); ?>" alt="Loading icon">
       </p>
   </div>
   <div id="mutation-tables">
       <?php print $result_table; ?>
   </div>
</div>
