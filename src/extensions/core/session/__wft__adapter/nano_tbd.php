<?php
/**
 * Class that parses nano template files
 *


  * @TODO review this file and clean up historical code/comments
 * @subpackage Template
ONEDISCLAIMER

 **/
class One_Templater_Nano extends One_Templater_Abstract
{
	/**
	 * NanoScript object used to parse the template
	 * @var nScript
	 */
	protected $nScript = NULL;

	/**
	 * Class constructor
	 * @param array $searchpaths
	 */
	public function __construct( array $searchpaths = array(), $setSearchpaths = true )
	{
		parent::__construct( $searchpaths, $setSearchpaths );
		$this->nScript = new One_Script();
	}

	public function setFile( $filename )
	{
		parent::setFile( $filename );

		if($this->nScript->isError()) {
			throw new One_Exception($this->nScript->error);
		}
	}

	/**
	 * Parse the template or if $section is set and the section exists, parse the specified section
	 * @param string $section
	 */
	public function parse( $section = NULL )
	{
		$oldSearchpath = One_Script_Factory::getSearchPath();
		One_Script_Factory::clearSearchpath();
		One_Script_Factory::setSearchPath( $this->getSearchPath() );

		if( $this->useFile() )
		{
			$this->nScript->load( $this->getFile() );
			$this->nScript->select( $section );
			if( !$this->nScript->isError() ) {
				$output = $this->nScript->execute( $this->getData() );
			}
		}
		else
		{
			$this->nScript->select( $section );
			$output = $this->nScript->executeString( $this->getContent(), $this->getData() );
		}

		One_Script_Factory::clearSearchpath();
		One_Script_Factory::setSearchPath($oldSearchpath);
		One_Script_Content_Factory::$nsContentCache = array();

		if($this->nScript->isError()) {
			throw new One_Exception($this->nScript->error);
		}

		return trim($output);
	}
}