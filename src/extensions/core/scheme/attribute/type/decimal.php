<?php

  /**
   * Treats a scheme-attribute as a decimal
   *
   * ONEDISCLAIMER
   **/
  class One_Scheme_Attribute_Type_Decimal extends One_Scheme_Attribute_Type_Abstract
  {
    /**
     * Get the name of the attribute
     *
     * @return string
     */
    public function getName()
    {
      return "decimal";
    }

    /**
     * Returns the value as a float value
     *
     * @return float
     */
    public function toString($value)
    {
      $value = preg_replace('!\%?([0-9\.\,]*)\%?!', '\1', $value);
      return floatval(str_replace(',', '.', $value));
    }
  }
