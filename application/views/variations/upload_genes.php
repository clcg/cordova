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
        <li class="progtrckr-todo">Gather Variants</li>
            <li class="progtrckr-todo">Normalize</li>
                <li class="progtrckr-todo">Expert Curation</li>
                    <li class="progtrckr-todo">Release Changes</li>
                    </ol>

<h1>Upload Genes</h1>
<br/>
<?php
$attributes = array('id'    => 'form_upload_genes',
                    'class' => 'rounded',
                   );
//echo $error;
echo form_open_multipart('variations/upload_genes/$time_stamp', $attributes);
?>
    <div class="span4">
      <p>To begin the process of initializing your variation database please upload a gene file</p>
    
      <input type="file" id="file" name="file"/>
      <br/>
      <br/>
      <input type="submit" value="Upload" name="file-submit" class="btn btn-success"/>
      <br/>
    </div>
  </form>
    <div class="span2">
      Or..
    </div>
  <?php echo form_open('variations/upload_genes', $attributes);?>
    <div class="span4">
      <p> Enter genes of interest in the text box. Each gene entered in the text box should be separated by a new line.</b><p/>
      <textarea rows="4" cols="100" id="text" name="text"></textarea>
    </div>
    <br/><br/>
    <input type="submit" value="Upload" name="text-submit" class="btn btn-success"/>
    </form>
<!--
progress bar maybe?
--!>
