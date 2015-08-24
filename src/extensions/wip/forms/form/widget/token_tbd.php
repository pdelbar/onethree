<?php
/**
 * Handles the token widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/

Class One_Form_Widget_Token extends One_Form_Widget_Scalar_Hidden
{
	/**
	 * @var string Value of the token
	 */
	protected $token;

	/**
	 * Class constructor
	 *
	 * @param array $config
	 */
	public function __construct(array $config = array())
	{
		parent::__construct('token', 'token', $config);
		$this->_type = 'token';

		$value = One_Form_Helper::createToken();
		$_SESSION['OneToken'] = $value;
		$this->token = $_SESSION['OneToken'];
	}

	/**
	 * Get the value of this model
	 *
	 * @return string
	 */
	public function getValue( $model )
	{
		return $this->token;
	}

	/**
	 * Get the request value of the widget
	 *
	 * @return mixed
	 */
	public function requestValue()
	{
		return null;
	}

	/**
	 * Bind the model to this widget
	 *
	 * @param One_Model $model
	 */
	public function bindModel( $model )
	{
		return true;
	}

	/**
	 * Is the submitted value valid?
	 *
	 * @return boolean
	 */
	public function validate()
	{
		return true;
	}

	/**
	 * Overrides PHP's native __toString function
	 *
	 * @return string
	 */
	public function __toString()
	{
		return get_class() . ': ' . $this->getID();
	}
}
