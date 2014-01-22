<div class="row-fluid">
  <?php
  $attributes = array('class' => 'form-login span6 rounded');
  echo form_open('forgotpassword', $attributes);
  ?>
    <h2 class="form-login-heading">Forgot password</h2>
    <p>Please enter your email address so we can send you an email to reset your password.</p>
    <input name="identity" type="text" class="input-block-level" value="<?php echo set_value('identity'); ?>" placeholder="Email">
    <input name="submit" class="btn btn-medium btn-primary" type="submit" value="Submit">
  </form>
</div>
