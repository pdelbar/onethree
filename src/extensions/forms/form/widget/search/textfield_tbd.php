<?php
/**
 * Handles a searchfield in the form of a textfield
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Search_Textfield extends One_Form_Widget_Search
{
	/**
	 * @var string Type of the field
	 */
	protected $_totf;

	/**
	 * Class constructor
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $label
	 * @param array $config
	 */
	public function __construct( $id = NULL, $name = '', $label = NULL, $config = array() )
	{
		parent::__construct($id, $name, $label, $config );
		$this->_type = 'textfield';
		$this->_totf = 'text';

		// make sure there always is an operator
		if(null === $this->getCfg('optype')) {
			$this->config['optype'] = 'text';
		}
	}

	/**
	 * Return the allowed options for this widget
	 *
	 * @return array
	 */
	protected static function allowedOptions()
	{
		$additional = array(
							'dir' => 1,
							'lang' => 1,
							'xml:lang' => 1,
							'disabled' => 1,
							'maxlength' => 1,
							'readonly' => 1,
							'size' => 1,
							'specifier' => 2,
							'placeholder' => 1 // added for support of chosen jQuery plugin

							);
		return array_merge( parent::allowedOptions(), $additional );
	}

	/**
	 * Render the output of the widget and add it to the DOM
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	protected function _render( $model, One_Dom $d )
	{
		$op = $this->getOp( $this->getCfg('optype') );

		$this->setCfg('class', 'OneFieldInput ' . $this->getCfg('class'));
		$data = array(
						'id' => $this->getID(),
						'name' => $this->getName(),
						'totf' => $this->_totf,
						'op'     =>  (null !== $op) ? $op->render() : NULL,
						'events' => $this->getEventsAsString(),
						'params' => $this->getParametersAsString(),
						'value' => ( is_null( $this->getValue( $model ) ) ? $this->getDefault() : $this->getValue( $model ) ),
						'class' => $this->getCfg('class'),
						'label' => $this->getLabel(),
						'lblLast' => $this->getCfg('lblLast')
					);

		$dom = $this->parse( $model, $data );
		$d->addDom( $dom );
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

	/**
	 * Affect the One_Query appropriately
	 *
	 * @param One_Query $q
	 */
	public function affectQuery( One_Query $q )
	{
		$scheme = $q->getScheme();
		$name   = $this->getName();
		$value  = $this->getValue();
		$op     = $this->getOp( $this->getCfg('optype') );

		if( trim( $value ) != '' )
		{
			if( !is_null( $this->getCfg( 'role' ) ) )
			{
				$parts = explode( ':', $this->getCfg( 'role' ) );
				$role  = $parts[ 1 ];
				$link  = $scheme->getLink( $role );

				if( !is_null( $link ) )
				{
					$target       = $link->getTarget();
					$targetScheme = One_Repository::getScheme( $target );
					$targetAttr   = $this->getCfg( 'targetAttribute' );

					if( !is_null( $targetScheme->getAttribute( $targetAttr ) ) )
					{
						if( !is_null( $op ) )
						{
							$op->affectQuery( $q, $role . ':' . $targetAttr, $value );
						}
						else
						{
							$q->where( $role . ':' . $targetAttr, 'contains', $value ); // default action
						}
					}
				}
			}
			else
			{
				if( !is_null( $scheme->getAttribute( $name ) ) )
				{
					if( !is_null( $op ) )
					{
						$op->affectQuery( $q, $name, $value );
					}
					else
					{
						$q->where( $name, 'contains', $value ); // default action
					}
				}
			}
		}
	}
}
