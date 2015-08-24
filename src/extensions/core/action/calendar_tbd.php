<?php
/**
 * This is the parent class which takes care the calendar view in general.
 * Only subclasses should be instantiated.
 *


  * @TODO review this file and clean up historical code/comments
 *

 **/
class One_Action_Calendar extends One_Action
{
	/**
	 * @var int Current chosen day
	 */
	public $day;

	/**
	 * @var int Current chosen month
	 */
	public $month;

	/**
	 * @var int Current chosen year
	 */
	public $year;

	/**
	 * @var string Scheme attribute that represents the date of the object
	 */
	public $dateAttribute;

	/**
	 * @var string Scheme attribute that represents the enddate of the object if any
	 */
	public $enddateAttribute = null;

	/**
	 * @var string Scheme attribute that represents the time of the object
	 */
	public $timeAttribute;

	/**
	 * @var string Scheme attribute that represents the title of the object
	 */
	public $titleAttribute;

	/**
	 * Class constructor
	 *
	 * @param One_Controller $controller
	 * @param array $options
	 */
	public function __construct( One_Controller $controller, $options = array() )
	{
		set_include_path(get_include_path().PATH_SEPARATOR.One_Vendor::getInstance()->getFilePath().'/zend');

		if(!class_exists('Zend_Locale', false)) {
			require_once 'Zend/Locale.php';
		}
		parent::__construct( $controller, $options );

		$this->scheme         = $options[ 'scheme' ];
		$scheme               = One_Repository::getScheme( $this->scheme );
		$calendarOptions      = $scheme->get('behaviorOptions.calendar');
		$date                 = $calendarOptions[ 'date' ];
		$enddate                 = $calendarOptions[ 'enddate' ];
		$time                 = $calendarOptions[ 'time' ];
		$title                = $calendarOptions[ 'title' ];
		$this->dateAttribute  = $this->getVariable( 'dateAttribute', $date ? $date : 'date' );
		$this->enddateAttribute  = $this->getVariable( 'enddateAttribute', $enddate ? $enddate : NULL );
		$this->timeAttribute  = !is_null($time) ? $this->getVariable( 'timeAttribute', $time ? $time : 'time' ) : NULL;
		$this->titleAttribute = $this->getVariable( 'titleAttribute', $title ? $title : 'title' );

		// determine currently requested date
		$day   = trim( $this->getVariable( 'day', '' ) );
		$month = trim( $this->getVariable( 'month', '' ) );
		$year  = trim( $this->getVariable( 'year', '' ) );
		if( isset( $calendarOptions[ 'startdate' ] ) && $day == '' && $month == '' && $year == '' )
		{
			if( preg_match( '/([0-9]{4}).([0-9]{1,2}).([0-9]{1,2})/', $calendarOptions[ 'startdate' ], $matches ) > 0
				&& checkdate( $matches[ 2 ], $matches[ 3 ], $matches[ 1 ] )
				&& mktime( 0, 0, 0, $matches[ 2 ], $matches[ 3 ], $matches[ 1 ] ) > time() )
			{
				$day   = $matches[ 3 ];
				$month = $matches[ 2 ];
				$year  = $matches[ 1 ];
			}
			else
			{
				$day   = date('d');
				$month = date('m');
				$year  = date('Y');
			}
		}
		else if( $day != '' || $month != '' || $year != '' )
		{
			$day   = $this->getVariable( 'day', date('d') );
			$month = $this->getVariable( 'month', date('m') );
			$year  = $this->getVariable( 'year', date('Y') );
		}
		else
		{
			$day   = date('d');
			$month = date('m');
			$year  = date('Y');
		}

		$this->day   = $day;
		$this->month = $month;
		$this->year  = $year;
	}

	/**
	 * Returns whether the user is allowed to perform this task
	 *
	 * @param One_Scheme $scheme
	 * @param mixed $id
	 * @return boolean
	 */
	public function authorize($scheme)
	{
		return One_Permission::authorize('calendar', $scheme, null);
	}

	/**
	 * Check what the default action is for the calendar, day, month or week
	 *
	 * @return string returns the default calendar action
	 */
	protected function getDefaultAction()
	{
		// @deprecated put this in a configuration somewhere instead of using constants
		return 'month';
	}
}
