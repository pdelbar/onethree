<?php
//------------------------------------------------------------------
// package jone : joomla-specific one functions
//------------------------------------------------------------------

	class oneScriptPackageJone extends One_Script_Package
	{
		public static function editurl( $model, $options=array() )
		{
			return oneScriptPackageJone::url( 'edit', $model, $options );
		}

		public static function listurl( $model, $options=array() )
		{
			if (is_string($model)) $model = OneRepository::getInstance($model);
			return oneScriptPackageJone::url( 'list', $model, $options );
		}

		public static function detailurl( $model, $options=array() )
		{
			return oneScriptPackageJone::url( 'detail', $model, $options );
		}

		public static function removeurl( $model, $options=array() )
		{
			return oneScriptPackageJone::url( 'remove', $model, $options );
		}

		public static function url( $task, $model, $options=array() )
		{
			$scheme = $model->getScheme();
			$idAttr = $scheme->getIdentityAttribute()->getName();
			$id = $model->$idAttr;
			$params = array(
				'option' => 'com_one',
				'scheme' => $scheme->getName(),
				'id' => $id,
				'task' => $task,
				'view' => $task,
			);
			$params = array_merge( $params, $options );
//			$params = self::setItemId( $task, $model, $params );

			if (!isset($params['Itemid'])) $params['Itemid'] = JRequest::getInt( 'Itemid' );
			return JRoute::_( 'index.php?' . http_build_query($params));
		}

		public static function 	setItemId( $task, $model, $params )
		{
			if ($params['scheme'] == 'account') $params['Itemid'] = 2;
			if ($params['scheme'] == 'deal') $params['Itemid'] = 5;
			if ($params['scheme'] == 'contact') $params['Itemid'] = 6;
			return $params;
		}

	}

