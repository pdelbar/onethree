<?php
/**
 * Handles a searchfield in the form of a dropdown
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Search_Dropdown extends One_Form_Widget_Search
{
	/**
	 * @var array List of options belonging to the checkboxgroup
	 */
	private $_options = array();

	/**
	 * Class constructor
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $label
	 * @param array $config
	 * @param array $options
	 */
	public function __construct( $id = NULL, $name = '', $label = NULL, array $config = array(), array $options = array() )
	{
		parent::__construct($id, $name, $label, $config );
		$this->_type = 'dropdown';
		$this->setOptions($options);
	}

	/**
	 * Get the searchwidget's name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
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
							'accesskey' => 1,
							'tabindex' => 1,
							'disabled' => 1,
							'size' => 1,
							'multiple' => 1,
							'showEmpty' => 2,
							'options' => 2,
							'specifier' => 2,
							'data-placeholder' => 1 // added for support of chosen jQuery plugin
							);
		return array_merge(parent::allowedOptions(), $additional);
	}

	/**
	 * Return the allowed events for this widget
	 *
	 * @return array
	 */
	protected static function allowedEvents()
	{
		return array(
						'onfocus',
						'onblur',
						'onchange'
					);
	}

	/**
	 * Render the output of the widget and add it to the DOM
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	protected function _render( $model, One_Dom $d )
	{
		if( !is_null( $this->getCfg( 'options' ) ) )
		{
			$this->_options = array();
			$options = explode( ';', $this->getCfg( 'options' ) );
			foreach( $options as $val )
			{
				$parts = explode( '=', $val, 2 );
				$this->_options[ $parts[ 0 ] ] = $parts[ 1 ];
			}
		}

		if( is_null( $this->getCfg( 'multiple' ) ) )
		{
			$tmp = $this->_options;
			$this->_options = array( '' => '---' );
			foreach( $tmp as $key => $val ) // need to do it this way because array_merge reindexes the array
			{
				$this->_options[ $key ] = $val;
			}
		}

		$value   = ( ( count( $this->getValue() ) == 0 ) ? $this->getDefault() : $this->getValue() );

		if( !is_array( $value ) && !is_null( $value ) )
			$value = array( $value );
		else if( is_null( $value ) )
			$value = array();


		$this->setCfg('class', 'OneFieldDropdown ' . $this->getCfg('class'));
		$data = array(
						'id' => $this->getID(),
						'name' => ( ( !is_null( $this->getCfg( 'multiple' ) ) ) ? $this->getName() . '[]' : $this->getName() ),
						'events' => $this->getEventsAsString(),
						'params' => $this->getParametersAsString(),
						'value' => $value,
						'options' => $this->_options,
						'class' => $this->getCfg('class'),
						'label' => $this->getLabel(),
						'lblLast' => $this->getCfg('lblLast')
					);

		$dom = $this->parse( $model, $data );
		$d->addDom( $dom );
	}

	/**
	 * Add an option
	 *
	 * @param array $option
	 */
	public function addOption( array $option )
	{
		$this->_options[$option[0]] = $option[1];
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
	 * Get all the options set for this widget
	 *
	 * @return array
	 */
	public function getOptions()
	{
		return $this->_options;
	}

	/**
	 * Get the value for the widget
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		$session = One_Repository::getSession();
		if($session->varExists('usedSearchOptions', 'one_search')) {
			$cx   = new One_Context($session->get('usedSearchOptions', 'one_search'));
		}
		else {
			$cx   = new One_Context();
		}
		$name  = $this->getName();
		$value = $cx->get( $name );

		if( !is_null( $value ) )
		{
			if( !is_array( $value )  )
			{
				if( trim( $value ) != '' )
					return array( $value );
				else
					return array();
			}
			else
				return $value;
		}
		else
			return array();
	}

	/**
	 * Affect the One_Query appropriately
	 *
	 * @param One_Query $q
	 */
	public function affectQuery(One_Query $q)
	{
		$scheme = $q->getScheme();
		$name   = $this->getName();
		$value  = $this->getValue();
		$op     = $this->getOp($this->getCfg('optype'));

		if(count($value) > 0)
		{
			if(!is_null($this->getCfg('role')))
			{
				$parts = explode(':', $this->getCfg('role'));
				$role  = $parts[ 1 ];
				$link  = $scheme->getLink($role);

				if(!is_null($link))
				{
					$target       = $link->getTarget();
					$targetScheme = One_Repository::getScheme($target);
					$targetAttr   = $targetScheme->getIdentityAttribute()->getName();

					if(!is_null($targetScheme->getAttribute($targetAttr)))
					{
						if(!is_null($op))
						{
							$op->affectQuery($q, $role . ':' . $targetAttr, $value);
						}
						else
						{
							$q->where($role . ':' . $targetAttr, 'in', $value); // default action
						}
					}
				}
			}
			else
			{
				if(!is_null($scheme->getAttribute($name)))
				{
					if(!is_null($op))
					{
						$op->affectQuery($q, $name, $value);
					}
					else
					{
						$q->where($name, 'in', $value); // default action
					}
				}
			}
		}
	}

	/**
	 * Set the options for this widget
	 *
	 * @param array $options
	 */
	public function setOptions( array $options )
	{
		$this->_options = $options;
	}

	/**
	 * Should the options be set when the widget is instantiated
	 *
	 * @return boolean
	 */
	public function setOptionsOnDetermination()
	{
		return true;
	}
}
