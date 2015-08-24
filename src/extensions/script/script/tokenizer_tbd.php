<?php
//-------------------------------------------------------------------------------------------------
// One_Script_Tokenizer
//
// Form tokens from a string
//-------------------------------------------------------------------------------------------------

	define( "NST_NONE", 					"NONE" );
	define( "NST_LITERAL", 				"LITERAL" );
	define( "NST_NUMERIC", 			"NUMERIC" );
	define( "NST_NUMERICFRAC", 	"NUMERICFRAC" );
	define( "NST_WHITESPACE", 		"WHITESPACE" );
	define( "NST_DELIM", 				"DELIM" );
	define( "NST_COLON", 				"COLON" );
	define( "NST_DASH", 					"DASH" );
	define( "NST_OBJECT", 				"OBJECT" );
	define( "NST_DQSTR", 				"DQSTRING" );
	define( "NST_DQSTR_ESC", 		"DQSTRINGE" );
	define( "NST_SQSTR", 				"SQSTRING" );
	define( "NST_SQSTR_ESC", 		"SQSTRINGE" );

//-------------------------------------------------------------------------------------------------
// Tokenizer
//-------------------------------------------------------------------------------------------------
	
class One_Script_Tokenizer
{
	
	public static function tokenize( $str )
	{
		$stack = preg_split('#(?<=.)(?=.)#s', $str );
		reset($stack);

		//while (list($k,$c) = each($stack)) echo "<br>$c";

		$error = "";
		$state = NST_NONE;
		$buffer = "";
		$i = 0;
		$len = strlen($str);
		$tokens = array();

		while (!$error)
		{
			//echo "<br>[$i:$state]";
			$c = $stack[$i];
			switch ($state)
			{
				case NST_NONE :
					if (ctype_alpha($c)) 	{ $buffer .= $c;	$i++;	$state = NST_LITERAL;			break; }
					if ($c == '_') 				{ $buffer .= $c;	$i++;	$state = NST_LITERAL;			break; }
					if ($c == '-') 				{ $buffer .= $c;	$i++;	$state = NST_DASH;				break; }
					if ($c == ':') 				{ $buffer .= $c;	$i++;	$state = NST_COLON;			break; }
					if (ctype_digit($c)) 		{ $buffer .= $c;	$i++;	$state = NST_NUMERIC;			break; }
					if ($c == '"') 				{ $buffer .= $c;	$i++;	$state = NST_DQSTR;			break; }
					if ($c == '\'') 				{ $buffer .= $c;	$i++;	$state = NST_SQSTR;				break; }
					if (ctype_space($c)) 	{ $buffer .= $c;	$i++;	$state = NST_WHITESPACE;	break; }
					if (ctype_punct($c))	{ $buffer .= $c;	$i++;	$state = NST_DELIM;				break; }
					$error = "error, cannot start token";
					break;

				case NST_DQSTR :
					if ($c == '\\') 			{ $buffer .= $c;	$i++;	$state = NST_DQSTR_ESC;		break; }
					if ($c != '"') 				{ $buffer .= $c;	$i++;	break; }
					$buffer .= $c;
					$i++;
					$tokens[] = array( $state, $buffer);
					$state = NST_NONE;
					$buffer = "";
					//echo $i;
					break;

				case NST_DQSTR_ESC :
					$buffer .= $c;
					$i++;
					$state = NST_DQSTR;
					break;

				case NST_SQSTR :
					if ($c == '\\') 		{ $buffer .= $c;	$i++;	$state = NST_SQSTR_ESC;		break; }
					if ($c != '\'') 			{ $buffer .= $c;	$i++;	break; }
					$buffer .= $c;
					$i++;
					$tokens[] = array( $state, $buffer);
					$state = NST_NONE;
					$buffer = "";
					//echo $i;
					break;

				case NST_SQSTR_ESC :
					$buffer .= $c;
					$i++;
					$state = NST_SQSTR;
					break;

				case NST_DASH :
					if ($c == '>') 			{ $buffer .= $c;	$i++;	$state = NST_OBJECT;		break; }
					$tokens[] = array( NST_DELIM, $buffer);
					$state = NST_NONE;
					$buffer = "";
					//echo $i;
					break;

				case NST_OBJECT :
					$tokens[] = array( $state, $buffer);
					$state = NST_NONE;
					$buffer = "";
					//echo $i;
					break;

				case NST_COLON :
					$tokens[] = array( $state, $buffer);
					$state = NST_NONE;
					$buffer = "";
					//echo $i;
					break;

				case NST_WHITESPACE :
					if (ctype_space($c)) 	{ $buffer .= $c;	$i++;	break; }
					$tokens[] = array( $state, $buffer);
					$state = NST_NONE;
					$buffer = "";
					//echo $i;
					break;

				case NST_LITERAL :
					if (ctype_alpha($c)) 	{ $buffer .= $c;	$i++;	break; }
					if ($c == '_') 				{ $buffer .= $c;	$i++;	break; }
					if (ctype_digit($c)) 		{ $buffer .= $c;	$i++;	break; }
					$tokens[] = array( $state, $buffer);
					$state = NST_NONE;
					$buffer = "";
					//echo $i;
					break;

				case NST_NUMERIC :
					if (ctype_digit($c)) 		{ $buffer .= $c;	$i++;	break; }
					if ($c == ".") 				{ $buffer .= $c;	$i++;	$state = NST_NUMERICFRAC;	break; }
					$tokens[] = array( $state, $buffer);
					$state = NST_NONE;
					$buffer = "";
					//echo $i;
					break;

				case NST_NUMERICFRAC :
					if (ctype_digit($c)) 		{ $buffer .= $c;	$i++;	break; }
					$tokens[] = array( NST_NUMERIC, $buffer);
					$state = NST_NONE;
					$buffer = "";
					//echo $i;
					break;

				// any delimiter
				case NST_DELIM :
					$tokens[] = array( $state, $buffer);
					$state = NST_NONE;
					$buffer = "";
					//echo $i;
					break;

				default:
					$error = "cannot handle '$c'";
					break;
			}
			if ($i >= $len)
			{
				if ($state == NST_DQSTR) 			$error = "Unfinished string at end";
				if ($state == NST_SQSTR) 			$error = "Unfinished string at end" ;
				if ($state == NST_DQSTR_ESC) 	$error = "Unfinished escape character at end";
				if ($state == NST_SQSTR_ESC) 	$error = "Unfinished escape character at end";
				if ($buffer !== "") $tokens[] = array( $state, $buffer);
				break;
			};
		}

		if ($error) return $error;
		return $tokens;
	}

}
