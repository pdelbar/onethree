<?php
/**
 * The One_Store_Connection_Mongo class supplies the connection to MongoDB
 *


  * @TODO review this file and clean up historical code/comments
 * @subpackage Store

 **/
class One_Store_Connection_Mongo extends One_Store_Connection_Abstract
{
	/**
	 * The current Mongo-connection
	 * @var Mongo
	 */
	protected $_connection = NULL;

	/**
	 * The selected database
	 * @var MongoDB
	 */
	protected $_database = NULL;

	/**
	 * Open the connection
	 * @return MongoDB
	 */
	public function open()
	{
		$meta = $this->getMeta();

		$connection = isset($meta['db']['host']) && '' != trim($meta['db']['host']) ? $meta['db']['host'] : 'localhost';
		$connection .= isset($meta['db']['port']) && '' != trim($meta['db']['port']) ? ':'.intval($meta['db']['port']) : '';

		if(isset($meta['db']['user']) && '' != trim($meta['db']['user'])) {
			$credentials = $meta['db']['user'];
			if(isset($meta['db']['password']) && '' != trim($meta['db']['password'])) {
				$credentials .= ':'.$meta['db']['password'];
			}

			$connection = $credentials.'@'.$connection;
		}

		try
		{
			$this->_connection = new Mongo('mongodb://'.$connection);
			$this->_database   = $this->_connection->selectDB($meta['db']['database']);
		}
		catch(Exception $e) {
			throw new One_Exception('Could not connect: ' . $e->getMessage());
		}

		return $this->_database;
	}

	/**
	 * Close the connection
	 */
	public function close($ch = NULL)
	{
		$this->_connection->close();
	}
}
