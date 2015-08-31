<? xml version = "1.0" encoding = "utf-8"?>
<extension
  type="package"
  version="3.0"
  method="upgrade">
  <?php

    list($ignore, $root, $release) = $argv;

  ?>

  <name>one|content package</name>
  <packagename>one</packagename>
  <version><?php echo $release; ?></version>
  <packager>Paul Delbar</packager>
  <packagerurl>http://pauldelbar.com/</packagerurl>

  <author>Paul Delbar</author>
  <creationDate>2015-08</creationDate>
  <copyright>Copyright (C) 2015 Paul Delbar. All rights reserved.</copyright>
  <license>http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL</license>
  <authorEmail>paul.delbar@gmail.com</authorEmail>
  <authorUrl>pauldelbar.com</authorUrl>
  <version><?php echo $release; ?></version>
  <description>one|content is a rich content display and management component</description>

  <files folder="packages">
    <file type="plugin" id="plg_one" group="system">plg_sys_one.zip</file>
    <file type="component" id="com_one">com_one.zip</file>
    <?php
      foreach (explode(',','core,joomla,script') as $ext) :
        ?>
        <file type="library" id="one/lib/<?php echo $ext; ?>">lib_one_lib_<?php echo $ext; ?>.zip</file>
      <?php
      endforeach;
    ?>
  </files>

</extension>
