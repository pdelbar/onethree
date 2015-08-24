<?php
/**
 * Handles Ajax inserts


  * @TODO review this file and clean up historical code/comments
 **/
class One_Action_Ajaxinsert extends One_Action
{
	public function execute()
	{
		//$this->id = $this->getVariable('id');

		if($this->scheme instanceof One_Scheme) {
			$scheme = $this->scheme;
			$idattr = $this->scheme->getIdentityAttribute();
		}
		else {
			$scheme = One_Repository::getScheme($this->scheme);
			$idattr = $scheme->getIdentityAttribute();
		}

		$idat = $idattr->getName();
		$schemeName = $scheme->getName();

		$this->authorize($schemeName, $this->id);

		$factory = One_Repository::getFactory($schemeName);
		//$model   = $factory->selectOne($this->id);
		$model = $factory->getInstance($schemeName);

		if(is_null($model)) {
			throw new One_Exception('Item could not be found');
		}

		foreach ($scheme->get('attributes') as $attr) {
			$v = $this->getVariable($attr->getName(), null);
			if ($v !== null) {
				$an = $attr->getName();
				$model->$an = $v;
			}
		}
		//$model->$idat = $this->id;
		$model->insert();

		exit();
	}


	/**
	 * Returns whether the user is allowed to perform this task
	 *
	 * @param OneScheme $scheme
	 * @param mixed $id
	 * @return boolean
	 */
	public function authorize($scheme)
	{
		return One_Permission::authorize('ajaxinsert', $scheme, $id);
	}
}
