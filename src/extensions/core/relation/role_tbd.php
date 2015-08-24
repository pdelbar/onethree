<?php
/**
 * Class that represents the meta-information of a role in a relation
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Relation_Role
{
	/**
	 * @var string Name of the role in the relationship
	 */
	public $name;

	/**
	 * @var array Meta-information of the role
	 */
	public $meta = array();

	/**
	 * @var string Name of the scheme the role belongs to
	 */
	public $schemeName;

	/**
	 * @var string Is the role "One" or "Many" or "Subscheme"?
	 */
	public $cardinality;

	/**
	 * Class constructor
	 *
	 * @param string $name
	 * @param string $schemeName
	 */
	public function __construct( $name, $schemeName, array $options = array())
	{
		$this->meta = $options;
		$this->name = $name;
		$this->schemeName = $schemeName;
		$this->cardinality = $this->meta['cardinality'] ? $this->meta['cardinality'] : 'one';
	}
}
