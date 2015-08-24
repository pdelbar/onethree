<?php

/**
 * A One_Query object is a meta-representation of a query, which is a structure describing the characteristics
 * of a set of instances
 *
 * ONEDISCLAIMER
 **/
class One_Query {
  /**
   * @var One_Scheme Scheme the query should be performed on
   * @access private
   */
  protected $scheme = NULL;

  /**
   * @var array List containing attributes to select
   * @access protected
   */
  protected $select = array();

  /**
   * @var One_Query_Condition_Container Clause container containing clauses to use in the where part
   * @access protected
   */
  /**
   * @var One_Query_Condition_Container Clause container containing clauses to use in the having part
   * @access protected
   */
  protected $havingContainer = NULL;

  /**
   * @var array Roles used in the query
   * @access protected
   */
  protected $roles = array();

  /**
   * @var array List containing aliases of the joins
   * @access protected
   */
  protected $roleAliases = array();

  /**
   * @var array List of attributes to order on
   * @access protected
   */
  protected $order = array();

  /**
   * @var One_Type_Calculated_Interface Keep track whether the results should be ordered by a calculated field
   */
  protected $orderByCalculated = NULL;

  /**
   * @var string "asc" or "desc"
   */
  protected $orderDirectionByCalculated = 'asc';

  /**
   * @var array Array containing length and offset
   * @access protected
   */
  protected $limit = array();

  /**
   * @var string What kind of join to use for this query, "LEFT OUTER", "RIGHT", ...
   * @access protected
   */
  protected $joinType = '';

  /**
   * @var array List of attributes to group on
   * @access protected
   */
  protected $group = NULL;

  /**
   * @var array List of joins to include in the query
   * @access protected
   */
  protected $joins = array();

  /**
   * @var string Raw query to perform
   * @access protected
   */
  protected $raw = NULL;

  protected static $_debug = false;

  /**
   * Class constructor
   * Note: used to accept strings, pass One_Repository::getScheme($string) instead
   *
   * @param One_Scheme $scheme
   */
  public function __construct(One_Scheme $scheme) {
    $this->setScheme($scheme);
  }

  public static function setDebug($bool) {
    self::$_debug = $bool;
  }

  public static function getDebug() {
    return self::$_debug;
  }

  /**
   * Set the One_Scheme used in this One_Query instance
   *
   * @param One_Scheme $scheme
   */
  protected function setScheme(One_Scheme $scheme) {
    $this->scheme = $scheme;
  }

  public function getScheme() {
    return $this->scheme;
  }



  public function setOrder($order, $clearPrevious = false) {}

  public function getOrder() { return ''; }

  public function setLimit($limit, $start = -1) {}

  public function getLimit() { return 0; }

  /**
   * Add a One_Query_Condition_Container or a condition to the top condition-container
   *
   * @param mixed $object fieldname or rolename:fieldname
   * @param string $op Operator @see One_Renderer
   * @param mixed $val Value
   * @return One_Query_Condition_Container
   */
  public function where($object, $op = 'eq', $val = '') {
    if ($this->isRelationDefinition($object)) {
      $parts = explode(':', $object);
      $this->setJoin($parts[0]);
    } else {
      $attr = $this->getScheme()->getAttribute($object);
      if (null !== $attr && $attr->getType instanceof One_Type_Calculated_Interface) {
        return $this->having($object, $op, $val);
      }
    }

    if (is_null($this->clauseContainer))
      $this->clauseContainer = $this->addAnd();

    return $this->clauseContainer->where($object, $op, $val);
  }



  /* "Result functions" */
  /**
   * Get the result of the One_Query
   *
   * @param boolean $asInstance Should the results be instantiated as One_Models?
   * @param boolean $overrideFilters Should filters be overridable?
   * @return mixed
   */
  public function execute($asInstance = true, $overrideFilters = false) {
    return $this->getScheme()->getStore()->executeSchemeQuery($this, $asInstance, $overrideFilters);
  }

  /**
   * Return the amount of results of the query
   *
   * @return int
   */
  public function getCount() {
    return $this->getScheme()->getStore()->executeSchemeCount($this);
  }


  public function dump() {
    echo '<br/><b>QUERY </b>', $this->scheme->getName();
  }
}
