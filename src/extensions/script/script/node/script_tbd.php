<?php
//-------------------------------------------------------------------------------------------------
// One_Script_Node_Script
//-------------------------------------------------------------------------------------------------

/**
 * the Script node evaluates its chain and interprets the result as a new script. This is useful in situations where you want to
 * have a variable or the result of a package call to contain nano instructions.
 *
 * @author Paul Delbar - delius
 *
 */
class One_Script_Node_Script extends One_Script_Node_Abstract
{

	function execute( &$data, &$parent )
	{
		$s = $this->executeChain( $this->chain, $data, $parent );
//		echo '<hr>',$s;

		$ns = new One_Script();
		$ss = $ns->executeString( $s, $data );

//		echo '<hr>',$ss;
		return $ss;
	}

}

?>