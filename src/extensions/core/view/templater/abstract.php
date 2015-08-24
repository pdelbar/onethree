<?php
/**
 * Class that parses template files
 *
 * ONEDISCLAIMER
 **/
abstract class One_View_Templater_Abstract
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
	 * Search path for glob to find the files
	 * @staticvar array
	 */
	protected static $searchPath;

	/**
	 * Class constructor
	 * @param array $searchpaths
	 */
	public function __construct()
	{
	}

	/**
	 * Set the data used in the template
	 * @param array $data
	 */
	public function setData( array $data )
	{
		$data = $this->formatDataKeys($data);
		$this->data = $data;
	}

	/**
	 * Add data used in the template
	 * @param string $key
	 * @param mixed $value
	 */
	public function addData( $key, $value )
	{
		$key = $this->formatDataKey($key);
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
	 * Format the keys in the data-array so that they do not conflict while parsing
	 * @param array $data
	 * @return array
	 */
	protected function formatDataKeys(array $data)
	{
		return $data;
	}

	/**
	 * Format the key so that it does not conflict while parsing
	 * @param string $key
	 * @return string
	 */
	protected function formatDataKey($key)
	{
		return $key;
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
	public function getSearchPath()
	{
		return self::$searchPath;
	}

	/**
	 * Set the searchpath
	 */
	public function setSearchPath( $path )
	{
        self::$searchPath = $path;
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
	public abstract function render( $section = NULL );
}