<?php
die('shoudl not be calling ' . __FILE__ );

/**
 * The introduction of a One_ModelFactory as a means to create instances of a particular scheme
 * makes it possible to extend the selection of models beyond select using query and select a single
 * instance. In many cases, it is desirable to encapsulate specific selections in a class and avoid a
 * situation where a controller needs to manipulate a query to select recurring definitions. For instance,
 * for a scheme 'invoice' it may be useful to select open invoices, paid invoices, ... without having
 * to specify the details of how the selection works outside the model factory.
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Model_Factory
{
	/**
	 * Gets an instance of a scheme according to a path
	 *
	 * @param string $path
	 * @return One_Model
	 */
	public function get( $path )
	{
		$parts = explode( '.', trim( strtolower( $path ) ) );
		return One::make( $parts[ 0 ] );
	}
}