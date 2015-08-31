<?php
  defined('_JEXEC') or die;

  class OneViewSchemes extends JViewLegacy
  {
    protected $schemes;

    protected $groups;

    protected $state;

    public function display($tpl = null)
    {
      $this->schemes = $this->get('Schemes');
      $this->groups= $this->get('Groups');
      $this->state = $this->get('State');

      $this->addToolbar();
      $this->sidebar = JHtmlSidebar::render();

      parent::display($tpl);
    }

    protected function addToolbar()
    {
//      $canDo = FolioHelper::getActions();
//      $bar   = JToolBar::getInstance('toolbar');
//
//      JToolbarHelper::title(JText::_('COM_FOLIO_MANAGER_FOLIOS'), '');
//      JToolbarHelper::addNew('folio.add');
//      if ($canDo->get('core.edit')) {
//        JToolbarHelper::editList('folio.edit');
//      }
//      if ($canDo->get('core.admin')) {
//        JToolbarHelper::preferences('com_folio');
//      }

      JToolbarHelper::title('one|content: Schemes');

      JToolbarHelper::custom('tools', $icon = 'cogs', 'cogs', 'Tools');

      JHtmlSidebar::setAction('index.php?option=com_one&view=schemes');
      JHtmlSidebar::addEntry(
        'All schemes',
        'index.php?option=com_one&view=schemes&group=all',
        'schemes'
      );
      foreach ($this->groups as $group ) {
        if ($group != 'all') {
          JHtmlSidebar::addEntry(
            $group,
            'index.php?option=com_one&view=schemes&group=' . $group,
            'schemes'
          );
        }
      }
    }
  }