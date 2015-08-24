<?php
/**
 * One_Query_Renderer_Mongo handles a One_Query instance for MongoDB
 *

ONEDISCLAIMER
 **/
class One_Query_Renderer_Mongo extends One_Query_Renderer_Abstract
{
	/**
	 * @var string The table to look for data in
	 * @access protected
	 */
	protected $_collection;

	/**
	 * Render the query
	 *
	 * @param One_Query $query
	 * @return string
	 */
	public function render(One_Query $query, $overrideFilters = false)
	{
		$this->query  = $query;
		$this->scheme = $this->query->getScheme();

		// if the person wants to perform a raw query, return the raw query
		if(!is_null($query->getRaw()))
		{
			if(One_Config::get('debug.query'))
			{
				echo '<pre>';
				var_dump($query->getRaw());
				echo '</pre>';
			}
			return $query->getRaw();
		}

		$this->query  = $query;
		$this->scheme = $this->query->getScheme();
		$resources    = $this->scheme->getResources();

		// fetch collection to fetch data from
		$this->_collection = $resources['collection'];

		// add possible filters to the query
		if(!$overrideFilters && isset($resources['filter']))
		{
			$filters = explode(';', $resources['filter']);
			if(count($filters) > 0)
			{
				foreach($filters as $filterName)
				{
					if($filterName != '')
					{
						$filter = One_Repository::getFilter($filterName, $query->getScheme()->getName());
						$filter->affect($query);
					}
				}
			}
		}

		$findQuery = array(
								'fields' => array(),
								'query' => array()
							);
		if(count($query->getSelect()) > 0) {
			$findQuery['fields'] = $this->createSelects($query->getSelect());
		}

		// get where clauses
		$whereClauses = $query->getWhereClauses();
		$where = NULL;
		if(!is_null($whereClauses)) {
			$where = $this->whereClauses($whereClauses);
		}

		if(!is_null($where)) {
			$findQuery['query'] = $where;
		}

		if(One_Config::get('debug.query'))
		{
			echo '<pre>';
			var_dump($findQuery);
			echo '</pre>';
		}

		$findQuery = json_encode($findQuery);

		return $findQuery;
	}

	/**
	 * Generate the select part of the query
	 *
	 * @param array $selects
	 * @return string
	 * @access protected
	 */
	protected function createSelects(array $selects)
	{
		$tmp = array();
		foreach($selects as $select) {
			$tmp[$select] = 1;
		}

		return $tmp;
	}

	/**
	 * Generic join generator, this function will dispatch to the proper function according to linkType
	 *
	 * @access protected
	 * @param array $link
	 * @return string
	 */
	protected function createJoin($link, $linkType = '')
	{
		return true;
	}

	/**
	 * Generate the WHERE clauses of the query
	 *
	 * @access protected
	 * @param One_Query_Condition_Container $whereClauses
	 * @return string
	 */
	protected function whereClauses(One_Query_Condition_Container $whereClauses)
	{
		$conditions = $whereClauses->getConditions();

		if(count($conditions) == 0) {
			return null;
		}
		else if(count($conditions) == 1)
		{
			if($conditions[0] instanceof One_Query_Condition_Container) {
//				return $this->whereClauses($conditions[0]);
			}
			else {
				return $this->clause($conditions[0]);
			}
		}
		else
		{
			$all = array();
			foreach($conditions as $condition)
			{
				if($condition instanceof One_Query_Condition_Container) {
//					$all[] = $this->whereClauses($condition);
				}
				else
				{
					$newClause = $this->clause($condition);
					if(!is_null($newClause)) {
						$all[] = $newClause;
					}
				}
			}

			if(count($all) > 0) {
				$clause = $all;
			}
			else {
				$clause = NULL;
			}

			return $clause;
		}

		return NULL;
	}

	/**
	 * Generate the correct way how the particular where clause should look like
	 *
	 * @access protected
	 * @param One_Query_Condition $condition
	 * @return string
	 */
	protected function clause(One_Query_Condition $condition)
	{
		if($condition->op != 'literal')
		{
			$scheme    = $this->scheme;
			$attribute = $condition->attribute;
			$custVal   = false;

			// format value depending on the type of value
			$left = $right = '';
			switch (trim(strtolower($condition->op)))
			{
				case 'eq':
					$op = null;
					break;
				case 'gt':
					$op = '$gt';
					break;
				case 'gte':
					$op = '$gte';
					break;
				case 'lt':
					$op = '$lt';
					break;
				case 'lte':
					$op = '$lte';
					break;
				case 'neq':
					$op = '$ne';
					break;
				case 'in':
					$op = '$in';
					break;
				case 'nin':
					$op = '$nin';
					break;
				case 'ends':
					$op = null;
					$right = '/';
					$left = '$/';
					break;
				case 'begins':
					$op = null;
					$right = '/^';
					$left = '/';
					break;
				case 'contains':
					$op = null;
					$right = '/';
					$left = '/';
					break;
				default:
					$op = $condition->op;
			}

			if(preg_match('/^custval:([a-z]+)/i', $op, $opmatch) > 0)
			{
				$custVal = true;
				$op = strtoupper($opmatch[1]);
			}

			$clause = NULL;

			$ats = $scheme->get('attributes');
			$at  = false;
			if(isset($ats[$attribute])) {
				$at = $ats[$attribute];
			}
			if($at)
			{
				if(($at->getType() == 'string' ||  $at->getType() == 'text') && !is_array($condition->value))
				{
					if($custVal) {
						$tmp = $condition->value;
					}
					else {
						$tmp = $at->toString($condition->value);
					}
					if(strlen($tmp) > 2 && preg_match('/^["]{2,}[^"]*["]{2,}$/', $tmp) != false) {
						$tmp = preg_replace(array('/^""/', '/""$/'), '"', $tmp);
					}

					$attName = $at->getName();
					if(null === $op) {
						if('' != $left || '' != $right) {
							$tmp = substr($tmp, 1, -1);
							$regex = new MongoRegex($left.$tmp.$right);
							$clause = array($at->getName() => $regex);
						}
						else {
							$clause = array($at->getName() => $tmp);
						}
					}
					else {
						$clause = array($at->getName() => array($op => $tmp));
					}
				}
				else
				{
					if(is_array($condition->value))
					{
						$ar = array();
						foreach($condition->value as $key => $arg)
						{
							if($custVal) {
								$tmp = $arg;
							}
							else {
								$tmp = $at->toString($arg);
							}

							// TR20090318 The following must be performed because in some rare cases (due to referencing), the ToString method will be performed twice, which results in double double quotes
							if(strlen($tmp) > 2 && preg_match('/^["]{2,}[^"]*["]{2,}$/', $tmp) != false)
							{
								$tmp = preg_replace(array('/^""/', '/""$/'), '"', $tmp);
							}
							$ar[$key] = $tmp;

						}

						if(count($ar) > 0) {
							$clause = array($at->getName() => array($op => $ar));
						}
					}
					else
					{
						if($custVal) {
							$tmp = $condition->value;
						}
						else {
							$tmp = $at->toString($condition->value);
						};

						$attName = $at->getName();
						if(null === $op) {
							if('' != $left || '' != $right) {
								$tmp = substr($tmp, 1, -1);
								$regex = new MongoRegex($left.$tmp.$right);
								$clause = array($at->getName() => $regex);
							}
							else {
								$clause = array($at->getName() => $tmp);
							}
						}
						else {
							$clause = array($at->getName() => array($op => $tmp));
						}
					}
				}
			}
			else
			{
				if(null === $op) {
					$clause = array($condition->attribute => $left.$tmp.$right);
				}
				else {
					$clause = array($condition->attribute => array($op => $tmp));
				}
			}
		}
		else
		{
			$clause = $condition->value;
		}

		if(!is_array($clause)) {
			$clause = json_decode($clause);
		}

		return $clause;
	}

	/**
	 * return ORDER BY part of the MySQL query
	 *
	 * @access protected
	 * @return string
	 */
	protected function createOrder()
	{
		$qOrder = $this->query->getOrder();

		if(count($qOrder) == 0) {
			return NULL;
		}
		else
		{
			$orders = array();
			foreach($qOrder as $key => $by)
			{
				if($key == 'oneDoRandOrder') {
					$orders[] = 'RAND()';
				}
				else
				{
					$alias = $this->aliases['$self$'];
					preg_match('/^([a-zA-Z0-9_]+)(:([a-zA-Z0-9_]+))*(\+|\-)?$/', $by, $matches);
					$dir = (isset($matches[4]) && $matches[4] == '-') ? ' DESC' : ' ASC';

					if(!isset($matches[2]) || trim($matches[2]) == '') {
						$orders[] = $alias.'.'.$matches[1].$dir;
					}
					else
					{
						$roleName  = $matches[1];
						$attribute = $matches[3];

						$alias  = $this->aliases[$roleName];
						$orders[] = $alias.'.'.$attribute.$dir;
					}
				}
			}

			$order = implode(', ', $orders);
		}

		return $order;
	}

	/**
	 * return GROUP BY part of the MySQL query
	 *
	 * @access protected
	 * @return string
	 */
	protected function createGroup()
	{
		$group = NULL;
		$qGroups = $this->query->getGroup();

		if(count($qGroups) > 0)
		{
			$fGroup = array();
			foreach($qGroups as $qGroup)
			{
				if(trim($qGroup) == '')
					continue;
				else
				{
					$alias = $this->aliases['$self$'];

					// also allow MySQL-functions now in group by
					preg_match('/^(([a-z0-9_-]+)\()?\s*(([a-z0-9_-]+)(:([a-z0-9_-]+))?)\s*(\))?$/i', $qGroup, $matches);
					if(!isset($matches[5]) || trim($matches[5]) == '') {
						$withAlias = $alias.'.'.$matches[4];
					}
					else
					{
						$roleName  = $matches[4];
						$attribute = $matches[6];
						$alias     = $this->aliases[$roleName];

						$withAlias = $alias.'.'.$attribute;
					}

					if(!isset($matches[1]) || trim($matches[1]) == '') {
						$fGroup[] = $withAlias;
					}
					else {
						$fGroup[] = $matches[2].'('.$withAlias.')';
					}
				}
			}
			$group = implode(', ', $fGroup);
		}

		return $group;
	}

	/**
	 * return LIMIT part of the MySQL query
	 *
	 * @access protected
	 * @return string
	 */
	protected function createLimit()
	{
		$qLimit = $this->query->getLimit();

		$limit = NULL;
		if(isset($qLimit['limit']) && intval($qLimit['limit']) > 0)
		{
			if(isset($qLimit['start']) && intval($qLimit['start']) > -1)
				$limit = intval($qLimit['start']) . ', ' . intval($qLimit['limit']);
			else
				$limit = intval($qLimit['limit']);
		}
		else if((isset($qLimit['limit']) && intval($qLimit['limit']) == 0) && (isset($qLimit['start']) && intval($qLimit['start']) > 0)) {
			$limit = intval($qLimit['start']).', 9999999999999999999';
		}

		return $limit;
	}
}