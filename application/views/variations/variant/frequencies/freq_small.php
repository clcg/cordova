  <div id="frequency-small" class="section" style="display:<?php echo $disp_freqs?>">

    <h4><span>Variant Frequencies</span></h4>

    <p id="frequency-description">Hover or click a population to see its full name.</p>

    <table border="0" cellspacing="0" cellpadding="0" style="display:<?php echo $disp_otoscope?>">
      <tr>
        <th scope="row"><h5>OtoSCOPE&trade;</h5></th>
        <td>
          <div><img src="<?php print site_url("variant/freq?value=".($otoscope_aj_af*100)."&amp;small"); ?>" /><br /><small><?php print $otoscope_aj_label ?></small><br /><span>AJ</span><br /><strong>Ashkenazi Jewish living in New York</strong></div>
          <div><img src="<?php print site_url("variant/freq?value=".($otoscope_co_af*100)."&amp;small"); ?>" /><br /><small><?php print $otoscope_co_label ?></small><br /><span>CO</span><br /><strong>Colombian</strong></div>
          <div><img src="<?php print site_url("variant/freq?value=".($otoscope_jp_af*100)."&amp;small"); ?>" /><br /><small><?php print $otoscope_jp_label ?></small><br /><span>JP</span><br /><strong>Japanese</strong></div>
          <div><img src="<?php print site_url("variant/freq?value=".($otoscope_us_af*100)."&amp;small"); ?>" /><br /><small><?php print $otoscope_us_label ?></small><br /><span>US</span><br /><strong>European-Americans from Iowa, USA</strong></div>
          <div><img src="<?php print site_url("variant/freq?value=".($otoscope_es_af*100)."&amp;small"); ?>" /><br /><small><?php print $otoscope_es_label ?></small><br /><span>ES</span><br /><strong>Spanish from Almería and Granada</strong></div>
          <div><img src="<?php print site_url("variant/freq?value=".($otoscope_tr_af*100)."&amp;small"); ?>" /><br /><small><?php print $otoscope_tr_label ?></small><br /><span>TR</span><br /><strong>Turkish</strong></div>
          <div><img src="<?php print site_url("variant/freq?value=".($otoscope_all_af*100)."&amp;small"); ?>" /><br /><small><?php print $otoscope_all_label ?></small><br /><span>ALL</span><br /><strong>All populations</strong></div>
        </td>
      </tr>
    </table>

    <table border="0" cellspacing="0" cellpadding="0" style="display:<?php echo $disp_evs?>">
      <tr>
        <th scope="row"><h5>Exome Variant Server</h5></th>
        <td>
          <div><img src="<?php print site_url("variant/freq?value=".($evs_ea_af*100)."&amp;small"); ?>" />  <br /><small><?php print $evs_ea_label ?></small>    <br /><span>EA</span><br /><strong>European-American</strong></div>
          <div><img src="<?php print site_url("variant/freq?value=".($evs_aa_af*100)."&amp;small"); ?>" />  <br /><small><?php print $evs_aa_label ?></small>    <br /><span>AA</span><br /><strong>African-American</strong></div>
          <div><img src="<?php print site_url("variant/freq?value=".($evs_all_af*100)."&amp;small"); ?>" />  <br /><small><?php print $evs_all_label ?></small>    <br /><span>ALL</span><br /><strong>All populations</strong></div>
        </td>
      </tr>
    </table>

    <table border="0" cellspacing="0" cellpadding="0" style="display:<?php echo $disp_1000g?>">
      <tr>
        <th scope="row"><h5>1000 Genomes</h5></th>
        <td>
          <div><img src="<?php print site_url("variant/freq?value=".($tg_afr_af*100)."&amp;small"); ?>" /><br /><small><?php print $tg_afr_label ?></small><br /><span>AFR</span><br /><strong>African</strong></div>
          <div><img src="<?php print site_url("variant/freq?value=".($tg_eur_af*100)."&amp;small"); ?>" /><br /><small><?php print $tg_amr_label ?></small><br /><span>AMR</span><br /><strong>American</strong></div>
          <div><img src="<?php print site_url("variant/freq?value=".($tg_amr_af*100)."&amp;small"); ?>" /><br /><small><?php print $tg_asn_label ?></small><br /><span>ASN</span><br /><strong>Asian</strong></div>
          <div><img src="<?php print site_url("variant/freq?value=".($tg_asn_af*100)."&amp;small"); ?>" /><br /><small><?php print $tg_eur_label ?></small><br /><span>EUR</span><br /><strong>European</strong></div>    
          <div><img src="<?php print site_url("variant/freq?value=".($tg_all_af*100)."&amp;small"); ?>" /><br /><small><?php print $tg_all_label ?></small><br /><span>ALL</span><br /><strong>All populations</strong></div>
        </td>
      </tr>
    </table>

  </div><!-- #frequency-small -->

  <script type="text/javascript" src="<?php echo site_url('assets/public/js/jquery.min.js'); ?>"></script>
  <script type="text/javascript" src="<?php echo site_url('assets/public/js/jquery.cookie.min.js'); ?>"></script>
  <script type="text/javascript" src="<?php echo site_url('assets/public/js/jquery.simplemodal.min.js'); ?>"></script>
  <script type="text/javascript" src="<?php echo site_url('assets/public/js/jquery.tablesorter.min.js'); ?>"></script>
  <script type="text/javascript" src="<?php echo site_url('assets/public/js/jquery.tipsy.js'); ?>"></script>
  <script type="text/javascript" src="<?php echo site_url('assets/public/js/jquery.shadow.min.js'); ?>"></script>
  <script type="text/javascript" src="<?php echo site_url('assets/public/js/script.js'); ?>"></script>
