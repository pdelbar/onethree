<?php

  /**
   * The  One_View_Locator is used to determine the correct view search pattern
   */
  class One_View_Locator
  {
    public static function getPatternForScheme($schemeName)
    {
      return "%ROOT%/views/";
    }

  }