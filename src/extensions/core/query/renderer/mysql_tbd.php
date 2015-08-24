<?php
/**
 * One_Query_Renderer_Mysql handles a One_Query instance for MySQL
 *

ONEDISCLAIMER
 **/
class One_Query_Renderer_Mysql extends One_Query_Renderer_Abstract
{
	/**
	 * @var string The table to look for data in
	 * @access protected
	 */
	protected $mainTable;

	/**
	 * Render the query
	 *
	 * @param One_Query $query
	 * @return string
	 */
	public function render( One_Query $query, $overrideFilters = false )
	{
		$this->query  = $query;
		$this->scheme = $this->query->getScheme();

		// if the person wants to perform a raw query, return the raw query
		if( !is_null( $query->getRaw() ) )
		{
			if(One_Config::get('debug.query'))
			{
				echo '<pre>';
				var_dump( $query->getRaw() );
				echo '</pre>';
			}
			return $query->getRaw();
		}

		$this->query  = $query;
		$this->scheme = $this->query->getScheme();
		$resources    = $this->scheme->getResources();

		// fetch main table to fetch data from
		$this->mainTable = $resources[ 'table' ];

		$this->defineRole( '$self$' );

		// add possible filters to the query
		if( !$overrideFilters && isset( $resources['filter'] ) )
		{
			$filters = explode( ';', $resources['filter'] );
			if( count( $filters ) > 0 )
			{
				foreach( $filters as $filterName )
				{
					if( $filterName != '' )
					{
						$filter = One_Repository::getFilter( $filterName, $query->getScheme()->getName() );
						$filter->affect( $query );
					}
				}
			}
		}

		// TR20100531 No longer needs to be run after the rest since joins are now checked while adding
		// sselects, order, ...
		$joins  = NULL;
		$qJoins = $query->getJoins();
		if( count( $qJoins ) > 0 )
		{
			foreach( $qJoins as $join => $type )
			{
				$this->defineRole( $join );
				$query->setRoleAlias( $join, $this->aliases[ $join ] );
				$joins .= $this->createJoin( $query->getRole( $join ), $type );
			}
		}

    // *** TODO: change the '*' to only the relavant fields defined in the scheme
		$selects = $this->aliases[ '$self$' ] . '.*';
		if( count( $query->getSelect() ) > 0 )
			$selects = $this->createSelects( $query->getSelect() );

		// get where clauses
		$whereClauses = $query->getWhereClauses();
		$where = NULL;
		if( !is_null( $whereClauses ) )
		{
			$where = $this->whereClauses( $whereClauses );
		}

		// get having clauses
		$havingClauses = $query->getHavingClauses();
		$having = NULL;
		if( !is_null( $havingClauses ) )
		{
			$having = $this->whereClauses( $havingClauses );
		}

		// get order
		$order = $this->createOrder();

		//get grouping
		$group = $this->createGroup();

		// get limit
		$limit = $this->createLimit();

		$sql = 'SELECT ' . $selects . ' FROM `' . $this->mainTable . '` ' . $this->aliases[ '$self$' ];

		if( !is_null( $joins ) )
			$sql .= $joins;

		if( !is_null( $where ) )
			$sql .= ' WHERE ' . $where;

		if( !is_null( $group ) )
			$sql .= ' GROUP BY ' . $group;

		if( !is_null( $having ) )
			$sql .= ' HAVING ' . $having;

		if( !is_null( $order ) )
			$sql .= ' ORDER BY ' . $order;

		if( !is_null( $limit ) )
			$sql .= ' LIMIT ' . $limit;

		if(One_Config::get('debug.query'))
		{
			echo '<pre>';
			var_dump( $sql );
			echo '</pre>';
		}

		return $sql;
	}

	/**
	 * Generate the select part of the query
	 *
	 * @param array $selects
	 * @return string
	 * @access protected
	 */
	protected function createSelects( array $selects )
	{
		$tmp = array();
		foreach( $selects as $select )
		{
			if( preg_match( '/\(/', $select ) == 0 )
			{
				preg_match( '/^([a-z0-9_-]+(:[a-z0-9_-]+|:[*]?)?)\s*(\s+as\s+([a-z0-9_-]+))?$/i', trim($select), $fMatches);

				$attribute = trim($fMatches[1]);
				$table     = $this->mainTable;
				$alias     = $this->aliases[ '$self$' ];
				if( preg_match( '/([^:]+):([^:]+)/', $attribute, $matches ) > 0 )
				{
					$roleName  = $matches[1];
					$attribute = $matches[2];

					$scheme = $this->joins[ $roleName ][ 'scheme' ];
					$table  = $this->joins[ $roleName ][ 'table' ];
					$alias  = $this->aliases[ $roleName ];

					$attWithAlias = $alias.'.'.$attribute;
				}
				else
				{
					$scheme = $this->getInstanceScheme();
					$at     = $scheme->getAttribute($attribute);
					if(null !== $at && $at->getType() instanceof One_Type_Calculated_Interface)
					{
						$type = $at->getType();
						$calcAttr = $this->getCalcAttribute($type);
						$attWithAlias = $calcAttr;
						if(is_null($fMatches[3])) {
							$attWithAlias .= ' AS "'.$attribute.'"';
						}
					}
					else {
						$attWithAlias = $alias.'.'.$attribute;
					}
				}

				if(isset($fMatches[3]) && !is_null($fMatches[3])) {
					$attWithAlias .= ' AS "'.$fMatches[4].'"';
				}

				$tmp[] = $attWithAlias;
			}
			else if( preg_match( '/^([a-z0-9_-]+)\(\s*([a-z0-9_-]+(:[a-z0-9_-]+)?)\s*\)(\s+as\s+([a-z0-9_-]+))?$/i', trim( $select ), $fMatches ) > 0 )
			{
				$attribute = $fMatches[2];
				$table     = $this->mainTable;
				$alias     = $this->aliases[ '$self$' ];
				if( preg_match( '/([^:]+):([^:]+)/', $attribute, $aMatches ) > 0 )
				{
					$roleName  = $aMatches[1];
					$attribute = $aMatches[2];

					$scheme = $this->joins[ $roleName ][ 'scheme' ];
					$table  = $this->joins[ $roleName ][ 'table' ];
					$alias  = $this->aliases[ $roleName ];
				}
				else
				{
					$scheme = $this->getInstanceScheme();
					$at     = $scheme->getAttribute($attribute);
					if(null !== $at && $at->getType() instanceof One_Type_Calculated_Interface)
					{
						$type      = $at->getType();
						$calcAttr  = $this->getCalcAttribute($type);
						$calcParts = explode('.', $calcAttr, 2);
						$alias     = $calcParts[0];
						$attribute = $calcParts[1];
					}
				}

				$setAs = preg_replace( '/^([a-z0-9_-]+)\(\s*([a-z0-9_-]+(:[a-z0-9_-]+)?)\s*\)(\s+as\s+([a-z0-9_-]+))?$/i', '${1}( ' . $alias . '.' . $attribute . ' )', trim( $select ) );
				if( isset($fMatches[ 5 ]) && !is_null( $fMatches[ 5 ] ) )
					$setAs .= ' AS `' . $fMatches[ 5 ] . '`';
				$tmp[] = $setAs;
			}
			else
				$tmp[] = $select;
		}

		return implode( ', ', $tmp );
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
		switch($link['link']->getLinkType())
		{
			case 'onetomany':
				return $this->OneToManyJoin($link, $linkType);
				break;
			case 'manytoone':
				return $this->ManyToOneJoin($link, $linkType);
				break;
			case 'manytomany':
				return $this->ManyToManyJoin($link, $linkType);
				break;
		}
	}

	/**
	 * Generate a OneToMany join
	 *
	 * @access protected
	 * @param array $link
	 * @return string
	 */
	protected function OneToManyJoin(array $link, $linkType = '')
	{
		$target = One_Repository::getScheme($link['link']->getTarget());
		$backlinks = $target->getLinks();
		$backlink = $backlinks[$link['link']->getLinkId()];
		if(!$backlink) {
			throw new One_Exception("There is no link with id ".$link['link']->getLinkId()." in scheme ".$link['link']->getTarget());
		}

		$fk = $link['link']->getLinkType()->remoteFK($link['link'], $target, $backlink);
//		$idAtt = $this->scheme->getIdentityAttribute()->getName();
		$idAtt = $this->scheme->getIdentityAttribute()->getColumn();

		$table = $this->joins[$link['link']->getName()]['table'];
		$alias = $this->aliases[$link['link']->getName()];
		$linkType = (trim($linkType) != '') ? ' '.$linkType : '';
		$join = $linkType.' JOIN `'.$table.'` '.$alias.' ON '.$alias.'.'.$fk.' = '.$this->aliases['$self$'].'.'.$idAtt;

		return $join;
	}

	/**
	 * Generate a ManyToOne join
	 *
	 * @access protected
	 * @param array $link
	 * @return string
	 */
	protected function ManyToOneJoin($link, $linkType = '')
	{
		$target = One_Repository::getScheme($link['link']->getTarget());
		$fk = $link['link']->getLinkType()->localFK($link['link'], $target);
//		$idAtt = $link['scheme']->getIdentityAttribute()->getName();
		$idAtt = $link['scheme']->getIdentityAttribute()->getColumn();

		$table = $this->joins[$link['link']->getName()]['table'];
		$alias = $this->aliases[$link['link']->getName()];

		$linkType = (trim($linkType) != '') ? ' '.$linkType : '';
		$join = $linkType.' JOIN `'.$table.'` '.$alias.' ON '.$this->aliases['$self$'].'.'.$fk.' = '.$alias.'.'.$idAtt;

		return $join;
	}

	/**
	 * Generate a ManyToMany join
	 *
	 * @access protected
	 * @param array $link
	 * @return string
	 */
	protected function ManyToManyJoin($link, $linkType = '')
	{
		$source = $this->scheme;
		$target = One_Repository::getScheme($link['link']->getTarget());

		$backlinks = $target->getLinks();
		$backlink = $backlinks[$link['link']->getLinkId()];
		if(!$backlink) {
			throw new One_Exception("There is no link with id ".$link['link']->getLinkId()." in scheme ".$target->getName());
		}

		$fkLocal  = $link['link']->getLinkType()->localFK($link['link'], $target);
		$fkRemote = $link['link']->getLinkType()->remoteFK($link['link'], $source, $backlink );

//		$sourceId = $source->getIdentityAttribute()->getName();
		$sourceId = $source->getIdentityAttribute()->getColumn();
//		$targetId = $target->getIdentityAttribute()->getName();
		$targetId = $target->getIdentityAttribute()->getColumn();

		$lName = $link['alias'];
		if(!isset($this->joins[$lName])) {
			$lName = $link['link']->getName();
		}

		$targetTable = $this->joins[$lName]['table'];
		$targetAlias = $this->aliases[$lName];

		$joinTable = $link['link']->meta['table'];
		$joinAlias = $this->createAlias();

		$linkType = (trim($linkType) != '') ? ' '.$linkType : '';
		$join  = $linkType.' JOIN `'.$joinTable.'` '.$joinAlias.' ON '.$this->aliases['$self$'].'.'.$sourceId.' = '.$joinAlias.'.'.$fkLocal;
		$join .= $linkType.' JOIN `'.$targetTable.'` '.$targetAlias.' ON '.$joinAlias.'.'.$fkRemote.' = '.$targetAlias.'.'.$targetId;

		return $join;
	}

	/**
	 * Generate the WHERE clauses of the query
	 *
	 * @access protected
	 * @param One_Query_Condition_Container $whereClauses
	 * @return string
	 */
	protected function whereClauses( One_Query_Condition_Container $whereClauses )
	{
		$conditions = $whereClauses->getConditions();

		if( count( $conditions ) == 0 )
			return null;
		else if( count( $conditions ) == 1 )
		{
			if( $conditions[0] instanceof One_Query_Condition_Container )
				return $this->whereClauses( $conditions[0] );
			else
				return $this->clause( $conditions[0] );
		}
		else
		{
			$clause = '( ';
			$all = array();
			foreach( $conditions as $condition )
			{
				if( $condition instanceof One_Query_Condition_Container )
					$all[] = $this->whereClauses( $condition );
				else
				{
					$newClause = $this->clause( $condition );
					if( !is_null( $newClause ) )
						$all[] = $newClause;
				}
			}

			if( count( $all ) > 0 )
			{
			
				$clause .= implode( ' ' . $whereClauses->getType() . ' ', $all );
				$clause .= ' )';
			}
			else
				$clause = NULL;
				
			
			return $clause;
		}
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
			$table     = $this->mainTable;
			$alias     = $this->aliases[ '$self$' ];
			$custVal   = false;

			if( preg_match( '/\(/', $condition->attribute ) == 0 )
			{
				if( preg_match( '/([^:]+):([^:]+)/', $condition->attribute, $matches ) > 0 )
				{
					$roleName  = $matches[1];
					$attribute = $matches[2];

					$scheme = $this->joins[$roleName]['scheme'];
					$alias  = $this->aliases[$roleName];
				}
				$alias .= '.';
			}
			else if( preg_match( '/^([a-z0-9_-]+)\(\s*([a-z0-9_-]+(:[a-z0-9_-]+)?)\s*\)$/i', trim( $condition->attribute ), $fMatches ) > 0 )
			{
				$attribute = $fMatches[2];
				if( preg_match( '/([^:]+):([^:]+)/', $attribute, $aMatches ) > 0 )
				{
					$roleName  = $aMatches[1];
					$attribute = $aMatches[2];

					$scheme = $this->joins[ $roleName ][ 'scheme' ];
					$alias  = $this->aliases[ $roleName ];
				}

				$attribute = preg_replace( '/^([a-z0-9_-]+)\(\s*([a-z0-9_-]+(:[a-z0-9_-]+)?)\s*\)$/i', '${1}( ' . $alias . '.' . $attribute . ' )', trim( $condition->attribute ) );
			}

			$ats = $scheme->get('attributes');
			$at  = false;
			if(isset($ats[$attribute])) {
				$at = $ats[$attribute];
			}

			// format value depending on the type of value
			$left = $right = '';
			switch ( trim( strtolower( $condition->op ) ) )
			{
				case 'eq':
					$op = "=";
					break;
				case 'gt':
					$op = ">";
					break;
				case 'gte':
					$op = ">=";
					break;
				case 'lt':
					$op = "<";
					break;
				case 'lte':
					$op = "<=";
					break;
				case 'neq':
					$op = "<>";
					break;
				case 'in':
					$op = "IN";
					$left = "(";
					$right = ")";
					break;
				case 'nin':
					$op = "NOT IN";
					$left = "(";
					$right = ")";
					break;
				case 'ends':
					$op = "LIKE";
					$left = '%';
					break;
				case 'endsnot':
					$op = "NOT LIKE";
					$left = '%';
					break;
				case 'begins':
					$op = "LIKE";
					$right = '%';
					break;
				case 'beginsnot':
					$op = "NOT LIKE";
					$right = '%';
					break;
				case 'contains':
					$op = "LIKE";
					$left = '%';
					$right = '%';
					break;
				case 'containsnot':
					$op = "NOT LIKE";
					$left = '%';
					$right = '%';
					break;
				default:
					$op = $condition->op;
			}

			if( preg_match( '/^custval:([a-z]+)/i', $op, $opmatch ) > 0 )
			{
				$custVal = true;
				$op = strtoupper( $opmatch[ 1 ] );
			}

			$s = NULL;
			if($at)
			{
				if(($at->getType() == 'string' ||  $at->getType() == 'text') && !is_array($condition->value))
				{
					if($custVal || $op == 'IS' && ($condition->value == 'NULL' || $condition->value == 'NOT NULL')) {
						$tmp = $condition->value;
					}
					else {
						$tmp = $at->toString($left.$condition->value.$right);
					}
					if(strlen($tmp) > 2 && preg_match('/^["]{2,}[^"]*["]{2,}$/', $tmp) != false) {
						$tmp = preg_replace( array( '/^""/', '/""$/' ), '"', $tmp );
					}
//					$s = $alias . $at->getName() . ' ' . $op . ' ' . $tmp;
					$s = $alias . $at->getColumn() . ' ' . $op . ' ' . $tmp;
				}
				else if($at->getType() instanceof One_Type_Calculated_Interface)
				{
					$type = $at->getType();
					$tmp  = $at->toString($left.$condition->value.$right);
					if(strlen($tmp) > 2 && preg_match('/^["]{2,}[^"]*["]{2,}$/', $tmp) != false) {
						$tmp = preg_replace( array( '/^""/', '/""$/' ), '"', $tmp );
					}

					$calcAttr = $this->getCalcAttribute($type);
					$s = $calcAttr.' '.$op.' '.$tmp;
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
							if( strlen( $tmp ) > 2 && preg_match( '/^["]{2,}[^"]*["]{2,}$/', $tmp ) != false )
							{
								$tmp = preg_replace( array( '/^""/', '/""$/' ), '"', $tmp );
							}
							$ar[$key] = $tmp;

						}

						if( count( $ar ) > 0 ) {
//							$s = $alias . $at->getName() . ' ' . $op . ' (' . implode( ', ', $ar) . ')';
							$s = $alias . $at->getColumn() . ' ' . $op . ' (' . implode( ', ', $ar) . ')';
            }
					}
					else
					{
						if( $custVal )
							$tmp = $condition->value;
						else
						{
							if( preg_match( '/([a-z_]+)\(\)$/i', $condition->value, $valMatch ) > 0 )
							{
								$mySqlFuncs = $this->MySQLFunctions();
								if( in_array( strtoupper( $valMatch[ 1 ] ), $mySqlFuncs ) )
								{
									$tmp = $condition->value;
								}
							}
							else {
								$tmp = $at->toString($left.$condition->value.$right);
							}
						}
//						$s = $alias . $at->getName() . ' ' . $op . ' ' . $tmp;
						$s = $alias . $at->getColumn() . ' ' . $op . ' ' . $tmp;
					}
				}
			}
			else
			{
				
				if(strtoupper($op) == 'IS' && ($condition->value == 'NOT NULL' || $condition->value == 'NULL'))
					$conditionvalue = $left . $condition->value . $right;
				else
					$conditionvalue = '"' . $left . $condition->value . $right . '"';
				
				if( preg_match( '/\(/', $attribute ) == 0 )
					$s = $alias . '`' . $condition->attribute . '` ' . $op . ' ' . $conditionvalue;			// PD22OCT08: used if a non-attribute is set (FK or scheme for hybrid relations) // TR28JAN10 added $alias to avoid ambiguous fields anything that shouldn't be related to SELF should be marked using the :-notation anyway
				else
					$s = $attribute . ' ' . $op . ' ' . $conditionvalue;			// PD22OCT08: used if a non-attribute is set (FK or scheme for hybrid relations)
				
			}
		}
		else
		{
			$s = $condition->value;
		}
		

		return $s;
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
						if(!$this->query->getScheme()->hasAttribute($matches[1])) {
							throw new One_Exception('Attribute "'.$matches[1].'" does not exist for scheme "'.$this->query->getScheme()->getName().'"', 500);
						}
						$attrType = $this->query->getScheme()->getAttribute($matches[1])->getType();
						if($attrType instanceof One_Type_Calculated_Interface) {
							$calcAttr = $this->getCalcAttribute($attrType);
							$orders[] = $calcAttr.$dir;
						}
						else {
						$orders[] = $alias.'.'.$matches[1].$dir;
						}
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
		if( isset( $qLimit[ 'limit' ] ) && intval( $qLimit[ 'limit' ] ) > 0 )
		{
			if( isset( $qLimit[ 'start' ] ) && intval( $qLimit[ 'start' ] ) > -1 )
				$limit = intval( $qLimit[ 'start' ] ) . ', ' . intval( $qLimit[ 'limit' ] );
			else
				$limit = intval( $qLimit[ 'limit' ] );
		}
		else if((isset($qLimit['limit']) && intval($qLimit['limit']) == 0) && (isset($qLimit['start']) && intval($qLimit['start']) > 0)) {
			$limit = intval($qLimit['start']).', 9999999999999999999';
		}

		return $limit;
	}

	/**
	 * Defines a role into the query.
	 * These are used for join-statements
	 *
	 * @access protected
	 * @param string $role
	 */
	protected function defineRole( $roleName )
	{
		if( $roleName == '$self$' )
		{
			$this->aliases[ '$self$' ] = $this->createAlias();
		}
		else if( !isset( $this->joins[ $roleName ] ) )
		{
			$alias = $this->query->getRoleAlias( $roleName );
			$role  = $this->query->getRole( $alias );

			$this->joins[ $roleName ] = array();
			if( !is_null( $alias ) )
			{
				$role = $this->query->getRole( $alias );
				$this->joins[ $roleName ][ 'link' ]     = $role[ 'link' ];
				$this->joins[ $roleName ][ 'scheme' ]   = $role[ 'scheme' ];
				$this->joins[ $roleName ][ 'table' ]    = $role[ 'table' ];
				$this->joins[ $roleName ][ 'alias' ]    = $role[ 'alias' ];
				$this->aliases[ $roleName ]             = $role[ 'alias' ];
			}
			else if( !is_null( $role ) )
			{
				$this->joins[ $roleName ][ 'link' ]     = $role[ 'link' ];
				$this->joins[ $roleName ][ 'scheme' ]   = $role[ 'scheme' ];
				$this->joins[ $roleName ][ 'table' ]    = $role[ 'table' ];
				$this->joins[ $roleName ][ 'alias' ]    = $role[ 'alias' ];
				$this->aliases[ $roleName ]             = $role[ 'alias' ];
			}
			else
			{
				$link = $this->scheme->getLink( $roleName );
				$scheme = One_Repository::getScheme( $link->getTarget() );

				$resources = $scheme->getResources();
				if(isset($resources['table'])) {
					$table = $resources['table'];
				}
				else {
					throw new One_Exception('There is no table defined for the scheme "'.$scheme->getName().'"');
				}

				$alias = $this->createAlias();

				$this->joins[ $roleName ][ 'link' ]   = $link;
				$this->joins[ $roleName ][ 'scheme' ] = $scheme;
				$this->joins[ $roleName ][ 'table' ]  = $table;
				$this->joins[ $roleName ][ 'alias' ]  = $role[ 'alias' ];
				$this->aliases[ $roleName ] = $alias;
			}
		}
	}

	/**
	 * Generate an alias name
	 *
	 * @return string
	 */
	protected function createAlias()
	{
		return 'a'.substr( md5( microtime() ), 0, 6 );
	}

	public function formatAttribute(One_Scheme_Attribute $attribute, $value)
	{
//		return '`'.$attribute->getName().'` = '.$attribute->toString(mysql_real_escape_string($value));
		return '`'.$attribute->getColumn().'` = '.$attribute->toString(mysql_real_escape_string($value));
	}

	public function getCalcAttribute(One_Type_Calculated_Interface $type)
	{
		$calcAttr = $type->getNativeComposition();
		preg_match_all('/([^\s\(\)]+):/is', $calcAttr, $matches);
		$foundRelations = array_unique($matches[1]);
		foreach($foundRelations as $foundRelation) {
			$calcAttr = str_replace($foundRelation.':', $this->aliases[$foundRelation].'.', $calcAttr);
		}

		return $calcAttr;
	}

	/**
	 * Return all the names of the MySQL functions
	 *
	 * @return array
	 */
	public final function MySQLFunctions()
	{
		return self::$MYSQL_FUNCTIONS;
	}

	/**
	 * @staticvar array List of names of MySQL functions
	 */
	protected static $MYSQL_FUNCTIONS = array(
											'ABS',
											'ACOS',
											'ADDDATE',
											'ADDTIME',
											'AES_DECRYPT',
											'AES_ENCRYPT',
											'ASCII',
											'ASIN',
											'ATAN2',
											'ATAN',
											'ATAN',
											'AVG',
											'BENCHMARK',
											'BIN',
											'BIT_AND',
											'BIT_COUNT',
											'BIT_LENGTH',
											'BIT_OR',
											'BIT_XOR',
											'CAST',
											'CEIL',
											'CEILING',
											'CHAR_LENGTH',
											'CHAR',
											'CHARACTER_LENGTH',
											'CHARSET',
											'COALESCE',
											'COERCIBILITY',
											'COLLATION',
											'COMPRESS',
											'CONCAT_WS',
											'CONCAT',
											'CONNECTION_ID',
											'CONV',
											'CONVERT_TZ',
											'CONVERT',
											'COS',
											'COT',
											'COUNT',
											'CRC32',
											'CURDATE',
											'CURRENT_DATE',
											'CURRENT_TIME',
											'CURRENT_TIMESTAMP',
											'CURRENT_USER',
											'CURTIME',
											'DATABASE',
											'DATE_ADD',
											'DATE_FORMAT',
											'DATE_SUB',
											'DATE',
											'DATEDIFF',
											'DAY',
											'DAYNAME',
											'DAYOFMONTH',
											'DAYOFWEEK',
											'DAYOFYEAR',
											'DECODE',
											'DEFAULT',
											'DEGREES',
											'DES_DECRYPT',
											'DES_ENCRYPT',
											'ELT',
											'ENCODE',
											'ENCRYPT',
											'EXP',
											'EXPORT_SET',
											'EXTRACTVALUE',
											'FIELD',
											'FIND_IN_SET',
											'FLOOR',
											'FORMAT',
											'FOUND_ROWS',
											'FROM_DAYS',
											'FROM_UNIXTIME',
											'GET_FORMAT',
											'GET_LOCK',
											'GREATEST',
											'GROUP_CONCAT',
											'HEX',
											'HOUR',
											'IF',
											'IFNULL',
											'IN',
											'INET_ATON',
											'INET_NTOA',
											'INSERT',
											'INSTR',
											'INTERVAL',
											'IS_FREE_LOCK',
											'IS_USED_LOCK',
											'ISNULL',
											'LAST_INSERT_ID',
											'LCASE',
											'LEAST',
											'LEFT',
											'LENGTH',
											'LN',
											'LOAD_FILE',
											'LOCALTIME',
											'LOCALTIMESTAMP',
											'LOCATE',
											'LOG10',
											'LOG2',
											'LOG',
											'LOWER',
											'LPAD',
											'LTRIM',
											'MAKE_SET',
											'MAKEDATE',
											'MASTER_POS_WAIT',
											'MAX',
											'MD5',
											'MICROSECOND',
											'MID',
											'MIN',
											'MINUTE',
											'MOD',
											'MONTH',
											'MONTHNAME',
											'NAME_CONST',
											'NOT IN',
											'NOW',
											'NULLIF',
											'OCT',
											'OCTET_LENGTH',
											'OLD_PASSWORD',
											'ORD',
											'PASSWORD',
											'PERIOD_ADD',
											'PERIOD_DIFF',
											'PI',
											'POSITION',
											'POW',
											'POWER',
											'PROCEDURE ANALYSE',
											'QUARTER',
											'QUOTE',
											'RADIANS',
											'RAND',
											'RELEASE_LOCK',
											'REPEAT',
											'REPLACE',
											'REVERSE',
											'RIGHT',
											'ROUND',
											'ROW_COUNT',
											'RPAD',
											'RTRIM',
											'SCHEMA',
											'SEC_TO_TIME',
											'SECOND',
											'SESSION_USER',
											'SHA1',
											'SHA',
											'SHA2',
											'SIGN',
											'SIN',
											'SLEEP',
											'SOUNDEX',
											'SPACE',
											'SQRT',
											'STD',
											'STDDEV_POP',
											'STDDEV_SAMP',
											'STDDEV',
											'STR_TO_DATE',
											'STRCMP',
											'SUBDATE',
											'SUBSTR',
											'SUBSTRING_INDEX',
											'SUBSTRING',
											'SUBTIME',
											'SUM',
											'SYSDATE',
											'SYSTEM_USER',
											'TAN',
											'TIME_FORMAT',
											'TIME_TO_SEC',
											'TIME',
											'TIMEDIFF',
											'TIMESTAMP',
											'TIMESTAMPADD',
											'TIMESTAMPDIFF',
											'TO_DAYS',
											'TRIM',
											'TRUNCATE',
											'UCASE',
											'UNCOMPRESS',
											'UNCOMPRESSED_LENGTH',
											'UNHEX',
											'UNIX_TIMESTAMP',
											'UPDATEXML',
											'UPPER',
											'USER',
											'UTC_DATE',
											'UTC_TIME',
											'UTC_TIMESTAMP',
											'UUID_SHORT',
											'UUID',
											'VALUES',
											'VAR_POP',
											'VAR_SAMP',
											'VARIANCE',
											'VERSION',
											'WEEK',
											'WEEKDAY',
											'WEEKOFYEAR',
											'WEIGHT_STRING',
											'YEAR',
											'YEARWEEK',
										);
}