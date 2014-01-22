<h3>Select a gene by letter</h3>
<div id="letters-list-wrapper">
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
