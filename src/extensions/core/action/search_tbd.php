<?php
/**
 * This class handles the search actions,
 * showing a search form as well as fetching and showing the results
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Action_Search extends One_Action
{
	/**
	 * @var string name of the searchform
	 */
	protected $searchform;

	/**
	 * Class constructor
	 *
	 * @param One_Controller $controller
	 * @param array $options
	 */
	public function __construct(One_Controller $controller, $options = array())
	{
		parent::__construct( $controller, $options );

		$view = '' != trim($this->getVariable('view', 'search')) ? trim($this->getVariable('view', 'search')) : 'search';
		$this->searchform = '' != trim($this->getVariable('formfile', 'search')) ? trim($this->getVariable('formfile', 'search')) : 'search';

		$this->view = new One_View($this->scheme, $view);
	}

	/**
	 * This method renders the search form and if needed fetches and show the search results
	 * @see plugins/system/one/lib/action/One_Action#execute()
	 */
	public function execute()
	{
		$this->authorize( $this->scheme, $model->id );

		$results = NULL;
		$model   = One::make($this->scheme->getName());
		$cx      = new One_Context();
		$session = One_Repository::getSession();

		if(!is_null($cx->get('doSearch')))
		{
			$session->remove('usedSearchOptions', 'one_search');
			$results = $this->performSearch();
			$session->set('usedSearchOptions', $this->options, 'one_search');
		}
		else if(!is_null($cx->get('doWeightSearch')))
		{
			$session->remove('usedSearchOptions', 'one_search');
			$results = $this->performWeightSearch();
			$session->set('usedSearchOptions', $this->options, 'one_search');
		}
		else if(isset($this->options['savedSearch']))
		{
			if($session->varExists('usedSearchOptions', 'one_search'))
			{
				$this->options = $session->get('usedSearchOptions', 'one_search');
				if(isset($this->options['doSearch'])) {
					$results = $this->performSearch();
				}
				else if(isset($this->options['doWeightSearch'])) {
					$results = $this->performWeightSearch();
				}
			}
		}
		else {
			$session->remove('usedSearchOptions', 'one_search');
			$session->remove('results', 'one_search');
		}

		$dom = One_Repository::getDom();

		$this->view->setModel( $model );
		$this->view->set( 'scheme', $this->scheme );
		$this->view->set( 'formfile', $this->searchform );
		$this->view->set( 'results', $results );

		$vForm = $this->view->show();
		return $vForm;
	}

	/**
	 * Perform regular search using the searchfilter
	 * @return array
	 */
	protected function performSearch()
	{
		$query   = One_Repository::selectQuery($this->scheme->getName());

		$filter = One_Repository::getFilter('search', $this->scheme->getName(), array_merge($this->options, array('searchform' => $this->searchform, 'scheme' => $this->scheme)));
		$filter->affect($query);

		$this->processQueryConditions($query);

		$results = $query->execute();

		return $results;
	}

	/**
	 * Perform weighted search that returns the results according to matching compatibility
	 * @return array
	 */
	protected function performWeightSearch()
	{
		$session = One_Repository::getSession();
		$allSearchValues = array();
		$results = array(
						'count' => 0,
						'all' => array(),
						'most' => array(),
						'some' => array()
				);

		// Get all used widgets
		$form          = One_Form_Factory::createForm($this->scheme, $this->searchform);
		$widgets       = $this->getWidgets($form, $this->scheme);
		$idAttr        = $this->scheme->getIdentityAttribute()->getName();
		$searchOptions = $this->scheme->get('behaviorOptions.searchable');

		$conditions       = 0;
		$weights          = array();
		$conditionByModel = array();
		$specificWeights  = array();


		foreach($widgets as $widget)
		{
			$widgetData = $widget->getWidgetData(); // Get the widget's name, value and operator


			// Check whether a widget is a specifier or not
			$specifier = false;
			if($widget->getCfg('specifier')) {
				$specifier = true;
			}

			if(null !== $widgetData['value']) // don't check if the value === null
			{

				$allSearchValues[$widgetData['name']] = $widgetData['value'];
				if(false !== strpos($widgetData['name'], ':')) // treat as a related field
				{

					$parts    = explode(':', $widgetData['name'], 2);
					$relation = One_Repository::getRelation($parts[0]); // get the used relation

					$link         = $this->scheme->getLink($parts[1]);
					$targetScheme = One_Repository::getScheme($link->getTarget()); // get the scheme of the related field
					$tidAttr      = $targetScheme->getIdentityAttribute()->getName();
          $tSearchOptions = $this->scheme->get('behaviorOptions.searchable');

					$wOptions = null;
					if(method_exists($widget, 'getOptions')) {
						$wOptions = $widget->getOptions();
					}

					// Get the role of the current scheme as seen from the relation from the target's side
					$otherRole = null;
					foreach($relation->getRoles() as $role) {
						if($parts[1] != $role->name) {
							$otherRole = $role;
						}
					}

					if(null !== $otherRole)
					{
						// Dirty trick to enforce the value(s) as an array so they are easier manageable
						$values = $widgetData['value'];
						if(!is_array($widgetData['value'])) {
							$values = array($widgetData['value']);
						}



						if(null !== $widgetData['operator'])
						{
							$tmpValues = array();
							$op = $widgetData['operator'];
							$allowed = $op->getAllowed();
							$opVal   = $op->getValue();

							if(!is_null($opVal))
							{
								if(in_array($opVal, $allowed)) {
									$op = $opVal;
								}
								else {
									$op = $allowed['default'];
								}
							}
							else {
								$op = $allowed['default'];
							}

							// Perform special operators
							foreach($values as $value) {
								$tQ = One_Repository::selectQuery($targetScheme->getName());
								$tQ->setSelect(array($tidAttr));
								$tQ->where($tidAttr, $op, $value);



								$tmpResults = $tQ->execute(false);

								foreach($tmpResults as $tmpResult) {
									$tmpValues[$tmpResult->$tidAttr] = $tmpResult->$tidAttr;
								}
							}


							$values = $tmpValues;
						}


						// Get all related data // @TODO in the future it could be possible that you don't always get ID's of the related fields, but works for now
						foreach($values as $value)
						{
							$current = One_Repository::selectOne($targetScheme->getName(), $value);
							if(!is_null($current)) {
								if(isset($tSearchOptions['publishField'])) {
									$tPubField = $searchOptions['publishField'];
									if(0 == $current->$tPubField) {
										continue;
									}
								}

								$conditions++;
								$relateds = $current->getRelated($otherRole->name);
								if(null === $relateds) {
									continue;
								}

								if(!is_array($relateds)) {
									$relateds = array($relateds);
								}

								foreach($relateds as $related)
								{


									if(!isset($weights[$related->$idAttr]))
									{
										if(false === $specifier) {
											$weights[$related->$idAttr] = 0;
										}
										$conditionByModel[$related->$idAttr] = array();
									}

									if(!isset($conditionByModel[$related->$idAttr][$widgetData['name']])) {
										$conditionByModel[$related->$idAttr][$widgetData['name']] = array();
									}

									if(isset($wOptions[$value])) {
										$conditionByModel[$related->$idAttr][$widgetData['name']][] = $wOptions[$value];
									}
									else {
										$conditionByModel[$related->$idAttr][$widgetData['name']][] = $value;
									}

									// if the current widget is a specifier, maintain the data in a separate array to perform an array_intersect to
									if(true == $specifier)
									{
										if(!isset($specificWeights[$related->$idAttr])) {
											$specificWeights[$related->$idAttr] = 0;
										}
										$specificWeights[$related->$idAttr]++;
										continue;
									}

									$weights[$related->$idAttr]++;
								}
							}
						}
					}
				} // end look in related fields
				else
				{

					$values = $widgetData['value'];
					if(!is_array($widgetData['value'])) {
						$values = array($widgetData['value']);
					}

					if(null !== $widgetData['operator'])
					{

						$op = $widgetData['operator'];
						$allowed = $op->getAllowed();
						$opVal   = $op->getValue();

						if(!is_null($opVal))
						{
							if(in_array($opVal, $allowed)) {
								$op = $opVal;
							}
							else {
								$op = $allowed['default'];
							}
						}
						else {
							$op = $allowed['default'];
						}
					}else{
						$op = 'eq';
					}

					foreach($values as $value) {
						if('' != trim($value))
						{
							$conditions++;
							$cQ = One_Repository::selectQuery($this->scheme->getName());
							$cQ->setSelect(array($idAttr));
							$cQ->where($widgetData['name'], $op, $value);



							$tmpResults = $cQ->execute(false);

							foreach($tmpResults as $tmpResult)
							{
								if(!isset($weights[$tmpResult->$idAttr]))
								{
									if(false === $specifier) {
										$weights[$tmpResult->$idAttr] = 0;
									}
									$conditionByModel[$tmpResult->$idAttr] = array();
								}

								if(!isset($conditionByModel[$tmpResult->$idAttr][$widgetData['name']])) {
									$conditionByModel[$tmpResult->$idAttr][$widgetData['name']] = array();
								}

								$conditionByModel[$tmpResult->$idAttr][$widgetData['name']][] = $value;


								// if the current widget is a specifier, maintain the data in a separate array to perform an array_intersect to
								if(true == $specifier)
								{
									if(!isset($specificWeights[$tmpResult->$idAttr])) {
										$specificWeights[$tmpResult->$idAttr] = 0;
									}
									$specificWeights[$tmpResult->$idAttr]++;
									continue;
								}

								$weights[$tmpResult->$idAttr]++;
							}
						}
					}

				}
			}
		}

		$tilt = $this->getSearchTilt($conditions);

		foreach($weights as $id => $weight)
		{
			if(0 < count($specificWeights))
			{
				if(false === array_key_exists($id, $specificWeights))
				{
					unset($weights[$id]);
					unset($conditionByModel[$id]);
					continue;
				}
				else {
					$weight += $specificWeights[$id];
				}
			}

			$model = One_Repository::selectOne($this->scheme->getName(), $id); // using selectOne as the models needed here are already in cache, a global search by there id would take more resources
			$model->One_Search_Weight = round((($weight / $conditions) * 100), 2);
			$model->One_Search_Weight_Conditions = $conditions;
			$model->One_Search_Weight_ConditionsMet = $weight;
			$model->One_Search_Weight_ConditionObject = $conditionByModel[$id];

			$type = 'some';
			if($conditions == $weight) {
				$type = 'all';
			}
			elseif($conditions >= $tilt) {
				$type = 'most';
			}
			$results[$type][] = $model;
			$results['count']++;
		}


		foreach(array('all', 'most', 'some') as $type) {
			usort($results[$type], array($this, 'sortResultsByWeight'));
		}

		$session->set('results', $results, 'one_search');

		return $results;
	}

	/**
	 * Get all searchwidgets used by the searchform
	 *
	 * @param One_Form_Container_Form $form
	 * @param One_Scheme $scheme
	 * @param boolean $root is the current container the root container
	 * @param One_Form_Container $container pass the container that should be looked in
	 * @return array
	 */
	protected function getWidgets($form, One_Scheme $scheme, $root = true, $container = NULL)
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

	/**
	 * Processes any possibly given filters and alters the One_Query object accordingly
	 *
	 * @param One_Query $q
	 */
	protected function processQueryConditions($q)
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
	 * USort callback-function that sorts the results by their weight
	 *
	 * @param One_Model $a
	 * @param One_Model $b
	 */
	protected function sortResultsByWeight($a, $b)
	{
		if($a->One_Search_Weight_ConditionsMet == $b->One_Search_Weight_ConditionsMet) {
			return 0;
		}

		return ($a->One_Search_Weight_ConditionsMet < $b->One_Search_Weight_ConditionsMet) ? 1 : -1;
	}

	/**
	 * Get the number of conditions that have to be met for a searchresult to belong to the "met most" or "met some" criteria part
	 * @param int $nrConditions
	 * @return int
	 */
	protected function getSearchTilt($nrConditions)
	{
		$tilt = round($nrConditions * 0.65);

		return $tilt;
	}

	/**
	 * Returns whether the user is allowed to perform this task
	 *
	 * @param One_Scheme $scheme
	 * @param mixed $id
	 * @return boolean
	 */
	public function authorize($scheme, $id)
	{
		return One_Permission::authorize( 'search', $scheme->getName(), $id );
	}
}
