<?php

  /**
   * Treats a scheme-attribute as a date
   *
   * ONEDISCLAIMER
   **/
  class One_Scheme_Attribute_Type_Date extends One_Scheme_Attribute_Type_Abstract
  {
    /**
     * Get the name of the attribute
     *
     * @return string
     */
    public function getName()
    {
      return "date";
    }

    /**
     * Returns the value as a date value
     *
     * @return string
     */
    public function toString($value)
    {
      return '"' . $value . '"';
    }

  }
