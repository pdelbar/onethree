<?xml version="1.0" encoding="utf-8"?>
<extension
  type="library"
  version="3.0"
  method="upgrade">
  <?php

    list($ignore,$root,$folder,$extension,$release) = $argv;

  ?>

  <name>one|content extension '&lt;i&gt;<?php echo $extension; ?>&lt;/i&gt;'</name>
  <author>Paul Delbar</author>
  <creationDate>2015-08</creationDate>
  <copyright>Copyright (C) 2015 Paul Delbar. All rights reserved.</copyright>
  <license>http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL</license>
  <authorEmail>paul.delbar@gmail.com</authorEmail>
  <authorUrl>pauldelbar.com</authorUrl>
  <version><?php echo $release; ?></version>
  <description>one|content is a rich content display and management component</description>

  <libraryname>one/<?php echo $folder; ?>/<?php echo $extension; ?></libraryname>

  <files><?php
      $iterator = new DirectoryIterator($root . "/../src/extensions/" . $extension);
      foreach ($iterator as $fileinfo) {
        if ($fileinfo->isDot()) continue;
        if ($fileinfo->isDir()) {
          echo "\n\t<folder>" . $fileinfo->getFilename() . "</folder>";
        }
        else {
          echo "\n\t<file>" . $fileinfo->getFilename() . "</file>";
        }
      }
    ?>

  </files>

</extension>