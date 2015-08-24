<?php
/**
 * Thebehavior will show the identity-attribute when the model is loaded
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Behavior_Scheme_Debug extends One_Behavior_Scheme
{
	/**
	 * Return the name of the behaviour
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'debug';
	}

	/**
	 * This will show the identity-attribute when the model is loaded
	 *
	 * @param One_Scheme $scheme
	 * @param One_Model $model
	 */
	public function afterLoadModel(One_Scheme $scheme, One_Model $model)
	{
		echo '<div style="display: inline; padding: 1px 3px;background-color: darkgreen;margin: 1px 2px;border: 1px solid green;">';
		echo $scheme->getName();
		echo ':';
		$at = $scheme->getIdentityAttribute()->getName();
		echo $model->$at;
		echo '</div>';
	}
}
