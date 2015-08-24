<?php
//-------------------------------------------------------------------------------------------------
// nsNode
//
// Represents a node in the script structure. A nanoScript is converted into a series of nodes by
// the parser.
//-------------------------------------------------------------------------------------------------

class One_Script_Node_Root extends One_Script_Node_Abstract
{

	function execute( &$data, &$parent )
	{
// 		print "(in handleRoot)";
		$s = "";
		$desiredSection =& $parent->activeSection;
		//print "(desired section = $desiredSection)";
		foreach ($this->chain as $n)
//		echo '--', get_class($n), '--';
			switch (get_class($n)) {
				case 'One_Script_Node_Section' :
					if (($desiredSection == "") or ($n->data == $desiredSection)) $s .= $n->execute($data, $parent);		// parent was & in PHP4
					break;
				case 'One_Script_Node_Include' :
					//PD09NOV09: I have added the check for desiredSection. The rationale is that this a root node, therefore
					//	the highest level. If there is a selected section, the include (at this level) is not iside it and should NOT
					// be executed. In an include node, this is different : the includes inside it should be executed
					// if its parent include is.
					// DO NOT CHANGE THIS BEHAVIOR WITHOUT CONSULTING ME !!

//					echo '<br>handling include ', $n->data;
					//if ($desiredSection == "")
					$s .= $n->execute($data,$parent);
					break;
				default :
					if ($desiredSection == "")
						$s .= $n->execute($data, $parent);
					break;
			}
//		print "(leaving handleRoot)";
		return $s;
	}

	function compile( $hash, &$parent )
	{
		$s = '';
		foreach ($this->chain as $n) $s .= $n->compile( $hash, $parent );
		return $s;
	}
}
