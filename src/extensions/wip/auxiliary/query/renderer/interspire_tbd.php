<?php
/**
 * One_Renderer_Interspire handles a One_Query instance for Interspire
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Query_Renderer_Interspire extends One_Query_Renderer_Abstract
{
	/**
	 * @var One_Query
	 * @access protected
	 */
	protected $query;

	/**
	 * @var One_Scheme
	 * @access protected
	 */
	protected $scheme;

	/**
	 * @var array
	 * @access protected
	 */
	protected $joins = array();

	/**
	 * @var array
	 * @access protected
	 */
	protected $aliases = array();

	/**
	 * One_Renderer_Interspire renders an instance of One_Query into an understandable "query" for Interspire
	 * @param One_Query $query
	 * @return array
	 */
	public function render(One_Query $query)
	{
		$this->query  = $query;
		$this->scheme = $this->query->getScheme();
		$resources    = $this->scheme->getResources();

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
						$filter = One_Repository::getFilter( $filterName, $query->getScheme()->name() );
						$filter->affect( $query );
					}
				}
			}
		}

		$details = array();
		// For interspire, you can basicly only use the where clauses and even still only certain fields
		// The validation of the fields must be done in the functions themselves because they are too random
		// get where clauses
		$whereClauses = $query->getWhereClauses();
		if( !is_null( $whereClauses ) )
		{
			$details = $this->whereClauses( $whereClauses );
		}

		$order = $query->getOrder();
		if( !is_null( $query->getOrder() ) )
		{
			$details[ 'SortInfo' ] = $this->createOrder();
		}

		return $details;
	}

	/**
	 * Returns the scheme of the One_Query instance
	 *
	 * @return One_Scheme
	 */
	public function getInstanceScheme()
	{
		$instanceScheme = $this->query->getScheme();
		return $instanceScheme;
	}

	/**
	 * Parses all where clauses of the One_Query instance to understandable data for Interspire
	 *
	 * @param array $whereclauses
	 * @return array
	 */
	private function whereClauses( $whereclauses )
	{
		// all fields are inherently AND for interspire
		// also no "sub-ANDs" are allowed, only the first level will be read
		$details = array();
		foreach( $whereclauses as $whereclause )
		{
			$details[ $whereclause->attribute ] = $whereclause->value;
		}

		return $details;
	}

	/**
	 * Parses the order from the One_Query instance to understandable data for Onterspire
	 *
	 * @return array
	 */
	private function createOrder()
	{
		$qOrders = $this->query->getOrder();

		foreach( $qOrders as $qOrder )
		{
			preg_match( '/^([a-zA-Z0-9_]+)*(\+|\-)?$/', $qOrder, $matches );
			$dir = ( $matches[ 2 ] == '-' ) ? ' DESC' : 'ASC';

			$order = array(
								'SortBy' => $matches[ 1 ],
								'Direction' => $dir
							);
		}

		return $order;
	}
}