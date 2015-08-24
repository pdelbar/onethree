<?php

/**
 * The One_Dom object represents a DOM-like structure designed to store output in a format which allows structured
 * rendering into a full-blown DOM object.
 *
 * ONEDISCLAIMER
 **/
class One_Dom {
  /**
   * @var array All data stored in the DOM
   */
  public $_data;

  /**
   * @var array Extra properties
   */
  public $_properties;

  /**
   * Class constructor
   */
  public function __construct() {
    $this->_data = array(
      "_head"   => array(),
      "main"    => array(),
      "_onload" => array(),
    );
  }

  /**
   * Add data to the DOM
   *
   * @param string $value The data that needs to be added to the DOM
   * @param string $section The section the data should be added to
   */
  public function add($value, $section = 'main') {
    if (!is_array($this->_data[$section]))
      $this->_data[$section] = array();

    switch ($section) {
      case '_head':
      case '_onload':
        if (!in_array($value, $this->_data[$section]))
          $this->_data[$section][] = $value;
        break;
      default:
        $this->_data[$section][] = $value;
        break;
    }
  }

  /**
   * Add a One_Dom instance to the instantiated One_Dom
   *
   * @param One_Dom $dom
   */
  public function addDom(One_Dom $dom) {
    $data = $dom->getData();

    foreach ($data as $section => $content) {
      foreach ($content as $item) {
        $this->add($item, $section);
      }
    }
  }

  /**
   * Return all data in the DOM
   *
   * @return array
   */
  public function getData() {
    return $this->_data;
  }

  /**
   * Renders the DOM or a section of the DOM
   *
   * @param $section The section to render, if $section equals NULL, all of the DOM will be rendered
   * @return string
   */
  public function render($section = null) {
    $result = '';
    if ($section)
      $result = implode('', $this->_data[$section]);
    else {
      foreach ($this->_data as $key => $section) {
        $result .= $this->render($key);
      }
    }

    return $result;
  }
}
