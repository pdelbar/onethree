<?php

  /**
   * Treats a scheme-attribute as an int
   *
   * ONEDISCLAIMER
   **/
  class One_Scheme_Attribute_Type_Int extends One_Scheme_Attribute_Type_Abstract
  {
    /**
     * Get the name of the attribute
     *
     * @return string
     */
    public function getName()
    {
      return 'int';
    }

    /**
     * Returns the value as an int value
     *
     * @return int
     */
    public function toString($value)
    {
      $value = preg_replace('!\%?([0-9\.\,]*)\%?!', '\1', $value);
      return intval($value);
    }
  }
