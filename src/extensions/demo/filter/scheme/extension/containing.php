<?php

  class One_Filter_Scheme_Extension_Containing implements One_Filter_Interface
  {

    private $_filterParams = array();

    public function __construct($filterParams = array())
    {
      $this->_filterParams = $filterParams;
    }

    // The filterParams contain a search string to match with the name

    public function affect(One_Query $query)
    {
      $search = $this->_filterParams['search'];
      if ($search) {
        $query->where('name', 'like', "%$search%");
      }
    }
  }
