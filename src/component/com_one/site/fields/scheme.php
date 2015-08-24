<?php
// Check to ensure this file is included in Joomla!
  defined('JPATH_BASE') or die;

  if (!JPluginHelper::isEnabled('system', 'one')) {
    throw new Exception("The one|content system plugin is not enabled.");
  };

  jimport('joomla.form.formfield');

  class JFormFieldScheme extends JFormField
  {
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.6
     */
    protected $type = 'Scheme';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     * @since  1.6
     */
    protected function getInput()
    {
      $doc = JFactory::getDocument();

      // find out which schemes are defined
      $schemeNames = One_Repository::getSchemeNames();
      sort($schemeNames);
      $options = array();
      foreach ($schemeNames as $schemeName) {
        $currScheme = One_Repository::getScheme($schemeName);
        if (!isset($currScheme->information['internal']) || isset($currScheme->information['menuonly'])) {
          $options[] = array("id" => $schemeName, "title" => $currScheme->get('info.title'));
        }
      }

      array_unshift($options, JHTML::_('select.option', '0', '- ' . JText::_('Select scheme') . ' -', 'id', 'title'));

      return JHTML::_('select.genericlist', $options, '' . $this->formControl . '[' . $this->group . '][' . $this->fieldname . ']', 'class="inputbox" ', 'id', 'title', $this->value, $this->formControl . $this->fieldname);
    }
  }
