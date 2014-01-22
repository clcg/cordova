<h1 id="logs-header"><?php echo $header; ?></h1>

<?php
$attributes = array('id' => 'form-logs');
echo form_open('logs', $attributes);
?>
  <button name="reset-logs" class="btn btn-danger btn-mini" type="submit" value="Reset">Reset logs</button>
  <small>Reset logs if this page is loading slowly</small>
</form>

<table class="table table-striped table-bordered table-condensed tablesorter">
  <thead>
	  <tr>
	  	<th>Activity</th>
	  	<th>Date</th>
	  	<th>Message</th>
	  </tr>
  </thead>
  <tbody>
	  <?php foreach ($logs as $log):?>
	  	<tr>
	  		<td><?php echo $log['activity']; ?></td>
	  		<td><?php echo $log['date']; ?></td>
	  		<td><?php echo $log['message']; ?></td>
	  	</tr>
	  <?php endforeach;?>
  </tbody>
</table>
