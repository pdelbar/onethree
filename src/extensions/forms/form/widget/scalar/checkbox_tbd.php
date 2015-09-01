<?php
/**
 * Handles the checkbox widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Scalar_Checkbox extends One_Form_Widget_Scalar
{
	/**
	 * Class constructor
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $label
	 * @param array $config
	 */
	public function __construct( $id = NULL, $name = '', $label = NULL, array $config = array() )
	{
		parent::__construct($id, $name, $label, $config );
		$this->_type = 'checkbox';
	}

	/**
	 * Bind the model to the widget
	 *
	 * @param One_Model $model
	 */
	public function bindModel( $model )
	{
		$value = $this->requestValue();	// bad name
		if( is_null( $value ) ) $value = 0; // if == NULL, set to 0, because NULL won't be picked up by $model->__modified
		$attributeName = $this->_name;
		$model->$attributeName = $value;
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
							'size' => 1
							);
		return array_merge(parent::allowedOptions(), $additional);
	}

	/**
	 * Render the output of the widget and add it to the DOM
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	protected function _render( $model, One_Dom $d )
	{
		$value = ( is_null( $this->getValue( $model ) ) ? $this->getDefault() : $this->getValue( $model ) );
		$this->setCfg('class', 'OneFieldCheckbox ' . $this->getCfg('class'));
		$data = array(
						'id' => $this->getID(),
						'name' => $this->getFormName(),
						'events' => $this->getEventsAsString(),
						'params' => $this->getParametersAsString(),
						'value' => $value,
						'checked' => ( $value ) ? ' checked="checked"' : '',
						'info' => $this->getCfg('info'),
						'error' => $this->getCfg('error'),
						'class' => $this->getCfg('class'),
						'required' => (($this->isRequired()) ? ' *' : ''),
						'label' => $this->getLabel(),
						'lblLast' => $this->getCfg('lblLast')
					);

		$dom = $this->parse( $model, $data );

		$d->addDom($dom);
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
    * Get the current value
    * @param One_Model $model
    */
   public function getValue($model)
   {
       if (!$model) return null;
   
       // pick up vars entered entered from an invalidly filled in form
       $session = One_Repository::getSession();
       $posted = NULL;
       if($session->varExists('posted', 'OneFormErrors')) {
           $posted = $session->get('posted', 'OneFormErrors');
       }
       if(!in_array($this->getCfg('one'), array('one', 'yes', 'true', '1')) && null !== $posted && isset($posted['oneForm'][$this->getName()])) {
           return $posted['oneForm'][$this->getName()];
       }
       else if(null !== $posted && isset($posted[$this->getName()])) {
           return $posted[$this->getName()];
       }
       else if(null !== $posted && !isset($posted[$this->getName()])) {
           return 0;
       }
       else
       {
           $role = $this->getCfg('role');
   
           if(!is_null($role) && $model instanceof One_Model)
           {
               $parts = explode(':', $role);
               $related = $model->getRelated($parts[1]);
               $relation = One_Repository::getRelation($parts[0]);
               $role = $relation->getRole($parts[1]);
               $relScheme = One_Repository::getScheme($role->schemeName);
   
               if ($related instanceof One_Model)
                   return $related[$relScheme->getIdentityAttribute()->getName()];
               elseif (is_array($related))
               {
                   // $related is an array of One_Models
                   $returnValues = array();
   
                   foreach ($related as $r)
                       $returnValues[] = $r[$relScheme->getIdentityAttribute()->getName()];
   
                   return $returnValues;
               }
           }
           else
           {
               //PD16FEB10 : add option to pick this up from request
   
               if ($this->getCfg('from') != "") {
                   list($source, $key) = preg_split("/:/", $this->getCfg('from'));
                   switch ($source) {
                       case 'request' :
                           $from = $_REQUEST;
                           if(!in_array($this->getCfg('one'), array('one', 'yes', 'true', '1'))) {
                               $from = $_REQUEST['oneForm'];
                           }
                           if (isset($from[$key])) return $from[$key];
                           break;
                   }
               }
   
               if($this->getCfg('novalue') != "novalue")
                   return $model[$this->getOriginalName()];
           }
       }
   }
}
