<?php
  defined('_JEXEC') or die;
?>

<?php if (!empty($this->sidebar)) : ?>
<div id="j-sidebar-container" class="span2">
  <?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
  <?php else : ?>
  <div id="j-main-container">
    <?php endif; ?>

    <div class="clearfix"></div>
    Here be tools
  </div>
</div>
