<?php
/**
 * This action handles how to return a feed of the chosen scheme
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Controller_Action_Feed extends One_Controller_Action
{
	/**
	 * Class constructor
	 *
	 * @param One_Controller $controller
	 * @param array $options
	 */
	public function __construct( One_Controller $controller, $options = array() )
	{
		parent::__construct( $controller, $options );

		$view = $this->getVariable( 'view', 'rss' );
		$this->view = new One_View( $this->scheme, $view);
	}

	/**
	 * This method will return a feed for the chosen scheme
	 *
	 * @return string The feed string
	 */
	public function execute()
	{
		$this->authorize();

		$factory = One_Repository::getFactory( $this->scheme->getName() );
		$q = $factory->selectQuery();

		$q->setOrder( $this->getVariable( 'order', '' ) );
		$q->setLimit( $this->getVariable( 'count', 0 ), $this->getVariable( 'start', 0 ) );

		$this->processQueryConditions( $q );

		$results = $q->result( false );

		$this->view->set( 'scheme', $this->scheme );
		$this->view->setModel( $results );
		echo $this->view->show();
		exit; // exit here because nothing else should be outputted after the feed
	}


	/**
	 * Returns whether the user is allowed to perform this task
	 *
	 * @param One_Scheme $scheme
	 * @param mixed $id
	 * @return boolean
	 */
	public function authorize()
	{
		return true;
	}
}
