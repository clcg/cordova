      </div> <!-- end content -->
    </div> <!-- end corpus -->
    
    <div id="copyright">
    	<p>
    		<?php echo $footer_info ?> | <a href="<?php echo site_url('login') ?>">Curators</a>
    	</p>
    	<p>
         Interested in setting up your own variation database? <a href="javascript://" class="contact-popup contact-cbcb">Let us know</a> or check out our free <a href="https://github.com/clcg/cordova">Cordova</a> software!
    	</p>
    </div>

    <?php
    $attributes = array('style' => 'display:none',
                        'id' => 'section-contact');
    echo form_open('email', $attributes);
    ?>
        <input type="submit" name="send" id="contact-send" value="send" style="display:none" />
        <label for="contact-name">Name</label>
        <input type="text" name="name" id="contact-name" /> 
        <div id="contact-name-error" style="display:none;"></div>

        <label for="contact-email">Email Address</label>
        <input type="text" name="email" id="contact-email" />
        <div id="contact-email-error" style="display:none;"></div>
        
        <label for="contact-comments">Questions &amp; Comments</label>
        <textarea name="comments" id="contact-comments" rows="12"></textarea>
        <div id="contact-comments-error" style="display:none;"></div>

        <p class="label-checkbox-pair">
          <input type="checkbox" id="contact-cbcb" name="contact-cbcb" value="contact-cbcb" />
          <label for="contact-cbcb">
            I'm interested in setting up my own variation database.
          </label>
        </p>

        <a href="javascript://" id="contact-submit">Submit</a>
        <div id="contact-thanks"></div>
        <small><a href="#" class="simplemodal-close">Close this dialog.</a></small>
    </form>
    
    <script type="text/javascript" src="<?php echo site_url('assets/public/js/jquery.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo site_url('assets/public/js/jquery.cookie.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo site_url('assets/public/js/jquery.simplemodal.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo site_url('assets/public/js/jquery.tablesorter.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo site_url('assets/public/js/jquery.tipsy.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo site_url('assets/public/js/jquery.shadow.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo site_url('assets/public/js/script.js'); ?>"></script>
  
  </body>
</html>
