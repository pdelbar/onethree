<?php
//------------------------------------------------------------------
// package request : functions to access the request
//------------------------------------------------------------------

	class One_Script_Package_Joomla extends One_Script_Package
	{
		public static function siteName()
		{
			global $mainframe;
			return $mainframe->getCfg('sitename');
		}

		public static function livesite()
		{
			return JURI::base();
		}

		public function getHost()
		{
			$juri = JURI::getInstance( JURI::current() );
			return $juri->getScheme() . '://' . $juri->getHost();
		}

		public static function getQuery()
		{
			$uri = JURI::getInstance();
			return $uri->getQuery();
		}

		public static function getLanguage()
		{
			if( JPluginHelper::isEnabled( 'system', 'koowa' ) && method_exists(JFactory::getLanguage(), 'getObject')) {
				return JFactory::getLanguage()->getObject()->get( 'tag' );
			}
			else
				return JFactory::getLanguage()->get( 'tag' );
		}

		function getParam( $paramName )
		{
			global $mainframe;

			$params = $mainframe->getParams();
			return $params->get($paramName);
		}

		function getParam2( $paramName )
		{
			$application = JFactory::getApplication();

			$params = $application->getParams();
			return $params->get($paramName);
		}

		function getVar($varName, $default = NULL, $from = 'default', $filter = NULL)
		{
			return JRequest::getVar($varName, $default, $from, $filter);

			JRequest::getVar();
		}

		function loadPosition( $position )
		{
			if( !function_exists( 'plgContentLoadPosition' ) )
				require_once( JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'loadmodule.php' );

			echo plgContentLoadPosition( $position );
		}


		function addTooltipBehaviour()
		{
			JHTML::_('behavior.tooltip');
		}

		function tooltip($tooltip, $title='', $image='tooltip.png', $text='', $href='', $link=1)
		{
			// http://docs.joomla.org/Tutorial:How_to_add_tooltips_to_your_Joomla!_website
			JHTML::_('behavior.tooltip');

			$tooltip = htmlspecialchars( $tooltip );
			$title   = htmlspecialchars( $title );

			if ( !$text ) {
				$image     = JURI::root(true).'/includes/js/ThemeOffice/'. $image;
				$text     = '<img src="'. $image .'" border="0" alt="'. JText::_( 'Tooltip' ) .'"/>';
			} else {
				$text     = JText::_( $text ); // TR20090506 Removed the parameter for JavascriptSafe, no use that this text is JsSafe anyway + it caused for quotes to be escaped which was viewable in the text
			}

			if($title) {
				$title = $title.'::';
			}

			$style = 'style="text-decoration: none; color: 333;"';

			if ( $href ) {
				$href = JRoute::_( $href );
				$style = '';
				$tip = '<span class="editlinktip hasTip" title="'.$title.$tooltip.'" '. $style .'><a href="'. $href .'">'. $text .'</a></span>';
			} else {
				$tip = '<span class="editlinktip hasTip" title="'.$title.$tooltip.'" '. $style .'>'. $text .'</span>';
			}

			return $tip;
		}

		function route( $url, $xhtml = true, $ssl = NULL )
		{
			return JRoute::_( $url, $xhtml, $ssl );
		}

		// set the pagetitle
		function setTitle( $title )
		{
			$document = JFactory::getDocument();
			$document->setTitle( $title );
			return;
		}

		function getPageTitle()
		{
			$document = JFactory::getDocument();
			return $document->getTitle();
		}

		// add text to the metadata description
		function addMetaDescription( $content )
		{
			$document = JFactory::getDocument();
			$metadescription = $document->getDescription ();
			$document->setDescription( $metadescription . ' ' . $content );
			return;
		}

		// add ext to the metadata keywords
		function addMetaKeywords( $content )
		{
			$document = JFactory::getDocument();
			$metakeywords = $document->getMetaData( "keywords" );
			$document->setMetaData( "keywords", $metakeywords .', ' . $content );
			return;
		}

		// add meta data
		function addMetaTag( $tag, $content )
		{
			$doc =& JFactory::getDocument();
			$doc->setMetaData( $tag, $content );
			return;
		}

			// retrieve the Itemid based on menu name
		function getItemid( $menuName = NULL )
		{
			if( !is_null( $menuName ) )
			{
				$db = JFactory::getDBO();
				$query = "SELECT id FROM jos_menu WHERE name = '$menuName'";
				$db->setQuery($query);

				return $id = $db->loadResult();
			}
			else
			{
				return JRequest::getInt( 'Itemid' );
			}
		}

		// retrieve the Itemid based on menu alias
		function getItemidByAlias( $alias = NULL )
		{
			if( !is_null( $alias ) )
			{
				$db = JFactory::getDBO();
				$query = "SELECT id FROM #__menu WHERE alias = '$alias'";
				$db->setQuery($query);

				return $id = $db->loadResult();
			}
			else
			{
				return JRequest::getInt( 'Itemid' );
			}
		}

		// retrieve a menu based on the Itemid
		function getItem( $id )
		{
			$menu = JSite::getMenu();
			$item = $menu->getItem($id);

			return $item;
		}
		
		// retrieve menu type
		function getMenutype( )
		{
			$itemid = JRequest::getVar('Itemid');
			$menu = &JSite::getMenu();
			$active = $menu->getItem($itemid);
			$menutype = $active->menutype;

			return $menutype;
		}

		function addScript( $url )
		{
			$doc = JFactory::getDocument();
			$doc->addScript( $url );
		}

		function addCustomTag( $tag )
		{
			$doc = JFactory::getDocument();
			$doc->addCustomTag( $tag );
		}

		function getActiveId()
		{
			$menu   = JSite::getMenu();
			$active = $menu->getActive();
			return $active->id;
		}

		function jtext( $var )
		{
			return JText::_( $var );
		}

		function getCurrentItemid()
		{
			return JRequest::getInt( 'Itemid', 0 );
		}

		function addToBreadcrumb( $var, $link = '' )
		{
			$app = JFactory::getApplication();
			$pathway = $app->getPathway();
			$pathway->addItem( $var, $link );
		}

		public static function alterPathway( $alter )
		{
			$app     = JFactory::getApplication();
			$path    = $app->getPathway();
			$current = $path->getPathway();

			if( preg_match( '/^\-(\d+)$/i', $alter, $matches ) > 0 )
				unset( $current[ intval( $matches[ 1 ] ) ] );

			$path->setPathway( $current );
		}

		public static function getCurrentURI()
		{
			return JURI::current();
		}

		public static function getUser( $userId = NULL )
		{
			return JFactory::getUser( $userId );
		}

		public static function isLoggedIn()
		{
			$juser = JFactory::getUser( $userId );
			return ( $juser->guest == 0 );
		}

		public static function setMetaTitle($content)
		{
			$document = JFactory::getDocument();
			$document->setMetaData("title", $content);
		}

		public static function setMetaAuthor($content)
		{
			$document = JFactory::getDocument();
			$document->setMetaData("author", $content);
		}

		public static function setMetaDescription($content)
		{
			$document = JFactory::getDocument();
			$document->setDescription($content);
		}

		// add ext to the metadata keywords
		public static function setMetaKeywords( $content )
		{
			$document = JFactory::getDocument();
			$document->setMetaData("keywords", $content);
			return;
		}

		public static function getJURIBase($pathonly = false)
		{
			return JURI::base($pathonly);
		}

		public static function currentURI()
		{
			return JURI::current();
		}

		public static function putIdForJqgrid()
		{
			$sef = JFactory::getApplication()->getCfg('sef');
			if('1' == $sef) {
				return '/';
			}
			else {
				return '&id=';
			}
		}

		public static function getJPath($app = 'site')
		{
			return constant('JPATH_'.strtoupper($app));
		}
	}
