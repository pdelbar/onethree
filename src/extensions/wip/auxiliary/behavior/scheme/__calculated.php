<?php
/**
 * Adds a flex behavior to a scheme
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Behavior_Scheme_Calculated extends One_Behavior_Scheme
{
	/**
	 * Return the name of the behavior
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'calculated';
	}

	/**
	 * Load the calculated fields into the model
	 */
	public function afterLoadModel(One_Scheme $scheme, One_Model $model)
	{
		$bOptions     = $scheme->get('behaviorOptions.' . strtolower($this->getName()));
		$forAttribute = $bOptions['attribute'];

		$typeClass = 'One_Type_Calculated_'.ucfirst($scheme->getName()).'_'.ucfirst($forAttribute);
		if(class_exists($typeClass))
		{
			$type = new $typeClass();
			$model->$forAttribute = $type->calculate($model);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see plugins/system/one/lib/behavior/One_Behavior_Scheme::beforeInsertModel()
	 */
	public function beforeInsertModel(One_Scheme $scheme, One_Model $model)
	{
		$this->unsetAttribute($scheme, $model);
	}

	/**
	 * (non-PHPdoc)
	 * @see plugins/system/one/lib/behavior/One_Behavior_Scheme::beforeUpdateModel()
	 */
	public function beforeUpdateModel(One_Scheme $scheme, One_Model $model)
	{
		$this->unsetAttribute($scheme, $model);
	}

	/**
	 * Unset the attribute so that it is never inserted or updated
	 * @param One_Scheme $scheme
	 * @param One_Model $model
	 */
	protected function unsetAttribute(One_Scheme $scheme, One_Model $model)
	{
    $bOptions     = $scheme->get('behaviorOptions.' . strtolower($this->getName()));
		$forAttribute = $bOptions['attribute'];

		unset($model->$forAttribute);
	}
}
