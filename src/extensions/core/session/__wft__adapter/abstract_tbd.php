<?php
/**
 * Class that parses templte files
 *


  * @TODO review this file and clean up historical code/comments
 * @subpackage Template
ONEDISCLAIMER

 **/
abstract class One_Template_Adapter_Abstract
{
	/**
	 * Data used in the template
	 * @var array
	 */
	protected $data = array();

	/**
	 * Content to be parsed
	 * @var string
	 */
	protected $content = NULL;

	/**
	 * File to be parsed
	 * @var string
	 */
	protected $file = NULL;

	/**
	 * Whether or not to use a file or the content; true for file, false for content
	 * @var boolean
	 */
	protected $useFile = false;

	/**
	 * Error that might occur
	 * @var string
	 */
	protected $error = '';

	/**
	 * List of absolute paths where the files can be found
	 * @staticvar array
	 */
	protected static $searchpath = array();

	/**
	 * Variable that can store searchpaths, so it can be restored later on
	 * @staticvar array
	 */
	protected static $storedpath = array();

	/**
	 * Class constructor
	 * @param array $searchpaths
	 */
	public function __construct( array $searchpaths = array(), $setSearchpaths = true )
	{
		if($setSearchpaths) {
			$this->setSearchpath( $searchpaths );
		}
	}

	/**
	 * Set the data used in the template
	 * @param array $data
	 */
	public function setData( array $data )
	{
		$this->data = $data;
	}

	/**
	 * Add data used in the template
	 * @param string $key
	 * @param mixed $value
	 */
	public function addData( $key, $value )
	{
		$this->data[ $key ] = $value;
	}

	/**
	 * Get the data used in the template
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Set the content to be parsed
	 * @param string $content
	 */
	public function setContent( $content = '' )
	{
		$this->content = $content;
	}

	/**
	 * Get the content to be parsed
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Return the latest error message
	 * @return string
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 *
	 * Set the error message
	 * @var string $error
	 */
	protected function setError( $error )
	{
		$this->error = $error;
	}

	/**
	 * Does the template have have an error or not
	 * @return boolean
	 */
	public function hasError()
	{
		return ( trim( $this->getError() ) != '' );
	}

	/**
	 * Get currently set searchpaths
	 * @return array
	 */
	public function getSearchpath()
	{
		return self::$searchpath;
	}

	/**
	 * Set the searchpaths
	 * @param mixed $paths Absolute path or array of paths where the file can be found
	 */
	public function setSearchpath( $paths )
	{
		if( !is_array( $paths ) )
		{
			$paths = array( $paths );
		}

		$this->clearSearchpath();
		foreach( $paths as $path )
		{
			$this->addSearchpath( $path );
		}
	}

	/**
	 * Add an absolute path to the searchpaths
	 * @param string $path
	 */
	public function addSearchpath( $path )
	{
		if( is_dir( $path ) )
			self::$searchpath[] = $path;
		// don't set an error if the path doesn't exist, because sometimes a path will be tried to be added that doesn't exist
		// else
		// 	$this->setError( '"' . $path . '" is not a directory' );
	}

	/**
	 * Clear all searchpaths
	 */
	public function clearSearchpath()
	{
		self::$searchpath = array();
	}

	/**
	 * Temporarily store the current searchpaths
	 */
	public function saveSearchpath()
	{
		self::$storedpath = self::$searchpath;
	}

	/**
	 * Restore saved searchpaths to the current searchpaths
	 */
	public function restoreSearchpath()
	{
		self::$searchpath = self::$storedpath;
	}

	/**
	 * Set whether to use the file or not
	 * @param boolean $useFile
	 */
	public function setUseFile( $useFile = false )
	{
		$this->useFile = (boolean) $useFile;
	}

	/**
	 * Get whether to use the file or not
	 * @return boolean
	 */
	public function useFile()
	{
		return (boolean) $this->useFile;
	}

	/**
	 * Set the file to use
	 * @param string $filename
	 */
	public function setFile( $filename )
	{
		$this->file = $filename;
		$this->setUseFile( true );
	}

	/**
	 * Get the file to use
	 * @return string
	 */
	public function getFile()
	{
		return $this->file;
	}

	/**
	 * Parse the template or if $section is set and the section exists, parse the specified section
	 * @param string $section
	 */
	public abstract function parse( $section = NULL );
}