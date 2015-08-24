<?php
/**
 * Interface used for One_Filters
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Interface One_Filter_Interface
{
	/**
	 * Function that will affect the One_Query
	 *
	 * @param One_Query $query
	 */
	public function affect(One_Query $query);
}
