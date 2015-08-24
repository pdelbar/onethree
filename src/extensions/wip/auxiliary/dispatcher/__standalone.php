<?php
class One_Dispatcher_Standalone extends One_Dispatcher
{
	protected $_root = 'index.php';

	public function __construct(array $options = array())
	{
		if(isset($options['one_script_root']))
		{
			$this->_root = $options['one_script_root'];
			unset($options['one_script_root']);
		}

		// precedence:
		// POST > GET > constructor options > defaults > parent defaults

		$defaults = array(
			'id' => null,
			'schemeName' => null,
			'task' => null,
			'view' => null,
			'parseContentPlugins' => false,
		);

		// @deprecated params should never come in through $_POST
		// should be replaced by:
		// $request = JRequest::get('get');
		$request = array_merge($_GET, $_POST);

		// convert order to one format (foo+ or foo-)
		if(array_key_exists('order', $options)) {
			$options['order'] = $options['order'] . ($options['orderdirection']=='asc' ? '+' : '-');
		}

		// merge everything
		$options = array_merge($defaults, $options, $request);

		parent::__construct( $options );
	}

	public function dispatch()
	{
		try
		{
			$scheme           = $this->_scheme;
			$this->controller = One_Repository::getController($scheme, $this->_options);
			$content          = $this->controller->execute($this->_task, $this->_options);

			if(is_null($this->controller->getRedirect()))
			{
				echo $content;
			}
			else
			{
				$this->setRedirect($this->controller->getRedirect());
				$this->redirect();
			}
		}
		catch(Exception $e)
		{
			if(One_Config::get('debug.exitOnError') === false) {
				echo $e->getMessage();
			}
			else {
				throw new Exception($e);
			}
		}
	}

	public function setRedirect(array $options)
	{
		if( is_null( $options ) )
			$this->_redirect = NULL;
		else if( count( $options ) == 0 || !is_array( $options ) )
			$this->_redirect = JURI::base();
		else
		{
			if( isset( $options[ 'rawURL' ] ) )
				$this->_redirect = $options[ 'rawURL' ];
			else
			{
				$gotoOptions = array();
				$gotoOptions[] = ( isset( $options[ 'task' ] ) && trim( $options[ 'task' ] ) != '' ) ?  'task=' . $options[ 'task' ] : 'task=list';

				if( isset( $options[ 'scheme' ] ) && trim( $options[ 'scheme' ] ) != '' )
					$gotoOptions[] = 'scheme=' . $options[ 'scheme' ];
				else
					throw new One_Exception( 'Redirect must contain a scheme' );

				unset( $options[ 'task' ] );
				unset( $options[ 'scheme' ] );

				foreach( $options as $option => $value )
				{
					$gotoOptions[] = $option . '=' . $value;
				}

				$this->_redirect = $this->_root.'?'.implode('&', $gotoOptions);
			}
		}
	}

	public function redirect()
	{
		if( !is_null( $this->_redirect ) )
		{
			if( headers_sent() )
				echo '<script> document.location = "' . $this->_redirect . '";</script>';
			else
				header( 'Location: ' . $this->_redirect );
			exit;
		}
	}
}

// needed for PHP-versions prior to 5.3.0
if(!function_exists('parse_ini_string'))
{
  function parse_ini_string($ini, $process_sections = false, $scanner_mode = null)
  {
    # Generate a temporary file.
    $tempname = tempnam('/tmp', 'ini');
    $fp = fopen($tempname, 'w');
    fwrite($fp, $ini);
    $ini = parse_ini_file($tempname, !empty($process_sections));
    fclose($fp);
    @unlink($tempname);
    return $ini;
  }
}