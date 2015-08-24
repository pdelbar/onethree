<?php

  /**
   * Treats a scheme-attribute as a string
   *
   * ONEDISCLAIMER
   **/
  class One_Scheme_Attribute_Type_String extends One_Scheme_Attribute_Type_Abstract
  {
    /**
     * Get the name of the attribute
     *
     * @return string
     */
    public function getName()
    {
      return 'string';
    }

    /**
     * Returns the value as a string value
     *
     * @return string
     */
    public function toString($value)
    {
      return '"' . $value . '"';
    }
  }
