<?php
/**
 * This class contains several conditions and joins them with an AND or OR operator
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Query_Condition_Container
{
	/**
	 * @var string Should be either "AND" or "OR"
	 */
	protected $type;

	/**
	 * @var array list of conditions in the container
	 */
	protected $conditions;

	/**
	 * @var One_Query $query The One_Query object this container belongs to
	 */
	protected $query;

	/**
	 * Class constructor
	 *
	 * @param string $type
	 */
	public function __construct(One_Query $query, $type = 'AND')
	{
		switch(strtoupper($type))
		{
			case 'OR':
				$this->type = 'OR';
				break;
			default:
				$this->type = 'AND';
		}

		$this->conditions = array();

		$this->query = $query;
	}

	/**
	 * Add a condition to the container
	 *
	 * @param string $object
	 * @param string $op
	 * @param mixed $val
	 */
	public function where($object, $op = 'eq', $val = '')
	{
		if($this->query->isRelationDefinition($object))
		{
			$parts = explode(':', $object);
			$this->query->setJoin($parts[0]);
		}

		$condition = new One_Query_Condition($object, $op, $val);
		$this->conditions[] = $condition;

		return $this->conditions[(count($this->conditions) - 1)];
	}

	/**
	 * Get the type of the container
	 *
	 * @return string "AND" or "OR"
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Get all conditions present in the container
	 *
	 * @return array
	 */
	public function getConditions()
	{
		return $this->conditions;
	}

    /**
     * Returns a One_Query_Condition_Container of type AND
     *
     * @return One_Query_Condition_Container
     */
    public function addAnd(One_Query $query)
    {
    	$tmp = new One_Query_Condition_Container($query, 'AND');
    	$this->conditions[] = $tmp;

		return $this->conditions[(count($this->conditions) - 1)];
    }

    /**
     * Returns a One_Query_Condition_Container of type OR
     *
     * @return One_Query_Condition_Container
     */
    public function addOr(One_Query $query)
    {
    	$tmp = new One_Query_Condition_Container($query, 'OR');
    	$this->conditions[] = $tmp;

		return $this->conditions[(count($this->conditions) - 1)];
    }

    public function qAnd()
    {
    	throw new One_Exception_Deprecated('One_Query_Condition_Container::qAnd() is no longer used in favor of One_Query_Condition_Container::addAnd()');
    }

    public function qOr()
    {
    	throw new One_Exception_Deprecated('One_Query_Condition_Container::qAnd() is no longer used in favor of One_Query_Condition_Container::addAnd()');
    }

    public function dump()
    {
    	echo '<br/><b>CONTAINER </b>', count($this->conditions);
    	foreach ($this->conditions as $c) $c->dump();

    }
}