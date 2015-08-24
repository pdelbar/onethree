<?php

  /**
   * The One_View_Locator is used to determine the correct view search pattern for Joomla
   */
  class One_View_Locator_Joomla extends One_View_Locator
  {
    public static function getPatternForScheme($schemeName)
    {
      return "%ROOT%/views/"
      . "{" . ($schemeName != '' ? "%APP%/" . $schemeName . "," : "") . "%APP%,default}" . DIRECTORY_SEPARATOR
      . "{%LANG%/,}";
    }

  }