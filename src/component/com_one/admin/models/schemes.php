<?php
  defined('_JEXEC') or die;

  /**
   * Class OneModelSchemes
   *
   * Joomla model wrapper to retrieve all schemes
   */
  class OneModelSchemes extends JModelList
  {
    protected $schemes;
    protected $groups;

    public function __construct($config = array())
    {
      parent::__construct($config);
      $this->schemes = array( );

      $schemeNames = One_Repository::getSchemeNames();
      foreach ($schemeNames as $name) {
        $scheme = One_Repository::getScheme($name);
        if ($scheme->get('info.options.internal') != 'internal') {
          $group = $scheme->get('info.group', 'all');
          if (!isset($this->schemes[$group])) $this->schemes[$group] = array();
          $this->schemes[$group][] = $scheme;
          $this->schemes['all'][] = $scheme;
        }
      }

      $this->groups  = array_keys($this->schemes);
    }

    protected function populateState($ordering = null, $direction = null)
    {
      $group = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
      $this->setState('filter.state', $group);
    }


    public function getSchemes()
    {
      $selectedGroup = $this->getState('filter.state');
      if (!$selectedGroup) $selectedGroup = 'all';
      return $this->schemes[$selectedGroup];
    }

    public function getGroups()
    {
      return $this->groups;
    }
  }