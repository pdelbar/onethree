<?php

  /**
   * Treats a scheme-attribute as a boolean
   *
   * ONEDISCLAIMER
   **/
  class One_Scheme_Attribute_Type_Boolean extends One_Scheme_Attribute_Type_Abstract
  {
    /**
     * Get the name of the attribute
     *
     * @return string
     */
    public function getName()
    {
      return "boolean";
    }

    /**
     * Returns the value as a boolean value
     *
     * @return string
     */
    public function toString($value)
    {
      $value = preg_replace('!\%?([0-9\.\,]*)\%?!', '\1', $value);
      if (trim($value) == '' || is_null($value) || trim($value) == '0' || $value == false) {
        $value = 0;
      }
      else {
        $value = 1;
      }
      return $value;
    }

  }
