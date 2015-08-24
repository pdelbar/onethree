<?php
// http://www.trirand.com/jqgridwiki/doku.php?id=wiki:how_to_install

class oneScriptPackageJqgrid extends One_Script_Package
{
	public static function grid( $scheme, $model )
	{

		if(!$scheme instanceof One_Scheme){
			$scheme = One_Repository::getScheme($scheme);
		}

		$view = new One_View( $schemeName, 'jqgrid' );
		$view->setModel( $model );
		$view->setAll(  array( 'scheme' => $scheme ));

		$content = $view->show();
		return $content;
	}

	public static function init( $schemeName, $iniFile = "jqgrid"  )
	{
		One_Vendor::getInstance()->loadScript('http://code.jquery.com/jquery-latest.js', 'head', -9999);
		One_Vendor::getInstance()->loadScript('jquery/js/jqextra.js', 'head', -9998);
		One_Vendor::getInstance()->loadScript('http://code.jquery.com/ui/1.8.16/jquery-ui.js', 'head', -8888);
		One_Vendor::getInstance()->loadScriptDeclaration('jQuery.noConflict();', 'head', -9999);
		One_Vendor::getInstance()->loadStyle('http://code.jquery.com/ui/1.8.16/themes/ui-lightness/jquery-ui.css', 'head', 1);
		One_Vendor::getInstance()->loadScript('jquery/js/jquery.jqGrid.min.js', 'head', 2);
		One_Vendor::getInstance()->loadStyle('jquery/css/ui.jqgrid.css', 'head', 2);
		One_Vendor::getInstance()->loadScript('jquery/js/i18n/grid.locale-'.strtolower(substr(One_Config::get('app.language'), 0, 2)).'.js', 'head', 1);

		$ini = One_Config::getInstance()->getCustomPath().'/views/'.One_Config::get('app.name').'/'.$schemeName.'/'.$iniFile.'.ini';
		$iniLang = One_Config::getInstance()->getCustomPath().'/views/'.One_Config::get('app.name').'/'.$schemeName.'/language/'.strtolower(One_Config::get('app.language')).'/'.$iniFile.'.ini';
		if(file_exists($iniLang)) {
			$ini = $iniLang;
		}

		if(file_exists($ini)) {
			$formula = parse_ini_file( $ini, true);
		}
		else
		{
			$scheme = One_Repository::getScheme($schemeName);
			$formula = array();
			$idAtt = $scheme->getIdentityAttribute();
			$atts = $scheme->get('attributes');
			foreach($atts as $att)
			{
				$formula[$att->getName()] = array('header' => $att->getName());
				if($att->getName() == $idAtt->getName()) {
					$formula[$att->getName()]['formatter'] = 'detailFormatter';
				}
			}
		}

		$params = NULL;
		if(isset($formula['_params']))
		{
			$params = $formula['_params'];
			unset($formula['_params']);
		}

		$titles = array();
		$cols = array();
		foreach($formula as $section => $content)
		{
			$col = array();
			$col['name'] = $section;
			foreach($content as $at => $val)
			{
				if($val == '_true') {
					$val = 'true';
				}
				if($val == '_false') {
					$val ='false';
				}
				$parts = explode('.',$at);
				$at    = $parts[0];
				$sub   = isset($parts[1]) ? $parts[1] : NULL;
				switch($at)
				{
					case 'header' :
						$titles[] = $val;
						break;

					case 'editoptionsview' :
						// create value like
						//		editoptions: {value:"FE:FedEx;IN:InTime;TN:TNT;AR:ARAMEX"}
						list($scheme,$view) = explode(':',$val);
						$ob = One::make($scheme);
						$options = trim(oneScriptPackageOne::view($ob,$view));
						$col['editoptions'] = array( 'value' => $options );
						break;
					default :
						if ($sub) {
							$col[ $at ][ $sub] = $val;
						} else {
							$col[ $at ] = $val;
						}
						break;
				}
			}
			$cols[] = $col;
		}

		return array( $titles, $cols,$params );
	}

	public static function rowFormula( One_Scheme $scheme )
	{
		list( $titles, $cols, $params) = self::init( $scheme->getName() );
		$formula = array();
		foreach ($cols as $col) {
			$formula[ $col['name'] ] = isset($col[ 'view' ]) ? $col[ 'view' ] : NULL;
		}
		return $formula;
	}


	public static function createRow( $model, $formula )
	{
		$row = array();
		foreach ($formula as $name => $view) {
			if ($view) {
				$row[] = trim(oneScriptPackageOne::view($model,$view));
			} else {
				$row[] = $model->$name;
			}
		}
		return $row;
	}

	public static function encodeCols(array $cols = array())
	{
		foreach($cols as $key => $col) {
			$cols[$key] = self::myjson($col);
		}

		$return =  '['.implode(', ', $cols).']';
		return $return;
	}

	public function myjson( $kv )
	{
		$first = 1;
		$s = '{';
		foreach ($kv as $k => $v)
		{
			if(!$first) {
				$s .= ' , ';
			}

			$s .= $k.' : ';
			if($v == 'true') {
				$s .= 'true';
			}
			else if($v == 'false') {
				$s .= 'false';
			}
			else if(is_array($v)) {
				$s .= self::myjson($v);
			}
			else if(strpos($v, '[') !== false && strpos($v, ']') !== false) {
				$s .= str_replace(array('[', ']'), array('{', '}'), $v);
			}
			else {
				$s .= '"'. $v. '"';
			}
			$first = 0;
		}
		$s .='}';

		return $s;
	}

}
