<?php

/**
 * One class to call them
 * Will gradually absorb One_Repository, turning that class into a subclass of One
 *
 * ONEDISCLAIMER
 **/
class One extends One_Repository
{

    public static function make($schemeName)
    {
        $scheme = parent::getScheme($schemeName);
        $object = null;
        $behaviors = $scheme->get('behaviors');
        if ($behaviors) foreach ($behaviors as $behavior) {
            $object = $behavior->onCreateModel($scheme);
            if ($object)
                return $object;
        }
        $object = new One_Model($scheme);
        return $object;
    }

    /**
     * Return a One_Model of a certain scheme
     *
     * @param string $schemeName
     * @return One_Model
     */
    public static function getInstance($schemeName)
    {
        return self::make($schemeName);
    }

    public static function meta()
    {
        $route = implode('/', func_get_args());
        $parts = explode('/', $route, 2);
        if (count($parts) == 2)
            list($stub, $subroute) = explode('/', $route, 2);
        else
            list($stub, $subroute) = array($route, null);
        $methodName = 'meta' . ucFirst($stub);
        if (method_exists('One', $methodName)) return One::$methodName($subroute);
        throw new Exception('One cannot get ', $route);
    }

    // SCHEMES

    /**
     * Respond to get('schemes')
     * Respond to get('schemes/SCHEMENAME')
     *
     * @param $route
     * @return mixed
     * @throws Exception
     */
    public static function metaSchemes($route)
    {
        switch ($route) {
            case '' :
                return parent::getSchemeNames();
            default:
                return parent::getScheme($route);
        }
        throw new Exception('One cannot get schemes/', $route);
    }

}
