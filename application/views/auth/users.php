<h1><?php echo lang('index_heading');?></h1>
<p><?php echo lang('index_subheading');?></p>

<table class="table table-striped table-bordered table-condensed tablesorter">
  <thead>
	  <tr>
	  	<th><?php echo lang('index_fname_th');?></th>
	  	<th><?php echo lang('index_lname_th');?></th>
	  	<th><?php echo lang('forgot_password_username_identity_label');?></th>
	  	<th><?php echo lang('index_email_th');?></th>
	  	<th><?php echo lang('index_groups_th');?></th>
	  	<th><?php echo lang('index_status_th');?></th>
	  	<th><?php echo lang('index_action_th');?></th>
	  </tr>
  </thead>
  <tbody>
	  <?php foreach ($users as $user):?>
	  	<tr>
	  		<td><?php echo $user->first_name;?></td>
	  		<td><?php echo $user->last_name;?></td>
	  		<td><?php echo $user->username;?></td>
	  		<td><?php echo $user->email;?></td>
	  		<td>
	  			<?php foreach ($user->groups as $group):?>
	  				<?php echo $group->name; ?><br />
          <?php endforeach?>
	  		</td>
	  		<td><?php echo ($user->active) ? anchor("auth/deactivate/".$user->id, lang('index_active_link')) : anchor("auth/activate/". $user->id, lang('index_inactive_link'));?></td>
	  		<td><?php echo anchor("auth/edit_user/".$user->id, 'Edit');?><br /><?php echo anchor("auth/delete_user/".$user->id, 'Delete');?></td>
	  	</tr>
	  <?php endforeach;?>
  </tbody>
</table>
