<?php
/**
 * Handles Ajax updates


  * @TODO review this file and clean up historical code/comments
 **/
class One_Action_Ajaxupdate extends One_Action
{
	public function execute()
	{

		$this->id = $this->getVariable('id');
		$fromJQGrid = intval($this->getVariable('fromJqgrid', 0));

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
		$model   = $factory->selectOne($this->id);
		if(is_null($model)) {
			throw new One_Exception('Item could not be found');
		}

		foreach ($scheme->get('attributes') as $attr)
		{

			if($idat != $attr->getName() && !in_array($attr->getName(), array('id', 'oper')))
			{
				$v = $this->getVariable($attr->getName(), null);
				if ($v !== null) {
					$an = $attr->getName();
					$model->$an = $v;
				}
			}
		}

		$model->$idat = $this->id;
		$model->update();

		$context = new One_Context();
		$posted = $context->getPost();

		// Check for posted reltional fields
		foreach($posted as $field => $val)
		{
			if(false === strpos($field, ':')) {
				continue;
			}

			// Get the role and targetAttribute to change
			list($role, $rAttr) = explode(':', $field, 2);

			// Surround in try-catch block to avoid errors on non-existing relations
			try
			{
				// Only save one-to-many relations
				$rel = $model->getRelated($role);
				if(false === is_array($rel) && null !== $rel)
				{
					$tIdAttrName = $rel->getScheme()->getIdentityAttribute()->getName();
					if($rAttr == $tIdAttrName) {
						continue;
					}

					$rel->$rAttr = $val;
					$rel->update();
				}
			}
			catch(Exception $e) {}
		}

		exit;
	}


	/**
	 * Returns whether the user is allowed to perform this task
	 *
	 * @param OneScheme $scheme
	 * @param mixed $id
	 * @return boolean
	 */
	public function authorize($scheme, $id)
	{
		return One_Permission::authorize('ajaxupdate', $scheme, $id);
	}
}
