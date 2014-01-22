<h1><?php echo $gene; ?></h1>
<h3>Select a variation to edit</h3>
<table id="variations-table" class="table table-striped table-bordered tablesorter">
  <thead>
    <tr>
      <th>HGVS Protein Change</th>
      <th>HGVS Nucleotide Change</th>
      <th>Variant Locale</th>
      <th>Genomic Position (Hg19)</th>
      <th>Variant Type</th>
      <th>Phenotype</th>
    </tr>
  </thead>
  <tbody data-provides="rowlink">
    <?php foreach ($rows as $row): ?>
      <tr>
        <td><a href="<?php echo site_url('variations/edit/'.$row->id); ?>"><?php echo $row->hgvs_protein_change; ?></a></td>
        <td><?php echo $row->hgvs_nucleotide_change; ?></td>
        <td><?php echo $row->variantlocale; ?></td>
        <td><?php echo $row->variation; ?></td>
        <td><?php echo $row->pathogenicity; ?></td>
        <td><?php echo $row->disease; ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
