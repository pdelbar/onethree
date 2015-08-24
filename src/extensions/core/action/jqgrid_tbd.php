<?php
/**
 * This class handles the list view of the chosen item
 *
 * @name OneActionList


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Action_Jqgrid extends One_Action
{
	/**
	 * Class constructor
	 *
	 * @param OneController $controller
	 * @param array $options
	 */
	public function __construct(One_Controller_Interface $controller, array $options = array())
	{
		parent::__construct($controller, $options);

		$view = $this->getVariable('view', 'list');
		$this->view = new One_View($this->scheme, $view);
	}

	/**
	 * This method returns the list view of the currently chosen item
	 *
	 * @return string The list view of the currently chosen item
	 */
	public function execute()
	{
		$this->authorize();

		$results = $this->getData();
		$total   = $this->getData(true);

		$data = array();
		$data['page'] = $this->getVariable('page', 1);

		if(0 >= intval($this->getVariable('rows', 10))) {
			$data['total'] = 1;
		}
		else {
			$data['total'] = ceil($total/intval($this->getVariable('rows', 10)));
		}
		$data['records'] = $total;

		$formula = oneScriptPackageJqgrid::rowFormula($this->scheme);
		$idat = $this->scheme->getIdentityAttribute()->getName();
		foreach($results as $r)
		{
			$a = array();
			$a['id'] = $r->$idat;
			$a['cell'] = oneScriptPackageJqgrid::createRow($r, $formula);
			$data['rows'][] = $a;
		}

		header('Content-type: application/json');
		header("Cache-Control: no-cache");
		header("Pragma: no-cache");
		header("Expires: Fri, 01 Jan 2010 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		echo json_encode($data);
		exit;
	}

	protected function getData($countOnly = false)
	{
		$page = $this->getVariable('page', 1);
		$rows = $this->getVariable('rows', 10);
		$start = $page * $rows - $rows;
		$cnt   = $rows;

		$factory = One_Repository::getFactory($this->scheme->getName());
		$q       = $factory->selectQuery($this->scheme->getName());

		$sort = $this->getSortOrder();

		$q->setOrder($sort);

		if(false === $countOnly) {
			$q->setLimit($cnt, $start);
		}

		list($titles,$cols,$params) = oneScriptPackageJqgrid::init($this->scheme->getName());
		$formula = array();
		foreach($cols as $col)
		{
			if(isset($col['name'])) {
				$formula[ $col['name'] ] = $col;
			}
			else {
				$formula[] = $col;
			}
		}



		// Perform search
		//	_search	true
		// filters	{"groupOp":"AND","rules":[{"field":"account_id","op":"bw","data":"un"}]}
		if($this->getVariable('_search', 'false') == 'true')
		{
			$fs = $this->getVariable('filters', '');
			$fs = json_decode($fs);
			foreach($fs->rules as $rule)
			{
				$selector = $rule->field;
				if (isset($formula[$selector]['searchby'])) {
					$selector = $formula[$selector]['searchby'];
				}

				$op = $rule->op;
				if (isset($formula[$rule->field]['searchop'])) {
					$op = $formula[$rule->field]['searchop'];
				}



				// 20110905TR No longer needed, mapped operators properly
//				switch ($op) {
//					case 'bw' :
//					case 'bn' :
//						$rule->data .= '%';
//						break;
//					case 'ew' :
//					case 'en' :
//						$rule->data = '%' . $rule->data;
//						break;
//					case 'nc' :
//					case 'cn' :
//						$rule->data = '%' . $rule->data  . '%';
//						break;
//				}

				$op = isset(self::$opMap[$op]) ? self::$opMap[$op] : $op;

				if($op == 'range'){

					$data = explode(':', $rule->data);

					if(count($data) > 0){
						if(count($data) == 1){
							$q->where($selector, 'begins', trim($data[0]));
						}else{

							$q->where($selector, 'gte', trim($data[0]). ' 00:00:00');
							$q->where($selector, 'lte', trim($data[1]) . ' 23:59:59');
						}
					}
				}else{
					$q->where($selector, $op, $rule->data);
				}
			}
		}

		$this->processQueryConditions($q);
		if(true === $countOnly) {
			$results = $q->getCount();
		}
		else {
			$results = $q->execute();
		}
		return $results;
	}

	protected function getTotalNumberRows()
	{
		$factory = One_Repository::getFactory($this->scheme->getName());
		return $factory->selectCount($this->scheme);
	}

	protected function getSortOrder()
	{
		$sortOrder = $this->getVariable('sidx', '');
		if($this->getVariable('sord', 'asc') == 'desc') {
			$sortOrder .= '-';
		}

		// if there is group ordering, the group field is added to the sidx field, so take it off
		$sort = array();
		foreach(explode(',', $sortOrder) as $piece)
		{
			$piece = explode(' ', trim($piece));
			$field = $piece[0];
			if(isset($piece[1]) && strtolower($piece[1]) == 'desc') {
				$field .= '-';
			}
			$sort[] = $field;
		}

		return $sort;
	}

	/**
	 * Returns whether the user is allowed to perform this task
	 *
	 * @return boolean
	 */
	public function authorize()
	{
		return true;
	}

	protected static $opMap = array(
									'eq'	=> 'eq',
									'ne'	=> 'neq',
									'lt'	=> 'lt',
									'le'	=> 'lte',
									'gt'	=> 'gt',
									'ge'	=> 'gte',
									'bw'	=> 'begins',
									'bn'	=> 'beginsnot',
									'in'	=> 'in',
									'ni'	=> 'nin',
									'ew'	=> 'contains',
									'en'	=> 'containsnot',
									'cn'	=> 'contains',
									'nc'	=> 'containsnot',
								);
}
