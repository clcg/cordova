  <div id="frequency" class="section" style="display:<?php echo $disp_freqs?>">
    <h4><span>Variant Frequencies</span></h4>

<div style="display:<?php echo $disp_otoscope ?>">
    <h5><span>OtoSCOPE&trade;</span></h5>
    <table border="0" cellspacing="0" cellpadding="0">
      <tbody>
        <tr>
          <td><img src="<?php print site_url("variant/freq?value=".$otoscope_aj_af*100); ?>" /><br /><small><?php print $otoscope_aj_label ?></small><br /><span>Ashkenazi Jewish living in New York</span></td>
          <td><img src="<?php print site_url("variant/freq?value=".$otoscope_co_af*100); ?>" /><br /><small><?php print $otoscope_co_label ?></small><br /><span>Colombian</span></td>
          <td><img src="<?php print site_url("variant/freq?value=".$otoscope_jp_af*100); ?>" /><br /><small><?php print $otoscope_jp_label ?></small><br /><span>Japanese</span></td>
        </tr>
        <tr>
          <td><img src="<?php print site_url("variant/freq?value=".$otoscope_us_af*100); ?>" /><br /><small><?php print $otoscope_us_label ?></small><br /><span>European-Americans from Iowa, USA</span></td>
          <td><img src="<?php print site_url("variant/freq?value=".$otoscope_es_af*100); ?>" /><br /><small><?php print $otoscope_es_label ?></small><br /><span>Spanish from Almería and Granada</span></td>
          <td><img src="<?php print site_url("variant/freq?value=".$otoscope_tr_af*100); ?>" /><br /><small><?php print $otoscope_tr_label ?></small><br /><span>Turkish</span></td>
        </tr>  
        <tr>
          <td><img src="<?php print site_url("variant/freq?value=".$otoscope_all_af*100); ?>" /><br /><small><?php print $otoscope_all_label ?></small><br /><span>All populations</span></td>
          <td><div style="width:200px;"></div></td>
          <td><div style="width:200px;"></div></td>
        </tr>  
      </tbody>
    </table>
</div>
    
<div style="display:<?php echo $disp_evs ?>">
    <h5><span>Exome Variant Server</span></h5>

    <table border="0" cellspacing="0" cellpadding="0">
      <tbody>
        <tr>
          <td><img src="<?php print site_url("variant/freq?value=".$evs_ea_af*100); ?>" /><br /><small><?php print $evs_ea_label ?></small><br /><span>European-American</span></td>
          <td><img src="<?php print site_url("variant/freq?value=".$evs_aa_af*100); ?>" /><br /><small><?php print $evs_aa_label ?></small><br /><span>African-American</span></td>
          <td><img src="<?php print site_url("variant/freq?value=".$evs_all_af*100); ?>" /><br /><small><?php print $evs_all_label ?></small><br /><span>All populations</span></td>
        </tr>
      </tbody>
    </table>
</div>

<div style="display:<?php echo $disp_1000g ?>">
    <h5><span>1000 Genomes</span></h5>
    <table border="0" cellspacing="0" cellpadding="0">
      <tbody>
        <tr>
          <td><img src="<?php print site_url("variant/freq?value=".$tg_afr_af*100); ?>" /><br /><small><?php print $tg_afr_label ?></small><br /><span>African</span></td>
          <td><img src="<?php print site_url("variant/freq?value=".$tg_amr_af*100); ?>" /><br /><small><?php print $tg_amr_label ?></small><br /><span>American</span></td>
          <td><img src="<?php print site_url("variant/freq?value=".$tg_eur_af*100); ?>" /><br /><small><?php print $tg_eur_label ?></small><br /><span>European</span></td>
        </tr>  
        <tr>
          <td><img src="<?php print site_url("variant/freq?value=".$tg_eas_af*100); ?>" /><br /><small><?php print $tg_eas_label ?></small><br /><span>East Asian</span></td>
          <td><img src="<?php print site_url("variant/freq?value=".$tg_sas_af*100); ?>" /><br /><small><?php print $tg_sas_label ?></small><br /><span>South Asian</span></td>
          <td><img src="<?php print site_url("variant/freq?value=".$tg_all_af*100); ?>" /><br /><small><?php print $tg_all_label ?></small><br /><span>All populations</span></td>
        </tr>  
      </tbody>
    </table>
</div>

<div style="display:<?php echo $disp_exac ?>">
    <h5><span>ExAC</span></h5>
    <table border="0" cellspacing="0" cellpadding="0">
      <tbody>
        <tr>
          <td><img src="<?php print site_url("variant/freq?value=".$exac_afr_af*100); ?>" /><br /><small><?php print $exac_afr_label ?></small><br /><span>African</span></td>
          <td><img src="<?php print site_url("variant/freq?value=".$exac_amr_af*100); ?>" /><br /><small><?php print $exac_amr_label ?></small><br /><span>American (Latino)</span></td>
          <td><img src="<?php print site_url("variant/freq?value=".$exac_fin_af*100); ?>" /><br /><small><?php print $exac_fin_label ?></small><br /><span>European (Finnish)</span></td>
        </tr>  
        <tr>
          <td><img src="<?php print site_url("variant/freq?value=".$exac_nfe_af*100); ?>" /><br /><small><?php print $exac_nfe_label ?></small><br /><span>European (non-Finnish)</span></td>
          <td><img src="<?php print site_url("variant/freq?value=".$exac_eas_af*100); ?>" /><br /><small><?php print $exac_eas_label ?></small><br /><span>East Asian</span></td>
          <td><img src="<?php print site_url("variant/freq?value=".$exac_sas_af*100); ?>" /><br /><small><?php print $exac_sas_label ?></small><br /><span>South Asian</span></td>
        </tr>  
        <tr>
          <td><img src="<?php print site_url("variant/freq?value=".$exac_oth_af*100); ?>" /><br /><small><?php print $exac_oth_label ?></small><br /><span>Other</span></td>
          <td><img src="<?php print site_url("variant/freq?value=".$exac_all_af*100); ?>" /><br /><small><?php print $exac_all_label ?></small><br /><span>All populations</span></td>
          <td><div style="width:200px;"></div></td>
        </tr>  
      </tbody>
    </table>
</div>

  </div><!-- #frequency -->
