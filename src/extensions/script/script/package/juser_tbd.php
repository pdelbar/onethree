<?php
//------------------------------------------------------------------
// package request : functions to access the request
//------------------------------------------------------------------

	class oneScriptPackageJuser extends One_Script_Package
	{
		public function getId()
		{
			$my = JFactory::getUser();
			return $my->id;
		}

		function isLoggedIn()
		{
			$my = & JFactory::getUser();

			if ($my->id > 0)
				return true;
			else
				return false;
		}

		function isCxLoggedIn()
		{
			$cx =& new Context();

			if ($cx->get('userid') > 0)
				return true;
			else
				return false;
		}


		function isAdmin()
		{
			$my = & JFactory::getUser();

			return ($my->usertype == 'Super Administrator') ? true : false;
		}

		function getEmail($id)
		{
			global $db;

			$query = "SELECT email FROM jos_users WHERE id = '$id'";
			$result = $db->one($query);
			if ($result['email']) return $result['email'];
		}

		function getProperty( $property )
		{
			$my = & JFactory::getUser();
			return $my->$property;
		}


    function isInGroup( $groupid ) {
      $my = & JFactory::getUser();
      return in_array( $groupid, $my->getAuthorisedGroups());
    }
	}
