<?php
/**
 * Deletes children of a certain model on deletion of the parent model
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/

class One_Behavior_Scheme_Deletechildren extends One_Behavior_Scheme
{
	/**
	 * Return the name of the behaviour
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'deletechildren';
	}

	/**
	 * Deletes children of a certain model on deletion of the parent model
	 *
	 * @param One_Scheme $scheme
	 * @param One_Model $model
	 */
	public function beforeDeleteModel(One_Scheme $scheme, One_Model $model)
	{
    $options     = $scheme->get('behaviorOptions.deletechildren' );

		$dependent = explode( ';', $options[ 'dependent' ] );
		$cascade   = explode( ';', $options[ 'cascade' ] );

		$dependentLeft = array();
		if( count( $dependent ) > 0 )
		{
			foreach( $dependent as $depends )
			{
				if( trim( $depends ) != '' )
				{
					$related = $model->getRelated( trim( $depends ) );
					if( count( $related ) > 0 )
					{
						$dependentLeft[] = trim( $depends );
						break;
					}
				}
			}
		}

		if( count( $dependentLeft ) > 0 )
		{
			throw new One_Exception( 'You can not delete this item untill all items of "' . implode( '", "', $dependentLeft ) . '" have been deleted.' );
			return false;
		}
		else
		{
			$tbds = array_merge( $dependent, $cascade );
			if( count( $tbds ) > 0 )
			{
				foreach( $tbds as $tbd )
				{
					if( trim( $tbd ) != '' )
					{
						$related = $model->getRelated( $tbd );

						if( count( $related ) > 0 )
						{
							foreach( $related as $relation )
								$relation->delete();
						}
					}
				}
			}
		}

		return true;
	}
}