<?php
/**
 * One_Script_Tag_Handler_Ignore handles the list of tags to ignore. This code used to be inside the abstract node class.
 *
 * @author Paul
 *
 */
class One_Script_Tag_Handler_Ignore extends One_Script_Tag_Handler
{
		public function name() { return 'ignore handler'; }

	public function handle( $node, $tag, $tagOptions )
	{
//		echo "<br/>One_Script_Tag_Handler_Ignore requested to do something with $tag";
		if (in_array( $tag, One_Script_Config::$ignoreTags))
		{
//			echo ' -- and complying';
			$tagNode = $tag . ( ( trim( $tagOptions ) != '' ) ? ' ' . $tagOptions : '' ); // TR20100929 used to set an extra space at the end if tagoptions were empty which could cause problems
			return $node->newNode( "ignore", $tagNode, "" );
//			return $node->newNode( "ignore", $tag . ' ' . $tagOptions, "" );
		}
		return false;
	}

}
