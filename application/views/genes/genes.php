<div class="row-fluid">
  <div class="span7">
    <h3>Filter by gene letter</h3>
      <ul id="letters-list" class="nav nav-pills">
        <?php foreach ($letters as $letter): ?>
          <li>
            <a href="<?php echo site_url('genes/'.$letter); ?>">
              <?php echo $letter ?>
            </a>
          </li>
        <?php endforeach ?>
      </ul>
  </div>

  <div id="gene-selection-partition" class="span1">
    <h3>or</h3>
  </div>
    
  <div class="span4">
    <h3>Find the gene below</h3>
    <div id="genes-list-wrapper">
    <ul id="genes-list" class="nav nav-tabs nav-stacked">
      <?php foreach ($genes as $gene): ?>
        <li>
          <a href="<?php echo site_url('variations/'.$gene); ?>">
            <?php echo $gene; ?>
          </a>
        </li>
      <?php endforeach ?>
    </ul>
    </div>
  </div>
</div>
