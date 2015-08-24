<?php
/**
 * One_Query_Renderer_Mysql handles a One_Query instance for XML
 *

ONEDISCLAIMER
 **/
class One_Query_Renderer_Xml extends One_Query_Renderer_Abstract
{
	/**
	 * Renders the One_Query into an xpath that can fetch results from the XML-datastore
	 *
	 * @param One_Query $query
	 */
	public function render( One_Query $query )
	{
		$this->query  = $query;
		$this->scheme = $this->query;
		$resources    = $this->scheme->getResources();

		if( trim( $query->getRaw() ) != '' )
			return $query->getRaw();

		$xpath = $resources[ 'item' ];

		// add possible filters to the query
		if( isset( $resources['filter'] ) )
		{
			$filters = explode( ';', $resources['filter'] );
			if( count( $filters ) > 0 )
			{
				foreach( $filters as $filterName )
				{
					if( $filterName != '' )
					{
						$filter = One_Repository::getFilter( $filterName, $query->name() );
						$filter->affect( $query );
					}
				}
			}
		}

		$whereClauses = $query->getWhereClauses();
		if( !is_null( $whereClauses ) )
		{
			$clauses = $this->whereClauses( $whereClauses );
			if( !is_null( $clauses ) )
				$xpath .= '[' . $clauses . ']';
		}

		return $xpath;
	}

	/**
	 * Generate the WHERE clauses of the query
	 *
	 * @access private
	 * @param One_Query_Condition_Container $whereClauses
	 * @return string
	 */
	private function whereClauses( One_Query_Condition_Container $whereClauses )
	{
		$conditions = $whereClauses->getConditions();

		if( count( $conditions ) == 0 )
			return NULL;
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
	 * @access private
	 * @param One_Query_Condition $condition
	 * @return string
	 */
	private function clause(One_Query_Condition $condition )
	{
		if( $condition->op != 'literal' )
		{
			$scheme    = $this->scheme;
			$attribute = $condition->attribute;
			$custVal   = false;

			$ats = $scheme->get('attributes');
			$at = $ats[ $attribute ];

			$isFunction = false;
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
					$op = "!=";
					break;
				case 'ends':
					$op         = "ends-with";
					$isFunction = true;
					break;
				case 'begins':
					$op         = "starts-with";
					$isFunction = true;
					break;
				case 'contains':
					$op         = "contains";
					$isFunction = true;
					break;
			}

			if( preg_match( '/^custval:([a-z]+)/i', $op, $opmatch ) > 0 )
			{
				$custVal = true;
				$op = strtoupper( $opmatch[ 1 ] );
			}

			$s = NULL;
			if( $at )
			{
				if( $custVal )
					$tmp = $condition->value;
				else
					$tmp = $at->toString( $left . $condition->value . $right );
				if( strlen( $tmp ) > 2 && preg_match( '/^["]{2,}[^"]*["]{2,}$/', $tmp ) != false )
				{
					$tmp = preg_replace( array( '/^""/', '/""$/' ), '"', $tmp );
				}

				if( $isFunction )
					$s = $op . '(' . $at->name() . ',' . $tmp . ')';
				else
					$s = $at->name() . ' ' . $op . ' ' . $tmp;
			}
		}
		else
		{
			$s = $condition->value;
		}

		return $s;
	}

	/**
	 * Returns the scheme of the One_Query instance
	 *
	 * @return One_Scheme
	 */
	public function getInstanceScheme()
	{
		$instanceScheme = $this->query;
		return $instanceScheme;
	}
}