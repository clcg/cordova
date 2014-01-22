<h1><?php echo lang('create_user_heading');?></h1>
<p><?php echo lang('create_user_subheading');?></p>

<?php 
$attr = array('autocomplete' => 'off');
echo form_open("auth/create_user", $attr);
?>

      <p>
            <?php echo lang('create_user_fname_label', 'first_name');?>
            <?php echo form_input($first_name);?>
      </p>

      <p>
            <?php echo lang('create_user_lname_label', 'first_name');?>
            <?php echo form_input($last_name);?>
      </p>

      <p>
            <?php echo lang('forgot_password_username_identity_label', 'username');?>
            <?php echo form_input($username);?>
      </p>

      <p>
            <?php echo lang('create_user_email_label', 'email');?>
            <?php echo form_input($email);?>
      </p>


      <p>
            <?php echo lang('create_user_company_label', 'company');?>
            <?php echo form_input($company);?>
      </p>


      <p>
            <?php echo lang('create_user_phone_label', 'phone');?>
            <?php echo form_input($phone);?>
      </p>

      <p>
            <?php echo lang('create_user_password_label', 'password');?>
            <?php echo form_input($password);?>
            <small style="color:red;">Leave this blank if the user will ONLY use an external method of authentication (i.e. University login, LDAP, etc.)</small>
      </p>

      <p>
            <?php echo lang('create_user_password_confirm_label', 'password_confirm');?>
            <?php echo form_input($password_confirm);?>
      </p>

      <p>
        <label class="checkbox">
          <input name="externalauth" type="checkbox" value="externalauth" > This user may use an external method of authentication (i.e. University login, LDAP, etc.)
        </label>
      </p>

      <p><?php echo form_submit('submit', lang('create_user_submit_btn'));?></p>

<?php echo form_close();?>
