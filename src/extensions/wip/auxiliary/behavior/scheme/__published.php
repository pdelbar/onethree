<?php
/**
 * Adds a published behavior to a scheme
 * It allows to see whether or not a scheme can be (un)publishable
 * and also specify which attribute is used to determine if the model is published or not ( "published" by default )
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Behavior_Scheme_Published extends One_Behavior_Scheme
{
	/**
	 * Return the name of the behaviour
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'published';
	}

}
