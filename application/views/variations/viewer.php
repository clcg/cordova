<noscript>
  <style type="text/css">
    #viewers-container {display: none;}
  </style>
  <div class="noScriptMsg" style="color: red;">
    <h1><strong>You need to have Javascript enabled to use PV</strong></h1>
  </div>
</noscript>

<div id="viewers-container">
  <?php if(isset($error)): ?>
    <h2 style="color: red;"><?= $error; ?></h2>
  <?php else: ?>
    <h1><?= $title; ?>
    <?php foreach ($structures as $currStruct): ?>
      <div id="<?= $gene.'-'.$currStruct['name']; ?>-contatiner" style="display: block; width: 48%; margin-bottom: 1%; text-align: center;">
        <h5><?= $currStruct['name']; ?></h5>
        <div class="viewer-inputs" style="text-align: center; margin-bottom: 1%;">
          <button onclick="changeRenderMode('<?= $gene."_".str_replace("-", "_", $currStruct['name']); ?>', 'cartoon')" style="display: inline-block;">Cartoon</button>
          <button onclick="changeRenderMode('<?= $gene."_".str_replace("-", "_", $currStruct['name']); ?>', 'ballsAndSticks')" style="display: inline-block;">Ball and Sticks</button>
          <button onclick="changeRenderMode('<?= $gene."_".str_replace("-", "_", $currStruct['name']); ?>', 'spheres')" style="display: inline-block;">Spheres</button>
          <br>
          <label for="<?= $gene.'_'.str_replace("-", "_", $currStruct['name']); ?>-select-index" style="font-size: .5em;">Desired residue index: </label>
          <input type="number" id="<?= $gene.'_'.str_replace("-", "_", $currStruct['name']); ?>-select-index" min="0" max="<?= (count($currStruct['residues'])-1); ?>"></input>
          <button id="<?= $gene.'_'.str_replace("-", "_", $currStruct['name']); ?>-select-button" onclick="selectResidue('<?= $gene."_".str_replace("-", "_", $currStruct['name']); ?>', 'spheres')">Select</button>
        </div>
        <div id="<?= $gene.'_'.str_replace("-", "_", $currStruct['name']); ?>-viewer" style="border-style: solid; border-width: .1%;"></div>
        <div class="viewer-output" style="text-align: center;">
          <label for="<?= $gene.'_'.str_replace("-", "_", $currStruct['name']); ?>-select-res-name" style="font-size: .5em;">Picked residue name: </label>
          <div id="<?= $gene.'_'.str_replace("-", "_", $currStruct['name']); ?>-select-res-name" style="display: inline-block; font-size: .5em;">&nbsp;</div>
          <label for="<?= $gene.'_'.str_replace("-", "_", $currStruct['name']); ?>-select-res-score" style="font-size: .5em;">Picked residue score: </label>
          <div id="<?= $gene.'_'.str_replace("-", "_", $currStruct['name']); ?>-select-res-score" style="display: inline-block; font-size: .5em;">&nbsp;</div>
        </div>
      </div <?= (array_search($currStruct, $structures) < count($structures)-1)?("style='border-bottom: 5px solid;'"):(""); ?>>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script type='text/javascript' src='<?php echo base_url("assets/public/js/bio-pv.js"); ?>'></script>
<script type="text/javascript">
  <?php foreach($structures as $currStruct): ?>
    var <?= $gene.'_'.str_replace("-", "_", $currStruct['name']); ?> = {
      viewer: null,
      structure: null,
      geom: null,
      renderMode: null,
      name: "<?= $currStruct['name']; ?>",
      residues: [
        <?php for($i=0;$i < count($currStruct['residues']); $i++): ?>
          {start_index: <?= intval($currStruct['residues'][$i]['start_index']); ?>, name: "<?= $currStruct['residues'][$i]['name']; ?>", residue_index: <?= intval($currStruct['residues'][$i]['residue_index']); ?>}<?= ($i < (count($currStruct['residues'])-1)?(",\n"):("\n")); ?>
        <?php endfor; ?>
      ]
    };
  <?php endforeach; ?>
  function initViewer(viewer, viewerParent, pdbFile) {
    var parent = document.getElementById(viewerParent);
    viewer.viewer = pv.Viewer(parent, {width: parent.offsetWidth, height: parent.offsetWidth, antialias: true, quality: "medium", selectionColor: "#dd00ff"});
    pv.io.fetchPdb(pdbFile, function(structure) {
      viewer.structure = structure;
      viewer.renderMode = "cartoon";
      viewer.geom = viewer.viewer.cartoon(viewer.name, viewer.structure, {color: color.ssSuccession()});
      viewer.viewer.centerOn(viewer.structure);
      viewer.viewer.autoZoom();
    });
  }
  function selectResidue(proteinId) {
    var currViewer = eval(proteinId);
    var selectedIndex = document.getElementById(proteinId+"-select-index").value;
    var selectedRes = currViewer.residues[selectedIndex];
    var currentAtom = selectedRes["start_index"];
    var newSelection = currViewer.structure.createEmptyView(); 
    currViewer.viewer.clear();
    while(currViewer.structure.atoms()[currentAtom].residue().num() == selectedRes["residue_index"]) {
      newSelection.addAtom(currViewer.structure.atoms()[currentAtom]);
      currentAtom++;
    }
    currViewer.geom.setSelection(newSelection);
    if(currViewer.renderMode == "cartoon") {
      currViewer.geom = currViewer.viewer.cartoon(currViewer.name, currViewer.structure, {color: color.ssSuccession()});
    } else if(currViewer.renderMode == "ballsAndSticks") {
      currViewer.geom = currViewer.viewer.ballsAndSticks(currViewer.name, currViewer.structure, {color: color.ssSuccession()});
    } else if(currViewer.renderMode == "spheres") {
      currViewer.geom = currViewer.viewer.spheres(currViewer.name, currViewer.structure, {color: color.ssSuccession()});
    } else {
      console.log("Unknown rendering mode: "+currViewer.renderMode);
      currViewer.geom = currViewer.viewer.cartoon(currViewer.name, currViewer.structure, {color: color.ssSuccession()});
    }
    //var labelPos = newSelection.atoms()[0].pos();
    //labelPos[0] += 10;
    //labelPos[1] += 10;
    //currViewer.viewer.label(proteinId+"-label", selectedRes["name"]+" CADD: "+newSelection.atoms()[0].tempFactor(), labelPos, labelOptions);
    currViewer.geom.setSelection(newSelection);
    currViewer.viewer.centerOn(newSelection);
    //currViewer.viewer.autoZoom();
    console.log(newSelection.atoms()[0].pos()+"wow");
    document.getElementById(proteinId+"-select-res-name").innerHTML = selectedRes["name"];
    document.getElementById(proteinId+"-select-res-score").innerHTML = newSelection.atoms()[0].tempFactor();
  }
  function changeRenderMode(proteinId, newRenderMode) {
    var currViewer = eval(proteinId);
    var oldSelection = currViewer.geom.selection();
    
    currViewer.viewer.clear();
    currViewer.renderMode = newRenderMode;
    switch (newRenderMode) {
      case "cartoon":
        currViewer.geom = currViewer.viewer.cartoon(currViewer.name, currViewer.structure, {color: color.ssSuccession()});
        break;
      case "ballsAndSticks":
        currViewer.geom = currViewer.viewer.ballsAndSticks(currViewer.name, currViewer.structure, {color: color.ssSuccession()});
        break;
      case "spheres":
        currViewer.geom = currViewer.viewer.spheres(currViewer.name, currViewer.structure, {color: color.ssSuccession()});
        break;
      default:
        console.log("Unknown rendering mode: "+newRenderMode);
        currViewer.geom = currViewer.viewer.cartoon(currViewer.name, currViewer.structure, {color: color.ssSuccession()});
        break;
    }
    if(oldSelection && oldSelection.length > 0) {
      currViewer.geom.setSelection(oldSelection);
      var labelPos = oldSelection.atoms()[0].pos();
      labelPos[0] += 10;
      labelPos[1] += 10;
      currViewer.viewer.label(proteinId+"-label", oldSelection.atoms()[0].residue().name()+" CADD: "+oldSelection.atoms()[0].tempFactor(), oldSelection.atoms()[0].pos(), labelOptions);
    }
  }
  <?php foreach($structures as $currStruct): ?>
    document.getElementById("<?= $gene.'_'.str_replace('-', '_', $currStruct['name']); ?>-select-index").addEventListener("keyup", function(event) {
      event.preventDefault();
      if(event.keyCode === 13) {
        document.getElementById("<?= $gene.'_'.str_replace('-', '_', $currStruct['name']); ?>-select-button").click();
      }
    });
  <?php endforeach; ?>
  <?php foreach($structures as $currStruct): ?>
    document.addEventListener("DOMContentLoaded", initViewer(<?= $gene.'_'.str_replace('-', '_', $currStruct['name']); ?>, "<?= $gene.'_'.str_replace('-', '_', $currStruct['name']); ?>-viewer", "<?= base_url($path.$currStruct['name'].'/'.$gene.'_'.$currStruct['name'].$suffix); ?>"));
  <?php endforeach; ?>
</script>