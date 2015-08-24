<?php
/**
 * This class handles the removal of chosen items
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Action_Remove extends One_Action
{
	/**
	 * @var mixed the ID of the chosen item
	 */
	protected $id;

	/**
	 * Class constructor
	 *
	 * @param One_Controller $controller
	 * @param array $options
	 */
	public function __construct(One_Controller $controller, $options = array())
	{
		parent::__construct($controller, $options);

		$this->id = $this->getVariable('id');
		if(!is_array($this->id)) {
			$this->id = array($this->id);
		}
	}

	/**
	 * This method removes the chosen item and redirects to the proper page
	 */
	public function execute()
	{
		$factory = One_Repository::getFactory($this->scheme->getName());
		foreach($this->id as $id)
		{
			$this->authorize($this->scheme->getName(), $id);

			$model   = $factory->selectOne($id);
			if(is_null($model))
			{
				throw new One_Exception('Item could not be found');
			}

			$model->delete();
		}

		$redirects = One_Controller_Flow::getInstance($this->scheme)->getRedirects();

		$redirect = $redirects['default'];
		if(isset($redirects['remove'])) {
			$redirect = $redirects['remove'];
		}

		$redirect = $this->replaceOtherVariables($redirect);
		$this->getController()->setRedirect($redirect);
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
		return One_Permission::authorize('remove', $scheme, $id);
	}
}
