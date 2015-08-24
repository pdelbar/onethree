<?php
//-------------------------------------------------------------------------------------------------
// One_Script_Node_Loop
//-------------------------------------------------------------------------------------------------

class One_Script_Node_Loop extends One_Script_Node_Abstract
{

	//------------------------------------------------------------------
	//TODO: loopArray should be capable to contain an expression

	function execute( &$data, &$parent )
	{
		$s = "";

		$parts = preg_split( "!\s!", $this->data);
		switch (min(count($parts),3))
		{
			case 0 :	return "Expression syntax error: missing array specification in loop '".htmlspecialchars($this->data)."'"
								. " ({$this->location} : {$this->lineNumber})";
						break;

			case 1 :	return "Expression syntax error: missing loop variable specification in loop '".htmlspecialchars($this->data)."'"
								. " ({$this->location} : {$this->lineNumber})";
						break;

			case 2 :	$arrayVariable = $parts[0];
							$loopVariable = $parts[1];
							$loopArray =& $data[$arrayVariable];
							if ($loopArray) reset($loopArray);
							if ($loopArray) while (list($key, $val) = each($loopArray))
							{
								$data[$loopVariable] =& $loopArray[$key];
								$s .= $this->executeChain( $this->chain, $data, $parent );
							}
							break;

			case 3 :	$arrayVariable = $parts[0];
							$keyVariable = $parts[1];
							$loopVariable = $parts[2];
							$loopArray =& $data[$arrayVariable];
							reset($loopArray);
							while (list($key, $val) = each($loopArray))
							{
								$data[$loopVariable] =& $loopArray[$key];
								$data[$keyVariable] = $key;
								$s .= $this->executeChain( $this->chain, $data, $parent );
							}
							break;

			default :	return "Expression syntax error: too many specifiers in loop '".htmlspecialchars($this->data)."'"
								. " ({$this->location} : {$this->lineNumber})";
							break;

		}
		/*
		$ar = $parts[0];
		$kVar = $parts[1];
		$vVar = $parts[2];

		$s = "";
		foreach ($data[$ar] as $k => $v )
		{
			$data[$kVar] = $k;
			$data[$vVar] = $v;
			$s .= $this->executeChain( $this->chain, $data, $parent );
		}
		*/
		return $s;
	}

	function compile( $hash, &$parent )
	{
		$s = "";

		$parts = preg_split( "!\s!", $this->data);
		switch (min(count($parts),3))
		{
			case 0 :	return "Expression syntax error: missing array specification in loop '".htmlspecialchars($this->data)."'"
								. " ({$this->location} : {$this->lineNumber})";
							break;

			case 1 :	return "Expression syntax error: missing loop variable specification in loop '".htmlspecialchars($this->data)."'"
								. " ({$this->location} : {$this->lineNumber})";
							break;

			case 2 :	$arrayVariable = $parts[0];
						$loopVariable = $parts[1];

						$token = md5(uniqid(rand(), true));
						$token="1";
						$loopArray = "\$var_" . $token;
						$kVar = "\$vark_" . $token;
						$vVar = "\$varv_" . $token;

						$s .= "    " . $loopArray . " =& \$data['" . $arrayVariable . "'];\n";

						$s .= "     if (is_array( " . $loopArray. " ) ) {\n";
							$s .= "     reset( " . $loopArray. " );\n";
							$s .= "     while (list( " . $kVar . ", " . $vVar . " ) = each( " . $loopArray . ")) {\n";
								$s .= "    \$data['" . $loopVariable . "'] =& " . $loopArray . "[" . $kVar . "];\n";
								foreach ($this->chain as $n) $s .= $n->compile( $hash, $parent );
							$s .= " }\n";
						$s .= " }\n";

						break;

			case 3 :	$arrayVariable = $parts[0];
							$keyVariable = $parts[1];
							$loopVariable = $parts[2];
								$loopArray =& $data[$arrayVariable];
							reset($loopArray);
							while (list($key, $val) = each($loopArray))
							{
								$data[$loopVariable] =& $loopArray[$key];
								$data[$keyVariable] = $key;
								$s .= $this->executeChain( $this->chain, $data, $parent );
							}
							break;

			default :	return "Expression syntax error: too many specifiers in loop '".htmlspecialchars($this->data)."'"
								. " ({$this->location} : {$this->lineNumber})";
							break;

		}
		return $s;
	}

}

?>