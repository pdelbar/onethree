<?php
//-------------------------------------------------------------------------------------------------
// One_Script_Node_Abstract
//
// Represents a node in the script structure. A nanoScript is converted into a series of nodes by
// the parser.
//-------------------------------------------------------------------------------------------------

class One_Script_Node_Abstract
{
	//------------------------------------------------------------------
	// PROPERTIES
	//------------------------------------------------------------------

	var $data;							// what is its local data (if any)
	var $args;							// placeholder for any args needed

	var $chain;						// if it has a chain of tokens
	var $altChain;					// if it has another one (like if/else)

	var $error;

	var $script;						// added PD13NOV05 to allow sending different scriptdir to include script

	var $parsedExpression;		// added: cache the parse result for future use

	var $location;						// location the script containing this node was read from
	var $lineNumber;				// line number in the script
	var $token;						// for debug purposes: used to trace copies being made of objects

	//------------------------------------------------------------------

	public function __construct( &$script, $data = "", $args = "", $location = "", $lineNumber = "" )
	{
		$this->data = $data;
		$this->args = $args;

		$this->chain = array();
		$this->altChain = array();

		$this->error = "";

		$this->script = $script;

		$this->location = $location;
		$this->lineNumber = $lineNumber;
		$this->parsedExpression = null;
	}

	/**
	 * Factory method to create nodes
	 *
	 * @param $tag	node type to instantiate
	 * @param $data
	 * @param $args
	 * @return node instance
	 */
	public function &newNode( $tag,  $data = "", $args = "" )//, $location = "", $lineNumber = "" )
	{
		if ($tag == '@' )
			$tag = 'at';
		$nodeClass = 'One_Script_Node_' . ucfirst(strtolower($tag));

		if (!class_exists($nodeClass))
		{
			$nodeClass = 'One_Script_Node_Abstract';
			$show = $tag . ( ( trim( $data ) != '' ) ? ' ' . $data : '' ) . ( ( trim( $args ) != '' ) ? ' ' . $args : '' );
			$data = One_Script_Config::NSNODE_OPEN . $show . One_Script_Config::NSNODE_CLOSE;
		}

		$nNode = new $nodeClass( $this->script, $data, $args, $this->location, $this->lineNumber  );

		return $nNode;
	}

	//------------------------------------------------------------------
	// parseTokens : receives an array of text chunks and converts all
	// or part of it into a chain of nsNode objects.
	//------------------------------------------------------------------

	function &parseTokens( $originalTokens, $endToken = false, $midToken = false, $includePath = "" )
	{
		$c = array();
		$endFound = false;
		$midFound = false;

		$tokens = $originalTokens;

		while (count($tokens) and !$endFound)
		{
			//print "[" . count($tokens) . ":" . htmlspecialchars($tokens[0][0]) . "]";
			list($token,$location,$lineNumber) = array_shift( $tokens);
			if (!empty($token))
			{

				if ($midToken and (!strcmp($token,$midToken)))
				{
					$midFound = true;
					//print "<br><b>FOUND MIDTOKEN=$midToken</b>";
					$this->chain = $c;										// store and start on altChain
					$c = array();
				}
				else if ($endToken and (!strcmp($token,$endToken)))
				{
					$endFound = true;
					//print "<br><b>FOUND ENDTOKEN=$endToken</b>";
					//print count($tokens);
				}
				else
				{
					//print "<br>&nbsp;&nbsp;&nbsp;<span style=\"color: #3333ff;\">(" . htmlspecialchars($token) . ")</span>";

					if ($token[0] == One_Script_Config::NSNODE_OPEN)
					{
						//--------------------------------------------------------------------------
						// this is a nanoScript tag
						//--------------------------------------------------------------------------
						preg_match( "!".One_Script_Config::NSNODE_OPEN."\s*(.*?)\s*".One_Script_Config::NSNODE_CLOSE."!s", $token, $match );			// decode the tag
						$content = empty($match) ? "" : $match[1];

						$parts = preg_split( "!\s!", $content, 2 );				// the tag and the rest
						if (count($parts) > 1)
							list( $tag, $tagOptions ) = $parts;
						else
						{
							$tag = $parts[0];
							$tagOptions = "";
						}

						//print "<b>TAG=$tag</b>";

						$pairedToken = $this->hasEndToken($tag);

						if ($pairedToken)
						{
							//--------------------------------------------------------------------------
							// this is a block tag
							//-------------------------------------------------------�-------------------
							$n = $this->newNode( $tag, $tagOptions, "" );
							$tokens = $n->parseTokens( $tokens, One_Script_Config::NSNODE_OPEN . $pairedToken . One_Script_Config::NSNODE_CLOSE, 0, $includePath );
							if (!$tokens)
							{
								$this->error = $n->error;
								return 0;
							}
							// print "adding BLOCK " . $this->args;
							$c[] = $n;
						}
						else switch ($tag) {

							case 'set' :
								list( $lvalue, $expr ) = explode( "=", $tagOptions ,2);
								$n = $this->newNode( "set", trim($expr), trim($lvalue) );
								$c[] = $n;
								break;

							case 'if' :
								$n = $this->newNode( "if", $tagOptions, "" );
								$tokens = $n->parseTokens( $tokens,
											One_Script_Config::NSNODE_OPEN."endif".One_Script_Config::NSNODE_CLOSE,
											One_Script_Config::NSNODE_OPEN."else".One_Script_Config::NSNODE_CLOSE,
											$includePath );
								if (!$tokens)
								{
									$this->error = $n->error;
									return 0;
								}
								$c[] = $n;
								break;

							case 'include' :
								// parse the file
								$sp = new One_Script;
								$sp->load( $tagOptions, $includePath );

								// create an include node
								$n = $this->newNode( "include", $tagOptions, "" );
								$n->chain =$sp->rootNode->chain;
								$c[] = $n;
								break;

							case '=' :
								$n = $this->newNode( "evaluate", $tagOptions, "" );
								$c[] = $n;
								break;

							default :
								//--------------------------------------------------------------------------
								// this is an old-style = tag (kept for compatibility)
								//--------------------------------------------------------------------------
								if ($tag[0] == '=')
								{
									$comp = preg_split( "!\s!", substr($content,1), 2);
									$identifier = $comp[0];
									$modifier = "";
									if (count($comp) > 1) $modifier = $comp[1];
									$n = $this->newNode( "value", $identifier, $modifier );
									$c[] = $n;
								}
								else
								{
									//--------------------------------------------------------------------------
									// this is another tag : check for tags to be ignored
									//--------------------------------------------------------------------------
									//PD13MAY10 : run sequence of tag naming handlers
//									echo '<br>oops, an unhandled tag called ', $tag;
									$tagHandled = false;
									foreach (One_Script_Config::$tagHandlers as $handler) {
//										echo '<br/>running ', $handler->name();
										if (!$tagHandled) {
											$nn = $handler->handle( $this, $tag, $tagOptions );
											if ($nn !== false) {
												$c[] = $nn;
												$tagHandled = true;
											}
										}
//										else echo ' (skipping)';
									}

									if (!$tagHandled) {
//										if (in_array( $tag, One_Script_Config::$ignoreTags))
//										{
//											$n =& $this->newNode( "text", $token, "" );
//											//print "adding ignored tag '$tag'";
//											$c[] =& $n;
//										}
//										else
//										{
//											$n =& $this->newNode( $tag, $tagOptions, "" );
////											print "adding $tag";
//											$c[] =& $n;
//										}
									}

								}
							}
					}
					else
					{
						//--------------------------------------------------------------------------
						// this is a regular text block
						//--------------------------------------------------------------------------
						$n = $this->newNode( "generic", $token, "" );
						$c[] = $n;
					}
				}
			}
		}

		if ($midFound)
			$this->altChain = $c;		// store collected tokens in alternate chain
		else
			$this->chain = $c;			// store collected tokens in primary chain

		if ($endToken and !$endFound)
		{
			if ($midToken)
				$this->error = "One_Script_Error: could not find '$midToken' or '$endToken'";
			else
				$this->error = "One_Script_Error: could not find '$endToken'";
			// $this->dump();
			return 0;
		}

		return $tokens;				// send back whatever is left
	}

	//------------------------------------------------------------------
	// parseBlock : similar to parseSection, but recursive. This means
	// that we start consuming tokens until we find the end token. If
	// we run out before we find an end token, that is an error.
	//------------------------------------------------------------------

	function &parseBlock( $startToken, $endToken, $tokens, $localPath )
	{
		$remainder = $this->parseTokens( $tokens, $endToken, 0, $localPath );

		return $remainder;
	}

	//------------------------------------------------------------------
	// hasEndToken : true if this token has an end token
	//------------------------------------------------------------------

	function hasEndToken( $startToken )
	{
		if (array_key_exists( $startToken, One_Script_Config::$nodeBlockTokens ))
			return One_Script_Config::$nodeBlockTokens[$startToken];
		else
			return false;
	}

	//------------------------------------------------------------------
	// parseExpression : turn expression into executable code
	//------------------------------------------------------------------

	function parseExpression( &$code, &$data )
	{
		//var_dump($data);
//		echo "<br>parseExpression(" . htmlspecialchars(''.$code.'') . ")";
		$t = One_Script_Tokenizer::tokenize( $code );
		$result = "";

		if (!is_array($t))
		{
			return "Expression syntax error: $tokens";
		}

		$nTokens = count($t);

		for ($tIndex = 0; $tIndex < $nTokens; $tIndex++ )
		{
			$tv = $t[$tIndex];
			if (is_array($tv))
			{
				switch ($tv[0])
				{
					case NST_OBJECT :
						$tIndex++;
						$tv = $t[$tIndex];
						$result .= "->".$tv[1];
						break;

					case NST_COLON :
						$tIndex++;
						$result .= "::";
						break;

					case NST_LITERAL :
						// PD31OXT08: corrected, do not assume this is a package if not followed by colon: rather assume it is a literal
						if (One_Script_Package::isPackage($tv[1]) && ($t[$tIndex+1][0] == NST_COLON))
						{
//							echo "<br> --- package " . $tv[1];
							$thePackage = $tv[1];

							// anticipate a colon
							$tIndex++;
							$tv = $t[$tIndex];

							// check for a method string
							$tIndex++;
							$tv = $t[$tIndex];
							if (is_array($tv))
							{
								if ($tv[0] == NST_LITERAL)
								{
									$theMethod = $tv[1];

									// anticipate an open parentheses
									$tIndex++;
									$tv = $t[$tIndex];
									if (($tv[0] == NST_DELIM) and ($tv[1] == "("))
									{
										$result .= "One_Script_Package::call('$thePackage','$theMethod'";
										$ntv = $t[$tIndex+1];
										if (!(($ntv[0] == NST_DELIM) and ($ntv[1] == ")")))
										{
											$result .= ',';
										}
										break;
									}
									else
									{
										$this->error = "Expression syntax error: expected '(' following package method '$thePackage:$theMethod' when evaluating '".htmlspecialchars($code)."'"
										. " ({$this->location} : {$this->lineNumber})";
										return $this->error;
									}
								}
								else
								{
									$this->error = "Expression syntax error2: expected method name following package prefix '$thePackage:' when evaluating '".htmlspecialchars($code)."'"
									. " ({$this->location} : {$this->lineNumber})";
									return $this->error;
								}
							}
							else
							{
								$this->error = "Expression syntax error: expected method name following package prefix '$thePackage:' when evaluating '".htmlspecialchars($code)."'"
								. " ({$this->location} : {$this->lineNumber})";
								return $this->error;
							}
							$result .= $tv[1];
						}
						else if (array_key_exists( $tv[1], $data ))
						{
							$newVal = $data[$tv[1]];
							if (is_null($newVal)) $newVal = '';
							if (is_string($newVal) )
							{
								$result .= "\$data['" . $tv[1] . "']";
								//$result .= "'" . addslashes($newVal) . "'";
								//echo "<br> --- replacing " . $tv[1] . " by |" . $newVal . "|";
							}
							else if (is_array($newVal))
							{
								$result .= "\$data['" . $tv[1] . "']";
								//echo "<br> --- replacing " . $tv[1] . " by |" . $newVal . "|";
							}
							else if (is_object($newVal))
							{
								$result .= "\$data['" . $tv[1] . "']";
								//echo "<br> --- replacing " . $tv[1] . " by |" . $newVal . "|";
							}
							else
							{
								//$result .= $newVal;
								$result .= "\$data['" . $tv[1] . "']"; // keep rvalue
								//echo "<br> --- replacing " . $tv[1] . " by |" . $newVal . "|";
							}
						}
						else if (function_exists( $tv[1] ))
						{
							// a function name is used : determine whether this is a call
							$ntv = $t[$tIndex+1];
							if (($ntv[0] == NST_DELIM) and ($ntv[1] == "("))
							{
								if (One_Script_Config::$expressionAllowsFunctions)
								{
									if (!empty(One_Script_Config::$expressionForbidFunctions))
									{
										if (in_array( $tv[1], One_Script_Config::$expressionForbidFunctions ))
										{
											$this->error = "Expression syntax error: call to function '{$tv[1]}' is forbidden "
											. " ({$this->location} : {$this->lineNumber})";
											return $this->error;
										}
									}
									if (!empty(One_Script_Config::$expressionRestrictFunctions))
									{
										if (!in_array( $tv[1], One_Script_Config::$expressionRestrictFunctions ))
										{
											$this->error = "Expression syntax error: call to function '{$tv[1]}' is not allowed "
											. " ({$this->location} : {$this->lineNumber})";
											return $this->error;
										}
									}

									$result .= $tv[1];
									//echo "<br> --- replacing <b>" . $tv[1] . "</b> by function |" . $tv[1] . "|";
								}
								else
								{
									$this->error = "Expression syntax error: call to function '{$tv[1]}' is not allowed "
									. " ({$this->location} : {$this->lineNumber})";
									return $this->error;
								}
							}
							else
							{
								$result .= $tv[1];
								//echo "<br> --- replacing <b>" . $tv[1] . "</b> by function |" . $tv[1] . "|";
							}
						}
						else if ($tv[1] == 'array')
						{
							$result .= "array";
							//$result .= "''";
							//echo "<br> --- replacing <b>" . $tv[1] . "</b> by NADA";
						}
						else
						{
							$result .= "\$data['" . $tv[1] . "']";
							//$result .= "''";
							//echo "<br> --- replacing <b>" . $tv[1] . "</b> by NADA";
						}
						break;

					default :
						// any other token
						$result .= $tv[1];

				}
			}
			else
			{
				switch ($tv)
				{
					default :
						$result .= $tv;
				}
				//echo "<br>$tv";
			}
		}
		return $result;
	}

	//------------------------------------------------------------------
	// evaluateExpression : execute the expression's code
	//------------------------------------------------------------------


	function evaluateExpression( $expression, $data, &$parent )
	{
		//echo "<br>Cache ". $this->token." is set to [" . htmlspecialchars($this->parsedExpression) . "]";
		// check expression cache
		if (is_null($this->parsedExpression))
		{
			//echo "<br>evaluateExpression[" . htmlspecialchars($expression) . "]";
			$expr = $this->parseExpression( $expression, $data );
			if ($this->error) return $this->error;

			//echo "Expression is [" . htmlspecialchars($expr) . "]";
			$dummy;
			//echo " global \$condition; \$condition = ($expr);";

			$this->parsedExpression = $expr;
			//echo "<br>Caching ". $this->token."[<pre>" . htmlspecialchars($expr) . "</pre>]";
//			echo "<br>Cache ". $this->token." is now set to [<pre>" . htmlspecialchars($this->parsedExpression) . "</pre>]";
		}
		else
		{
			//echo "Using cached expression";
			$expr = $this->parsedExpression;
//			echo "Expression is [" . htmlspecialchars($expr) . "]";
		}

		$catcher = rand(1,pow(10,8));
		$condition = $catcher;
		try {
			global $debugNsExpression, $debugNsExpressionError, $debugNsData;
			$debugNsData = $data;
			$debugNsExpressionError = false;
			$debugNsExpression = array( $expr, $this->location, $this->lineNumber );

			One_Script_Error::set_error_handler();
			One_Script_Error::setInNano(true);
			@eval("\$condition = ($expr);");
			One_Script_Error::setInNano(false);
	//		if ($debugNsExpressionError) exit;
			$debugNsData = null;
		} catch ( Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
			echo 'during eval of ', $expr;
		}
//		if ($debugNsExpressionError) exit;

		if (!is_object($condition) && ($condition == $catcher))
		{
			$condition = "Expression evaluation failed when evaluating '".htmlspecialchars($expression)."'";
			//echo $condition;
			$condition .= " into '".htmlspecialchars($expr)."'" . " ({$this->location} : {$this->lineNumber})";
		}

//		print "<br>EXPRESSION = ($expr) and it is " . $condition;
		return $condition;
	}

	//------------------------------------------------------------------
	// execute : do this node's actions
	//
	// corresponds to the old handleGeneric functionality
	//------------------------------------------------------------------

	function execute( &$rdata, &$parent )
	{
		return $this->data;
	}

	//------------------------------------------------------------------
	// compile
	//------------------------------------------------------------------

	function compile( $hash, &$parent )
	{
		$s = "?>" . $this->data . "<?php\n";
		return $s;
	}

	//------------------------------------------------------------------

	function executeChain( &$chain, &$data, &$parent, $myParent = NULL )
	{
		$s = "";
		reset($chain);
		while (list ($key, $nv) = each($chain))
		{
			$n = $chain[$key];
			$s .= $n->execute($data, $parent, $myParent);
		}
		return $s;
	}

	//------------------------------------------------------------------

	function dump( $indent = 0 )
	{
		if ($indent == 0)
		{
			print "<style>";
			print ".bloobs { font-size: 10px; font-family: Tahoma;}";
			print ".bloobhl { background-color: #ccccff;font-family: Tahoma; }";
			print ".bloobhl i { color: #666699; }";
			print ".bloobhls { background-color: #ccff99; font-size: 10px;font-family: Tahoma; }";
			print ".bloobbox1 { background-color: #ffffff; padding: 2px; border: 0px solid #0000ff; font-size: 10px;font-family: Tahoma; margin: 2px 0px 0px 8px;}";
			print ".bloobbox0 { background-color: #ccccff; padding: 2px; border: 0px solid #0000ff; font-size: 10px;font-family: Tahoma; margin: 2px 0px 0px 8px;}";
			print "</style>";
		}

		print '<div class="bloobbox' . ($indent % 2) . '">';

		print "<span class=bloobhl><i>". $this->location. " : " . $this->lineNumber . "</i> <b>[" . str_replace('nsNode','',get_class($this));
		//print count($this->chain) ? " chain=" . count($this->chain) : "";
		print "]</span></b>";
		print htmlspecialchars($this->data);
		if ($this->args != "")
		{
			print "<b><span class=bloobhl>[args]</span></b>";
			print $this->args;
			print "<b><span class=bloobhl>[/args]</span></b>";
		}
		print "<b><span class=bloobhl>[/" . str_replace('nsNode','',get_class($this)) . "]</span></b>";
		echo '(', get_class($this), ')';

		if ($this->error) print " ".$this->error;
		if ($this->parsedExpression) print "<br>cache: ".htmlspecialchars($this->parsedExpression);

		if (count($this->chain) > 0)
		{
			foreach ($this->chain as $n)
			{
				$n->dump( $indent + 1 );
			}
		}
		if (count($this->altChain) > 0)
		{
			print '</div>';
			print str_repeat("&nbsp;",$indent);
			print "<b><span class=bloobhl>[alt][/alt]</span></b>";
			print '<div class="bloobbox' . ($indent % 2) . '">';
			foreach ($this->altChain as $n)
			{
				$n->dump( $indent + 1 );
			}
		}
		print "</div>";
	}

	//------------------------------------------------------------------
	// isError : return true on error
	//------------------------------------------------------------------

	function isError( )
	{
		return ($this->error != "");
	}

	//------------------------------------------------------------------

}
