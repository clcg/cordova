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
 <li class="progtrckr-todo">Normalize</li>
 <li class="progtrckr-todo">Expert Curation</li>
 <li class="progtrckr-todo">Release Changes</li>
</ol> 



<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>

<script>
$(document).ready(function(){   
    $("#submit").click(function()
    {
      $("#before").hide();
      $("#after").show();
    });// you have missed this bracket
  });
</script>
<?php
  $attributes = array('class' => 'query_public_database',
                      'id' => 'query_public_database_form');
?>
<h1>Query Public Databases</h1>
<div id="before">
  <p>You have chosen to submit: </p>
  <?php echo $genes; ?>
  <br/>
  <?php echo form_open("variations/query_public_database/$time_stamp", $attributes)?>
  <input type="submit" value="submit" id="submit" name="submit" class="btn btn-success"></input>
  </form>
</div>
<div id="after" style="display:none">
  <p class="bg-success">Your request has been submitted. You will receive and email when your request has processed with instructions to continue.</p>
</div>
<br/>
