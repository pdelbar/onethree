<?php

/**
 * A One_Query_Sql object is a meta-representation of a database query.
 *
 * @TODO review this file and clean up historical code/comments
 * ONEDISCLAIMER
 **/
class One_Query_Sql extends One_Query {

  /**
   * @var array List containing attributes to select
   * @access protected
   */
  protected $select = array();

  /**
   * @var One_Query_Condition_Container Clause container containing clauses to use in the where part
   * @access protected
   */
  protected $clauseContainer = NULL;

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
    parent::__construct($scheme);
    $this->clauseContainer = new One_Query_Condition_Container($this, 'AND');
    $this->havingContainer = new One_Query_Condition_Container($this, 'AND');
  }





  /**
   * Set the raw query to be performed
   *
   * @param mixed $raw
   */
  public function setRaw($raw) {
    $this->raw = $raw;
  }

  /**
   * Get the raw query to be performed
   *
   * @return mixed
   */
  public function getRaw() {
    return $this->raw;
  }

  /**
   * Set the fields you want to select
   *
   * @param array $select
   */
  public function setSelect(array $select) {
    foreach ($select as $sselect) {
      if ($this->isRelationDefinition($sselect)) {
        preg_match('/([^\(]+\()?([^:]+):([^:]+)/', $sselect, $aMatches);
        $this->setJoin(trim($aMatches[2]));
      }
    }

    $this->select = $select;
  }

  /**
   * Returns the desired fields to be selected
   *
   * @return array
   */
  public function getSelect() {
    return $this->select;
  }

  /**
   * Returns the attributes to order the results by
   *
   * @return array
   */
  public function getOrder() {
    return $this->order;
  }

  /**
   * setOrder : define the ordering for the selection expressed by this query
   *
   * Format for order specification:
   * field+
   * part:part:part:rest-
   * rand()
   *
   * @param mixed $order either string (attributename) or array of strings (attributenames)
   * @param boolean $clearPrevious set to true if you want to remove the previously set order, otherwise the added orders will just be added to the order
   * @return One_Query
   */
  public function setOrder($order, $clearPrevious = false) {
    // Don't set orders if a calculated field is used to sort the list
    if (null !== $this->orderByCalculated) {
      return $this;
    }

    if ($clearPrevious) {
      $this->order = array();
    }

    if (is_array($order)) {
      $tmp = array();
      foreach ($order as $ord) {
        // Check whether the passed string is an attributename or a related attribute name (attName or roleName:attName)
        if (preg_match('/^([a-zA-Z0-9_]+)(:([a-zA-Z0-9_]+))*(\+|\-)?$/', $ord, $matches) > 0) {
          $this->order[$matches[1] . $matches[2]] = $ord;
          if (trim($matches[2]) != '') {
            $this->setJoin($matches[1]);
          }
        } elseif (preg_match('/rand\((\s*)\)/i', $ord) > 0) {
          $this->order['oneDoRandOrder'] = 'RAND()';
        }
      }
    } else {
      if (preg_match('/^([a-zA-Z0-9_]+)(:([a-zA-Z0-9_]+))*(\+|\-)?$/', $order, $matches) > 0) {
        $orderField = $matches[1];
        if (isset($matches[2]) && trim($matches[2]) != '') {
          $orderField .= $matches[2];
          $this->setJoin($matches[1]);
        }
        $this->order[$orderField] = $order;

      } elseif (preg_match('/rand\((\s*)\)/i', $order) > 0) {
        $this->order['oneDoRandOrder'] = 'RAND()';
      }
    }
    return $this;
  }

  /**
   * Returns the length and the offst to be used for the results
   *
   * @return array
   */
  public function getLimit() {
    return $this->limit;
  }

  /**
   * Set the length and the offst to be used for the results
   *
   * @param int $limit
   * @param int $start
   * @return One_Query
   */
  public function setLimit($limit, $start = -1) {
    $this->limit['limit'] = abs(intval($limit));
    $this->limit['start'] = (intval($start) == -1) ? intval($start) : abs(intval($start));

    return $this;
  }

  /**
   * Set the attributes to group by
   *
   * @param string $group
   * @return Onequery
   */
  public function setGroup($group) {
    if (!is_array($group))
      $group = array($group);

    foreach ($group as $sgroup) {
      if ($this->isRelationDefinition($sgroup)) {
        preg_match('/([^\(]+\()?([^:]+):([^:]+)/', $sgroup, $aMatches);
        $this->setJoin(trim($aMatches[2]));
      }
    }

    $this->group = $group;

    return $this;
  }

  /**
   * Returns the attributes to group by
   *
   * @return array
   */
  public function getGroup() {
    return $this->group;
  }

  /**
   * Shortcut to quickly set options for this One_Query instance, mainly used by One_Model::getRelated()
   *
   * @param array $options (limit, start, order, query=>array(array(field, operator, value), array(...))
   * @return One_Query
   */
  public function setOptions($options) {
    $this->__options = $options;

    // set possible limit and start
    $limit = NULL;
    $start = NULL;
    if (array_key_exists('limit', $options))
      $limit = $options['limit'];
    if (array_key_exists('start', $options))
      $limit = intval($options['start']);

    if (!is_null($limit) || !is_null($start)) {
      if (is_null($start)) $start = -1;
      if (is_null($limit)) $limit = 0;
      $this->setLimit($limit, $start);
    }

    // set possible order
    if (array_key_exists('order', $options))
      $this->setOrder($options['order']);

    // Adjust the query to only show certain items
    // All added will be added with and logic
    // TODO make it more flexible
    if (array_key_exists('query', $options) && is_array($options['query']) && count($options['query']) > 0) {
      foreach ($options['query'] as $qOption) {
        if (count($qOption) == 3)
          $this->where($qOption[0], $qOption[1], $qOption[2]);
      }
    }

    return $this;
  }

  /**
   * Set an alias for a specified role
   *
   * @param string $roleName
   * @param string $alias
   */
  public function setRoleAlias($roleName, $alias) {
    if (!isset($this->roles[$roleName])) {
      $link = $this->scheme->getLink($roleName);
      $scheme = One_Repository::getScheme($link->getTarget());
      $resources = $scheme->getResources();
      $table = $resources['table'];

      $this->roles[$roleName]['link'] = $link;
      $this->roles[$roleName]['scheme'] = $scheme;
      $this->roles[$roleName]['table'] = $table;
    }

    $this->roles[$roleName]['alias'] = $alias;
    $this->roleAliases[$alias] = $roleName;
  }

  /**
   * Get an alias for a specified role
   *
   * @param string $alias
   * @return string
   */
  public function getRoleAlias($alias) {
    if (isset($this->roleAliases[$alias])) {
      return $this->roleAliases[$alias];
    } else {
      return NULL;
    }
  }

  /**
   * Get the specified role
   *
   * @param string $roleName
   * @return array
   */
  public function getRole($roleName) {
    if (isset($this->roles[$roleName])) {
      return $this->roles[$roleName];
    } else {
      return NULL;
    }
  }

  /**
   * Get all the roles present in the One_Query instance
   *
   * @return array
   */
  public function getRoles() {
    return $this->roles;
  }

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
    }

    if (is_null($this->clauseContainer))
      $this->clauseContainer = $this->addAnd();

    return $this->clauseContainer->where($object, $op, $val);
  }

  /**
   * Add a One_Query_Condition_Container or a condition to the top condition-container
   *
   * @param mixed $object
   * @param string $op
   * @param mixed $val
   * @return mixed
   */
  public function having($object, $op = 'eq', $val = '') {
    if ($this->isRelationDefinition($object)) {
      $parts = explode(':', $object);
      $this->setJoin($parts[0]);
    }

    if (is_null($this->havingContainer))
      $this->havingContainer = $this->addAnd(true);

    return $this->havingContainer->where($object, $op, $val);
  }

  /**
   * Returns the whereClauses
   *
   * @return One_Query_Condition_Container
   */
  public function getWhereClauses() {
    return $this->clauseContainer;
  }

  /**
   * Returns the havingClauses
   *
   * @return One_Query_Condition_Container
   */
  public function getHavingClauses() {
    return $this->havingContainer;
  }

  /**
   * Set a join for a specific role and determine it's type
   *
   * @param array $role
   * @param string $type
   */
  public function setJoin($role, $type = '') {
    if ((array_key_exists($role, $this->joins) && '' != $type) || !array_key_exists($role, $this->joins)) {
      $this->joins[$role] = $type;
    }
  }

  /**
   * Get all joins present in the One_Query instance
   *
   * @return array
   */
  public function getJoins() {
    return $this->joins;
  }

  /**
   * Returns a One_Query_Condition_Container of type AND to the clauseContainer
   *
   * @param boolean $having Set to true if the "and" needs to be added to the havingContainer, false will add the "and" to the clauseContainer
   * @return One_Query_Condition_Container
   */
  public function addAnd($having = false) {
    if ($having)
      return $this->havingContainer->addAnd($this);
    else
      return $this->clauseContainer->addAnd($this);
  }

  /**
   * Adds a One_Query_Condition_Container of type OR to the clauseContainer
   *
   * @param boolean $having Set to true if the "or" needs to be added to the havingContainer, false will add the "or" to the clauseContainer
   * @return One_Query_Condition_Container
   */
  public function addOr($having = false) {
    if ($having)
      return $this->havingContainer->addOr($this);
    else
      return $this->clauseContainer->addOr($this);
  }

  public function qAnd() {
    throw new One_Exception_Deprecated('One_Query::qAnd() is no longer used in favor of One_Query::addAnd()');
  }

  public function qOr() {
    throw new One_Exception_Deprecated('One_Query::qAnd() is no longer used in favor of One_Query::addAnd()');
  }

  /**
   * Is the passed variable a relation definition? (EG: role:attribute)
   *
   * @param string $object
   * @return boolean
   */
  public function isRelationDefinition($object) {
    return (preg_match('/:[^:]/', $object) > 0);
  }



  public function result() {
    throw new One_Exception_Deprecated('One_Query::result() is no longer used in favor of One_Query::execute()');
  }


  /**
   * Set the encoding for the query (mainly for MySQL atm)
   *
   * @param string $encoding (utf8, iso-8859-1, ...)
   * @return void
   */
  public function setEncoding($encoding) {
    return $this->getScheme()->getStore()->setEncoding($this->getScheme(), $encoding);
  }

  /**
   * Does the id passed have a the relationship of role "rolename" with the current Query
   * @param string $roleName
   * @param mixed $idValue
   * @return boolean
   */
  public function isRelated($roleName, $idValue) // @TODO finish isRelated function
  {
    throw new One_Exception_NotImplemented('One_Query::isRelated() is not properly implemented yet');

    $scheme = $this->getScheme();
    $link = $scheme->getLink($roleName);
    $relatedSchemeName = $link->getTarget();

    $relatedModel = One_Repository::selectOne($relatedSchemeName, $idValue);
    $relatedScheme = $relatedModel->getSchemeName();
    $relId = $relatedScheme->identityColumn();
  }

  /**
   * Return whether or not the results must be sorted by a calculated field
   * @return boolean
   */
  public function isSortedByCalculatedField() {
    return (null !== $this->orderByCalculated);
  }

  /**
   * Return the calculated field used to sort by
   * @return One_Type_Calculated_Interface
   */
  public function getCalculatedSortType() {
    return $this->orderByCalculated;
  }

  /**
   * Return the direction the results should be sorted in using a calculated field
   * @return string
   */
  public function getCalculatedSortDirection() {
    return $this->orderDirectionByCalculated;
  }

  public function dump() {
    echo '<br/><b>QUERY </b>', $this->scheme->getName();
    echo '<br/><b>SELECT </b><br/>';
    print_r($this->select);

    echo '<br/><b>WHERE </b>';
    $this->clauseContainer->dump();

  }
}
