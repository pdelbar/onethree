<?php
/**
 * One_Query_Renderer_Oci handles a One_Query instance for Oracle
 *

ONEDISCLAIMER
 **/
class One_Query_Renderer_Oci extends One_Query_Renderer_Abstract
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

		$sql = 'SELECT ' . $selects . ' FROM ' . $this->mainTable . ' ' . $this->aliases[ '$self$' ];

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
		{
			/* Use the following format to replace MySQL LIMIT for PL/SQL :

				SELECT * FROM (
						SELECT rownum rnum, a.*
						FROM(
								SELECT fieldA,fieldB
								FROM table
								ORDER BY fieldA
						) a
						WHERE rownum <= START + LIMIT
				)
				WHERE rnum >= START

				** or **

				SELECT rownum rnum, a.*
				FROM(
						SELECT fieldA,fieldB
						FROM table
						ORDER BY fieldA
				) a
				WHERE rownum <= LIMIT


			*/
			$qLimit = $this->query->getLimit();
			if( isset( $qLimit[ 'start' ] ) && intval( $qLimit[ 'start' ] ) > -1 )
			{
				$start = intval( $qLimit[ 'start' ] );
			}
			else
				$start = 0;
			if( isset( $qLimit[ 'limit' ] ) && intval( $qLimit[ 'limit' ] ) > 0 )
			{
				$limit = intval( $qLimit[ 'limit' ] );
			}
			else
				$limit = 50;		// @TODO: clean this up

			// create alias for rownum field
			$rnfield = $this->createAlias();
			$subsel = $this->createAlias();
			$sql = "SELECT rownum $rnfield, $subsel.* FROM ( $sql ) $subsel WHERE rownum <= $limit";
			if ($start) {
				$sql = "SELECT * FROM ( $sql )  WHERE $rnfield > $start";
			}
		}

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
					$setAs .= ' AS ' . $fMatches[ 5 ] . '';
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
		$idAtt = $this->scheme->getIdentityAttribute()->getName();

		$table = $this->joins[$link['link']->getName()]['table'];
		$alias = $this->aliases[$link['link']->getName()];
		$linkType = (trim($linkType) != '') ? ' '.$linkType : '';
		$join = $linkType.' JOIN '.$table.' '.$alias.' ON '.$alias.'.'.$fk.' = '.$this->aliases['$self$'].'.'.$idAtt;

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
		$idAtt = $link['scheme']->getIdentityAttribute()->getName();

		$table = $this->joins[$link['link']->getName()]['table'];
		$alias = $this->aliases[$link['link']->getName()];

		$linkType = (trim($linkType) != '') ? ' '.$linkType : '';
		$join = $linkType.' JOIN '.$table.' '.$alias.' ON '.$this->aliases['$self$'].'.'.$fk.' = '.$alias.'.'.$idAtt;

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

		$sourceId = $source->getIdentityAttribute()->getName();
		$targetId = $target->getIdentityAttribute()->getName();

		$lName = $link['alias'];
		if(!isset($this->joins[$lName])) {
			$lName = $link['link']->getName();
		}

		$targetTable = $this->joins[$lName]['table'];
		$targetAlias = $this->aliases[$lName];

		$joinTable = $link['link']->meta['table'];
		$joinAlias = $this->createAlias();

		$linkType = (trim($linkType) != '') ? ' '.$linkType : '';
		$join  = $linkType.' JOIN '.$joinTable.' '.$joinAlias.' ON '.$this->aliases['$self$'].'.'.$sourceId.' = '.$joinAlias.'.'.$fkLocal;
		$join .= $linkType.' JOIN '.$targetTable.' '.$targetAlias.' ON '.$joinAlias.'.'.$fkRemote.' = '.$targetAlias.'.'.$targetId;

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
	 * Generate the correct representation of this clause
	 *
	 * @access protected
	 * @param One_Query_Condition $condition
	 * @return string
	 */
	protected function clause(One_Query_Condition $condition)
	{
//echo '<hr>',
//print_r($condition);
		// handle 'normal' clauses first, ie. REFERENCE OP LITERALVALUE
		if( $condition->op != 'literal' )
		{
			$scheme    = $this->scheme;
			$attribute = $condition->attribute;		// reference
			$table     = $this->mainTable;
			$alias     = $this->aliases[ '$self$' ];
			$custVal   = false;

			// determine the alias
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
//echo '<br>Looking for attribute |' . $attribute . '|';
			if(isset($ats[$attribute])) {
				$at = $ats[$attribute];
//echo ' and found it';
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
				case 'IS':
					$op = "IS";
					$left = '';
					$right = '';
					break;
				default:
					$op = $condition->op;
			}

			// allow for custom values : REFERENCE OP custval:XXXXXX
			if( preg_match( '/^custval:([a-z]+)/i', $op, $opmatch ) > 0 )
			{
				$custVal = true;
				$op = strtoupper( $opmatch[ 1 ] );
			}
			// avoid quoting NULL
			if ($op == 'IS') {
				$custVal = true;
// echo '<br/>detected IS, set custval = true, de rest : /' . $condition->value . '/';
			}

			// handle the value itself : convert if the attribute type dictates it
			$s = NULL;
			if($at)
			{
//echo '<br>Attribute: ' . $at->getName() . ' : ' . $at->getType();
				// --------------------------------------------------------------------------------------------
				// DATETIME
				// --------------------------------------------------------------------------------------------
				if( $at->getType() == 'datetime' )
				{
					if($custVal) {
						$tmp = $condition->value;
					}
					else {
						$tmp = " TIMESTAMP '" . str_replace("/","-",$left.$condition->value.$right) . "'";
					}
					$s = $alias . $at->getName() . ' ' . $op . ' ' . $tmp;
				}
				// --------------------------------------------------------------------------------------------
				// DATE
				// --------------------------------------------------------------------------------------------
				else if( $at->getType() == 'date' )
				{
					if($custVal) {
						$tmp = $condition->value;
					}
					else {
						$tmp = " DATE '" . $left.$condition->value.$right . "'";
					}
					$s = $alias . $at->getName() . ' ' . $op . ' ' . $tmp;
				}
				// --------------------------------------------------------------------------------------------
				// STRING, TEXT
				// --------------------------------------------------------------------------------------------
				else if(($at->getType() == 'string' ||  $at->getType() == 'text') && !is_array($condition->value))
				{
					if($custVal) {
						$tmp = $condition->value;
//echo '<br>string /' . $condition->value;
					}
					else {
						// $tmp = $at->toString($left.$condition->value.$right);
						$tmp = "'" . $left.$condition->value.$right . "'";
					}
					// patch temporary for dates

					$s = $alias . $at->getName() . ' ' . $op . ' ' . $tmp;
				}
				// --------------------------------------------------------------------------------------------
				// CALCULATED
				// --------------------------------------------------------------------------------------------
				else if($at->getType() instanceof One_Type_Calculated_Interface)
				{
					$type = $at->getType();
					// $tmp  = $at->toString($left.$condition->value.$right);
					if ($custVal)
						$tmp = $condition->value;
					else
						$tmp = "'" . $left.$condition->value.$right . "'";

					$calcAttr = $this->getCalcAttribute($type);
					$s = $calcAttr.' '.$op.' '.$tmp;
				}
				// --------------------------------------------------------------------------------------------
				// REGULAR
				// --------------------------------------------------------------------------------------------
				else
				{
//echo '<br>REGULAR';
					if(is_array($condition->value))
					{
						$ar = array();
						foreach($condition->value as $key => $arg)
						{
							if($custVal) {
								$tmp = $arg;
							}
							else {
								$tmp = "'" . $arg . "'";
							}
							$ar[$key] = $tmp;

						}

						if( count( $ar ) > 0 )
							$s = $alias . $at->getName() . ' ' . $op . ' (' . implode( ', ', $ar) . ')';
					}
					else
					{
						if( $custVal ) {
							$tmp = $condition->value;
//echo '<br>string2 /' . $condition->value;
						}
						else
						{
							if( preg_match( '/([a-z_]+)\(\)$/i', $condition->value, $valMatch ) > 0 )
							{
								$mySqlFuncs = $this->MyOCIFunctions();
								if( in_array( strtoupper( $valMatch[ 1 ] ), $mySqlFuncs ) )
								{
									$tmp = $condition->value;
								}
							}
							else {
								if ($custVal) {
//echo "<br>boo";
									$tmp = $condition->value;
								} else 
									$tmp = "'" .$left.$condition->value.$right. "'";
							}
						}
						$s = $alias . $at->getName() . ' ' . $op . ' ' . $tmp;
					}
				}
			}
			else
			{
				if( preg_match( '/\(/', $attribute ) == 0 ) {
					if ($custVal) {
						$tmp = $condition->value;
//echo '<br>string23 /' . $condition->value;
					} else {
						$tmp = "'" .$left.$condition->value.$right. "'";
//echo '<br>string24 /' . $condition->value;
					}
					$s = $alias . '' . $condition->attribute . ' ' . $op . ' ' . $tmp;			// PD22OCT08: used if a non-attribute is set (FK or scheme for hybrid relations) // TR28JAN10 added $alias to avoid ambiguous fields anything that shouldn't be related to SELF should be marked using the :-notation anyway
				}
				else {
					if ($custVal) {
						$tmp = $condition->value;
//echo '<br>string3 /' . $condition->value;
					} else {
						$tmp = "'" .$left.$condition->value.$right. "'";
//echo '<br>string4 /' . $condition->value;
					}

					$s = $attribute . ' ' . $op . " " . $tmp;			// PD22OCT08: used if a non-attribute is set (FK or scheme for hybrid relations)
				}
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
		return 'oci'.substr( md5( microtime() ), 0, 6 );
	}

	public function formatAttribute(One_Scheme_Attribute $attribute, $value)
	{
		return ''.$attribute->getName().' = '.$attribute->toString(mysql_real_escape_string($value));
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
	public final function MyOCIFunctions()
	{
		return self::$OCI_FUNCTIONS;
	}

	/**
	 * @staticvar array List of names of MySQL functions
	 */
	protected static $OCI_FUNCTIONS = array(
											'ABS',
											'ACOS',
											'ADD_MONTHS',
											'APPENDCHILDXML',
											'ASCII',
											'ASCIISTR',
											'ASIN',
											'ATAN',
											'ATAN2',
											'AVG',
											'BFILENAME',
											'BIN_TO_NUM',
											'BITAND',
											'CARDINALITY',
											'CAST',
											'CEIL',
											'CHARTOROWID',
											'CHR',
											'CLUSTER_ID',
											'CLUSTER_PROBABILITY',
											'CLUSTER_SET',
											'COALESCE',
											'COLLECT',
											'COMPOSE',
											'CONCAT',
											'CONVERT',
											'CORR',
											'COS',
											'COSH',
											'COUNT',
											'COVAR_POP',
											'COVAR_SAMP',
											'CUME_DIST',
											'CURRENT_DATE',
											'CURRENT_TIMESTAMP',
											'CV',
											'DBTIMEZONE',
											'DECODE',
											'DECOMPOSE',
											'DELETEXML',
											'DENSE_RANK',
											'DEPTH',
											'DEREF',
											'DUMP',
											'EMPTY_BLOB',
											'EMPTY_CLOB',
											'EXISTSNODE',
											'EXP',
											'EXTRACT',
											'EXTRACTVALUE',
											'FEATURE_ID',
											'FEATURE_SET',
											'FEATURE_VALUE',
											'FIRST',
											'FIRST_VALUE',
											'FLOOR',
											'FROM_TZ',
											'GREATEST',
											'GROUP_ID',
											'GROUPING',
											'GROUPING_ID',
											'HEXTORAW',
											'INITCAP',
											'INSERTCHILDXML',
											'INSERTXMLBEFORE',
											'INSTR',
											'ITERATION_NUMBER',
											'LAG',
											'LAST',
											'LAST_DAY',
											'LAST_VALUE',
											'LEAD',
											'LEAST',
											'LENGTH',
											'LN',
											'LNNVL',
											'LOCALTIMESTAMP',
											'LOG',
											'LOWER',
											'LPAD',
											'LTRIM',
											'MAKE_REF',
											'MAX',
											'MEDIAN',
											'MIN',
											'MOD',
											'MONTHS_BETWEEN',
											'NANVL',
											'NEW_TIME',
											'NEXT_DAY',
											'NLS_CHARSET_DECL_LEN',
											'NLS_CHARSET_ID',
											'NLS_CHARSET_NAME',
											'NLS_INITCAP',
											'NLS_LOWER',
											'NLS_UPPER',
											'NLSSORT',
											'NTILE',
											'NULLIF',
											'NUMTODSINTERVAL',
											'NUMTOYMINTERVAL',
											'NVL',
											'NVL2',
											'ORA_HASH',
											'PATH',
											'PERCENT_RANK',
											'PERCENTILE_CONT',
											'PERCENTILE_DISC',
											'POWER',
											'POWERMULTISET',
											'POWERMULTISET_BY_CARDINALITY',
											'PREDICTION',
											'PREDICTION_COST',
											'PREDICTION_DETAILS',
											'PREDICTION_PROBABILITY',
											'PREDICTION_SET',
											'PRESENTNNV',
											'PRESENTV',
											'PREVIOUS',
											'RANK',
											'RATIO_TO_REPORT',
											'RAWTOHEX',
											'RAWTONHEX',
											'REF',
											'REFTOHEX',
											'REGEXP_INSTR',
											'REGEXP_REPLACE',
											'REGEXP_SUBSTR',
											'REMAINDER',
											'REPLACE',
											'ROUND',
											'ROW_NUMBER',
											'ROWIDTOCHAR',
											'ROWIDTONCHAR',
											'RPAD',
											'RTRIM',
											'SCN_TO_TIMESTAMP',
											'SESSIONTIMEZONE',
											'SET',
											'SIGN',
											'SIN',
											'SINH',
											'SOUNDEX',
											'SQRT',
											'STATS_BINOMIAL_TEST',
											'STATS_CROSSTAB',
											'STATS_F_TEST',
											'STATS_KS_TEST',
											'STATS_MODE',
											'STATS_MW_TEST',
											'STATS_ONE_WAY_ANOVA',
											'STATS_WSR_TEST',
											'STDDEV',
											'STDDEV_POP',
											'STDDEV_SAMP',
											'SUBSTR',
											'SUM',
											'SYS_CONNECT_BY_PATH',
											'SYS_CONTEXT',
											'SYS_DBURIGEN',
											'SYS_EXTRACT_UTC',
											'SYS_GUID',
											'SYS_TYPEID',
											'SYS_XMLAGG',
											'SYS_XMLGEN',
											'SYSDATE',
											'SYSTIMESTAMP',
											'TAN',
											'TANH',
											'TIMESTAMP_TO_SCN',
											'TO_BINARY_DOUBLE',
											'TO_BINARY_FLOAT',
											'TO_CHAR',
											'TO_CLOB',
											'TO_DATE',
											'TO_DSINTERVAL',
											'TO_LOB',
											'TO_MULTI_BYTE',
											'TO_NCHAR',
											'TO_NCLOB',
											'TO_NUMBER',
											'TO_SINGLE_BYTE',
											'TO_TIMESTAMP',
											'TO_TIMESTAMP_TZ',
											'TO_YMINTERVAL',
											'TRANSLATE',
											'TREAT',
											'TRIM',
											'TRUNC',
											'TZ_OFFSET',
											'UID',
											'UNISTR',
											'UPDATEXML',
											'UPPER',
											'USER',
											'USERENV',
											'VALUE',
											'VAR_POP',
											'VAR_SAMP',
											'VARIANCE',
											'VSIZE',
											'WIDTH_BUCKET',
											'XMLAGG',
											'XMLCDATA',
											'XMLCOLATTVAL',
											'XMLCOMMENT',
											'XMLCONCAT',
											'XMLFOREST',
											'XMLPARSE',
											'XMLPI',
											'XMLQUERY',
											'XMLROOT',
											'XMLSEQUENCE',
											'XMLSERIALIZE',
											'XMLTABLE',
											'XMLTRANSFORM',

										);
}