<?php
/**
 * One_Form_Widget_SearchOps are ways to search for a value, EG: equal to a string, greater than some int, ...
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 * @abstract
 **/
abstract class One_Form_Widget_Search_Op_Abstract
{
	/**
	 * @var One_Form_Widget_Abstract What widget is being searched for?
	 */
	protected $forWidget = NULL;

	/**
	 * @var string What type of op is this?
	 */
	protected $type      = NULL;

	/**
	 * Render the op
	 *
	 * @abstract
	 */
	public abstract function render();

	/**
	 * Affect the One_Query appropriately
	 *
	 * @param One_Query $q
	 * @param string $attrName
	 * @param mixed $attrVal
	 * @abstract
	 */
	public abstract function affectQuery( One_Query $q, $attrName, $attrVal );

	/**
	 * Get the allowed operators
	 *
	 * @return array
	 * @abstract
	 */
	public abstract function getAllowed();

	/**
	 * Get the searchvalue
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		$cx   = new One_Context();
		$name = 'op__' . $this->forWidget . '__' . $this->type;
		return $cx->get( $name );
	}

	/**
	 * Parse the output using nanoscript
	 *
	 * @param array $data
	 * @return One_Dom
	 */
	protected function parse( $data = array() )
	{
		$dom = One_Repository::getDom();

    die('this is really deprecated stuff : ' . __FILE __ );

		$templater = One_Config::get('view.templater');
		$oldSearchpath = $templater->getSearchpath();
		$templater->clearSearchpath();

		$language = strtolower(One_Config::get('app.language'));

		$templater->addSearchpath(One_Config::getInstance()->getCustomPath().'/views/'.One_Config::get('app.name').'/oneform/widget/search/op/'.strtolower(One_Config::get('app.language')).'/');
		$templater->addSearchpath(ONE_LIB_PATH.'/views/'.One_Config::get('app.name').'/oneform/widget/search/op/'.strtolower(One_Config::get('app.language')).'/');

		$templater->addSearchpath(One_Config::getInstance()->getCustomPath().'/views/'.One_Config::get('app.name').'/oneform/widget/search/op/');
		$templater->addSearchpath(ONE_LIB_PATH.'/views/'.One_Config::get('app.name').'/oneform/widget/');

		$templater->addSearchpath(One_Config::getInstance()->getCustomPath().'/views/default/oneform/widget/search/op/'.strtolower(One_Config::get('app.language')).'/');
		$templater->addSearchpath(ONE_LIB_PATH.'/views/default/oneform/widget/search/op/'.strtolower(One_Config::get('app.language')).'/');

		$templater->addSearchpath(One_Config::getInstance()->getCustomPath().'/views/default/oneform/widget/search/op/');
		$templater->addSearchpath(ONE_LIB_PATH.'/views/default/oneform/widget/search/op/');

		$templater->setFile($this->type.'.html');
		$templater->setData($data);
		$dom->add($templater->parse());

		$templater->clearSearchpath();
		$templater->setSearchpath($oldSearchpath);

		return $dom;
	}

	/**
	 * Check which operator to use
	 *
	 * @param string $attrName
	 */
	protected static function useOperator( $attrName )
	{
		$op = 'contains';
		$cx = new One_Context();

		foreach( $cx->getRequest() as $key => $value )
		{
			if( preg_match( '{^op__' . preg_replace( '{(\[|\])}', '', $attrName ) . '__(.+)$}i', $key, $type ) > 0 )
			{
				$allowed = $this->allowedOps( $type[ 1 ] );
				if( in_array( $value, $allowed ) )
				{
					$op = $value;
					break;
				}
				else
				{
					$op = $allowed[ 'default' ];
					break;
				}
			}
		}
	}
}