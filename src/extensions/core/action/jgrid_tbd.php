<?php
/**
 * This class handles the list view of the chosen item
 *


  * @TODO review this file and clean up historical code/comments
 **/
class One_Action_Jgrid extends One_Action
{
	/**
	 * @var array Array of attributes that should have a filter in the view
	 */
	private $_filters = array();

	/**
	 * @var array Array of all the columns present in the list
	 */
	private $_columns = NULL;

	private $_sort      = NULL;

	private $_sortOrder = NULL;

	private $_limit     = NULL;

	//array to control operators

	private $_operators = array('eq', 'gt', 'gte', 'lt', 'lte', 'neq', 'in', 'nin', 'ends', 'endsnot', 'begins', 'beginsnot', 'contains', 'containsnot');


	/**
	 * Class constructor
	 *
	 * @param One_Controller $controller
	 * @param array $options
	 */
	public function __construct( One_Controller $controller, $options = array() )
	{
		parent::__construct( $controller, $options );
		$this->view = new One_View($this->scheme, 'jgrid');
	}

	/**
	 * Creates a list of items according to the given options ( and filter parameters )
	 *
	 * @return string The list of the items
	 */
	public function execute()
	{
		$this->authorize();

		$cx         = new One_Context();
		$session    = new One_Session();
		$schemeName = $this->scheme->getName();
		$idAttr     = $this->scheme->getIdentityAttribute()->getName();
		$columns    = $this->getColumns();

		// determine limit
		$defaultlimit = (!is_null($this->_limit)) ? $this->_limit : 20;
 		if(!is_null($session->get('limit', $schemeName.'--list'))) {
 			$defaultlimit = $session->get('limit', $schemeName.'--list');
 		}
		$session->set('limit', intval($cx->get('limit', $defaultlimit)), $schemeName.'--list');

		// determine page you're on
 		if(intval($session->get('limit', $schemeName . '--list')) > 0) {
 			$defaultStart = 1;
 			if(!is_null($session->get('start', $schemeName . '--list'))) {
 				$defaultStart = $session->get('start', $schemeName . '--list');
 			}
 			$session->set('start', intval($cx->get('start', $defaultStart)), $schemeName . '--list');
 		}
 		else {
  			$session->set('start', 1, $schemeName . '--list');
 		}
 		// determine sortorder and sortorder direction
  		$defaultSortorder    = (!is_null($this->_sort)) ? $this->_sort : $idAttr;
  		$defaultSortorderDir = (!is_null($this->_sortOrder)) ? $this->_sortOrder : 'asc';
 		if(!is_null($session->get('sortorder', $schemeName . '--list'))) {
 			$defaultSortorder = $session->get('sortorder', $schemeName . '--list');
 		}
 		if(!is_null($session->get('sortorderDir', $schemeName . '--list'))) {
 			$defaultSortorderDir = $session->get('sortorderDir', $schemeName . '--list');
 		}
  		$session->set('sortorder', $cx->get('sortorder', $defaultSortorder), $schemeName . '--list');
 		$session->set('sortorderDir', $cx->get('sortorderDir', $defaultSortorderDir), $schemeName . '--list');

		$data         = $this->getList();
		$results      = $data['results'];
		$rowcount     = $data['count'];
 		$limit        = $session->get('limit', $schemeName.'--list');

 		// If you're page is higher than the available rows, default the page to 1
		// $start        = ($session->get('limit', $schemeName . '--list') * $session->get('start', $schemeName . '--list')) <= $rowcount ? $session->get('start', $schemeName . '--list') : 1;
		if($session->get('limit', $schemeName . '--list') > 0)
		{
	 		$start        = $session->get('start', $schemeName . '--list') <= ceil($rowcount/$session->get('limit', $schemeName . '--list')) ? $session->get('start', $schemeName . '--list') : 1;
	 		$session->set('start', $start, $schemeName . '--list');
		}
		else
		{
			$start = 1;
			$session->set('start', 1, $schemeName . '--list');
		}

		$sortorder    = $session->get('sortorder', $schemeName.'--list');
		$sortorderDir = $session->get('sortorderDir', $schemeName.'--list');
		$counter      = ((($start - 1) * $limit) + 1);
		$pages        = (($limit > 0) ? ceil($rowcount / $limit) : 1);

		$this->view->set('scheme', $this->scheme);
		$this->view->set('columns', $columns);
		$this->view->set('filters', $this->_filters);
		$this->view->set('rowcount', $rowcount);
		$this->view->set('limit', $limit);
		$this->view->set('start', $start);
		$this->view->set('sortorder', $sortorder);
		$this->view->set('sortorderDir', $sortorderDir);
		$this->view->set('counter', $counter);
		$this->view->set('pages', $pages);

		$this->view->setModel($results);
		return $this->view->show();
	}

	/**
	 * Processes any possibly given filters and alters the One_Query object accordingly
	 *
	 * @param One_Query $q
	 * @param array $filters
	 */
	protected function processQueryConditions( $q, $filters )
	{

		$condition = $this->getVariable( 'query', '' );
		if ($condition) {
			$filters = array();
			parse_str( $this->getVariable('filters', ''), $filters );

			$c = One_Repository::getFilter( $condition, $q->getScheme()->getName(), $filters );
			$c->affect( $q );
		}
	}

	/**
	 * Get all the items that correspond with any possible filter parameters
	 *
	 * @return array Array of One_Models
	 */
	private function getList()
	{
		$results    = array();

		$session = new One_Session();
		$cx      = new One_Context();

		$idAttr = $this->scheme->getIdentityAttribute()->getName();

		$limit        = intval( $session->get( 'limit', $this->scheme->getName() . '--list' ) );
		$start        = ( ( $limit > 0 ) ? ( ( $session->get( 'start', $this->scheme->getName() . '--list' ) - 1 ) * $limit ) : 0 );
		$sortorder    = $session->get( 'sortorder', $this->scheme->getName() . '--list' );
		$sortorderDir = $session->get( 'sortorderDir', $this->scheme->getName() . '--list' );

		// setup the query
		$sFac   = One_Repository::getFactory( $this->scheme->getName() );
		$sQuery = $sFac->selectQuery();

		if( count( $this->_filters ) > 0 )
		{
			foreach( $this->_filters as $attr => $val )
			{
				$operator = 'contains';

				if(array_key_exists('operator', $val))
					$operator = $val['operator'];

				if(trim($val['type']) == "daterange") {
					if(is_array($val['value']) && count($val['value']) > 0){

						if(trim($val['value'][0]) != "")
							$sQuery->where( $attr, 'gte', trim( $val['value'][0] ) );

						if(count($val['value']) > 1 && trim($val['value'][1]) != "")
							$sQuery->where( $attr, 'lte', trim( $val['value'][1] ) );
					}
				}else{

					if(trim($val['value']) != "")
						$sQuery->where( $attr, $operator, trim( $val['value'] ) );

				}
			}
		}

		//count them
		$results[ 'count' ] = $sQuery->getCount();

		//PD 16SEP09:  fix pagination if filters reduce the count below the current range
		if ( $results[ 'count' ] < $start ) {
			$start = $limit * floor( $count / $limit );
		}

		$sort       = ( ( trim( $sortorder ) == '' ) ? $this->scheme->getIdentityAttribute()->getName() : trim( $sortorder ) );
		$sortprefix = ( strtolower( $sortorderDir ) == 'asc') ? '+' : '-';

		if( $limit > 0)
			$sQuery->setLimit( $limit, $start );

		if($sort)
			$sQuery->setOrder( $sort.$sortprefix );

		$results[ 'results' ] = $sQuery->execute();
		return $results;
	}

	/**
	 * Gets all the columns that should be shown in the list
	 *
	 * @return array Array of all columns in the form of object with extra data
	 */
	private function getColumns()
	{
		$session    = One_Repository::getSession();

		$exists = true;
		$filename = One_Locator::locateUsing('list.xml',ONE_LOCATOR_ROOTPATTERN .'views/'.One_Config::get('app.name').'/'.$this->scheme->getName(). '/');
		if ($filename === null)
		{
				$exists = false;
		}

		if ($exists) $xml = @simplexml_load_file($filename);

		if( $exists && $xml )
		{
			// JL06JAN2009 - if no sorting was clicked, check if the default sort column was set
			// in the xml file
			$xmlarray_defs = xmlpm($xml, "/view/columns");
			$sort = (string) $xmlarray_defs[0]->attributes()->sort;
			$limit = (string) $xmlarray_defs[0]->attributes()->limit;

			if('' != trim($sort))
			{
				preg_match( '/^([^\+\-]+)(\+|\-)?$/i', $sort, $sortMatch );
				$this->_sortOrder = 'asc';
				$this->_sort      = $sortMatch[ 1 ];
				if(isset( $sortMatch[ 2 ] ) && $sortMatch[ 2 ] == '-') {
					$this->_sortOrder = 'desc';
				}
			}

			if(0 < abs(intval($limit))) {
				$this->_limit = abs(intval($limit));
			}

			$xmlarray = $xmlarray_defs[0]->column;

			$this->_columns = array();
			foreach ($xmlarray as $xmlpart)
			{
				$tmp           = new stdClass();
				$name          = '';
				$setFilter     = false;
				$filterType    = 'text';
				$filterOptions = array();
				$operator = 'contains';

				foreach($xmlpart->attributes() as $key => $val)
				{
					switch( strtolower( $key ) )
					{
						case 'name':
							$tmp->name = (string) $val;
							break;
						case 'label':
							$tmp->label = (string) $val;
							break;
						case 'filter':
								if( $val == 1 )
									$setFilter = true;
								break;
						case 'filtertype':
								if( in_array( strtolower( $val ), array( 'dropdown', 'text', 'daterange' ) ) )
									$filterType = strtolower( $val );
								break;
						case 'filteroptions':
							$options = explode( ';', $val );
							foreach( $options as $option )
							{
								$parts = explode( '=', $option, 2 );
								$filterOptions[ $parts[ 0 ] ] = $parts[ 1 ];
							}
						case 'operator':
							if(in_array((string)$val, $this->_operators))
								$operator = (string) $val;
						default:
							$tmp->$key = (string) $val;
					}
				}

				if( $filterType == 'dropdown' && count( $filterOptions ) == 0 && trim( $tmp->name ) != '')
				{
					preg_match( '/([^:]+)((:)(.+))?/', $tmp->name, $matches );
					if( !is_null( $matches[ 4 ] ) )
					{
						$link         = $this->scheme->getLink( $matches[ 1 ] );

						$target       = One_Repository::getScheme( $link->getTarget() );
						$tAtt         = $matches[ 4 ];

						$tFac = One_Repository::getFactory( $target->getName() );
						$tQ   = $tFac->selectQuery();
						$tQ->setSelect( array( $tAtt ) );
						$tQ->setOrder( array( $matches[ 4 ] . '+' ) );
						$options = $tQ->execute( false );

						foreach( $options as $option )
						{
							$filterOptions[ $option->$tAtt ] = $option->$tAtt;
						}
					}
				}

				//PD16SEP09: if no name is given, interpret the body of the tag as CDATA containing nanoScript
				// TR20100408: change this to only set the name as the label if no name is given.
				if( !isset( $tmp->name ) )
				{
					$tmp->name    = $tmp->label;
				}

				//filter operator defaults to contains
				if( !isset( $tmp->name ) )
				{
					$tmp->operator    = 'contains';
				}

				// TR20100408: change this to interpret as nanoscript if a value is passed to the tag
				if( trim( (string) $xmlpart ) != '' )
				{
					$tmp->nScript = (string) $xmlpart;
				}

				$this->_columns[$tmp->name] = $tmp;

				if( $setFilter )
				{
					if($filterType != 'daterange')
						$value = JRequest::getVar( 's' . $tmp->name, NULL ); // @TODO JRequest doesn't belong here
					else
						$value = array(JRequest::getVar( 's' . $tmp->name . 'Start', NULL ), JRequest::getVar( 's' . $tmp->name . 'End', NULL ));

					if( is_null( $value ) )
						$value = $session->get( $tmp->name, $this->scheme->getName() . '--list' );

					$session->set( $tmp->name, $value, $this->scheme->getName() . '--list' );


					$this->_filters[$tmp->name] = array(
													'label'   => $tmp->label,
													'value'   => $value,
													'type'    => $filterType,
													'options' => $filterOptions,
													'operator' => $operator
												);
				}
			}
		}

		if( is_null( $this->_columns ) )
		{
			$columns = $this->scheme->get('attributes');
			$this->_columns = array();
			foreach( $columns as $name => $column )
			{
				$tmp = new stdClass();
				$tmp->label = $column->getName();
				$tmp->name = $column->getName(); // TR20100317 used to be $column->column() but should not be used anymore

				$this->_columns[ $tmp->name ] = $tmp;
			}
		}
		return $this->_columns;
	}

	/**
	 * Returns whether the user is allowed to perform this task
	 *
	 * @return boolean
	 */
	public function authorize()
	{
		return One_Permission::authorize('list', $this->scheme->getName(), 0);
	}
}
