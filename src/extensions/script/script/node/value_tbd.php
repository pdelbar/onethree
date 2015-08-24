<?php
//-------------------------------------------------------------------------------------------------
// oneScriptNodeValue
//-------------------------------------------------------------------------------------------------

class One_Script_Node_Value extends One_Script_Node_Abstract
{

	function execute( &$data, &$parent )
	{
		if (!array_key_exists( $this->data, $data ))
		{
			//echo "did not find " . $this->data;
			return "";
		}

		$result = $data [ $this->data ];


		if ($this->args)
		{
//			print "[[" . $this->args . "]]";
			$modifiers = preg_split( "!\s!", $this->args);
			//print count($modifiers) . "modifiers";
//			print_r($modifiers);
			foreach ($modifiers as $modifierString)
			{
				preg_match( "!(\w*)(.*)!", $modifierString, $matches  );
				//print_r($matches);
				$modifier = $matches[1];
				//print "[$modifier]";

				$argumentString = $matches[2];
				$arguments = array();
				if ($argumentString)
				{
					$delim = $argumentString[0];
					$arguments = explode( $delim, substr($argumentString,1) );
				}

				switch ($modifier)
				{
					case 'capUpper' :		$result = strtoupper($result);
											break;
					case 'capLower' :		$result = strtolower($result);
											break;
					case 'capWord' :		$result = ucWords($result);
											break;
					case 'capSentence' :	$result = ucFirst($result);
											break;
					case 'trim' :			$result = trim($result);
											break;
					case 'htmlencode' :		$result = htmlentities($result);
											break;
					case 'htmldecode' :		$result = html_entity_decode($result);
											break;

					case 'makesafe' :		$result = mysql_real_escape_string($result);
											break;

					case 'utf8decode' :		$result = utf8_decode($result);
											break;
					case 'utf8encode' :		$result = utf8_encode($result);
											break;

					case 'strtotime' :		$result = strtotime($result);
											break;
					case 'stripslashes' :	$result = stripslashes($result);
											break;
					case 'addslashes' :		$result = addslashes($result);
											break;
					case 'addone' :		$result++;
											break;
					case 'number' :			//print_r($arguments);
											$dec = 2;
											if (count($arguments) > 0) $dec = $arguments[0];
											$dot = ",";
											if (count($arguments) > 1) $dot = $arguments[1];
											$sep = ".";
											if (count($arguments) > 2) $sep = $arguments[2];
											$result = number_format($result,$dec,$dot,$sep);
											break;
					case 'printf' :			$format = "";
											if (count($arguments) == 0) break;
											$format = $arguments[0];
											$result = sprintf($format,$result);
											break;
					case 'date' :			$format = "";
											if (count($arguments) == 0) break;
											$format = $arguments[0];
											$result = date($format,$result);
											break;
					default:
				}
			}
		}

		return $result;
	}

}

?>