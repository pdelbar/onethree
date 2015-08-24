<?php
//-------------------------------------------------------------------------------------------------
// One_Script_Node_Include
//-------------------------------------------------------------------------------------------------

class One_Script_Node_Include extends One_Script_Node_Abstract
{

	function execute( &$data, &$parent )
	{
		// print "(in handleRoot)";
		$s = "";
		$desiredSection =& $parent->activeSection;
		//print "(desired section = $desiredSection)";
		foreach ($this->chain as $n)
			switch (get_class($n)) {
				case 'One_Script_Node_Section' :
					if (($desiredSection == "") or ($n->data == $desiredSection)) $s .= $n->execute($data, $parent);		// parent was & in PHP4
					break;
				case 'One_Script_Node_Include' :
					$s .= $n->execute($data,$parent);
					break;
				default :
					if ($desiredSection == "") $s .= $n->execute($data, $parent);
					break;
			}
		// print "(leaving handleRoot)";
		return $s;
	}

	function compile( $hash, &$parent )
	{
		$s = " \$ns = new One_Script();\n";
		$s .= " \$ns->load('" . $this->data . "');\n";
		$s .= " echo \$ns->execute( \$data );\n";				//TODO: make sure this is returned, not printed
		return $s;
	}

}

?>