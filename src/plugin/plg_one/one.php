<?php
  /**
   * ONEDISCLAIMER
   */

// no direct access
  defined('_JEXEC') or die('Restricted access');

  jimport('joomla.plugin.plugin');


  /**
   * One Plugin
   */
  class plgSystemOne extends JPlugin
  {
    public function onAfterInitialise()
    {
      // read folder locations from plugin settings
      $oneLibFolder    = JPATH_SITE . '/libraries/one/lib/';
      $oneCustomFolder = JPATH_SITE . '/libraries/one/custom/';

      // bootstrap one|content
      require_once $oneLibFolder . 'core/bootstrap.php';
      One_Bootstrap::bootstrap($oneLibFolder, $oneCustomFolder);

      // one|content for Joomla uses different views for front/backend and per language
      // which requires a particular setup of the locator pattern used for these

      $application = 'site';
      $app         = JFactory::getApplication();
      if (strpos($app->getName(), 'admin') !== false) {
        $application = 'admin';
      }
      One_Config::set('app.name', $application);

      // pickup language setting
      $language = JFactory::getLanguage()->getTag();
      One_Config::set('app.language', $language);

      // setup locator tokens
      $locatorTokens = array(
        '%APP%'  => One_Config::get('app.name'),
        '%LANG%' => One_Config::get('app.language'),
      );
      One_Config::set('locator.tokens', $locatorTokens);
      One_Config::set('view.locator', 'One_View_Locator_Joomla');

      // set the templater. You have the choice between One_View_Templater_Script and One_View_Templater_Php,
      // the latter being a standard PHP template hndler
      // *** TODO: needs to load this from plugin parameters
      One_Config::set('view.templater', 'One_View_Templater_Script');


      // debug behaviour
      One_Config::set('debug.exitOnError', $this->params->get('exitOnError'));
      One_Config::set('debug.query', $this->params->get('enableDebug', 0));


      // special form subfolder to use
      One_Config::set('form.chrome', $this->params->get('formChrome', ''));
    }


//    public function onAfterRender()
//    {
//      $buffer = JResponse::getBody();
//      // render javascript/css files/codes that need to be included
//      $buffer = One_Vendor::getInstance()->renderLoadsOnContent($buffer);
//
//      JResponse::setBody($buffer);
//    }
  }
