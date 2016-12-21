
<style>

<!-- Progress with steps -->

ol.progtrckr {
margin: 0;
padding: 0;
         list-style-type: none;
}

ol.progtrckr li {
display: inline-block;
         text-align: center;
         line-height: 3em;
}

ol.progtrckr[data-progtrckr-steps="2"] li { width: 49%; }
ol.progtrckr[data-progtrckr-steps="3"] li { width: 33%; }
ol.progtrckr[data-progtrckr-steps="4"] li { width: 24%; }
ol.progtrckr[data-progtrckr-steps="5"] li { width: 19%; }
ol.progtrckr[data-progtrckr-steps="6"] li { width: 16%; }
ol.progtrckr[data-progtrckr-steps="7"] li { width: 14%; }
ol.progtrckr[data-progtrckr-steps="8"] li { width: 12%; }
ol.progtrckr[data-progtrckr-steps="9"] li { width: 11%; }

ol.progtrckr li.progtrckr-done {
color: black;
       border-bottom: 4px solid yellowgreen;
}
ol.progtrckr li.progtrckr-todo {
color: silver; 
       border-bottom: 4px solid silver;
}

ol.progtrckr li:after {
content: "\00a0\00a0";
}
ol.progtrckr li:before {
position: relative;
bottom: -2.5em;
float: left;
left: 50%;
      line-height: 1em;
}
ol.progtrckr li.progtrckr-done:before {
content: "\2713";
color: white;
       background-color: yellowgreen;
height: 1.2em;
width: 1.2em;
       line-height: 1.2em;
border: none;
        border-radius: 1.2em;
}
ol.progtrckr li.progtrckr-todo:before {
content: "\039F";
color: silver;
       background-color: white;
       font-size: 1.5em;
bottom: -1.6em;
}

</style>

<ol class="progtrckr" data-progtrckr-steps="5">
     <li class="progtrckr-done">Upload Genes</li>
         <li class="progtrckr-done">Gather Variants</li>
             <li class="progtrckr-done">Normalize</li>
                 <li class="progtrckr-done">Expert Curation</li>
                     <li class="progtrckr-todo">Release Changes</li>
                     </ol>


<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
<script src="<?php echo site_url("assets/editor/js/bootstrap.min.js") ?>"></script>
<script src="<?php echo site_url("assets/editor/js/bootstrap-rowlink.min.js") ?>"></script>
<script src="<?php echo site_url("assets/editor/js/jquery.tablesorter.min.js") ?>"></script>
<script src="<?php echo site_url("assets/editor/js/script.js") ?>"></script>


<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script type="text/javascript">

// Ajax post
$(document).ready(function() {
$("#diffStats").click(function(event) {
event.preventDefault();

$.ajax(
  url: "<?php echo base_url() . 'variations/get_diff_stats'; ?>",
  type: "get",
  success: function(res) {
    alert(res);
    if (res)
    {
      alert("success");
      // Show Entered Value
      jQuery("div#result").show();
      jQuery("div#value").html(res.username);
      jQuery("div#value_pwd").html(res.pwd);
    }
  },
  'json'
);
});
});
</script>

<h1>Expert Curation</h1>
<!--<button id=diffStats>Diff Stats</button>-->
<br/>
<?php
  $attributes = array('id'    => 'form_expert_curation',
                       'class' => 'rounded',
                      );
   //echo $error;
  echo form_open_multipart("variations/expert_curation", $attributes);
?>
  <div>
    <p>If you wish to override any information gathered through this pipeline please upload a .csv file with the information you wish to change. The variation must match the existing file variation name exactly. These curations will be maintainted in the Cordova database for easy application to variations in the queue.</p>
  </div>
  <div class = "span6">
    <p>Upload a csv file describing your expert curation data. Any entries with matching variant to an exsisting variant in the Cordova expert_curations data table  will be updated instead of inserted into the database.
    <br/>
    <br/>Download <a type="application/octet-stream" href="http://cordova-dev.eng.uiowa.edu/cordova_sites_ah/rdvd/expertDataTemplate.csv" download="expertDataTemplate.csv">Template</a></p>
    <input type="file" id="file" name="file"/>
    <br/>
    <input type="submit" value="Upload" name="file-expert" class="btn btn-success"/>
    <br/>
    <br/>
    <!--<label class='control-label' for='gene'>Gene</label> 
    <input class='align-right' type='text' name='gene' id='gene'></input>
    <label class='control-label' for='variations'>Variation</label> 
    <input class='align-right' type='text' name='variation' id='variation'></input>
    <label class='control-label' for='pathogenicity'>Pathogenicity</label> 
    <input class='align-right' type='text' name='pathogenicity' id='pathogenicity'></input>
    <label class='control-label' for='pubmed'>PubMed ID</label> 
    <input class='align-right' type='text' name='pubmed' id='pubmed'></input>
    <label class='control-label' for='disease'>Disease</label> 
    <input class='align-right' type='text' name='disease' id='disease'></input>
    <br>
    <br>
    -->
    After expert curations have been submitted, select Apply Curations to apply these curations to the data in the queue prior to release.
    <br>
    <br>
    <input type="submit" value="Apply Curations" name="apply-curations" class="btn btn-success"/>
    <br>
    <br>
    <br>
  </div>
  </form>
  <div class = "span1">
  </div>
  <div class = "span3">
    <br/>Download <a href="http://cordova-dev.eng.uiowa.edu/cordova_sites_ah/rdvd/variations/get_queue_data" target="blank">Queue Data</a>
    <br/>Download <a href="http://cordova-dev.eng.uiowa.edu/cordova_sites_ah/rdvd/variations/get_expert_data" target="blank">Expert Data</a>
    <br/>Download <a href="http://cordova-dev.eng.uiowa.edu/cordova_sites_ah/rdvd/variations/get_expert_log" target="blank">Expert Log</a>
    <br/>Download <a type="application/octet-stream" href="http://cordova-dev.eng.uiowa.edu/cordova_sites_ah/rdvd/expertDataTemplate.csv" download="expertDataTemplate.csv">Template</a></p>
  </div>
  <!--
    <p>This is an example of formatting required for the input file.</p>
    <img src="<?php echo site_url('assets/editor/img/expertDataExample2.jpg'); ?>">
    <p>The variation name must match exactly to that in the file. Please surround each point with quotations and separate with a comma. Please insert a new line between each row.</p>
  </div>-->
