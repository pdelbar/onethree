<?php

// required if we do not use composer
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

/**
 * Class One_Controller_Rest
 */
class One_Controller_Rest extends One_Controller {

  /**
   * @var
   */

  /**
   * @var string
   *
   * The part of the route used by Joomla to get to the correct menu item, which therefore needs to be removed from
   * the route passed into the Slim app
   */
  protected $routeStem;

  /**
   * @var Slim\Slim
   *
   * the internal Slim instance
   */
  protected $slim;

  public function __construct($options = array()) {
    parent::__construct($options);

    // create and prepare a SLIM instance
    $this->slim = new \Slim\Slim($this->_defaultSettings());
    $this->slim->response->headers->set('Content-Type', 'application/json');
  }

  /**
   * @return array
   *
   * Default settings for SLIM instance
   */
  protected function _defaultSettings() {
    return array(
      'debug'       => true,
//      'templates.path' => '../templates',
//      'log.writer' => new \My\LogWriter(),
//      'log.level'   => \Slim\Log::DEBUG,
//      'log.enabled' => true,
//      'view'        => 'One_MyView',
    );
  }

  /**
   * Create routes for all schemes based on behavior
   */
  protected function _setupRoutes() {
    // default route for /
    $this->slim->get('/', array('One_Controller_Rest', 'defaultRouteHandler'));
    foreach (One_Repository::getSchemeNames() as $schemeName) {
      $scheme = One_Repository::getScheme($schemeName);
      if ($scheme->hasBehavior('restable')) {
        One_Behavior_Restable::slimSetup($this->slim,$scheme);
      }
    }

  }

  public static function defaultRouteHandler() {
    echo "one|content REST handler";
  }

  /**
   * @param One_Scheme $scheme
   *
   * GET schemename
   * Select all items
   *
   * Optional parameters: order, limit, start
   */
  public static function restGetAll(One_Scheme $scheme) {
    try {
      $q = One_Repository::selectQuery($scheme->getName());
      $model = $q->execute();
      $response = array();
      foreach ($model as $object) {
        $response[] = $object->asRestResponse();
      }
      echo self::toPrettyJson($response);
    } catch (Exception $e) {
        $this->slim->response()->status(400);
        $this->slim->response()->header('X-Status-Reason', $e->getMessage());
      print_r($e);
    }
  }

  /**
   * @param One_Scheme $scheme
   * @param $idOrAlias
   *
   * GET schemename/ID
   * Select individual item
   *
   */
  public static function restGet(One_Scheme $scheme, $idOrAlias) {
    try {
      $model = One_Repository::selectOne($scheme->getName(), $idOrAlias);
      if ($model === null) throw new One_Exception_Rest_404( 'Cannot locate instance of scheme ' . $scheme->getName() . ' identified by ' . $idOrAlias);
      echo self::toPrettyJson($model->asRestResponse());
    } catch (One_Exception_Rest_404 $e) {
      // return 404 server error
      $this->slim->response()->status(404);
      $this->slim->response()->header('X-Status-Reason', $e->getMessage());
      echo '{}';
    } catch (Exception $e) {
      $this->slim->response()->status(400);
      $this->slim->response()->header('X-Status-Reason', $e->getMessage());
    }
  }

  /**
   * @param One_Scheme $scheme
   *
   * POST schemename
   * Create a new instance
   */
  public static function restPost($scheme) {
    try {
      // retrieve input data from body (a JSON encoded structure)
      $request = $this->slim->request();
      $body = $request->getBody();
      $input = json_decode($body,true);

      // instantiate and fill the model
      $model = One::make($scheme->getName());
      $model->fromArray($input);
      $model->insert();

      echo self::toPrettyJson($model->asRestResponse());
    } catch (Exception $e) {
      $this->slim->response()->status(400);
      $this->slim->response()->header('X-Status-Reason', $e->getMessage());
    }
  }

  /**
   * @param One_Scheme $scheme
   *
   * PUT schemename/ID
   * Update an instance
   */
  public static function restPut( One_Scheme $scheme, $idOrAlias) {
    try {
      // retrieve input data from body (a JSON encoded structure)
      $request = $this->slim->request();
      $body = $request->getBody();
      $input = json_decode($body);

      $model = One_Repository::selectOne($scheme->getName(), $idOrAlias);
      if ($model === null) throw new One_Exception_Rest_404( 'Cannot locate instance of scheme ' . $scheme->getName() . ' identified by ' . $idOrAlias);

      foreach ($input as $k => $v) $model->$k = $v;
      $model->update();

      echo self::toPrettyJson($model->asRestResponse());
    } catch (One_Exception_Rest_404 $e) {
      // return 404 server error
      $this->slim->response()->status(404);
      echo '{}';
    } catch (Exception $e) {
      $this->slim->response()->status(400);
      $this->slim->response()->header('X-Status-Reason', $e->getMessage());
    }
  }

  /**
   * @param One_Scheme $scheme
   * @param $idOrAlias
   *
   * DELETE schemename/ID
   * Delete an item
   */
  public static function restDelete(One_Scheme $scheme, $idOrAlias) {
    try {
      $model = One_Repository::selectOne($scheme->getName(), $idOrAlias);
      if ($model === null) throw new One_Exception_Rest_404( 'Cannot locate instance of scheme ' . $scheme->getName() . ' identified by ' . $idOrAlias);
      $model->delete();
      $this->slim->response()->status(200);
      echo 'OK';
    } catch (One_Exception_Rest_404 $e) {
      // return 404 server error
      $this->slim->response()->status(404);
      echo '{}';
    } catch (Exception $e) {
      $this->slim->response()->status(400);
      $this->slim->response()->header('X-Status-Reason', $e->getMessage());
    }
  }


  // TODO: turn this into a view of some kind instead of a function (use $this->slim->render())
  protected static function toPrettyJson($object, $indent = 0) {
    $head = str_repeat("  ", $indent);
    $headPlus = $head . "  ";
    if (is_a($object, 'One_Model')) {
      $output = array();
      foreach ($object->toArray() as $key => $val) {
        $output[] = $headPlus . '"' . $key . '" : ' . self::toPrettyJson($val, $indent + 1);
      }
      return $head . "{\n"
      . implode(",\n", $output) . "\n"
      . $head . '}';
    } else if (is_object($object)) {
      $output = array();
      foreach (get_object_vars($object) as $key) {
        $output[] = $headPlus . '"' . $key . '" : ' . self::toPrettyJson($object->$key, $indent + 1);
      }
      return $head . "{\n"
      . implode(",\n", $output) . "\n"
      . $head . '}';
    } else if (is_array($object)) {
      $output = array();
      foreach ($object as $item) $output[] = $head . self::toPrettyJson($item, $indent + 1);
      return $head . "[\n"
      . implode(",\n", $output) . "\n"
      . $head . ']';
    } else
      return '"' . str_replace('"','\"',$object) . '"';
  }

  public function run($root) {
    $this->routeStem = $root;

    // hook to correct the route, stripping off the Joomla route part
    $app = $this->slim;
    $app->hook('slim.before', function () use ($app) {
      $app->environment['PATH_INFO'] = preg_replace('|/' . $this->routeStem . '|', '', $app->environment['PATH_INFO']);
    });
    $this->slim = $app;

    $this->_setupRoutes();

//    echo '(bot)';
    $this->slim->run();
//    echo '(eot)';
    exit;
  }
}