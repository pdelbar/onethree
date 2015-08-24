<?php
/**
 * Factory class for getting instances of One_Query_Renderer_Interface
 *


  * @TODO review this file and clean up historical code/comments
 * @subpackage Query

 * @abstract
 **/
class One_Query_Renderer
{
	/**
	 * Get a specified instance of One_Store_Interface
	 * @param string $type
	 * @throws One_Exception
	 * @return One_Store_Interface
	 */
	public static function getInstance($type)
	{
		$className = 'One_Query_Renderer_'.ucfirst(strtolower($type));
		if(class_exists($className)) {
			$store = new $className($name);
			return $store;
		}
		else {
			throw new One_Exception('A query renderer of type "'.$type.'" does not exist');
		}
	}
}