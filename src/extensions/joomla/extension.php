<?php

  One_Config::set('extensions.joomla', 'One_Extension_Joomla');

  class One_Extension_Joomla extends One_Extension
  {
    public static function onAfterInitialise($arguments = array())
    {
      parent::onAfterInitialise($arguments);

      // set default toolset used in backend
      // *** TODO: should be in the support pack for the admin component, to be added to the locator pattern
      // by the admin component (or enabled in the plugin to support the one admin component

//      One_Button::setToolset('joomla');

      // set DOM type. Not sure yet whether this is really a cool thing to do. This could override the standard
      // dom setting, and should be oved to the plugin initialiser
      One_Config::set('dom.type','joomla');
    }
  }
