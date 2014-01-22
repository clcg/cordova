<h1><?php echo lang('edit_user_heading');?></h1>
<p><?php echo lang('edit_user_subheading');?></p>

<?php 
$attr = array('autocomplete' => 'off');
echo form_open(uri_string(), $attr);
?>
      <p>
            <?php echo lang('edit_user_fname_label', 'first_name');?>
            <?php echo form_input($first_name);?>
      </p>

      <p>
            <?php echo lang('edit_user_lname_label', 'last_name');?>
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
            <?php echo lang('edit_user_company_label', 'company');?>
            <?php echo form_input($company);?>
      </p>

      <p>
            <?php echo lang('edit_user_phone_label', 'phone');?>
            <?php echo form_input($phone);?>
      </p>

      <p>
            <?php echo lang('edit_user_password_label', 'password');?>
            <?php echo form_input($password);?>
      </p>

      <p>
            <?php echo lang('edit_user_password_confirm_label', 'password_confirm');?>
            <?php echo form_input($password_confirm);?>
      </p>

	 <h3><?php echo lang('edit_user_groups_heading');?></h3>
	<?php foreach ($groups as $group):?>
	<label class="checkbox">
	<?php
		$gID=$group['id'];
		$checked = null;
		$item = null;
		foreach($currentGroups as $grp) {
			if ($gID == $grp->id) {
				$checked= ' checked="checked"';
			break;
			}
		}
	?>
	<input type="checkbox" name="groups[]" value="<?php echo $group['id'];?>"<?php echo $checked;?>>
	<?php echo $group['name'];?>
	</label>
	<?php endforeach?>

      <?php echo form_hidden('id', $user->id);?>
      <?php echo form_hidden($csrf); ?>

      <p><?php echo form_submit('submit', lang('edit_user_submit_btn'));?></p>

<?php echo form_close();?>
