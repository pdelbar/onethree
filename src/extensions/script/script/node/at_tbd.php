<?php
//-------------------------------------------------------------------------------------------------
// One_Script_Node_At
//-------------------------------------------------------------------------------------------------

class One_Script_Node_At extends One_Script_Node_Abstract
{

  public $sectionName;
  public $argumentExpression;

//  private $searchPath;				// stored when loaded, used when executing


  public function __construct( &$script, $data = "", $args = "", $location = "", $lineNumber = "" )
  {
    parent::__construct( $script, $data, $args, $location, $lineNumber );

    $parts = preg_split('/\s/',trim($data), 2);
    $this->sectionName = $parts[0];
    $this->argumentExpression = $parts[1] or null;
//		echo "Expr = /$parts[1]/";

//    $this->searchPath = One_Script_Factory::getSearchPath();
  }

  function execute( &$data, &$parent )
  {
    $sec = $this->sectionName;
//		echo "Looking for /$sec/";

    // added PD01JAN2005 to allow parameterised section names
    //TODO: make this an expression
    if (substr($sec,0,1) == "=") $sec = $data[ substr($sec,1,strlen($sec)-1) ];


    $sn =& $this->findSectionNode( $sec, $parent );
//		$sn->dump();
    if (empty($sn)) {
      return "";
    }

    // check for an argument
    $sectionArg = $sn->argument;
    if ($sectionArg) {
      $expr = $this->evaluateExpression( $this->argumentExpression, $data, $parent );
      $saveArg = $data[$sectionArg];
      $data[$sectionArg] = $expr;
    }
    //walk through the section's chain and execute
//		$s = $this->executeChain( $sn->chain, $data, $parent );
    $s = $sn->execute( $data, $parent, true );

    if ($sectionArg) {
      if ($saveArg) $data[$sectionArg] = $saveArg;
    }

    return $s;
  }


  /**
   * findSectionNode : helper function to resolve the section name into a node
   * @param $sec
   * @return unknown_type
   */
  private function &findSectionNode( $sec, &$parent )
  {
    //PD09SEP09: check  for a namespace in the section name, format namespace:section
    $parts = explode( ':', $sec, 2 );
    if (count($parts) > 1) {
      $ns = $parts[0];
      $sec = $parts[1];
    }

    // locate the section node using nanoContent
    if ($ns) {
//      One_Script_Factory::saveSearchPath();
//      One_Script_Factory::setSearchPath( $this->searchPath );
      $node = One_Script_Content_Factory::getNode( $ns, $sec  );
//      One_Script_Factory::restoreSearchPath( );
      //			print_r($node);
//			$node->dump();
      return $node;
    }

    // locate the section node in the traditinal style (inside the parent)
    $node = $parent->findSection( $sec, $parent );

    //PD11OCT09: if not found, try prefixing with default namespace
    if (One_Script_Config::$defaultNamespace) {
	    if ($node == null) {
//	      One_Script_Factory::saveSearchPath();
//	      One_Script_Factory::setSearchPath( $this->searchPath );
	      $node = One_Script_Content_Factory::getNode( One_Script_Config::$defaultNamespace , $sec  );
//	      One_Script_Factory::restoreSearchPath( );
	    }
    }

    return $node;
  }


  function compile( $hash, &$parent )
  {
    //TODO: add argument logic
    $fn = "sec_" . $hash . "_" . $this->data;
    $s .= "  if (function_exists('$fn')) $fn( 'data' ); else echo 'Section " . $this->data . " not compiled.';\n";
    return $s;
  }

}
