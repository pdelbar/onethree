<?php

/**
 * Fetch multiple XMLobjects from another XMLobject using XPATH
 *
 * @param SimpleXMLElement $xmlObject
 * @param string $xPath
 * @return array
 * @deprecated should no longer be used
 */
function &xmlpm( $xmlObject, $xPath )
{
	$x = $xmlObject->xpath( $xPath );
	return $x;
}

/**
 * Fetch a single XMLobject from another XMLobject using XPATH
 *
 * @param SimpleXMLElement $xmlObject
 * @param string $xPath
 * @return SimpleXMLElement
 * @deprecated should no longer be used
 */
function &xmlp( $xmlObject, $xPath )
{
	$x = $xmlObject->xpath( $xPath );
	return $x[0];
}

/**
 * Fetch the value of a single XMLobject from another XMLobject using XPATH
 *
 * @param SimpleXMLElement $xmlObject
 * @param string $xPath
 * @return string
 * @deprecated should no longer be used
 */
function xmla( $xmlObject, $xPath )
{
	$x = $xmlObject->xpath( $xPath );
	return (string) $x[0];
}

/**
 * Get the attributes of the given XMLobject
 *
 * @param SimpleXMLElement $xmlObject
 * @return array
 * @deprecated should no longer be used
 */
function xmlatts( $xmlObject )
{
	$x = $xmlObject->xpath( 'attribute::*' );
	$atts = array();

	foreach( $x as $y )
	{
		$atts[ $y[0]->getName() ] = (string) $y[0];
	}

	return $atts;
}
