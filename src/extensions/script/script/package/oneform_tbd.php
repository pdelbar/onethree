<?php
//------------------------------------------------------------------
// package oneform : functions to create OneForms
//------------------------------------------------------------------
class oneScriptPackageOneform extends One_Script_Package
{
	public function get($formFile, One_Model $model, array $options = array())
	{
		$cx       = new One_Context();
		$formName = 'oneForm';
		$action   = '';
		$task     = 'edit';
		$scheme   = $model->getScheme();

		if(isset($options[ 'action' ])) {
			$action = $options[ 'action' ];
		}
		if(isset($options[ 'formName' ])) {
			$formName = $options[ 'formName' ];
		}

		if(is_null($model->task) || trim($model->task) == '')
		{
			if(!is_null($cx->get('task'))) {
				$task = $cx->get('task');
			}
			if(isset($options[ 'task' ])) {
				$task = $options[ 'task' ];
			}
		}
		else {
			$task = $model->task;
		}

		$form = One_Form_Factory::createForm($scheme, $formFile, NULL, $formName, $action);

		return $form;
	}

	public function render(One_Form_Container_Form $form, $model)
	{
		$dom = One_Repository::getDom();

		try {
			$form->render($model, $dom);
		}
		catch(Exception $e) {
			return $e->getMessage();
		}
		return $dom->render();
	}

	public function renderStart(One_Form_Container_Form $form, $model)
	{
		$dom = One_Repository::getDom();
		$form->renderStart($model, $dom);

		return $dom->render();
	}

	public function renderBody(One_Form_Container_Form $form, $model)
	{
		$dom = One_Repository::getDom();
		$form->renderBody($model, $dom);
		return $dom->render();
	}

	public function renderEnd(One_Form_Container_Form $form, $model)
	{
		$dom = One_Repository::getDom();
		$form->renderEnd($model, $dom);

		return $dom->render();
	}

	public function renderPart(One_Form_Container_Form $form, $part, $model = NULL)
	{
		$toRender = self::getPart($form, $part);
		if(is_null($toRender)) {
			return '';
		}
		else
		{
			$dom = One_Repository::getDom();
			$toRender->render($model, $dom);

			return $dom->render();
		}
	}

	public function getPart(One_Form_Container_Form $form, $part)
	{
		$part = $form->findPart($part);
		if(is_null($part)) {
			return null;
		}
		else
		{
			return $part;
		}
	}

	/**
	 * Mangle the input given, take out all special characters, lowercasing, ...
	 * @param string $input
	 * @return string
	 */
	public static function mangle($input)
	{
		return One_Form_Helper::mangle($input);
	}
}
