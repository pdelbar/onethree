<?php
/**
 * One_Script_Tag_Handler_Catchall always returns a node, counting on the class to exist.
 *
 * @author Paul
 *
 */
class One_Script_Tag_Handler_Catchall extends One_Script_Tag_Handler
{
	public function name() { return 'catchall handler'; }

	public function handle( $node, $tag, $tagOptions )
	{
		return $node->newNode( $tag, $tagOptions, "" );
	}

}
