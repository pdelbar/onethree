<?php
  defined('_JEXEC') or die;

  class OneViewTools extends JViewLegacy
  {
    public function display($tpl = null)
    {
      $this->addToolbar();
      $this->sidebar = JHtmlSidebar::render();

      parent::display($tpl);
    }

    protected function addToolbar()
    {
      JToolbarHelper::title('one|content: Tools');

      JHtmlSidebar::setAction('index.php?option=com_one&view=tools');
      JHtmlSidebar::addEntry(
        'Development tools',
        'index.php?option=com_one&view=tool&topic=dev',
        'tools'
      );
    }
  }