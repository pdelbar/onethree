<?php
/**
 * This class performs the cancel task from an edit-form
 *


  * @TODO review this file and clean up historical code/comments
 **/
class One_Action_EditCancel extends One_Action_Edit
{
	/**
	 * This function performs the cancellation and returns to the proper page
	 */
	public function execute()
	{
		if(!is_null($this->getVariable('cancelToOne')))
		{
			parse_str(base64_decode($this->getVariable('cancelToOne')), $returnVals);
			$this->controller->setRedirect($returnVals);
		}
		else {
			$formFile = $this->getVariable('formFile', 'form');
			$form     = One_Form_Factory::createForm($this->scheme, $formFile, $this->getVariable('lang'), 'oneForm', '');
			$flow = One_Controller_Flow::getInstance($this->scheme)->getRedirects();
			$redirects = array_merge($flow, $form->getRedirects());
			
			$todo = 'cancel';
			
			if(isset($this->options['flow']))
				$todo = $this->options['flow'];
			
			$redirect = $redirects['default'];
			
			if(isset($redirects[$todo])) {
				$redirect = $redirects[$todo];
			}

			if(isset($redirect['id']) && strtoupper(trim($redirect['id'])) == '::ID::') {
				$redirect['id'] = $this->id;
			}

			$redirect = $this->replaceOtherVariables($redirect);

			$this->controller->setRedirect($redirect);
		}
	}
}
