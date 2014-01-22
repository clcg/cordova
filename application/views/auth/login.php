<div class="row-fluid">
  <?php
  $attributes = array('class' => 'form-login span4 rounded');
  echo form_open('login', $attributes);
  ?>
    <h2 class="form-login-heading">Log in</h2>
    <input name="identity" type="text" class="input-block-level" value="<?php echo set_value('identity'); ?>" placeholder="Username" autofocus>
    <input name="password" type="password" class="input-block-level" placeholder="Password">
    <label class="checkbox">
      <input name="remember" type="checkbox" value="remember" <?php echo $rememberme ?>> Remember me
    </label>
    <input name="submit" class="btn btn-medium btn-primary" type="submit" value="Log in">
  </form>

  <div class="span4 offset4 rounded <? echo hidden(empty($extauths)); ?>">
    <div id="extauths-wrapper" class="rounded">
      <h4>Using another form of authentication?</h4>
      <ul id="extauths-list" class="nav nav-tabs nav-stacked">
        <?php foreach ($extauths as $auth => $title): ?>
          <li>
            <a href="<?php echo site_url("auth/ext/$auth"); ?>">
              <i class="icon-circle-arrow-right icon-white"></i>&nbsp;&nbsp;&nbsp;<?php echo $title; ?>
            </a>
          </li>
        <?php endforeach ?>
      </ul>
    </div>
  </div>
</div>
