<?php
//------------------------------------------------------------------
// package request : functions to access the request
//------------------------------------------------------------------

	class One_Script_Package_Contentfactory extends One_Script_Package
	{
		function listUsedNodes()
		{
			if(isset($_SESSION['nsShowNodes'])) {
				ob_start();
				echo '<div id="nsShowNodes">';
				One_Script_Content_Factory::listUsedNodes();
				echo '<div>';
				$nodeInfo = ob_get_clean();
			} else {
				$nodeInfo = '';
			}

			return $nodeInfo;
		}
	}
