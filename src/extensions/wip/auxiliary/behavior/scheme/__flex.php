<?php
/**
 * Adds a flex behavior to a scheme
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Behavior_Scheme_Flex extends One_Behavior_Scheme
{
	protected static $_ignoreAttributes = array(
													'scheme',
													'task',
													'action'
												);
	/**
	 * Return the name of the behavior
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'flex';
	}

	/**
	 * Load the flex-fields into the model
	 */
	public function afterLoadModel(One_Scheme $scheme, One_Model $model)
	{
		$ignoreAttributes = array_merge(self::$_ignoreAttributes, $scheme->getForeignKeys()); // ignore all local foreign key attributes as well
    $bOptions     = $scheme->get('behaviorOptions.' . strtolower($this->getName()));
		$flexfield = $bOptions['flexfield'];
		$json = json_decode($model->$flexfield, true);
		if(is_array($json))
		{
			foreach($json as $key => $value)
			{
        //echo $key;
				if(!in_array($key, $ignoreAttributes) && null === $scheme->getAttribute($key)) {
					$model->$key = $value;
				}
			}
		}

		unset($model->$flexfield);
	}

	/**
	 * (non-PHPdoc)
	 * @see plugins/system/one/lib/behavior/One_Behavior_Scheme::beforeInsertModel()
	 */
	public function beforeInsertModel(One_Scheme $scheme, One_Model $model)
	{
		$this->flexToJson($scheme, $model);
	}

	/**
	 * (non-PHPdoc)
	 * @see plugins/system/one/lib/behavior/One_Behavior_Scheme::beforeUpdateModel()
	 */
	public function beforeUpdateModel(One_Scheme $scheme, One_Model $model)
	{
		$this->flexToJson($scheme, $model);
	}

	/**
	 * Convert all the excessive data into a json string
	 *
	 * @param One_Scheme $scheme
	 * @param One_Model $model
	 */
	private function flexToJson(One_Scheme $scheme, One_Model $model)
	{
		$ignoreAttributes = array_merge(self::$_ignoreAttributes, $scheme->getForeignKeys()); // ignore all local foreign key attributes as well
    $bOptions     = $scheme->get('behaviorOptions.' . strtolower($this->getName()));
		$flexfield  = $bOptions['flexfield'];

		unset($model->$flexfield);

		// Flexfields should not be set manually
//		// only auto-set the flexfield, if it's not in the modified fields
//		// if it is set in the modified fields, then the flex field was intentionally set manually
//		if(!array_key_exists($flexfield, $model->getModified()))
//		{
			$data       = $model->toArray();
			$attributes = $scheme->get('attributes');

			foreach($attributes as $attr) {
				unset($data[$attr->getName()]);
			}

			foreach($ignoreAttributes as $ignore) {
				unset($data[$ignore]);
			}

			$model->$flexfield = json_encode($data);
//		}
	}
}
