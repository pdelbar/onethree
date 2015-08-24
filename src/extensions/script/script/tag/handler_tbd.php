<?php
/**
 * One_Script_Tag_Handler is the prototype class for tag handlers. They extend the language by allowing unknown tag formats to be handled by
 * individual tag format analyzers. These return a node or false.
 *
 * @author Paul
 *
 */
class One_Script_Tag_Handler
{
	public function name() { return 'abstract handler'; }

	public function handle( $node, $tag, $tagOptions )
	{
		return false;
	}
}
