<?php
//-------------------------------------------------------------------------------------------------
// One_Script_Compiler
//
// Compiles a nanoScript into PHP code
//-------------------------------------------------------------------------------------------------

//TODO: make sure output is captured into a string instead of echo'd
//TODO: includes should be loaded from their own One_Script (dynamic) so they can be cached individually

class One_Script_Compiler
{

	static function compile( $script )
	{
		// compute lead hash for this node

		$hash = md5( "some kind of hash" );

		$s = self::header( $hash );

		$node = $script->rootNode;
		$s .= $node->compile( $hash, $script );

		$s .= self::footer( $hash );
		return $s;
	}

	static function header( $hash )
	{
//		$s = "<?php\n";
		$s =  "";

		$fn = "file_" . $hash;
		$s .= "  require_once 'class.One_Script.php';\n";
		$s .= "  function $fn( \$data )\n";
		$s .= "    {\n";

		return $s;
	}

	static function footer( $hash )
	{
		$fn = "file_" . $hash;

		$s = "   }\n";
		$s .= " oneMark();";
		$s .= "  $fn( \$data );\n";
		$s .= " oneMark('COMPILED');";
		$s .= "?>\n";

		return $s;
	}

}
