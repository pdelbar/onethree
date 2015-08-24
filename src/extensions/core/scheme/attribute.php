<?php

// get rid of alias, name is used in one and column in external source (if relevant)
  /**
   * The One_Scheme_Attribute class provides an abstraction of an object attribute. It works as
   * an interface between the One_Model (and its corresponding value stored in it) and
   * objects needing to access, convert or handle the attribute.
   *
   * ONEDISCLAIMER
   **/
  class One_Scheme_Attribute extends One_Registry
  {

    /**
     * Class constructor
     *
     * @param string $name
     * @param string $type name of the One_Scheme_Attribute_Type of the attribute
     * @param array additional options that can be passed to the array
     */
    public function __construct($name, $type, array $options = array())
    {
      $this->set('name', $name);
      $this->set('column', $name); // by default, keep it the same
      $this->set('alias', $name); // by default, keep it the same
      $this->set('type', One_Repository::getType($type));

      foreach ($options as $key => $val) {
        $this->set($key, $val);
      }
    }

    /**
     * Returns the name of the attribute
     *
     * @todo rename to getName()
     * @return string
     */
    public function getName()
    {
      return $this->get('name');
    }

    public function getColumn()
    {
      return $this->get('column');
    }

    /**
     * Returns the type of the attribute
     *
     * @rename to getType()
     * @return One_Scheme_Attribute_Type
     */
    public function getType()
    {
      return $this->get('type');
    }


    /**
     * Is this attribute the identity attribute
     *
     * @return boolean
     */
    public function isIdentity()
    {
      return in_array($this->get('identity'), array('true', 'yes', '1'));
    }

    /**
     * Get the default value for the attribute
     *
     * @return mixed
     */
    public function getDefault()
    {
      return $this->get('default');
    }

    /**
     * Bind data to the model from an array
     *
     * @param One_Model $model
     * @param array $data
     */
    public function bindFromArray($model, $data)
    {
      $a         = $this->get('name');
      $model->$a = $this->get('type')->valueFromArray($a, $data);
    }

    /**
     * Bind a link to the model from an array and return the value
     *
     * @param string $linkName
     * @param One_Model $model
     * @param array $data
     * @return mixed
     */
    public function bindLinkFromArray($linkName, One_Model &$model, array &$data)
    {
      $f         = $this->get('name');
      $f         = $linkName . "_" . $f;
      $val       = $this->get('type')->valueFromArray($f, $data);
      $model->$f = $val;
      return $val;
    }

    /**
     * Returns the string representation of the attribute according to the type of the attribute
     *
     * @param mixed $value
     * @return string
     */
    public function toString($value)
    {
      return $this->get('type')->toString($value);
    }


    /**
     * Is the attribute read-only?
     *
     * @return boolean
     */
    public function isReadOnly()
    {
      return in_array($this->get('readonly'), array('true', 'yes', '1'));
    }

    /**
     * Is the attribute an auto-increment field
     * A field is autoincrement by default
     *
     * @return boolean
     */
    public function isAutoInc()
    {
      return !in_array($this->get('autoinc'), array('no', '0', 'false'));
    }

  }
