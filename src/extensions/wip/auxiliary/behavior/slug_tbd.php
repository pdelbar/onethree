<?php
/**
 * Adds a slug to the model that can be used for SEF
 *
 * ONEDISCLAIMER
 **/
class One_Behavior_Slug extends One_Behavior {
  /**
   * Return the name of the behaviour
   *
   * @return string
   */
  public function getName() {
    return 'Slug';
  }

  /**
   * When the model is loaded, add a slug-field to the model that is composed of the specified fields
   *
   * @param One_Scheme $scheme
   * @param One_Model $model
   */
    public function afterLoadModel(One_Scheme $scheme, One_Model $model) {
    if ( null !== $scheme->getAttribute('slug') ) { // don't create the slug if the attribute "slug" actually exists
      return;
    }

    $options    = $scheme->get('behaviorOptions.slug');
    $createFrom = $options['createFrom'];

		$parts = preg_split( '/\+/', $createFrom );

		$mangled = array();
		foreach( $parts as $part )
		{
			if( preg_match( '/^([a-z0-9\_\-]+):([a-z0-9\_\-]+)$/', $part, $matches ) > 0 )
			{
				$scheme = $model->getScheme();
				$link   = $scheme->getLink( $matches[ 1 ] );

				if( !is_null( $link ) )
				{
					if( $link->getAdapterType() == 'manytoone' )
					{
						$related = $model->getRelated( $matches[ 1 ] );
						if( !is_null( $related ) )
						{
							$targetPart = $matches[ 2 ];
							$mangle = ( !is_null( $related->$targetPart ) ) ? trim( $this->mangle( $related->$targetPart ) ) : NULL;
							if( !is_null( $mangle ) )
								$mangled[] = $mangle;
						}
					}
				}
			}
			else
			{
				$mangle = ( !is_null( $model->$part ) ) ? trim( $this->mangle( $model->$part ) ) : NULL;
				if( !is_null( $mangle ) )
					$mangled[] = $mangle;
			}
		}

		if( count( $mangled ) > 0 )
			$model->slug = implode( '_', $mangled );
	}

	/**
	 * Translate all non-standard characters to their standard characters
	 *
	 * @param String $str
	 * @return String
	 */
	private function iso2ascii($str)
	{
		$tmp = utf8_decode($str);

		$from = utf8_decode("ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ");
		$to   = "SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy";

		$tmp = strtr($tmp, $from, $to);

		return $tmp;
	}

	/**
	 * Process a string to a name without special characters
	 *
	 * @param String $input
	 * @return String
	 */
	private function mangle( $input )
	{
		// Create an array with words that need to be stripped
		$elim  = "en:van:bij:tot:u:uw:de:een:door:voor:het:in:is:et:la:le:un:une:du:n:est:ce:ca:par:les:d:a:l:op:sur:des:mijn:mon";
		$kill = explode( ":", $elim );

 		$ss = preg_replace(array("/&amp;/", "/&/", "/\?/", "/'/",'/"/', "/`/", "/'/", "/â€™/", "/â€™/", "/’/")," ",$input);
		$ss = preg_replace("/@/"," at ",$ss);

		// iso2ascii must be done prior to strtolower to avoid problems due to encoding
		$ss = $this->iso2ascii($ss);

		$ss = strtolower($ss)." ";

		// Replace unacceptable characters
		$ss = preg_replace( "@&#(\d+);@", 'chr(\1)', $ss);
		$ss = preg_replace( "@(&\w+;)@", 'html_entity_decode("\1")', $ss);

		$ss = preg_replace("/\W/"," ",$ss);

		// Strip earlier defined words
		foreach( $kill as $w)
		{
			$ss = preg_replace( "/(^|\s)$w\s/"," ",$ss);
		}

		$ss = preg_replace("/(\s)+/","_",trim($ss));

		if (substr($ss,strlen($ss)-1,1) == "_") $ss = substr($ss,0,strlen($ss)-1);

		return $ss;
	}

}
