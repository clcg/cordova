<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title><?php print strip_tags($hgvs_protein_change); ?> &ndash; <?php echo $site_full_name; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php echo include_proper_variant_css(); ?>
  </head>
  <body>
  
    <div id="variant">
    
      <?php display_variant_header(); ?>
    
      <div id="protein">
        <h1><span><?php print $hgvs_protein_change ?></span></h1>
        <h2>
          <span><?php print $gene; ?></span><br />
          <span><?php print $hgvs_nucleotide_change ?></span>
        </h2>
      </div>
    
      <div id="info">
        <h4><span>Information</span></h4>
        <table border="0" cellspacing="0" cellpadding="0">
          <tr>
            <th scope="row">Variant Locale</th>
            <td><?php print $variantlocale ?></td>
          </tr>
          <tr>
            <th scope="row">PubMed ID</th>
            <td><code><?php print $link_pubmed ?></code></td>
          </tr>
          <tr>
            <th class="last" scope="row">dbSNP ID</th>
            <td><code><?php print $link_dbsnp ?></code></td>
          </tr>
        </table>
      </div>
    
      <div id="toprow">
        <div id="call" class="section-small">
          <h4><span>Call</span></h4>
          <table border="0" cellspacing="0" cellpadding="0">
            <tr>
              <th scope="row">Variation</th>
              <td><?php print $variation ?></td>
            </tr>
            <tr>
              <th scope="row">Pathogenicity</th>
              <td><?php print $pathogenicity; ?></td>
            </tr>
            <tr>
              <th scope="row">Phenotype</th>
              <td><?php print $disease; ?></td>
            </tr>
          </table>
        </div>
      </div>
    
      <h3><span>Interpretation</span></h3>
    
      <div id="scores" class="section">
        <h4><span><em>In Silico</em> Computational</span></h4>
        <table border="0" cellspacing="0" cellpadding="0">
          <thead>
            <tr>
              <th scope="col">SIFT</th>
              <th scope="col">Polyphen-2</th>
              <th scope="col">LRT</th>
              <th scope="col">MutationTaster</th>
              <th scope="col">PhyloP</th>
              <th scope="col">GERP++</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="<?php print $class_sift ?>"                ><?php print $desc_sift ?></td>
              <td class="<?php print $class_polyphen ?>"            ><?php print $desc_polyphen ?></td>
              <td class="<?php print $class_lrt ?>"                 ><?php print $desc_lrt ?></td>
              <td class="<?php print $class_mutationtaster ?>"      ><?php print $desc_mutationtaster ?></td>
              <td class="<?php print $class_phylop ?>"              ><?php print $desc_phylop ?></td>
              <td class="<?php print $class_gerp ?>"                ><?php print $desc_gerp ?></td>
            </tr>
            <tr class="scores-numbers">
              <td class="<?php print $class_sift ?>-light"          ><?php print $sift_score ?></td>
              <td class="<?php print $class_polyphen ?>-light"      ><?php print $polyphen2_score ?></td>
              <td class="<?php print $class_lrt ?>-light"           ><?php print $lrt_score ?></td>
              <td class="<?php print $class_mutationtaster ?>-light"><?php print $mutationtaster_score ?></td>
              <td class="<?php print $class_phylop ?>-light"        ><?php print $phylop_score ?></td>
              <td class="<?php print $class_gerp ?>-light"          ><?php print $gerp_rs ?></td>
            </tr> 
          </tbody>
        </table>
      </div>
    
      <?php display_variant_frequencies(); ?>
    
      <div id="comments" class="section">
        <h4><span>Published Data</span></h4>
        <p><?php print $comments; ?></p>
      </div>
    
      <?php display_variant_footer(); ?>
  
    </div>
  
    <?php echo include_variant_js(); ?>
    
  </body>
</html>
