<?php
class One_Script_Package_One extends One_Script_Package
{
	public function view( $model, $viewName = 'default', $section = NULL, $options = array() )
	{
		// TR 20100414 Removed check for an array, what about list views? and removed with check on first element
//		if (is_array($model)) {
//			return 'Error in one:view ' . $viewName . ' : model is an array.';
//		}

		if( is_array( $model ) )
			$instanced = $model[ 0 ];
		else
			$instanced = $model;

		$view = new One_View( $instanced, $viewName );
    	$view->setModel( $model );

    	if( count( $options ) > 0 )
    	{
    		foreach( $options as $opt => $val )
    		{
    			$view->set( $opt, $val );
    		}
    	}

		return $view->show( $section );
	}

	/**
	 * Run the macroView over the model (which is an array)
	 * and use the detailView on each item.
	 *
	 * @param One_Model& $model
	 * @param string $topViewName
	 * @param string $detailViewName
	 * @return string
	 */
	public function map( $model, $topViewName, $detailViewName = 'default' )
	{
		if (!is_array($model)) {
			return 'Error in one:map ' .  $topViewName .  ' : model is not an array.';
		}
		$view = new One_View( $model, 'macro' . '/' . $topViewName );
    	$view->setModel( $model );
    	$view->set( 'detailView', $detailViewName );
		return $view->show();
	}

	public function select( $schemeName, $selectors = array() )
	{
		$factory = One_Repository::getFactory( $schemeName );
		return $factory->select( $selectors );
	}

	public function selectAll( $scheme )
	{
		$factory = One_Repository::getFactory( $scheme );
		$q = $factory->selectQuery();
		return $q->result();
	}

	public function selectSet( $scheme, $start, $count, $order )
	{
		$factory = One_Repository::getFactory( $scheme );
		$q = $factory->selectQuery();
		$q->setOrder( $order );
		$q->setLimit( $limit, $start );
		return $q->result();
	}

	public function selectQueryScheme( $scheme )
	{
		$q = One_Repository::selectSet( $scheme);

		return $q;
	}

	public function selectQuery( $scheme )
	{
		$factory = One_Repository::getFactory( $scheme );
		$q = $factory->selectQuery();
		return $q;
	}

	public function selectOne( $scheme, $id )
	{
		$factory = One_Repository::getFactory( $scheme );
		$result  = $factory->selectOne( $id );
		return $result;
	}

	public function selectCount( $scheme, $options = null )
	{


		$factory = One_Repository::getFactory( $scheme );
		$q = $factory->selectQuery();


		$condition = $options['query'];
		if ($condition) {
			/*$filters = array();
			parse_str( $this->getVariable('filters', ''), $filters );*/

			$c = One_Repository::getFilter( $condition, $q->getScheme()->getName() );
			$c->affect( $q );
		}

		return $q->getCount( $q );
	}

	public function loadAll( &$storeName, $sql, $className = 'stdClass' )
	{
		$store = One_Repository::getStore( $storeName );
		$result = $store->loadAll( $sql, $className );

		return $result;
	}

	public function loadAssocList( $sql, $var1, $var2 )
	{
		$result = mysql_query($sql);

		while($row = mysql_fetch_array($result)){
			$my_array[$row[$var1]] = $row[$var2];
			}

		return $my_array;
	}

	public function error( $msg )
	{
		throw new One_Exception( $msg );
	}

	public function getOnePath()
	{
		return ONE_LIB_PATH;
	}

	public function getOneSitePath()
	{
		return One_Config::getInstance()->getUrl();
	}

	public function getFactory( $schemeName )
	{
		return One_Repository::getFactory( $schemeName );
	}

	public function getScheme( $schemeName )
	{
		return One::meta("schemes/$schemeName");
	}

	public function getIdentityValue( One_Model $model )
	{
		$idAttr = $model->getScheme()->getIdentityAttribute()->getName();
		return $model->$idAttr;
	}

	public function getVar( One_Model $model, $var )
	{
		$parts = explode( ':', $var, 2 );

		if( count( $parts ) == 2 )
		{
			$role            = $parts[ 0 ];
			$targetAttribute = $parts[ 1 ];
			return $model->$role->$targetAttribute;
		}
		else
			return $model->$var;
	}

	public function getInstance( $schemeName )
	{
		$fac      = One_Repository::getFactory( $schemeName );
		$instance = $fac->getInstance();

		return $instance;
	}

  //DEPRECATED
	public function parseModelScript( One_Model $model, $script )
	{
		$ns      = new One_Script();
		$output  = $ns->executeString( $script, array( 'model' => $model ) );

		if( $ns->isError() )
			$output = $ns->error;

		return $output;
	}

	public function getSession()
	{
		$session = new One_Session();
		return $session;
	}

	public function getCurrentLanguage()
	{
		return One_Config::get('app.language');
	}

	public function getOneInstance()
	{
		return One_Config::getInstance();
	}

	public function getVendorInstance()
	{
		return One_Vendor::getInstance();
	}

	/**
	 * Use a static function from One_Repository
	 * @param string $function The function's name
	 * @param mixed Multiple parameters can be given according to the needs of the One_Repository function
	 * @return mixed
	 */
	public function repository($function)
	{
		$params = func_get_args();

		if(is_array($params) && 1 < count($params)) {
			array_shift($params);
		}
		else {
			$params = array();
		}

		return call_user_func_array(array('One_Repository', $function), $params);
	}

	public function getAlias(One_Model $model)
	{
		$scheme = $model->getScheme();
    $aliasOpts = $scheme->get('behaviorOptions.linkalias' );
		if(null !== $behaviorOpts && isset($behaviorOpts['attribute']))
		{
			$aliasField = $behaviorOpts['attribute'];
			$alias = $model->$aliasField;
		}
		elseif(isset($model->slug)) {
			$alias = $model->slug;
		}
		else {
			$idAttr = $model->getIdentityName();
			$alias = $model->$idAttr;
		}

		return $alias;

	}

	public function stop() {
		exit;
	}
}
