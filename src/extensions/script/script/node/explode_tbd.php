<?php
//-------------------------------------------------------------------------------------------------
// One_Script_Node_Explode
//-------------------------------------------------------------------------------------------------

class One_Script_Node_Explode extends One_Script_Node_Abstract
{

	function execute( &$data, &$parent )
	{
		$parts = preg_split( "!\s!", $this->data);
		switch (min(count($parts),2))
		{
			case 0 :	return "Expression syntax error: missing variable name in explode '".htmlspecialchars($this->data)."'"
								. " ({$this->location} : {$this->lineNumber})";
						break;

			case 1 :	$toExplode = $parts[0];
						$arrayVariable =& $data[ $toExplode ];
						if (is_array($arrayVariable))
						{
							reset($arrayVariable);
							while (list($k,$v) = each($arrayVariable))
							{
								//echo "setting $k";
								$val =& $arrayVariable[ $k ];
								$data[ $k ] =& $val;
							}
						}
						break;

			default :	return "Expression syntax error: too many specifiers in explode '".htmlspecialchars($this->data)."'"
								. " ({$this->location} : {$this->lineNumber})";
						break;

		}

		return "";
	}


}

?>