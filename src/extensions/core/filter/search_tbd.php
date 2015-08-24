<?php
/**
 * Filter that alters the One_Query to fetch the correct results
 * filtered with the sent search queries
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Filter_Search implements One_Filter_Interface
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
		$filters    = $this->filters();
		$searchform = $filters['searchform'];
		$cx         = new One_Context();
		$scheme     = $query->getScheme();
		$idAttr     = $scheme->getIdentityAttribute()->getName();

		$form    = One_Form_Factory::createForm($scheme, $searchform);
		$widgets = $this->getWidgets($form, $scheme);

		foreach( $widgets as $widget ) {
			$widget->affectQuery($query);
		}

		// for searchresults, results can always be grouped by id, since we only want to show a same result once
		$query->setGroup($idAttr);
	}

	/**
	 * Get all searchwidgets used by the searchform
	 *
	 * @param OneFormContainerForm $form
	 * @param One_Scheme $scheme
	 * @param boolean $root is the current container the root container
	 * @param OneFormContainer $container pass the container that should be looked in
	 * @return array
	 */
	private function getWidgets($form, One_Scheme $scheme, $root = true, $container = NULL)
	{
		$widgets = array();
		$use = ($root) ? $form : $container;
		foreach($use->getContent() as $widget)
		{
			if($widget instanceof One_Form_Widget_Search) {
				$widgets[] = $widget;
			}
			else if($widget instanceof One_Form_Container_Abstract) {
				$widgets = array_merge($widgets, $this->getWidgets($form, $scheme, false, $widget));
			}
		}

		return $widgets;
	}
}
