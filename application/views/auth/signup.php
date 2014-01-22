<?php
$attributes = array('class' => 'form-signup rounded');
echo form_open('signup', $attributes);
?>
  <h2 class="form-signup-heading">Sign up</h2>
  <input name="first_name" type="text" class="input-large" value="<?php echo set_value('first_name'); ?>" placeholder="First name" autofocus>
  <input name="last_name" type="text" class="input-large" value="<?php echo set_value('last_name'); ?>" placeholder="Last name">
  <input name="email" type="text" class="input-xlarge" value="<?php echo set_value('email'); ?>" placeholder="Email address">
  <input name="username" type="text" class="input-xlarge" value="<?php echo set_value('username'); ?>" placeholder="Username">
  <input name="password" type="password" class="input-xlarge" placeholder="Password">
  <div>
    <input name="submit" class="btn btn-medium btn-primary" type="submit" value="Sign up">
  </div>
</form>
