<?php
//-------------------------------------------------------------------------------------------------
// One_Script_Node_Ln
//-------------------------------------------------------------------------------------------------

class One_Script_Node_Ln extends One_Script_Node_Abstract
{
	function execute( &$data, &$parent )
	{
		$output   = '';
		$selected = NULL;
		$parts    = explode( ':', trim( $this->data ), 2 );

		if( count( $parts ) < 2 )
			return '';

		$scheme    = One_Repository::getScheme( $parts[ 0 ] );
		$factory   = One_Repository::getFactory( $parts[ 0 ] );
		$requested = $parts[1];

		$behaviorOptions = $scheme->get('behaviorOptions.linkalias' );

		if( is_null( $behaviorOptions ) )
		{
			$selected = $factory->selectOne( $requested );
			if( is_null( $selected ) )
				return '';

			$output = $scheme->title() . ' ( ' . $requested . ' )';
		}
		else
		{
			if( isset( $behaviorOptions[ 'lookup' ] ) )
			{
				$query = $factory->selectQuery();
				$query->where( $behaviorOptions[ 'lookup' ], 'eq', $requested );
				$results = $query->result();

				if( count( $results ) == 0 )
					return '';
				else
					$selected = $results[ 0 ];
			}
			else
			{
				$selected = $factory->selectOne( $requested );
				if( is_null( $selected ) )
					return '';
			}

			if( isset( $behaviorOptions[ 'show' ] ) )
			{
				$shown  = $behaviorOptions[ 'show' ];
				$output = $selected->$shown;
			}
			else
			{
				$output = $scheme->title() . ' ( ' . $requested . ' )';
			}
		}

		if( trim( $output ) == '' || is_null( $selected ) )
			return '';
		else
		{
			$idAttr = $scheme->getIdentityAttribute()->getName();
			$task   = 'detail';
			$view   = 'detail';
			if( isset( $behaviorOptions[ 'task' ] ) )
				$task = $behaviorOptions[ 'task' ];
			if( isset( $behaviorOptions[ 'view' ] ) )
				$view = $behaviorOptions[ 'view' ];

			$link = JRoute::_( 'index.php?option=com_one&scheme=' . $scheme->getName() . '&task=' . $task . '&view=' . $view . '&id=' . $selected->$idAttr );
			return '<a href="' . $link . '">' . $output . '</a>';
		}
	}

}
