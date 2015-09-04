<?php

  /**
   * Authorisation-rule to always allow a task
   *
   * ONEDISCLAIMER
   **/
  class One_Permission_Rule_Always extends One_Permission_Rule
  {
    public function __construct($options = array())
    {
      parent::__construct($options);
      $this->rules = array();
    }

    public function authorize(array $args)
    {
      return true;
    }
  }
