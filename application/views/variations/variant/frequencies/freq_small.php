  <div id="frequency-small" class="section" style="display:<?php echo $disp_freqs?>">

    <h4><span>Variant Frequencies</span></h4>

    <p id="frequency-description">Hover or click a population to see its full name.</p>

    <div style="overflow:hidden;width:100%;">
      <div style="width:45%;float:left;display:<?php echo $disp_otoscope?>">
        <table border="0" cellspacing="0" cellpadding="0">
          <tr>
            <th scope="row"><h5>OtoSCOPE&trade;</h5></th>
            <td>
              <div><img src="<?php print site_url("variant/freq?value=$freq_otoscope&amp;small"); ?>" /><br /><small><?php print $label_otoscope ?></small><br /><span>OTO</span>   <br /><strong>OtoSCOPE</strong></div>
            </td>
          </tr>
        </table>
      </div>
      
      <div style="width:48%;float:left;display:<?php echo $disp_evs?>">
        <table border="0" cellspacing="0" cellpadding="0">
          <tr>
            <th scope="row"><h5>Exome Variant Server</h5></th>
            <td>
              <div><img src="<?php print site_url("variant/freq?value=$freq_evs_ea&amp;small"); ?>" />  <br /><small><?php print $label_evs_ea ?></small>    <br /><span>EVS-EA</span><br /><strong>European American Alternate Allele Count</strong></div>
              <div><img src="<?php print site_url("variant/freq?value=$freq_evs_aa&amp;small"); ?>" />  <br /><small><?php print $label_evs_aa ?></small>    <br /><span>EVS-AA</span><br /><strong>African American Alternate Allele Count</strong></div>
            </td>
          </tr>
        </table>
      </div>
    </div><!-- #outer div -->

    <table border="0" cellspacing="0" cellpadding="0" style="display:<?php echo $disp_1000g?>">
      <tr>
        <th scope="row"><h5>1000 Genomes</h5></th>
        <td>
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_ceu&amp;small"); ?>" /><br /><small><?php print $label_tg_ceu ?></small><br /><span>CEU</span><br /><strong>Utah residents (CEPH) with Northern and Western European ancestry</strong></div>
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_tsi&amp;small"); ?>" /><br /><small><?php print $label_tg_tsi ?></small><br /><span>TSI</span><br /><strong>Toscani in Italia</strong></div>
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_gbr&amp;small"); ?>" /><br /><small><?php print $label_tg_gbr ?></small><br /><span>GBR</span><br /><strong>British from England and Scotland</strong></div>
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_fin&amp;small"); ?>" /><br /><small><?php print $label_tg_fin ?></small><br /><span>FIN</span><br /><strong>Finnish from Finland</strong></div>    
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_ibs&amp;small"); ?>" /><br /><small><?php print $label_tg_ibs ?></small><br /><span>IBS</span><br /><strong>Iberian populations in Spain</strong></div>
                                                                                                                                                        
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_chb&amp;small"); ?>" /><br /><small><?php print $label_tg_chb ?></small><br /><span>CHB</span><br /><strong>Han Chinese in Beijing, China </strong></div>
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_jpt&amp;small"); ?>" /><br /><small><?php print $label_tg_jpt ?></small><br /><span>JPT</span><br /><strong>Japanese in Toyko, Japan</strong></div>          
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_chs&amp;small"); ?>" /><br /><small><?php print $label_tg_chs ?></small><br /><span>CHS</span><br /><strong>Han Chinese South</strong></div>
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_cdx&amp;small"); ?>" /><br /><small><?php print $label_tg_cdx ?></small><br /><span>CDX</span><br /><strong>Chinese Dai in Xishuangbanna </strong></div>
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_ibs&amp;small"); ?>" /><br /><small><?php print $label_tg_khv ?></small><br /><span>KHV</span><br /><strong>Kinh in Ho Chi Minh City, Vietnam</strong></div>
                                                                                                                                                       
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_yri&amp;small"); ?>" /><br /><small><?php print $label_tg_yri ?></small><br /><span>YRI</span><br /><strong>Yoruba in Ibadan, Nigeria</strong></div>    
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_lwk&amp;small"); ?>" /><br /><small><?php print $label_tg_lwk ?></small><br /><span>LWK</span><br /><strong>Luhya in Webuye, Kenya</strong></div>
                                                                                                                                                       
                                                                                                                                                       
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_asw&amp;small"); ?>" /><br /><small><?php print $label_tg_asw ?></small><br /><span>ASW</span><br /><strong>African Ancestry in Southwest US</strong></div>
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_acb&amp;small"); ?>" /><br /><small><?php print $label_tg_acb ?></small><br /><span>ACB</span><br /><strong>African Caribbean in Barbados </strong></div>
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_mxl&amp;small"); ?>" /><br /><small><?php print $label_tg_mxl ?></small><br /><span>MXL</span><br /><strong>Mexican Ancestry in Los Angeles, CA</strong></div>    
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_pur&amp;small"); ?>" /><br /><small><?php print $label_tg_pur ?></small><br /><span>PUR</span><br /><strong>Puerto Rican in Puerto Rico</strong></div>
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_clm&amp;small"); ?>" /><br /><small><?php print $label_tg_clm ?></small><br /><span>CLM</span><br /><strong>Colombian in Medellin, Colombia</strong></div>
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_pel&amp;small"); ?>" /><br /><small><?php print $label_tg_pel ?></small><br /><span>PEL</span><br /><strong>Peruvian in Lima, Peru</strong></div>    
                                                                                                                                                       
          <div><img src="<?php print site_url("variant/freq?value=$freq_tg_gih&amp;small"); ?>" /><br /><small><?php print $label_tg_gih ?></small><br /><span>GIH</span><br /><strong>Gujarati Indian in Houston, TX </strong></div>

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
