<?php

  /**
   * Treats a scheme-attribute as a time
   *
   * ONEDISCLAIMER
   **/
  class One_Scheme_Attribute_Type_Time extends One_Scheme_Attribute_Type_Abstract
  {
    /**
     * Get the name of the attribute
     *
     * @return string
     */
    public function getName()
    {
      return "time";
    }

    /**
     * Returns the value as a time value
     *
     * @return string
     */
    public function toString($value)
    {
      return '"' . $value . '"';
    }
  }
