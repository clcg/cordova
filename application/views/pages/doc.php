  <div class="width750">
  	<h2>Basic call</h2>
  	<p>Say you'd like data on three variants in the database. This is the most basic call:</p>
  	<code class="api-call"><?php echo base_url(); ?>api?terms=<strong class="red">chr6:25850845</strong>,<strong class="green">chr4:88533540</strong>,<strong class="blue">chr2:179325735</strong></code>
  	<p>And here's the output</p>
<pre>
#id hgvs_protein_change hgvs_nucleotide_change  variantlocale variation               ...
1562	NM_006632:p.Gly201Arg	NM_006632:c.601G>A	EXON7	chr6:25850845:C>T	...
249	NM_014208:p.Arg68Trp	NM_014208:c.202A>T	EXON4	chr4:88533540:A>T	...
2193	NM_001042702.3:p.Arg265Gly	NM_001042702.3:c.793C>G	EXON7	chr2:179325735:C>G	...
</pre>
  	<p>You'll see a lot more columns, but that should give you an idea. By default,</p>
  	<ul>
  	   <li>The output MIME is <strong>text/plain</strong></li>
  	   <li>The output format is <strong>tab-delimited</strong></li>
  	   <li>Data is from the <strong>most recent version</strong> of the database (currently <?php echo $version; ?>)</li>
  	</ul>
  	
  	<h3>Fields</h3>
    <table border="0" cellspacing="0" cellpadding="0" class="api-columns">
      <caption>Regular Fields</caption>
      <thead>
        <tr>
          <th>Field</th>
          <th>Explanation</th>
          <th>Field</th>
          <th>Explanation</th>
        </tr>
      </thead>
      <tbody>
        <?php for($i=0; $i < count($reg_fields); $i+=2): // print 2 fields at a time ?>
        <tr>
          <td class="api-columns-field"><code><?php echo isset($reg_keys[$i]) ? $reg_keys[$i] : NULL; ?></code></td>
          <td><?php echo isset($reg_keys[$i]) ? $reg_fields[$reg_keys[$i]] : NULL; ?></td>
          <td class="api-columns-field"><code><?php echo isset($reg_keys[$i+1]) ? $reg_keys[$i+1] : NULL; ?></code></td>
          <td><?php echo isset($reg_keys[$i+1]) ? $reg_fields[$reg_keys[$i+1]] : NULL; ?></td>
        </tr>
        <?php endfor; ?>
      </tbody>
    </table>
  	
    
    <p>
      For the table below, fields prefixed with 
      <ul>
        <li><strong>evs</strong> show <a href="http://evs.gs.washington.edu/EVS/HelpDescriptions.jsp?tab=SnpHelpTab">Exome Variant Server (EVS)</a> allele counts</li>
        <li><strong>tg</strong> show <a href="http://www.1000genomes.org/about#ProjectSamples">1000 Genomes</a> allele counts</li>
        <li><strong>otoscope</strong> show the <a href="http://www.healthcare.uiowa.edu/labs/morl/otoscope/home.html">MORL&#8217;s OtoScope&trade;</a> allele counts</li>
      </ul>
    <p>
      The fields also come in pairs: <code>{field}_<strong>ac</strong></code> and <code>{field}_<strong>an</strong></code>. Fields suffixed with &#8220;<code><strong>ac</strong></code>&#8221; show <em>allele count</em>, while those with &#8220;<code><strong>an</strong></code>&#8221; show <em>total allele count</em>. For example, <code><span class="blue">tb</span>_<span class="green">ibs</span>_<span class="orange">ac</span></code> shows <span class="blue">1000 Genomes</span> <span class="orange">allele counts</span> for <span class="green">Iberian populations in Spain</span>.
    </p>
    <table border="0" cellspacing="0" cellpadding="0" class="api-columns">
      <caption>Population Frequency Fields</caption>
      <thead>
        <tr>
          <th>Field</th>
          <th>Population</th>
          <th>Field</th>
          <th>Population</th>
        </tr>
      </thead>
      <tbody>
        <?php for($i=0; $i < count($pop_fields); $i+=2): // print 2 fields at a time ?>
        <tr>
          <td class="api-columns-field-narrow"><code><?php echo isset($pop_keys[$i]) ? $pop_keys[$i] : NULL; ?></code></td>
          <td><?php echo isset($pop_keys[$i]) ? $pop_fields[$pop_keys[$i]] : NULL; ?></td>
          <td class="api-columns-field-narrow"><code><?php echo isset($pop_keys[$i+1]) ? $pop_keys[$i+1] : NULL; ?></code></td>
          <td><?php echo isset($pop_keys[$i+1]) ? $pop_fields[$pop_keys[$i+1]] : NULL; ?></td>
        </tr>
        <?php endfor; ?>
      </tbody>
    </table>
  	
  	<br />
  
  	<h2>Tweaking the Call</h2>
  
  	<table border="0" cellspacing="0" cellpadding="0" id="api-params">
  	<caption>List of API parameters</caption>
  	<thead>
  	  <tr>
  	    <th scope="col" width="110px">Tweak</th>
  	    <th scope="col">Description</th>
  	    <th scope="col" width="85px">Append to call</th>
  	    <th scope="col" width="85px">Possible values</th>
  	    <th scope="col" width="85px">Default Value<br /><small>(i.e. if unspecified)</small></th>
  	  </tr>
  	</thead>
  	<tbody>
  	  <tr>
  	    <th scope="row">Search Type</th>
  	    <td class="api-desc">
    			Search by genomic position or by gene. 
    			<ul>
    			  <li><code>position</code> will perform a fuzzy search, while <code>exactposition</code> won&#8217;t.</li>
    			  <li><code>genelist</code> doesn&#8217;t require search terms and will give you a list of all genes and the number of variants per gene.</li>
    			  <li>This is true for <code>variantlist</code> list, which will give you a full list of all variants (and nothing else) in the database.</li>
    			</ul>
  		  </td>
  	    <td>&amp;type=</td>
  	    <td>
    			<ul>
    				<li>position</li>
    				<li>exactposition</li>
    				<li>gene</li>
    				<li>genelist</li>
    				<li>variantlist</li>
    			</ul>
  		  </td>
  	    <td>position</td>
  	  </tr>
  	  <tr>
  	    <th scope="row">Output Format</th>
  	    <td class="api-desc">
  			  Format results as JSON, XML, comma-separated (CSV) or tab-delimited values. Please note that the <code>vcf</code> output type is for type <code>variantlist</code> only.
  		  </td>
  	    <td>&amp;format=</td>
  	    <td>
    			<ul>
    				<li>json</li>
    				<li>xml</li>
    				<li>csv</li>
    				<li>tab</li>
    				<li>vcf</li>
    			</ul>
  		  </td>
  	    <td>tab</td>
  	  </tr>
  	  <tr>
  	    <th scope="row">Output Method</th>
  	    <td class="api-desc">
  			  If set to <code>download</code>, headers appropriate for the chosen output format are sent to the client. If unspecified or set explicitly to <code>plain</code>, the output sent as plain text (the <code>text/plain</code> MIME-type.)
  		  </td>
  	    <td>&amp;method=</td>
  	    <td>
    			<ul>
    				<li>plain</li>
    				<li>download</li>
  			  </ul>
  	 	  </td>
  	    <td>plain</td>
  	  </tr>
  	  <tr>
  	    <th scope="row">Data Version</th>
  	    <td class="api-desc">
  			  Specify a particular version the database that you would like to retrieve data for. 
  		  </td>
  	    <td>&amp;version=</td>
  	    <td>
          See the &#8220;Versions&#8221; table below.
  		  </td>
  	    <td><?php echo $version; ?></td>
  	  </tr>
  	</tbody>
  	</table>
  	<br />
  
    <h3>Versions</h3>
    <table border="0" cellspacing="0" cellpadding="0" id="api-versions">
      <thead>
        <tr>
          <th scope="col">Version</th>
          <th scope="col">Variants</th>
          <th scope="col">Genes</th>
          <th scope="col">Updated</th>
        </tr>
      </thead>
      <tbody>
        <?php
          foreach ($versions as $v) {
            echo '<tr>
                    <th scope="row">'.$v->version.'</th>
                    <td>'.number_format($v->variants).'</td>
                    <td>'.number_format($v->genes).'</td>
                    <td>'.$v->updated.'</td>
                  </tr>';
  
          }
        ?>      
      </tbody>
    </table>
    <br />
  
  	<h3>Examples</h3>
  	<p>To display JSON-formatted results, simply add <code>&amp;format=json</code> to the call
  	<p><code class="api-call api-tweak-appended"><?php echo base_url(); ?>api?terms=chr6:25850845,chr10:88533540<strong class="red">&amp;format=json</strong></code></p>
  	<p>Here's the output in plain text:</p>
<pre>
  {
      "chr6:25850845":{
          "pathogenicity":"Pathogenic",
          "disease":"Glycogen storage disease 1c ?",
          "pubmed_id":"15505377",
          .
          .
          .
      },
      "chr4:88533540":{
          "pathogenicity":"probable-pathogenic",
          "disease":"",
          "pubmed_id":"",
          .
          .
          .
      }
  }
</pre>
  	<p>To download the search results in CSV format,</p>
  	<p><code class="api-call api-tweak-appended"><?php echo base_url(); ?>api?terms=chr6:25850845,chr10:73375330<strong class="red">&amp;format=csv</strong><strong class="green">&amp;method=download</strong></code></p>
  	<p>To get all variants for the gene GJB6 in XML</p>
  	<p><code class="api-call api-tweak-appended"><?php echo base_url(); ?>api?<strong class="black">terms=gjb6</strong><strong class="blue">&amp;type=gene</strong><strong class="red">&amp;format=xml</strong><strong class="green">&amp;method=download</strong></code></p>
  	<p>To get all genes and number of variants per gene in JSON</p>
  	<p><code class="api-call api-tweak-appended"><?php echo base_url(); ?>api?<strong class="blue">&amp;type=genelist</strong><strong class="red">&amp;format=json</strong></code></p>
  	<br />
  
  	<h2>Support</h2>
  	<p>Please <a href="javascript://" class="contact-popup" title="Contact us">get in touch</a> if you experience any issues with API access.</p>
  	<br /><br />
  </div>
