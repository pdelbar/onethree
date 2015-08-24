<?php
/**
 * Filter that will allow querystring variables to affect the One_Query
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Filter_Model implements One_Filter_Interface
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
	public function affect(One_Query $query )
	{
		$filters = array();
		$unparsed_filters = $this->filters();

		foreach($unparsed_filters as $key => $value)
		{
			$tmp = explode("__", $key);
			switch($tmp[1])
			{
				case "op":
				case "value":
					$filters[$tmp[0]][$tmp[1]] = $value;
					break;

				default:
					throw new One_Exception('Expecting params in the form attribute__op or attribute__value');
					break;
			}
		}

		foreach ($filters as $attribute => $options)
		{
			if ( trim( $options['op'] ) != '' && trim( $options['value'] ) != '' )
			{
				$query->where( $attribute, $options['op'], $options['value'] );
			} else {
				throw new One_Exception('Expecting both an operator and a value for attribute ' . $attribute );
			}
		}
	}
}
