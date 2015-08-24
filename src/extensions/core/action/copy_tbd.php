<?php
/**
 * This class handles the copying of an item
 *


  * @TODO review this file and clean up historical code/comments
 **/
class One_Action_Copy extends One_Action
{
	/**
	 * Class constructor
	 *
	 * @param One_Controller_Interface $parent The parent action
	 * @param array $options Additional options
	 */
	public function __construct(One_Controller $controller, $options = array())
	{
		parent::__construct($controller, $options);

		$this->id = $this->getVariable('id', null);
		if($this->id === null)
		{
			$id = $this->scheme->getIdentityAttribute();
			$id = $id->getName();
			$this->id = $this->getVariable($id, null);
		}
	}

	/**
	 * This method copies the current model into a new item
	 */
	public function execute()
	{
		if(!$this->id) {
			throw new One_Exception('Can not copy a non existing item');
		}
		else {
			$factory = One_Repository::getFactory($this->scheme->getName());
			$model   = $factory->selectOne($this->id);

			if(is_null($model))
			{
				throw new One_Exception('Can not copy a non existing item');
			}
		}

		$idAttrName = $model->getScheme()->getIdentityAttribute()->getName();

		$this->authorize($this->scheme->getName(), $model->$idAttrName);

		// Get an instance of the current model
		$copy = clone $model;

		// Not really necessary as when the ID-attribute equals NULL it will treat the model as new
//		$attributes = $this->scheme->getAttributes();
//		foreach($attributes as $attribute)
//		{
//			$attr = $attribute->getName();
//			$copy->$attr = $model->$attr;
//		}


		$copy->$idAttrName = NULL;

		$copy->insert(); // create the copy

		$flow = One_Controller_Flow::getInstance($this->scheme)->getRedirects();
		$todo = is_null($this->getVariable('action')) ? $this->getVariable('task') : $this->getVariable('action');

		$redirect = $flow['default'];
		if(isset($flow['copy'])) {
			$redirect = $flow['copy'];
		}

		if(isset($redirect['id']) && strtoupper(trim($redirect['id'])) == '::ID::') {
			$redirect['id'] = $copy->$idAttrName;
		}

		$redirect = $this->replaceOtherVariables($redirect);

		$this->controller->setRedirect($redirect);
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
		return One_Permission::authorize('copy', $scheme, $id);
	}
}
