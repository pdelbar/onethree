<?php
/**
 * On selection from a certain scheme and when the feed task is being performed,
 * the behavior will select specified attributes, needed for creating a feed
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Behavior_Scheme_Feed extends One_Behavior_Scheme
{
	/**
	 * Return the name of the behaviour
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'Feed';
	}

	/**
	 * On selection from a certain scheme and when the feed task is being performed,
	 * select specified attributes needed for creating a feed
	 *
	 * @param One_Query $query
	 */
	public function onSelect(One_Query $query )
	{
		if( $_REQUEST[ 'task' ] == 'feed' )
		{
			$scheme      = $query->getScheme();
      $options     = $scheme->get('behaviorOptions.' . strtolower($this->getName()));
			$title       = $options['title'];
			$description = $options['description'];
			$pubDate     = $options['pubDate'];

			$id = $scheme->getIdentityAttribute()->getName();

			$select = array();
			$select[] = '`' . $id . '` AS `id`';
			if( !is_null( $title ) ) $select[] = '`' . $title . '` AS `title`';
			if( !is_null( $description ) ) $select[] = '`' . $description . '` AS `description`';
			if( !is_null( $pubDate ) ) $select[] = '`' . $pubDate . '` AS `pubDate`';

			$query->select( $select );
		}
	}
}
