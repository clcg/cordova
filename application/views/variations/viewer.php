<noscript>
  <style type="text/css">
    .proteinViewer {display: none;}
  </style>
  <div class="noScriptMsg" style="color: red">
    <h1><strong>You need to have Javascript enabled to use PV</strong></h1>
  </div>
</noscript>
<script type='text/javascript' src='<?php echo base_url("assets/public/js/bio-pv.js"); ?>'></script>
<div class="proteinViewer">
  <?php if(is_dir("assets/public/pdb/dvd_structures/$gene")) { ?>
  <h1><?php echo $gene; ?></h1> 
    <?php $proDirs = glob("assets/public/pdb/dvd_structures/$gene/*", GLOB_ONLYDIR); 
    $viewerArr = array();
    foreach($proDirs as $subDir) { 
      $nameParts = explode("/", $subDir); 
      $protName = $nameParts[count($nameParts)-1];
      $viewerArr[] = str_replace("-", "_", $protName); 
    ?>
      <h3><?php echo $protName; ?> </h3>
      <div id="picked_atom_name_<?php echo str_replace("-", "_", $protName); ?>">&nbsp;</div>
      <div id="view<?php echo str_replace("-", "_", $protName); ?>" style="border: solid; width: 600px"></div>
    <?php } ?>
  <?php } ?>
  
</div>
<script type='text/javascript'>
	
  var options = 
  {
    width: 600,
    height: 600,
    antialias: true,
    quality: 'medium'
  };
	
  <?php foreach($viewerArr as $protName): ?>
  var <?php echo "parent".$protName; ?>     = document.getElementById('<?php echo "view".$protName; ?>');
  var <?php echo "viewer".$protName; ?>     = pv.Viewer(document.getElementById('<?php echo "view".$protName; ?>'), options);
  var <?php echo "prevPicked".$protName; ?> = null;
  <?php endforeach; ?>

  function setColorForAtom(go, atom, color)
  {
    var view = go.structure().createEmptyView();
    view.addAtom(atom);
    go.colorBy(pv.color.uniform(color), view);
    console.log(go);
  }
	
	
  function loadStructs()
  {
    <?php foreach($viewerArr as $protName){ ?>
      pv.io.fetchPdb('<?php echo site_url("assets/public/pdb/dvd_structures/$gene/".str_replace("_", "-", $protName)."/".$gene."_".str_replace("_", "-", $protName)."_FFX.pdb"); ?>', function(structure)
        {
          <?php echo "viewer".$protName; ?>.cartoon('protein', structure, { color : color.ssSuccession() });
          //<?php echo "viewer".$protName; ?>.ballsAndSticks('protein', structure, { color: color.ssSuccession() });
          <?php echo "viewer".$protName; ?>.centerOn(structure);
	  <?php echo "viewer".$protName; ?>.autoZoom();
	  //<?php echo "viewer".str_replace("-", "_", $protName); ?>.spin(true);
        }
      );
    <?php } ?>
  }

  <?php foreach($viewerArr as $protName){ ?>
    <?php echo "parent".$protName; ?>.addEventListener('mousemove', function(event)
      {
        var rect   = <?php echo "viewer".$protName; ?>.boundingClientRect();
        var picked = <?php echo "viewer".$protName; ?>.pick({x: event.clientX - rect.left,
                                                             y: event.clientY - rect.top});
        console.log(picked);
        //console.log(event.clientX - rect.left);     
        if(<?php echo "prevPicked".$protName; ?> !== null &&
           picked !== null &&
           picked.target() === <?php echo "prevPicked".$protName; ?>.atom)
        {
          return;
        }

        if(<?php echo "prevPicked".str_replace("-", "_", $protName); ?> !== null)
        {
          setColorForAtom(<?php echo "prevPicked".$protName; ?>.node, <?php echo "prevPicked".$protName; ?>.atom, <?php echo "prevPicked".$protName; ?>.color);
        }

        if(picked !== null)
        {
          var atom = picked.target();
          document.getElementById('picked_atom_name_<?php echo $protName; ?>').innerHTML = atom.qualifiedName();
          var color = [0, 0, 0, 0];
          picked.node().getColorForAtom(atom, color);
          <?php echo "prevPicked".$protName; ?> = {atom: atom, color: color, node: picked.node()};
          setColorForAtom(picked.node(), atom, 'red');
        }
        else
        {
          document.getElementById('picked_atom_name_<?php echo $protName; ?>').innerHTML = '&nbsp;';
          <?php echo "prevPicked".$protName; ?> = null;
        }

        <?php echo "viewer".$protName; ?>.requestRedraw();
      }
    );
  <?php } ?>
  document.addEventListener('DOMContentLoaded', loadStructs);
</script>
