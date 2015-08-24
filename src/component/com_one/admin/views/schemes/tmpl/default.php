<?php
  defined('_JEXEC') or die;
?>

<?php if (!empty( $this->sidebar)) : ?>
<div id="j-sidebar-container" class="span2">
  <?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
  <?php else : ?>
  <div id="j-main-container">
    <?php endif;?>

  <div class="clearfix"></div>
  <?php
    foreach ($this->schemes as $scheme) {
      ?>
      <div class="span3" style="border: 1px solid #eeeeee;border-radius: 2px;box-shadow: 4px 4px 4px; padding: 0 8px;">
        <h3><?php echo $scheme->getName(); ?></h3>

      </div>
      <?php
    }
  ?>
</div>
