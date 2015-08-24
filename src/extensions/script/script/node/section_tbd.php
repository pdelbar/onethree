<?php
//-------------------------------------------------------------------------------------------------
// One_Script_Node_Section
//-------------------------------------------------------------------------------------------------

class One_Script_Node_Section extends One_Script_Node_Abstract
{
	public $sectionName;
	public $argument;

	public $mark;                                           // set to true if the output needs to be marked for debugging or so
	public $ns;

	public function __construct( &$script, $data = "", $args = "", $location = "", $lineNumber = "" )
	{
		parent::__construct( $script, $data, $args, $location, $lineNumber );

		$parts = preg_split('/\s/',trim($data));
		$this->sectionName = $parts[0];
		$this->argument = $parts[1] or null;
//              echo 'ARG=',$this->argument;

		$this->mark = false;
	}

	// never gets executed ...
	function execute( &$data, &$parent, $force = false )
	{
//               print "(checking whether '" . $this->sectionName . "' is the same as the desired section '" . $parent->activeSection . "')";
					if (!$force && ($parent->activeSection != $this->sectionName)) {
//                      echo ' *** not this section ***';
									return '';
					}

					$s = '';
//              echo "(stepping through ", count($this->chain), " children nodes)";
					$saveDesired = $parent->activeSection;
					$parent->activeSection = '';
//              $s .= "(inside " . $this->sectionName . ", select now blanked)";
					$s .= $this->executeChain( $this->chain, $data, $parent );
					$parent->activeSection = $saveDesired;
//              $s .= "(select now " . $saveDesired . ")";

					// if marking is necessary, apply marking to current contents
					if ($this->mark) {
//                      $s = '<span style="background-color: #FF2AF8; border: 0px solid #333;"><img style="display: inline;margin: 0px; float: none; padding: none;" src="../core/viewd.gif" alt="haha">' . $s . '</span>';
									switch ($_SESSION['nsShowNodes']) {
													case 'prefix' :
																	$s = '(' . $this->ns . ':' . $this->sectionName . ')' . $s ;
																	break;
													case 'only' :
																	$s = '###(' . $this->ns . ':' . $this->sectionName . ')' . '###';
																	break;
													case 'color' :
																	$s = '<span style="background-color: #FF2AF8; border: 0px solid #333;"><img style="display: inline;margin: 0px; float: none; padding: none;" src="../core/viewd.gif" alt="' . $this->ns . ':' . $this->sectionName . '" title="' . $this->ns . ':' . $this->sectionName . '">' . $s . '</span>';
																	break;
													default :
																	break;
									}
					}

					return $s;
	}

	function compile( $hash, &$parent )
	{
		//TODO: add argument logic
		$fn = "sec_" . $hash . "_" . $this->sectionName;
		$s .= "  function $fn() { \n";
		foreach ($this->chain as $n) $s .= $n->compile( $hash, $parent );
		$s .= "     }\n";
		return $s;
	}
}
