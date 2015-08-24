<?php
/**
 * nsContent is the class which loads database content using namespace and section (and possibly other parameters)
 * and returns a node containing this content. The content is loaded and cached by nsContentLoader.
 */

class One_Script_Content_Factory
{
        /**
         * $nsContentCache is the array holding all content by namespace
         * @var unknown_type
         */
        public static $nsContentCache = array();


        public static function clearContentCache()
        {
          self::$nsContentCache = array();
        }

        /**
         * Retrieve the specified node, loading it if needed
         *
         * @param $ns
         * @param $section
         * @return unknown_type
         */
        public static function &getNode( $ns, $section )
        {
//      		echo "called for $ns:$section, content cache for $ns is " . (isset(self::$nsContentCache[$ns]) ? "OK" : "Not OK") . "<br />";

			      if (array_key_exists( $ns, self::$nsContentCache)) {                                            // the namespace is available
	                if (!array_key_exists( $section, self::$nsContentCache[$ns])) {                 // but the section is not present
	                                return null;
	                        }
	                } else {                                                                                                                                                                                // autoload the namespace from the $ns.html file
	                        self::loadNamespace( $ns );
	                }

	                if ( is_array( self::$nsContentCache[$ns] ) && array_key_exists( $section, self::$nsContentCache[$ns])) {
	                        $node = self::$nsContentCache[$ns][$section];
	                        self::registerUsedNode( $ns, $section, $node );
	                        $node->mark = true;
	                        $node->ns = $ns;
	                        return $node;
	                }
                else return null;
        }

        private static function getStore()
        {
                if (One_Script_Config::$nsContentStoreClass) {
                        $storeClass = One_Script_Config::$nsContentStoreClass;
                        $store = new $storeClass;
                        return $store;
                }
                return null;
        }

        private static function loadNamespace( $namespace )
        {
//                              if (self::loadNamespaceFromDB( $namespace )) return;

                // try the autoload approach

//              echo '<hr>'; print_r(One_Script_Factory::getSearchPath());die;
                $ns = new One_Script();
                $ns->load( 'ini:'.$namespace.'.n.html' );
                if (!$ns->isError()) {
                        self::$nsContentCache[ $namespace ] = array();
                        // process nodes and place them in the cache
                        $root = $ns->rootNode;
                        foreach ($root->chain as $node) {
                                if ($node instanceof One_Script_Node_Section) self::$nsContentCache[ $namespace ][ $node->sectionName ] = $node;
                        }
                } else {
                      echo $ns->error;
                }

                if (One_Script_Config::$ncEnabled) {
//                      echo 'loading';
                        $store = self::getStore();
                        if ($store) $store->loadNamespace( $namespace );
                }

        }

        public static function updateNode( $ns, $section, $content, $options = array() )
        {
                $store = self::getStore();
                if ($store) {
                        $store->updateNode( $ns, $section, $content, $options );
                }
        }

        public static function retrieveNode( $ns, $section, $options = array() )
        {
                $store = self::getStore();
                if ($store) {
                        return $store->retrieveNode( $ns, $section, $options );
                }
                return '';
        }

        public static function deleteNode( $ns, $section, $options = array() )
        {
                $store = self::getStore();
                if ($store) {
                        $store->deleteNode( $ns, $section, $options );
                }
        }

        private static function registerUsedNode( $ns, $section, $node )
        {
                $_SESSION['One_Script_Content_Factory:usedNodes'][ $ns ][ $section ] = $node;
        }

        public static function getUsedNodes()
        {
                return $_SESSION['One_Script_Content_Factory:usedNodes'];
        }

        public static function resetUsedNodes()
        {
                $_SESSION['One_Script_Content_Factory:usedNodes'] = array();
        }

        public static function listUsedNodes()
        {
                $data = $_SESSION['One_Script_Content_Factory:usedNodes'];

//              echo "[";
//              print_r($_SESSION['One_Script_Content_Factory:usedNodes']);
//              echo "]";

//                echo '<ul id="nsShowNodesTree">';
//                if ($data) foreach ($data as $ns => $ar) {
//                        echo '<li>', $ns, '<ul>';
//                        asort($ar);
//                        foreach ($ar as $section => $node) {
//                                $id = ' id="nsli_' . $ns . '_' . $section . '"';
//                                echo ($node->origin == 'db') ? '<li class="db"' : '<li';
//                                echo $id . ' >';
//                                echo '<a onclick="nsEdit(\'' . $ns . '\',\''. $section . '\',\''. $node->origin. '\');">' . $section . '</a>';
//                                echo '</li>';
//                        }
//                        echo '</ul></li>';
//                }
//                echo '</ul>';

                $s = '<ul id="nsShowNodesTree">';
                if ($data) foreach ($data as $ns => $ar) {
                        $s .= '<li>'. $ns . '<ul>';
                        asort($ar);
                        foreach ($ar as $section => $node) {
                                $id = ' id="nsli_' . $ns . '_' . $section . '"';
                                $s .= ($node->origin == 'db') ? '<li class="db"' : '<li';
                                $s .= $id . ' >';
                                $s .= '<a onclick="nsEdit(\'' . $ns . '\',\''. $section . '\',\''. $node->origin. '\');">' . $section . '</a>';
                                $s .= '</li>';
                        }
                        $s .= '</ul></li>';
                }
                $s .= '</ul>';

                return $s;
        }


        public function getFlattened( $path, $section )
        {
                list($dummy,$s,$dummy) = One_Script_Factory::loadFileContents( $path, 'ini' );
                $pattern1 = "/\{section ".$section."\}/";
                $pattern2 = "/\{endsection\}/";
                list($dummy,$rest) = preg_split( $pattern1, $s, 2 );
                list($sectionContent,$rest) = preg_split( $pattern2, $rest, 2 );
                return $sectionContent;
        }

}


One_Script_Content_Factory::resetUsedNodes();