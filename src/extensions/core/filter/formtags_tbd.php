<?php
/**
 * Filter that helps searching for results that contain the given string
 *

ONEDISCLAIMER
 **/
class One_Filter_Formtags implements One_Filter_Interface
{
	/**
	 * @var array filters to affect the One_Query with
	 */
	private $_filters = array();

	/**
	 * Class constructor
	 *
	 * @param array $filters
	 */
	public function __construct( $filters = array() )
	{
		$this->_filters = $filters;
	}

	/**
	 * Returns all filters that affects the query
	 *
	 * @return array
	 */
	public function &filters()
	{
		return $this->_filters;
	}

	/**
	 * Apply filters to the queryscheme
	 *
	 * @param One_Query $q
	 */
	public function affect(One_Query $query)
	{
		$cx    = new One_Context();
		$term  = $cx->get('term', '');
		$field = $cx->get('field', $query->getScheme()->getIdentityAttribute()->getName());

		if('' != trim($term)) {
			$query->where($field, 'contains', $term);
		}
		else {
			$query->where('isFalse', 'literal', 'false');
		}
	}
}
