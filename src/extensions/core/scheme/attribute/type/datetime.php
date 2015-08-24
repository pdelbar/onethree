<?php

  /**
   * Treats a scheme-attribute as a datetime
   *
   * ONEDISCLAIMER
   **/
  class One_Scheme_Attribute_Type_Datetime extends One_Scheme_Attribute_Type_Abstract
  {
    /**
     * Get the name of the attribute
     *
     * @return string
     */
    public function getName()
    {
      return "datetime";
    }

    /**
     * Returns the value as a datetime value
     *
     * @return string
     */
    public function toString($value)
    {
      return '"' . $value . '"';
    }
  }
