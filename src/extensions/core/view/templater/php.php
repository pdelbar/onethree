<?php

  /**
   * The templater concept is designed to make it possible to use different template languages and tools inside One.
   * This is the renderer supporting regular php.
   *
   * ONEDISCLAIMER
   **/
  class One_View_Templater_Php extends One_View_Templater_Abstract
  {

    public function setFile($filename)
    {
      parent::setFile($filename . '.php');
    }


    public function __set($name, $value)
    {
      $this->addData($name, $value);
    }

    public function __get($name)
    {
      return $this->getData()[$name];
    }


    /**
     * Parse the template or if $section is set and the section exists, parse the specified section
     *
     * @param string $section
     */
    public function render($section = null)
    {
      $path = One_Locator::locateUsing($this->getFile(), $this->getSearchPath());

//        if ($section!== null) $this->script->select($section);
      if (!$path) {
        throw new One_Exception("Could not load template " . $this->getFile());
      }

      ob_start();
      include $path;
      $output = ob_get_clean();

      return trim($output);
    }


  }