<?php
//-------------------------------------------------------------------------------------------------
// ignore - chunk out the tag as if it was ignored by nanoscript, used for joomla passthru
//-------------------------------------------------------------------------------------------------

class One_Script_Node_Ignore extends One_Script_Node_Abstract
{

	function execute( &$data, &$parent )
	{
		return '{' . $this->data . '}';
	}

}

?>