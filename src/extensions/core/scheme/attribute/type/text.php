<?php

  /**
   * Treats a scheme-attribute as a text
   *
   * ONEDISCLAIMER
   **/
  class One_Scheme_Attribute_Type_Text extends One_Scheme_Attribute_Type_Abstract
  {
    /**
     * Get the name of the attribute
     *
     * @return string
     */
    public function getName()
    {
      return "text";
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
