<?php
/**
 * Handles the view for selecting documents
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Document2_Widget
{
	/**
	 * @var string Root of where to search for the documents
	 */
	private $root   = NULL;

	/**
	 * @var string Path of where to search for the documents
	 */
	private $path   = NULL;

	/**
	 * @var One_Form_Widget_Abstract Form that is looking for a document
	 */
	private $widget = NULL;

	/**
	 * Class constructor
	 *
	 * @param string $root
	 * @param string $path
	 * @param One_Form_Widget_Abstract $widget
	 */
	public function __construct( $root, $path, $widget )
	{
		$this->root   = preg_replace( array('{/|\\\}', '{(/|\\\)$}'), array(DIRECTORY_SEPARATOR, ''), $root );
		$this->path   = preg_replace( array('{/|\\\}', '{(/|\\\)$}'), array(DIRECTORY_SEPARATOR, ''), $path );
		$this->widget = $widget;
	}

	/**
	 * Get the root
	 *
	 * @return string
	 */
	public function getRoot()
	{
		return $this->root;
	}

	/**
	 * Get the widget that is looking for a document
	 *
	 * @return One_Form_Widget_Abstract
	 */
	public function getWidget()
	{
		return $this->widget;
	}

	/**
	 * Get the current folder
	 *
	 * @return string
	 */
	public function getCurrent()
	{
		$current = str_replace( array( JPATH_BASE, DIRECTORY_SEPARATOR ), array( '', '/' ), $this->path );

		if( trim( $current ) == '' || trim( $current ) == preg_replace('!/$!', '', JPATH_BASE) || !file_exists( $this->path ) )
			$current = '/';

		if(strpos($current, '/') !== 0) {
			$current = '/'.$current;
		}

		return $current;
	}

	/**
	 * Get the parent folder
	 *
	 * @return string
	 */
	public function getParent()
	{
		$parent = NULL;

		if( file_exists( $this->path ) )
		{
			$parts  = preg_split( '{/|\\\}', $this->path );
			array_pop( $parts );
			$parent = implode( DIRECTORY_SEPARATOR, $parts );
		}

		if( preg_match( '{^' . addslashes( $this->getRoot() ) . '}i', $parent ) == 0 )
			$parent = JPATH_BASE;

		if(strpos($current, '/') != 0) {
			$parent = '/'.$parent;
		}

		return $parent;
	}

	/**
	 * Get all files in the current path
	 *
	 * @return array
	 */
	public function getFiles()
	{
		$fnf = array(
						'folders' => array(),
						'files' => array(),
					);
		if( !file_exists( $this->path ) )
			$this->path = $this->root;

		$files = scandir( $this->path );

		foreach( $files as $file )
		{
			if( $file != '.' && $file != '..' )
			{
				if( is_dir( $this->path . DIRECTORY_SEPARATOR . $file ) )
				{
					$tmp = new StdClass();
					$tmp->name = $file;
					$tmp->link = $this->path . DIRECTORY_SEPARATOR . $file;
					$fnf[ 'folders' ][] = $tmp;
				}
				else
				{
					$tmp = new StdClass();
					$tmp->name = $file;
					$tmp->link = $this->path . DIRECTORY_SEPARATOR . $file;
					$fnf[ 'files' ][] = $tmp;
				}
			}
		}


		sort( $fnf[ 'folders' ] );
		sort( $fnf[ 'files' ] );

		return $fnf;
	}
}