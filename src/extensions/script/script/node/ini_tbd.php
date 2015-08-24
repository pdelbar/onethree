<?php
//-------------------------------------------------------------------------------------------------
// One_Script_Node_Ini
//-------------------------------------------------------------------------------------------------

//@deprecated
die('deprecatd node dude');

class One_Script_Node_Ini extends One_Script_Node_Abstract
{

	//----------------------------------------------------------------------
	// handleINI : load INI file (at runtime) and convert all entries
	// to sections (for now, to ensure backward compatibility)
	//
	// Usage:
	//		{ini myfile}			loads all entries in myfile.ini located
	//									using the One_Script_Factory search path
	//		{ini myfile section}	load only entries in this section
	//----------------------------------------------------------------------

	function execute( &$data, &$parent )
	{
//		print_r($this->data);
		list( $iniFile, $section ) = preg_split("!\s!", trim($this->data, 2));
		if (!$iniFile) return "Error: no INI filename specified";
		$iniFile = $iniFile. '.ini';
		$path = One_Script_Factory::resolveFilePath( $iniFile );

		if ($path === false) {
			return "One_Script_Factory: could not locate $iniFile";
		}

		$entries = parse_ini_file( $path, $section );				// if section evals to false, no sections will be returned, but a flat array is returned
		if ($section) {
			if (!isset($entries[$section])) return "Error: no section '$section' in $iniFile";
			$entries = $entries[$section];
		}
		if ($entries) foreach ($entries as $k => $v ) $parent->addSection( $k, $v );
	}

}

?>