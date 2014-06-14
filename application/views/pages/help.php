  <div class="width750">
  	<h2>Navigation</h2>
    <div id="content-help">
      <p>
        <img src="<?php echo site_url('assets/public/img/help-images/2.png'); ?>" alt="Using the gene letter table" class="float-left" />
        <span>When you click a letter on the table to the left, you will be shown all genes starting with that letter that have variants associated with them. </span>
      </p>
      <p>
        <img src="<?php echo site_url('assets/public/img/help-images/3.png'); ?>" alt="Viewing gene variants" class="float-right" />
        <span>The &#8220;<strong>+</strong>&#8221; sign next to each gene indicates that you can click it to show all variant data. Click the gene again if you want to hide the variants. Your viewing options are remembered the next time you visit that page.</span>
      </p>
      <p>
        <img src="<?php echo site_url('assets/public/img/help-images/4.png'); ?>" alt="Sorting variant information" class="float-left" />
        <span>When viewing variant data for a gene, you can sort the information by clicking the table headers. A <img src="assets/public/img/table-sort-arrow-down.png" /> arrow indicates descending order. Keep clicking until you see an <img src="assets/public/img/table-sort-arrow-up.png" style="vertical-align:bottom;" /> arrow for ascending order.</span>
      </p>
      <p>
        <img src="<?php echo site_url('assets/public/img/help-images/5.png'); ?>" alt="Viewing extended annotation data" class="float-right" />
        <span>You can view extended annotation data by clicking the small orange box to the left of each row. This opens up the information in a new page. You can either print or download a PDF of the variant of interest.</span>
      </p>
      <p>
        <img src="<?php echo site_url('assets/public/img/help-images/6.png'); ?>" alt="Seeing quick annotation data" class="float-left" />
        <span>To see a &#8216;quick&#8217; annotation overview, click any part of the row except the orange box. You'll see a popup with the annotation data. To dismiss the popup, click <img src="assets/public/img/modal-close-small.png" style="vertical-align:middle;" /> to the top left or anywhere outside the box.</span>
      </p>
    </div>
    
    <h2>Data</h2>
    <h3>Pathogenicity Assessment</h3>
    <p>Future versions of the database will be manually curated to incorporate strength of published data about each variant. In silico computational analyses are derived from dbNSFP (link and reference). Pathogenicity is determined as follows:</p>
    <dl>
      <dt><strong>Polyphen</strong></dt>
      <dd>
        D &ndash; probably damaging &gt; 0.85 <br />
        P &ndash; possibly damaging 0.85 - 0.15 <br />
        B &ndash; benign &lt; 0.15 <br />
      </dd>
      <dt><strong>MutationTaster</strong></dt>
      <dd>
        Automatically calculated categories: &#8220;disease_causing_automatic,&#8221; &#8220;disease_causing,&#8221; &#8220;polymorphism,&#8221; and &#8220;polymorphism_automatic,&#8221; which we coded as &#8220;A&#8221; &#8220;D&#8221; &#8220;N&#8221; and &#8220;P&#8221;. Values closer to 1 have a higher probability that the prediction is true.
      </dd>
      <dt><strong>PhyloP</strong></dt>
      <dd>    
        C &ndash; conserved &gt; 0.95 <br />
        N &ndash; not conserved &lt; 0.95
      </dd>
      <dt><strong>SIFT</strong></dt>
      <dd>  
        D &ndash; deleterious &gt; 0.95 <br />
        T &ndash; tolerated &lt; 0.95
      </dd>
      <dt><strong>LRT</strong></dt>
      <dd>
        D &ndash; deleterious fulfills the following: (i) from a codon defined by LRT as significantly constrained (LRTorio0.001 and oo1), (ii) from a site with Z10 eutherian mammals alignments, and (iii) the alternative AA is not presented in any of the eutherian mammals.<br />
        N &ndash; otherwise neutral
      </dd>
    </dl>
    
    
    <h3>Variant Categorization</h3>
    <dl>
      <dt><strong>Pathogenic</strong></dt>
      <dd>mutation published in the literature as causing disease</dd>
      <dt><strong>Probably Pathogenic</strong></dt>
      <dd>classified as such by the dbSNP database</dd>
      <dt><strong>Unknown Significance</strong></dt>
      <dd>variant reported in dbSNP without a disease association</dd>
    </dl>
  
  	<h2>Getting Support</h2>
  	<p>Please <a href="javascript://" class="contact-popup" title="Contact us">get in touch</a> if you experience any issues with using this site.</p>
  	<br /><br />
  </div>
