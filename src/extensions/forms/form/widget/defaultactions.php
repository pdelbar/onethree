<?php

/**
 * Handles the submit widget
 *
 * @TODO review this file and clean up historical code/comments
 * ONEDISCLAIMER
 **/
Class One_Form_Widget_Defaultactions extends One_Form_Widget_Abstract {
  /**
   * @var string The value of the submit button
   */
  protected $_value = 'Defaultactions';

  /**
   * Class constructor
   *
   * @param string $id
   * @param string $name
   * @param string $label
   * @param array $config
   */
  public function __construct($id = NULL, $name = '', $label = NULL, array $config = array()) {
    parent::__construct($id, $name, $label, $config);
    $this->_type = 'defaultactions';
    $this->_totf = 'defaultactions';
  }

  /**
   * Return the allowed options for this widget
   *
   * @return array
   */
  protected static function allowedOptions() {
    $additional = array(
      'dir'      => 1,
      'lang'     => 1,
      'xml:lang' => 1,
      'disabled' => 1,
      'size'     => 1
    );
    return array_merge(parent::allowedOptions(), $additional);
  }

  /**
   * Is the submitted value valid?
   *
   * @return boolean
   */
  public function validate() {
    return true;
  }

  /**
   * Bind the model to this widget
   *
   * @param One_Model $model
   */
  public function bindModel($model) {
  }

  /**
   * Render the output of the widget and add it to the DOM
   *
   * @param One_Model $model
   * @param One_Dom $d
   */
  protected function _render($model, One_Dom $d) {
    $this->setCfg('class', 'OneFieldSubmit ' . $this->getCfg('class'));

    $data = array(
      'id'       => $this->getID(),
      'name'     => $this->getFormName(),
      'totf'     => $this->_totf,
      'events'   => $this->getEventsAsString(),
      'params'   => $this->getParametersAsString(),
      'value'    => (is_null($this->getValue($model)) ? $this->getDefault() : $this->getValue($model)),
      'info'     => $this->getCfg('info'),
      'error'    => $this->getCfg('error'),
      'class'    => $this->getCfg('class'),
      'required' => (($this->isRequired()) ? ' *' : ''),
      'label'    => $this->getLabel(),
      'lblLast'  => $this->getCfg('lblLast')
    );

    $dom = $this->parse($model, $data);
    $d->addDom($dom);
  }
  /**
   * Overrides PHP's native __toString function
   *
   * @return string
   */
  public function __toString() {
    return get_class() . ': ' . $this->getID();
  }


}
