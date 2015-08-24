<?php

  /**
   * The templater concept is designed to make it possible to use different template languages and tools inside One.
   * This is the renderer supporting One_Script.
   *
   * ONEDISCLAIMER
   **/
  class One_View_Templater_Script extends One_View_Templater_Abstract
  {
    /**
     * One_Script object used to parse the template
     *
     * @var script
     */
    protected $script = NULL;

    /**
     * Class constructor
     *
     * @param array $searchpaths
     */
    public function __construct()
    {
      parent::__construct();
      $this->script = new One_Script();
    }

    public function setFile($filename)
    {
      parent::setFile($filename . '.html');

      if ($this->script->isError()) {
        throw new One_Exception_Script($this->script->error);
      }
    }

    /**
     * Parse the template or if $section is set and the section exists, parse the specified section
     *
     * @param string $section
     */

    public function render($section = null)
    {
      $oldSearchpath = One_Script_Factory::getSearchPath();

      One_Script_Factory::setSearchPath($this->getSearchPath());
      if ($this->useFile()) {
        $this->script->load($this->getFile());
        if ($section!== null) $this->script->select($section);
        if (!$this->script->isError()) {
          $output = $this->script->execute($this->getData());
        }
      }
      else {
        $this->script->select($section);
        $output = $this->script->executeString($this->getContent(), $this->getData());
      }

      One_Script_Factory::setSearchPath($oldSearchpath);
      One_Script_Content_Factory::clearContentCache();  // *** why is this here ? WTF !

      if ($this->script->isError()) {
        throw new One_Exception_Script($this->script->error);
      }

      return trim($output);
    }

    /**
     * I have no clue what this does.
     *
     * @see One_Template_Adapter_Abstract::formatDataKeys()
     */
    protected function formatDataKeys(array $data)
    {
      foreach ($data as $key => $val) {
        $oriKey = $key;
        $key    = $this->formatDataKey($key);

        if ($key != $oriKey) {
          $data[$key] = $data[$oriKey];
          unset($data[$oriKey]);
        }
      }

      return $data;
    }


    /**
     * I have no clue what this does.
     *
     * @see One_Template_Adapter_Abstract::formatDataKey()
     */
    protected function formatDataKey($key)
    {
      if (function_exists($key)) {
        $oriKey = $key;
        do {
          $key = '_' . $key;
        } while (array_key_exists($key, $this->getData()));

      }

      return $key;
    }
  }