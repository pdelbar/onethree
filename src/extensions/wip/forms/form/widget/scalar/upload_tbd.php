<?php
/**
 * Handles the upload widget
 * Warning! To use this widget, the scheme must always(!) have the following attributes (but preferably not visible in the forms:
 * secret, filepath, filename, mimetype
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Widget_Scalar_Upload extends One_Form_Widget_Scalar
{
	/**
	 * Class constructor
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $label
	 * @param array $config
	 */
	public function __construct( $id = NULL, $name = '', $label = NULL, $config = array() )
	{
		parent::__construct($id, $name, $label, $config );
		$this->_type = 'upload';
	}

	/**
	 * Return the allowed options for this widget
	 *
	 * @return array
	 */
	protected static function allowedOptions()
	{
		$additional = array(
							'dir' => 1,
							'lang' => 1,
							'xml:lang' => 1,
							'disabled' => 1,
							'maxlength' => 1,
							'readonly' => 1,
							'size' => 1,
							'path' => 4,
							'securename' => 4,
							'simple' => 4,
							'allowed' => 4
							);
		return array_merge( parent::allowedOptions(), $additional );
	}

	/**
	 * Render the output of the widget and add it to the DOM
	 *
	 * @param One_Model $model
	 * @param One_Dom $d
	 */
	protected function _render( $model, One_Dom $d )
	{
		$this->setCfg('class', 'OneFieldInput ' . $this->getCfg('class'));

		$value = (is_null($this->getValue($model)) ? $this->getDefault() : $this->getValue($model));

		$data = array(
						'id' => $this->getID(),
						'name' => $this->getFormName(),
						'value' => $value,
						'info' => $this->getCfg('info'),
						'error' => $this->getCfg('error'),
						'class' => $this->getCfg('class'),
						'required' => (($this->isRequired()) ? ' *' : ''),
						'label' => $this->getLabel(),
						'lblLast' => $this->getCfg('lblLast'),
						'oneUrl' => One_Config::getInstance()->getUrl(),
						'vendorUrl' => One_Vendor::getInstance()->getSitePath(),
						'path' => (!is_null($this->getCfg('path')) ? $this->getCfg('path') : One_Config::getInstance()->getSiterootUrl()),
						'isSimple' => (!is_null($this->getCfg('simple')) ? 'yes' : 'no')
					);

		if('' != trim($value)) {
			$salt     = 'DR$8efatrA4reb66fr+ch5$Ucujach3phe9U@AqutR8hajuq47a6&5tucHu58aSt';
			$encPath  = base64_encode($this->getCfg('path').'/'.$value);
			$forCheck = strlen($encPath);
			$check    = md5($forCheck.$encPath.$salt);
			$encLabel = base64_encode($this->getLabel());

			preg_match('/\.([a-z0-9]+)$/i', $value, $matches);
			$extension = $matches[1];

			$data['encPath']   = $encPath;
			$data['encLabel']  = $encLabel;
			$data['check']     = $check;
			$data['extension'] = $extension;
		}

		$dom = $this->parse( $model, $data );

		$d->addDom( $dom );
	}

	/**
	 * Overrides PHP's native __toString function
	 *
	 * @return string
	 */
	public function __toString()
	{
		return get_class() . ': ' . $this->getID();
	}

	/**
	 * Has the file been uploaded correctly or if was there no file uploaded?
	 *
	 * @return boolean
	 */
	public function validate()
	{
		$attributeName = $this->_name;

		if($_FILES['oneForm']['error'][$attributeName] == 4) {
			return true;
		}

		$path = $this->getCfg('path');
		if(!is_dir($path))
		{
			$this->_constraint->addError('{script}{@ formerrors:INVALID_PATH}{endscript}');
			return false;
		}

		if($_FILES['oneForm']['error'][$attributeName] == 0)
		{
			if(!is_null($this->getCfg('allowed')))
			{
				if(0 < preg_match('/\.([a-z]+)$/i', $_FILES['oneForm']['name'][$attributeName], $matches))
				{
					if(!in_array($matches[1], explode(';', trim($this->getCfg('allowed'))))) {
						$this->_constraint->addError('{script}{@ formerrors:INVALID_EXTENSION}{endscript}');
						return false;
					}
				}
				else {
					$this->_constraint->addError('{script}{@ formerrors:INVALID_EXTENSION}{endscript}');
					return false;
				}
			}

			return true;
		}
		else {
			$this->_constraint->addError('{script}{@ formerrors:UPLOAD_FAILED}{endscript}');
			return false;
		}
	}

	/**
	 * Bind the model to the widget
	 *
	 * @param One_Model $model
	 */
	public function bindModel( $model )
	{
		$attributeName = $this->_name;

		if(!is_null($this->getCfg('simple')))
		{
			$path = $this->getCfg('path');

			if(!is_dir($path)) {
				return;
			}

			if(isset($_POST['oneForm'][$attributeName . 'RemoveFile']) && $model->$attributeName != '')
			{
				@unlink($path . DIRECTORY_SEPARATOR . $model->$attributeName);
				$model->$attributeName = '';
			}

			if($_FILES['oneForm']['error'][$attributeName] == 4) {
				return;
			}

			if(!is_uploaded_file($_FILES['oneForm']['tmp_name'][$attributeName])) {
				throw new One_Exception('File could not be uploaded');
			}

			$name = $_FILES['oneForm']['name'][$attributeName];
			if(!is_null($this->getCfg('securename'))) {
				if(0 >= preg_match('/\.([a-z]+)$/i', $_FILES['oneForm']['name'][$attributeName], $matches)) {
					throw new One_Exception('Invalid extension');
				}

				$name = md5(microtime(true).$_FILES['oneForm']['name'][$attributeName]).'.'.$matches[1];
			}

			if(!move_uploaded_file($_FILES['oneForm']['tmp_name'][$attributeName], $path.DIRECTORY_SEPARATOR.$name)) {
				throw new One_Exception('File could not be moved');
			}

			$model->$attributeName = $name;
		}
		else
		{
			$path = $this->getCfg('path');
			if(!is_dir($path))
				return;

			if(isset($_POST['oneForm'][$attributeName . 'RemoveFile']) && $model->filepath != '' && $model->secret != '')
			{
				@unlink($model->filepath . DIRECTORY_SEPARATOR . $model->secret);
				$model->secret   = $secret;
				$model->filepath = '';
				$model->filename = '';
				$model->mimetype = '';
			}

			if($_FILES['oneForm']['error'][$attributeName] == 4) {
				return;
			}

			if($model->filepath != '' && $model->secret != '') {
				@unlink($model->filepath . DIRECTORY_SEPARATOR . $model->secret);
			}

			$secret = md5(microtime(true).$_FILES['oneForm']['name'][$attributeName]);

			if(!is_uploaded_file($_FILES['oneForm']['tmp_name'][$attributeName]) ||
				!move_uploaded_file($_FILES['oneForm']['tmp_name'][$attributeName], $path . DIRECTORY_SEPARATOR . $secret))
			{
				throw new One_Exception('File could not be uploaded');
			}

			$model->secret   = $secret;
			$model->filepath = $path;
			$model->filename = $_FILES['oneForm']['name'][$attributeName];
			$model->mimetype = $_FILES['oneForm']['type'][$attributeName];
		}
	}
}
