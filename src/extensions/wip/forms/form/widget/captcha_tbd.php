<?php
/**
 * Handles the captcha widget
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
class One_Form_Widget_Captcha extends One_Form_Widget_Abstract
{
	public function __construct()
	{
		parent::__construct('captcha');
		$this->_type = 'captcha';
		$this->_id    = 'captcha';
		$this->_name  = 'captcha';
		$this->_label  = 'CAPTCHA';
	}

	protected static function allowedOptions()
	{
		$additional = array(
								'explTxt'          => 2,
								'font_size'        => 3,
					            'font_path'        => 3,
					            'font_file'        => 3,
					            'text_color'       => 3,
					            'lines_color'      => 3,
					            'background_color' => 3,
								'width'            => 3,
						        'height'           => 3,
						        'output'           => 3
							);
		return array_merge(parent::allowedOptions(), $additional);
	}

	protected function _render( $model, One_Dom $d )
	{
		$output = '';
		$captcha = $this->generateCaptcha();
		$allowed = $this->allowedOptions();
		$info    = $this->getCfg('info');
		$error   = $this->getCfg('error');

		$dom = One_Repository::getDom();

		$config = array();
		foreach($this->getParameters() as $param => $value)
		{
			if($allowed[$param] & 1)
				$config[$param] = $value;
		}

		$input = new One_Form_Widget_Scalar_Textfield('captcha', 'captcha', NULL, NULL, $config);

		$dom->add( '<div class="OneCaptcha">' );

		$dom->add('<img src="'.$captcha.'" alt="captcha" />');
		$input->render($model, $dom);

		if(is_null($info))
			//$output .= '<span id="' . $id . 'Info" class="OneInfo">' . $info . '</span>';
			$dom->add('<span id="' . $id . 'Info" class="OneInfo">' . $info . '</span>');

		if(is_null($error))
			//$output .= '<span id="' . $id . 'Error" class="OneError">' . $error . '</span>';
			$dom->add('<span id="' . $id . 'Error" class="OneError">' . $error . '</span>');

		$dom->add( '</div>' );

		//return $output;
		$d->addDom($dom);
	}

	public function bind()
	{
		return true;
	}

	private function generateCaptcha()
	{
		require_once(ONE_LIB_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'captcha.php');

		// Generate captcha
		$captcha = Text_CAPTCHA::factory('Image');
		$imageOptions = array(
	            'font_size'        => (!is_null($this->getCfg('font_size'))) ? $this->getCfg('font_size') : 20,
	            'font_path'        => (!is_null($this->getCfg('font_path'))) ? $this->getCfg('font_path') : ONE_LIB_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'Image',
	            'font_file'        => (!is_null($this->getCfg('font_file'))) ? $this->getCfg('font_file') : 'arial.ttf',
	            'text_color'       => (!is_null($this->getCfg('text_color'))) ? $this->getCfg('text_color') : '#66720F',
	            'lines_color'      => (!is_null($this->getCfg('lines_color'))) ? $this->getCfg('lines_color') : '#66720F',
	            'background_color' => (!is_null($this->getCfg('background_color'))) ? $this->getCfg('background_color') : '#B9C84C'
	            );

		// Set CAPTCHA options
		$options = array(
				        'width' => (!is_null($this->getCfg('width'))) ? $this->getCfg('width') : 150,
				        'height' => (!is_null($this->getCfg('height'))) ? $this->getCfg('height') : 50,
				        'output' => (!is_null($this->getCfg('output'))) ? $this->getCfg('output') : 'png',
				        'imageOptions' => $imageOptions
	            		);

		// Generate a new Text_CAPTCHA object, Image driver
		$c = Text_CAPTCHA::factory('Image');
		$retval = $c->init($options);
		if (PEAR::isError($retval)) {
			printf('Error initializing CAPTCHA: %s!',
			$retval->getMessage());
			exit;
		}

		// Get CAPTCHA secret passphrase
		$session = One_Repository::getSession();
		$session->set( 'OnePhrase', $c->getPhrase(), 'OneCaptcha' );

		// Get CAPTCHA image (as PNG)
		$png = $c->getCAPTCHA();
		if (PEAR::isError($png)) {
			printf('Error generating CAPTCHA: %s!',
			$png->getMessage());
			exit;
		}

		file_put_contents("tmp/".md5(session_id()) . '.png', $png);

		$captcha = 'tmp/'.md5(session_id()) . '.png?' . time();

		return $captcha;
	}

	public function validate()
	{
		$session = One_Repository::getSession();
		if( $session->get( 'OnePhrase', 'OneCaptcha' ) != $this->requestValue() )
		{
			return false;
		}
		else
			return true;
	}

	public function __toString()
	{
		return get_class() . ': ' . $this->getID();
	}
}
