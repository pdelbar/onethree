<?php
/**
 * One_Session handles all session variable processing for one|content
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Session
{
	/**
	 * Class constructor
	 */
	public function __construct()
	{
		if( !isset( $_SESSION[ 'One_Session_Registry' ] ) )
		{
			$_SESSION[ 'One_Session_Registry' ] = array(
														'namespaces' => array(
																				'default' => array()
																			)
													);
		}
	}

	/**
	 * Get a variable from a session namespace
	 *
	 * @param string $name
	 * @param string $namespace
	 * @return mixed
	 */
	public function get( $name, $namespace = 'default' )
	{
		$useNamespace = $this->cleanNamespace( $namespace );
		if( !isset( $_SESSION[ 'One_Session_Registry' ][ 'namespaces' ][ $useNamespace ][ $name ] ) )
			return NULL;

		$var         = $_SESSION[ 'One_Session_Registry' ][ 'namespaces' ][ $useNamespace ][ $name ];
		$checkArrObj = unserialize( $var );
		if( $checkArrObj === false )
			return $var;
		else
			return $checkArrObj;
	}

	/**
	 * Set a variable in a session namespace
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param string $namespace
	 */
	public function set( $name, $value, $namespace = 'default' )
	{
		$useNamespace = $this->cleanNamespace( $namespace );
		$_SESSION[ 'One_Session_Registry' ][ 'namespaces' ][ $useNamespace ][ $name ] = serialize( $value );
	}

	/**
	 * Remove a variable from a sessoin namespace
	 *
	 * @param string $name
	 * @param string $namespace
	 */
	public function remove( $name, $namespace = 'default' )
	{
		$useNamespace = $this->cleanNamespace( $namespace );
		unset( $_SESSION[ 'One_Session_Registry' ][ 'namespaces' ][ $useNamespace ][ $name ] );
	}

	/**
	 * Check whether a variable exists in a session namespace
	 *
	 * @param string $name
	 * @param string $namespace
	 * @return boolean
	 */
	public function varExists( $name, $namespace = 'default' )
	{
		$useNamespace = $this->cleanNamespace( $namespace );
		return isset( $_SESSION[ 'One_Session_Registry' ][ 'namespaces' ][ $useNamespace ][ $name ] );
	}

	/**
	 * Clear an entire namespace inthe session
	 *
	 * @param string $namespace
	 */
	public function clearNamespace( $namespace = 'default' )
	{
		$useNamespace = $this->cleanNamespace( $namespace );
		unset( $_SESSION[ 'One_Session_Registry' ][ 'namespaces' ][ $useNamespace ] );
	}

	/**
	 * Destroy the entire One_Session
	 */
	public function destroy()
	{
		unset( $_SESSION[ 'One_Session_Registry' ] );
	}

	/**
	 * Make sure that the name for a name space is a valid name
	 * @param mixed $namespace
	 * @return string
	 */
	private function cleanNamespace( $namespace = 'default' )
	{
		if( trim( $namespace ) == '' || !is_string( $namespace ) )
			return 'default';
		else
			return $namespace;
	}
}