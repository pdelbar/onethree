<?php

/**
 * Output is being stored in a One_Dom instantiation in the proper location
 * and is rendered when asked.
 * This DOM is specifically for Joomla!
 *
 * @TODO review this file and clean up historical code/comments
 * ONEDISCLAIMER
 **/
Class One_Dom_Joomla extends One_Dom // @TODO filter this class to custom
{
  /**
   * Renders the DOM or a section of the DOM
   *
   * @param $section The section to render, if $section equals NULL, all of the DOM will be rendered
   * @return string
   */
  function render($section = null) {
    $result = '';
    if ($section) {
      switch ($section) {
        case '_head':
          if (count($this->_data['_head']) > 0) {
            $document = JFactory::getDocument();
            foreach ($this->_data['_head'] as $headpart) {
              // if this is a <script src=""></script> part
              preg_match("/<script(.*)src=\"(.*)\"(.*)><\/script>/isU", $headpart, $matches);
              if (isset($matches[2])) {
                $document->addScript($matches[2]);
                continue;
              }

              // if this is a <script>var/function definitions</script> part
              unset($matches);
              preg_match("/<script(.*)>(.*)<\/script>/isU", $headpart, $matches);

              if (isset($matches[2])) {
                $document->addScriptDeclaration($matches[2]);
                continue;
              }

              // if this is a <link href="" rel="" ...></link> part
              unset($matches);
              preg_match("/<link(.*)href=\"(.*)\"(.*)\/>/isU", $headpart, $matches);

              if (isset($matches[2])) {
                $href = $matches[2];

                unset($matches);
                preg_match("/<link(.*)type=\"(.*)\"(.*)\/>/isU", $headpart, $matches);
                if ($matches[2]) $type = $matches[2];

                unset($matches);
                preg_match("/<link(.*)rel=\"(.*)\"(.*)\/>/isU", $headpart, $matches);
                if (isset($matches[2])) $rel = $matches[2];

                $document->addStyleSheet($href);
                continue;
              }
            }
          }
          break;
        case '_onload':
          if (count($this->_data['_onload']) > 0) {
            $document = JFactory::getDocument();
            $myFunction = '
						function doOneContentOnloadActions() {
						';

            foreach ($this->_data['_onload'] as $onloadpart) {
              $myFunction .= $onloadpart;
            }

            $myFunction .= '
						}

						if(typeof(jQuery) !== "undefined") {
							jQuery(document).ready(function(){
								doOneContentOnloadActions()
							});
						}
						else{
							window.addEvent("domready", function(){
								doOneContentOnloadActions()
							});
						}';

            $document->addScriptDeclaration($myFunction);
          }
          break;
        default:
          $result = parent::render($section);
          break;
      }
    } else {
      foreach ($this->_data as $key => $section)
        $result .= $this->render($key);
    }

    return $result;
  }
}
