<?php
/**
 * One_Query_Renderer handles a One_Query instance for a specified datasource
 *

ONEDISCLAIMER
 * @abstract
 **/
abstract class One_Query_Renderer_Abstract implements One_Query_Renderer_Interface
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
	 * Renders the query from the One_Query instance
	 *
	 * @param One_Query $query
	 * @return mixed
	 * @abstract
	 */
	public function render(One_Query $query) {}

	/**
	 * Returns the scheme of the One_Query instance
	 *
	 * @return One_Scheme
	 */
	public function getInstanceScheme()
	{
		$selects = $this->query->getSelect();

		$instanceScheme = $this->query->getScheme();

		$usedSchemes = array();
		foreach($selects as $select)
		{
			if(preg_match('/([^\(]+\()?([^:]+):([^:]+)/', $select, $matches) > 0)
			{
				$usedSchemes[] = trim($matches[2]);
			}
		}

		if(count($usedSchemes) == 1)
		{
			$this->defineRole($usedSchemes[0]);
			$instanceScheme = $this->joins[$usedSchemes[0]]['scheme'];
		}

		return $instanceScheme;
	}

	public function formatAttribute(One_Scheme_Attribute $attribute, $value)
	{
		return $value;
	}
}