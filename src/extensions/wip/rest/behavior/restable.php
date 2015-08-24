<?php

/**
 * Adds REST behavior to the scheme
 *
 * ONEDISCLAIMER
 **/
class One_Behavior_Restable extends One_Behavior {
  /**
   * Return the name of the behaviour
   *
   * @return string
   */
  public function getName() {
    return 'restable';
  }

  public static function slimSetup(\Slim\Slim &$slim,One_Scheme $scheme) {
    //TODO: read specs from behaviour options or from a file
    $opt = $scheme->get('behaviorOptions.restable' );
    $route = $opt['route'];

    // retrieve
    $slim->get("/$route", function () use ($scheme) {
      One_Controller_Rest::restGetAll($scheme);
    });
// retrieve one
    $slim->get("/$route/:idOrAlias", function ($idOrAlias) use ($scheme) {
      One_Controller_Rest::restGet($scheme, $idOrAlias);
    });
// create new
    $slim->post("/$route", function () use ($scheme) {
      One_Controller_Rest::restPost($scheme);
    });
// update existing
    $slim->put("/$route/:idOrAlias", function ($idOrAlias) use ($scheme) {
      One_Controller_Rest::restPut($scheme, $idOrAlias);
    });
// delete existing
    $slim->delete("/$route/:idOrAlias", function ($idOrAlias) use ($scheme) {
      One_Controller_Rest::restDelete($scheme, $idOrAlias);
    });
  }

  public function onModelAsRestResponse($model) {
    return $model;
  }
}
