<?php
//-------------------------------------------------------------------------------------------------
// nsNodegeneric
//-------------------------------------------------------------------------------------------------

class One_Script_Node_Generic extends One_Script_Node_Abstract
{

	function execute( &$data, &$parent )
	{
		return $this->data;
	}

	function compile( $hash, &$parent )
	{
		$s = "?>" . $this->data . "<?php\n";
		return $s;
	}

}

?>