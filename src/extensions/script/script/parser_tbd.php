<?php
//-------------------------------------------------------------------------------------------------
// One_Script_Parser
//
// Turns a chunk of text into a nanoScript (a chain of nsNodes)
//-------------------------------------------------------------------------------------------------

class One_Script_Parser
{

	//------------------------------------------------------------------
	// PROPERTIES
	//------------------------------------------------------------------

	var $script;			// what type of node is this

	//------------------------------------------------------------------
	// STRUCTORS
	//------------------------------------------------------------------

	public function __construct( &$script )
	{
		$this->script = $script;
	}

	//------------------------------------------------------------------
	// PARSING
	//------------------------------------------------------------------
	
	function undouble( $matches )
	{
		if ($matches[0] == "{{") return "{opencurlybraces}";
		if ($matches[0] == "}}") return "{closecurlybraces}";
		return '***';
	}

	function &parse( $stringToParse, $filename, $path, $actualPath )
	{
		$root = new One_Script_Node_Root( $this->script, "", "", $path . $filename, 1  );
		$root->origin = $actualPath;

		$fileContents = $stringToParse;

		// replace {{ and }} by tokens to facilitate parsing of nodes

		$ot = One_Script_Config::NSNODE_OPEN;
		$ct = One_Script_Config::NSNODE_CLOSE;

//		$fileContents = str_replace( "$ot$ot", $ot."opencurlybraces".$ct, $fileContents);
//		$fileContents = str_replace( "$ct$ct", $ot."closecurlybraces".$ct, $fileContents);

		$fileContents = preg_replace_callback( "/(".preg_quote("$ot$ot") . "|" . preg_quote("$ct$ct"). ")/",
												array('One_Script_Parser','undouble'),
												$fileContents
											);

		// scan for nanotags

		$pat = "!(".One_Script_Config::NSNODE_OPEN."|".One_Script_Config::NSNODE_CLOSE.")!s";											//echo $pat;
		preg_match_all($pat, $fileContents, $match);

		$anchors = $match[1];																	//var_dump($tags);
		$blocks = preg_split( $pat, $fileContents);

//		print count($anchors) . " ANCHORS and " . count($blocks) . " BLOCKS";

		$tokens = array();
		$i = 0;
		$line = 1;
		$inTag = false;
		while ($i < count($blocks))
		{
			if ($inTag)
			{
				$tokens[] = array( ($ot . $blocks[$i] . $ct), $filename, $line );
//				echo "<br>TAG $line:". "<b>".$ot . htmlspecialchars($blocks[$i]) . $ct."</b>";
			}
			else
			{
				$tokens[] = array( $blocks[$i], $filename, $line );
//				echo "<br>TEXT $line:" . htmlspecialchars($blocks[$i]) . "";
			}

			// count number of line breaks in this block
			$line += substr_count( $blocks[$i], "\n" );

//			echo "i=$i and there are ", count($anchors), " anchors";
			if ($i < count($anchors))
			{
				$anchor = $anchors[$i];
				if ($inTag)
					if (($anchor == One_Script_Config::NSNODE_OPEN) && (strlen($block[$i]) > 0))
					{
						$root->error = "One_Script syntax error" . ($filename ? " in '$filename'" : "") . " : tag starting on line $line with '"
										. substr($blocks[$i],0,min(8,strlen($blocks[$i]))) . "...' does not end.";
						return $root;
					}
				if (!$inTag)
					if (($anchor == One_Script_Config::NSNODE_CLOSE) && (strlen($block[$i]) > 0))
					{
						$root->error = "One_Script syntax error" . ($filename ? " in '$filename'" : "") . " : tag closes before opening on line $line after '"
										. substr($blocks[$i],0,min(8,strlen($blocks[$i]))) . "...' .";
						return $root;
					}
				}

			$inTag = !$inTag;

			$i++;
		}

//		echo "<br/>i is now $i";
		if (!$inTag)
		{
			$i = $i - 1;
			$root->error = "One_Script syntax error" . ($filename ? " in '$filename'" : "") . " : tag starting on line $line with '"
							. substr($blocks[$i],0,min(8,strlen($blocks[$i]))) . "...' does not end.";
			return $root;
		}

		//echo "[parse of '$filename' done]";

		//--- evaluate the stream of tokens

		$root->parseTokens( $tokens, 0, 0, $path );

		//$root->dump();
		return $root;
	}
}
