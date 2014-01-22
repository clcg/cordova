  <div id="frequency" class="section" style="display:<?php echo $disp_freqs?>">
    <h4><span>Variant Frequencies</span></h4>

<div style="display:<?php echo $disp_otoscope ?>">
    <h5><span>OtoSCOPE&trade;</span></h5>
    <table border="0" cellspacing="0" cellpadding="0">
      <tbody>
        <tr>
          <td><img src="<?php print site_url("variant/freq?value=$freq_otoscope"); ?>" /><br /><small><?php print $label_otoscope ?></small><br /><span>Alternate Allele Count</span></td>
        </tr>  
      </tbody>
    </table>
</div>
    
<div style="display:<?php echo $disp_evs ?>">
    <h5><span>Exome Variant Server</span></h5>

    <table border="0" cellspacing="0" cellpadding="0">
      <tbody>
        <tr>
          <td><img src="<?php print site_url("variant/freq?value=$freq_evs_ea"); ?>" /><br /><small><?php print $label_evs_ea ?></small><br /><span>European American Alternate Allele Count</span></td>
          <td><img src="<?php print site_url("variant/freq?value=$freq_evs_aa"); ?>" /><br /><small><?php print $label_evs_aa ?></small><br /><span>African American Alternate Allele Count</span></td>
          <td><div style="width:200px;"></div></td>
        </tr>
      </tbody>
    </table>
</div>

<div style="display:<?php echo $disp_1000g ?>">
    <h5 style="margin:5px 0;"><span>1000 Genomes</span></h5>

    <h6>European</h6>    
    <table border="0" cellspacing="0" cellpadding="0">
      <tbody>
        <tr>
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_ceu"); ?>" /><br /><small><?php print $label_tg_ceu ?></small><br /><span>Utah residents, Northern and Western European ancestry</span></td>
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_tsi"); ?>" /><br /><small><?php print $label_tg_tsi ?></small><br /><span>Toscani in Italia</span></td>
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_gbr"); ?>" /><br /><small><?php print $label_tg_gbr ?></small><br /><span>British from England and Scotland</span></td>
        </tr>                                                                                                                                    
        <tr>                                                                                                                                     
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_fin"); ?>" /><br /><small><?php print $label_tg_fin ?></small><br /><span>Finnish from Finland</span></td>    
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_ibs"); ?>" /><br /><small><?php print $label_tg_ibs ?></small><br /><span>Iberian populations in Spain</span></td>    
          <td>&nbsp;</td>
        </tr>
      </tbody>
    </table>

    <h6>East Asian</h6>
    <table border="0" cellspacing="0" cellpadding="0">
      <tbody>
        <tr>
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_chb"); ?>" /><br /><small><?php print $label_tg_chb ?></small><br /><span>Han Chinese in Beijing, China</span></td>
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_jpt"); ?>" /><br /><small><?php print $label_tg_jpt ?></small><br /><span>Japanese in Toyko, Japan</span></td>          
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_chs"); ?>" /><br /><small><?php print $label_tg_chs ?></small><br /><span>Han Chinese South</span></td>
        </tr>                                                                                                                                    
        <tr>                                                                                                                                     
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_cdx"); ?>" /><br /><small><?php print $label_tg_cdx ?></small><br /><span>Chinese Dai in Xishuangbanna</span></td>
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_ibs"); ?>" /><br /><small><?php print $label_tg_ibs ?></small><br /><span>Iberian populations in Spain</span></td>
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_ibs"); ?>" /><br /><small><?php print $label_tg_khv ?></small><br /><span>Kinh in Ho Chi Minh City, Vietnam</span></td>
        </tr>
      </tbody>
    </table>

    <h6>West African</h6>
    <table border="0" cellspacing="0" cellpadding="0">
      <tbody>
        <tr>
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_yri"); ?>" /><br /><small><?php print $label_tg_yri ?></small><br /><span>Yoruba in Ibadan, Nigeria </span></td>    
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_lwk"); ?>" /><br /><small><?php print $label_tg_lwk ?></small><br /><span>Luhya in Webuye, Kenya</span></td>
          <td><div style="width:200px;">&nbsp;</div></td>
        </tr>
      </tbody>
    </table>

    <h6>Americas</h6>
    <table border="0" cellspacing="0" cellpadding="0">
      <tbody>
        <tr>
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_asw"); ?>" /><br /><small><?php print $label_tg_asw ?></small><br /><span>African Ancestry in Southwest US</span></td>
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_acb"); ?>" /><br /><small><?php print $label_tg_acb ?></small><br /><span>African Caribbean in Barbados</span></td>
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_mxl"); ?>" /><br /><small><?php print $label_tg_mxl ?></small><br /><span>Mexican Ancestry in Los Angeles, CA</span></td>    
        </tr>
        <tr>
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_pur"); ?>" /><br /><small><?php print $label_tg_pur ?></small><br /><span>Puerto Rican in Puerto Rico</span></td>
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_clm"); ?>" /><br /><small><?php print $label_tg_clm ?></small><br /><span>Colombian in Medellin, Colombia </span></td>
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_pel"); ?>" /><br /><small><?php print $label_tg_pel ?></small><br /><span>Peruvian in Lima, Peru</span></td>    
        </tr>
      </tbody>
    </table>

    <h6>South Asian</h6>
    <table border="0" cellspacing="0" cellpadding="0">
      <tbody>
        <tr>
          <td><img src="<?php print site_url("variant/freq?value=$freq_tg_gih"); ?>" /><br /><small><?php print $label_tg_gih ?></small><br /><span>Gujarati Indian in Houston, TX</span></td>           
          <td><div style="width:200px;">&nbsp;</div></td>
          <td><div style="width:200px;">&nbsp;</div></td>
        </tr>
      </tbody>
    </table>
</div>

  </div><!-- #frequency -->
