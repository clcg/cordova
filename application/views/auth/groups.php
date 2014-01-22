<h1><?php echo lang('index_groups_th');?></h1>

<table class="table table-striped table-bordered table-condensed tablesorter">
  <thead>
	  <tr>
	  	<th><?php echo lang('index_groups_th');?></th>
	  	<th><?php echo lang('create_group_validation_desc_label');?></th>
	  	<th><?php echo lang('index_action_th');?></th>
	  </tr>
  </thead>
  <tbody>
	<?php foreach ($groups as $group):?>
		<tr>
			<td><?php echo $group->name;?></td>
			<td><?php echo $group->description;?></td>
			<td><?php echo anchor("auth/edit_group/".$group->id, 'Edit') ;?><br /><?php echo anchor("auth/delete_group/".$group->id, 'Delete') ;?></td>
		</tr>
	<?php endforeach;?>
  </tbody>
</table>
