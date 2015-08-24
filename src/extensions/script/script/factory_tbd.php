<?php
//-------------------------------------------------------------------------------------------------
// 	One_Script_Factory
//
//	The One_Script_Factory class retrieves the script contents based on the script name. It handles
//  different storage types and is able to search a path for the correct script.
//-------------------------------------------------------------------------------------------------

class One_Script_Factory
{
    private static $searchPatterns = array();

    public static $nsLoadRoot = "";                    // file path where all searches start
//    private static $nsLoadPath = array();            // sequence of search patterns to add to loadRoot
//    private static $nsSavedLoadPath = array();

    public static $error;

    //--------------------------------------------------------------------------------
    // load : locate and retrieve the script
    //--------------------------------------------------------------------------------
    //@TODO: for http: protocol, check whether url_fopen is active, else use curl (if that is available)

    static function load($name, $path = "")
    {
        self::$error = "";

        // identify the chosen protocol

        $matches = array();
        $useProtocol = preg_match("|^([a-zA-Z]*):(.*)$|", $name, $matches);

        if ($useProtocol) {
            $protocol = $matches[1];
            $name = $matches[2];
        } else
            $protocol = "file";

        // handle the selected protocol

        switch ($protocol) {
            case "http" :
            case "https" :
                return self::loadFileContents($protocol . ":" . $name);
                break;

            case "ini" :
                return self::searchFile($name, $path, 'ini');
                break;

            case "file" :
                return self::searchFile($name, $path);
                break;

            default :
                self::$error = "One_Script_Factory error : unknown protocol '$protocol'";
                return false;

        }
    }

    //--------------------------------------------------------------------------------
    // searchFile : use the searchPath to locate the file and load its contents
    //--------------------------------------------------------------------------------

    static public function searchFile($filename, $path, $mode = 'nano')
    {
        // try the file as specified
        $path = $path . $filename;
//        print "<br>(trying specified $path)";
        if ($hash = One_Script_Cache::get($path)) return array(true, $hash, $path);
        if (file_exists($path)) {
            return self::loadFileContents($path, $mode);
        }

        // not found, walk the search sequence -- DEPRECATED
        /*
            $sequence = self::$nsLoadPath;

            if ($sequence) foreach ($sequence as $pathTemplate)  {
          $place = One_Locator::locateUsing($path,$pathTemplate);
          if ($hash = One_Script_Cache::inCache( $place )) return array( true, $hash, $place );
          return self::loadFileContents( $place, $mode );
        }
        */

        $place = One_Locator::locateUsing($path, self::currentSearchPattern());
        if ($place !== null) {
            if ($hash = One_Script_Cache::inCache($place)) return array(true, $hash, $place);
            return self::loadFileContents($place, $mode);
        }
        self::$error = "One_Script_Factory error : could not locate '$filename' in '$path'";
        return false;
    }

    static function createMap($descriptor)
    {
        if (!is_array($descriptor)) {
            if (One_Script_Config::$nsLanguagePackage) {
                $lang = One_Script_Package::call(One_Script_Config::$nsLanguagePackage, 'getLanguage', '');
                return array($descriptor . $lang . DIRECTORY_SEPARATOR, $descriptor);
            } else {
                return array($descriptor);
            }
        }

        if (One_Script_Config::$nsLanguagePackage) {
            $lang = One_Script_Package::call(One_Script_Config::$nsLanguagePackage, 'getLanguage', '');
            $descriptor[] = $lang;
        }

        $segments = array();
        $stub = '';
        foreach ($descriptor as $piece) {
            $stub .= $piece . DIRECTORY_SEPARATOR;
            $segments[] = $stub;
        }
        return array_reverse($segments);
    }

    // used by handleIni
    //TODO: refactor searchFile() with resolveFilePath()

    function resolveFilePath($filename)
    {
        $path = $filename;
        if (file_exists($path)) return $path;

        $sequence = self::$nsLoadPath;

        if ($sequence) foreach ($sequence as $pathTemplate) {
            $pathMap = self::createMap($pathTemplate);
            foreach ($pathMap as $pathSegment) {
                $path = self::createPath($pathSegment, $path, $filename);
                if (($hash = One_Script_Cache::inCache($path)) || file_exists($path))
                    return $path;
            }
        }

        return false;
    }

    //--------------------------------------------------------------------------------
    // createPath : form the correct path by replacing certain tags
    //	{path} is replaced by the path specified from load( filename, path )
    //--------------------------------------------------------------------------------

    function createPath($pathTemplate, $path, $filename)
    {
        // replace the tags by the correct values
        $s = str_replace("{path}", $path, $pathTemplate);

        return self::$nsLoadRoot . $s . $filename;
    }

//    static function addSearchPath($template)
//    {
//        self::$nsLoadPath[] = $template;
//    }

//    static function clearSearchPath()
//    {
//        self::$nsLoadPath = array();
//    }

    //--------------------------------------------------------------------------------
    // loadFileContents : load the contents of this file
    //--------------------------------------------------------------------------------

    static function loadFileContents($path, $mode = 'nano')
    {
//		echo "(loading $path in mode $mode)";
        $contents = "";

        switch ($mode) {
            case 'ini' :
                $contents = self::loadIni($path);
                if ($contents !== false) {
                    return array(false, $contents, $path);
                }

            case 'nano' :
            default:
                $contents = file_get_contents($path);
                if ($contents !== false) {
                    $contents .= "\n"; // TR20100407 Appending a newline on fetching the file contents takes care of THE nano-bug
                    return array(false, $contents, $path);
                } else {
                    die($path);
                    self::$error = "One_Script_Factory error : could not open '$path'";

                    return false;
                }
        }
    }

    /**
     * Permits loading INI format files as if they were sections
     *
     * @param $filename
     * @return unknown_type
     */
    private static function loadIni($filename)
    {
        $items = @parse_ini_file($filename);
//		echo 'loadIni zegt ', $items ? 'true':'false';
        if ($items === false) return false;
        if (count($items) == 0) return false;

        $s = '';
        foreach ($items as $k => $v) {
            $s .= '{section ' . $k . '}' . $v . '{endsection}
';
        }
        return $s;
    }

    //--------------------------------------------------------------------------------

    static function saveSearchPath()
    {
        self::$nsSavedLoadPath = self::$nsLoadPath;
    }

    //--------------------------------------------------------------------------------

    static function restoreSearchPath()
    {
        self::$nsLoadPath = self::$nsSavedLoadPath;
    }

    static public function getSearchPath()
    {
        return self::currentSearchPattern();
//        return self::$nsLoadPath;
    }

    static public function setSearchPath($pathArray)
    {
        self::pushSearchPath($pathArray);
//        self::$nsLoadPath = $pathArray;
    }


    public static function currentSearchPattern()
    {
        if (count(self::$searchPatterns)) {
//            echo '<br>Using search pattern /<b>' . self::$searchPatterns[0] . '</b>/';
            return self::$searchPatterns[0];
        }
        return '';
    }

    public static function pushSearchPath($pattern)
    {
        array_unshift(self::$searchPatterns, $pattern);
    }

    public static function popSearchPath()
    {
        array_shift(self::$searchPatterns);
    }
}
