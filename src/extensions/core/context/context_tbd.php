<?php

  /**
   * The One_Context class builds an abstraction of the outside world.
   *
   * @TODO review this file and clean up historical code/comments
   * ONEDISCLAIMER
   **/
  class One_Context
  {
    /**
     * @var array All fetched data
     */
    private $data;

    /**
     * @var boolean Can request values be used ?
     */
    private $useRequest;

    /**
     * @var string Which namespace should be used
     */
    private $namespace;

    /**
     * Class constructor
     *
     * @param array $data
     * @param boolean $useRequest
     */
    public function __construct(array $data = array(), $useRequest = 1)
    {
      $this->data = $data;
      if (!isset($_SESSION['CONTEXT'])) {
        $_SESSION['CONTEXT'] = array();
      }
      $this->useRequest = $useRequest;
    }

    /**
     * Gets the value from the requested key, returns the default value if not found
     *
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get($key, $defaultValue = NULL)
    {
      $value = array_key_exists($key, $this->data) ? $this->data[$key] : NULL;

      if (is_null($value)) {
        $value = $this->get_session($key);
      }

      if (is_null($value) && $this->namespace) {
        $value = $this->get_session($this->namespace . ":" . $key);
      }

      if (is_null($value) && $this->useRequest) {
        if (isset($_REQUEST[$key])) {
          $value = $_REQUEST[$key];      //TODO: use cleaned-up version of $_REQUEST
        }
      }

      // JL 01JUL2008 - We get problems when getting objects from our $_SESSION array
      // (see http://lists.evolt.org/archive/Week-of-Mon-20031027/150721.html for more info)
      // Joomla will execute a session_start before we are able to define the correct classes.
      // The (pretty easy) workaround goes like this:
      //
      // gettype($value) will return 'object', while is_object($value) will return false
      // for _implete objects

      if (is_null($value)) {
        $value = $defaultValue;
      }

      if (gettype($value) == 'object' && !is_object($value)) {
        $value = unserialize(serialize($value));
      }

      return $value;
    }

    /**
     * Gets the value from the requested key from the session, returns the default value if not found
     *
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get_session($key, $defaultValue = null)
    {
      $target  = $_SESSION['CONTEXT'];
      $keypath = explode(":", $key);
      for ($i = 0; $i < count($keypath) - 1; $i++) {
        $dir = $keypath[$i];
        if (!isset($target[$dir])) {
          return $defaultValue;
        }
        $target = $target[$dir];
      }

      if (count($keypath) > 1) {
        $key = $keypath[count($keypath) - 1];
      }

      if (!isset($target[$key])) {
        return $defaultValue;
      }

      return $target[$key];
    }

    /**
     * Sets a variable in the context
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
      $target = $_SESSION['CONTEXT'];

      $keypath = explode(":", $key);  // split dir:subdir:key
      for ($i = 0; $i < count($keypath) - 1; $i++)  // handle dir:subdir
      {
        $dir = $keypath[$i];
        if (!isset($target[$dir])) {
          $target[$dir] = array();
        }
        $target = $target[$dir];
      }

      if (count($keypath) > 1) {
        $key = $keypath[count($keypath) - 1];
      }

      $target[$key] = $value;
    }

    /**
     * Unsets the given key
     *
     * @param string $key
     */
    public function reset($key)
    {
      $target = $_SESSION['CONTEXT'];

      $keypath = explode(":", $key);  // split dir:subdir:key
      for ($i = 0; $i < count($keypath) - 1; $i++)  // handle dir:subdir
      {
        $dir = $keypath[$i];
        if (!isset($target[$dir])) {
          $target[$dir] = array();
        }
        $target = $target[$dir];
      }

      if (count($keypath) > 1) {
        $key = $keypath[count($keypath) - 1];
      }

      unset($target[$key]);
    }

    /**
     * Returns a string output with all variables in the request and the session
     *
     * @deprecated Use var_dump instead
     * @return string
     */
    public function dump()
    {
      throw new One_Exception_Deprecated('Use var_dump instead');
      $s = "<br /><b>REQUEST:</b>";
      foreach ($_REQUEST as $k => $v) {
        $s .= "<br />&nbsp;&nbsp;$k : $v";
      }

      $s .= "<br /><b>SESSION:</b>";
      foreach ($_SESSION['CONTEXT'] as $k => $v) {
        $s .= $this->dumpContextElement($k, $v);
      }

      return $s;
    }

    /**
     * Dump the data of the given key-value
     *
     * @param string $k Key of the element
     * @param mixed $v Value of the element
     * @param int $indent Level of indentation
     * @return string
     */
    private function dumpContextElement($k, $v, $indent = 0)
    {
      $lead = str_repeat("&nbsp;", $indent);

      if (is_array($v)) {
        $s = "<br />&nbsp;&nbsp;" . str_repeat("&nbsp;", $indent) . "$lead$k :";
        foreach ($v as $kk => $vv) {
          $s .= $this->dumpContextElement($kk, $vv, $indent + 2);
        }
      }
      else {
        $s = "<br />&nbsp;&nbsp;" . str_repeat("&nbsp;", $indent) . "$k : $v";
      }

      return $s;
    }

    /**
     * Set the current namespace to use
     *
     * @param string $namespace
     */
    public function useNamespace($namespace)
    {
      $this->namespace = $namespace;
    }

    /**
     * Gets all get-variables posted
     *
     * @return array
     */
    public function getGet()
    {
      return $_GET;
    }

    /**
     * Gets all post-variables posted
     *
     * @return array
     */
    public function getPost()
    {
      return $_POST;
    }

    /**
     * Gets all request-variables posted
     *
     * @return array
     */
    public function getRequest()
    {
      throw new One_Exception_Deprecated("Use getGet() or getPost() instead");
      return $_REQUEST;
    }
  }
