<?php

  /**
   * The One_Dispatcher class gets the correct controller and executes it.
   *
   * ONEDISCLAIMER
   **/
  abstract class One_Dispatcher
  {
    protected $_scheme;
    protected $_id;
    protected $_task;
    protected $_view;
    protected $_redirect = null;

    /**
     * @var array Extra options
     */
    protected $_options;

    /**
     * @var One_Controller Controller being used for the current task
     */
    protected $_controller;

    /**
     * Class constructor
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
      $defaults = array(
        'id'     => null,
        'scheme' => null,
        'task'   => null,
        'view'   => null,
      );

      $this->_options = array_merge($defaults, $options);

      $this->_scheme = $this->_options['scheme'];
      $this->_id     = $this->_options['id'];
      $this->_task   = $this->_options['task'];
      $this->_view   = $this->_options['view'];
    }
  }
