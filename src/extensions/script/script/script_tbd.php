<?php
//-------------------------------------------------------------------------------------------------
// 	One_Script (nanoScript)
//
//	This class represents a script context. Usage:
//
//		$ns = new One_Script();
//		$ns->readFile( 'filename' );
//		$ns->set( 'variable', 123 );
//		echo $ns->execute( $data );
//
//-------------------------------------------------------------------------------------------------

	ini_set("track_errors",1);					// ensure that eval() returns a useful error string

//-------------------------------------------------------------------------------------------------

	class One_Script
	{

		//--- PROPERTIES ---

		public $rootNode;				// holds the root nsNode

		public $activeSection;			// defines which section needs to be executed

		public $variables;				// parameter settings

		public $error;						// string describing the current error condition

		public $oCode;					// added PD 02JAN05: original directory path


		//--- STRUCTORS ---

		public function __construct()
		{
			$this->rootNode 		= new One_Script_Node_Root( $this );			// create new empty root
			$this->activeSection 	= "";									// preset to entire script
			$this->variables 		= array();
			$this->error			= "";
		}

		//--------------------------------------------------------------------------------
		// set : directly set a script variable
		//--------------------------------------------------------------------------------

		public function set( $k, $v )
		{
			//print "From " . count($this->variables) . " ...";
			$this->variables[ $k ] = $v;
			//print $k . " := " . $this->variables[$k];
			//print " to [$k] is " . count($this->variables) . " ...";
		}

		//--------------------------------------------------------------------------------
		// get : retrieve a variable value
		//--------------------------------------------------------------------------------

		public function &get( $k )
		{
			return $this->variables[ $k ];
		}

		//--------------------------------------------------------------------------------
		// copyRequest : copy all variables from the request superglobal
		//--------------------------------------------------------------------------------

		public function copyRequest()
		{
			foreach ($_REQUEST as $k => $v) $this->set( $k , $v );
		}

		//--------------------------------------------------------------------------------
		// copySession : copy all variables from the session superglobal
		//--------------------------------------------------------------------------------

		public function copySession()
		{
			foreach ($_SESSION as $k => $v) $this->set( $k , $v );
		}

		//--------------------------------------------------------------------------------
		// select : set the active section
		//--------------------------------------------------------------------------------

		public function select( $section )
		{
			$this->activeSection = $section;
		}

		//--------------------------------------------------------------------------------
		// isError : true us an error occurred during the last operation
		//--------------------------------------------------------------------------------

		public function isError()
		{
			return ($this->error != "");
		}

		//--------------------------------------------------------------------------------
		// dump : dump the node structure (for debugging purposes)
		//--------------------------------------------------------------------------------

		public function dump()
		{
			$this->rootNode->dump();
		}

		//--------------------------------------------------------------------------------
		// parse : process the string into an nsNode chain
		//--------------------------------------------------------------------------------

		public function parse( $load, $filename, $path = "" )
		{
			list( $cached, $codeOrPath, $actualPath ) = $load;

			if ($cached) {
//				echo ' (in cache)';
				if ($codeOrPath instanceof One_Script_Node_Root)
					$this->rootNode = $codeOrPath;
				else
					$this->rootNode = One_Script_Cache::get( $codeOrPath );
			}
			else {
//				echo ' (NOT in cache )';
				$parser = new One_Script_Parser( $this );
				$this->rootNode = $parser->parse( $codeOrPath, $filename, $path, $actualPath );

				One_Script_Cache::cache( $actualPath, $this->rootNode );
			}

			if ($this->rootNode->isError())
				$this->error = $this->rootNode->error;
		}

		//--------------------------------------------------------------------------------
		// parseString : process the string into an nsNode chain
		//--------------------------------------------------------------------------------

		public function parseString( $string )
		{
			return $this->parse( array( false, $string, ''), '' );
		}


		//--------------------------------------------------------------------------------
		// execute : interpret the script using data and preset variables
		//--------------------------------------------------------------------------------

		public function execute( $data = array() )
		{
			if (is_array($data))
				$context = array_merge( $data, $this->variables );
			else
				$context = $this->variables;
			return $this->rootNode->execute( $context, $this );
		}

		//--------------------------------------------------------------------------------
		// executeString : consider this string a script and execute it
		//--------------------------------------------------------------------------------

		public function executeString( $s, $data = array() )
		{
			$content = array( false, $s, "");
			$this->parse( $content, "" );
			return $this->execute( $data );
		}

		//--------------------------------------------------------------------------------
		// load : load the specified file
		//--------------------------------------------------------------------------------

		public function load( $filename, $path = "" )
		{
			$result = One_Script_Factory::load( $filename, $path );

			if ($result === false)
			{
				$this->error = One_Script_Factory::$error;
				return false;
			}

			$this->parse( $result, $filename, $path );
		}

		//--------------------------------------------------------------------------------
		// addSection : add the script string as an individual section (used to insert
		//	scripts loaded from a database)
		//--------------------------------------------------------------------------------

		public function addSection( $sectionName, $sectionString )
		{
			$proxy = new One_Script();
			$proxy->executeString( "{section " . $sectionName . "}" . $sectionString . "{endsection}", $this->oCode );
			$sectionNodes = $proxy->rootNode->chain;
			if (count($sectionNodes)) foreach ($sectionNodes as $sn) $this->rootNode->chain[] = $sn;
		}

		//--------------------------------------------------------------------------------
		// findSection : retrieve the section node for this section
		//--------------------------------------------------------------------------------

		public function findSection( $sectionName )
		{
			$topNodes = $this->rootNode->chain;
			$n = $this->findSectionInNodes( $sectionName, $topNodes );
			if ($n) return $n;
			return "";
		}


		public function findSectionInNodes( $sectionName, $nodes )
		{
			if ($nodes) foreach ($nodes as $sn)
			{
				//echo "(".$sn->type.")";
				//if ($sn instanceof One_Script_Node_Section) echo "<br>-- comparing to (".$sn->data . ")";
				if (($sn instanceof One_Script_Node_Section) and ($sn->sectionName == $sectionName))
				{
					//echo "found $sectionName";
					return $sn;
				}
				if ($sn instanceof One_Script_Node_Include)
				{
					$n = $this->findSectionInNodes( $sectionName, $sn->chain );
					if ($n) return $n;
				}
			}
			return "";
		}

		//--------------------------------------------------------------------------------
	}
