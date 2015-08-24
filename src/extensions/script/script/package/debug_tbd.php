<?php
//------------------------------------------------------------------
// package db : functions to access the database
//------------------------------------------------------------------

	class One_Script_Package_Debug extends One_Script_Package
	{

		public function dump( $var )
		{
			echo '<pre style="color: #777">';
			var_dump( $var );
			echo '</pre>';
		}

		public function doExit()
		{
			exit;
		}
	}
