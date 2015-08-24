<?php
//-------------------------------------------------------------------------------------------------
// 	Class for error handling in nano
//-------------------------------------------------------------------------------------------------

class One_Script_Error
{
	protected static $_errorHandlerSet = false;

	protected static $_inNano = false;

	public static function set_error_handler() {
		if(false === self::$_errorHandlerSet) {
			register_shutdown_function(array('One_Script_Error', 'errorHandler'));
			self::$_errorHandlerSet = true;
		}
	}

	/**
	 * Check if we are in Nano
	 * @return boolean
	 */
	public static function isInNano()
	{
		return self::$_inNano;
	}

	/**
	 * Set whether we are in nano
	 * @param boolean $inNano
	 */
	public static function setInNano($inNano = false)
	{
		if($inNano) {
			self::$_inNano = true;
		}
		else {
			self::$_inNano = false;
		}
	}

	public static function errorHandler()
	{
		if(true === self::$_inNano)
		{
			global $debugNsExpression, $debugNsExpressionError, $debugNsData;
			$error=error_get_last();
			if($error['type'] == 1) {
	    		$debugNsExpressionError = true;
	    		$variables = implode( ', ', array_keys($debugNsData));
				echo
						'
<div class="nanoErrorContainer" style="width: 100%; text-align: center;">
	<div class="nanoError" style="position: relative; width: 700px; left: 50%; margin-left: -350px; border: 1px solid C00; padding: 12px; text-align: left;">
		Fatal nano error in ' . $error['file'] . ' : ' . $error['message'] . '
		<br/>
	 	 parsing<span style="color:#C00;font-weight: bold;"> ' . $debugNsExpression[1] . '(' .  $debugNsExpression[2] . ')</span>
	 	<br/>
		expression parsed : <span style="color:#f00;font-weight: bold;">' . $debugNsExpression[0] . '</span>
	 	<br/>
		variables in scope : <span style="color:#c00;font-weight: bold;">' . $variables . '</span>
	</div>';
			}
		}
		else {
			return false;
		}
	}
}
