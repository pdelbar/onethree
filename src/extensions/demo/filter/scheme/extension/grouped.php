<?php

class One_Filter_Scheme_Extension_Grouped implements One_Filter_Interface
{

	public function affect(One_Query $query)
	{
    $query->setOrder('type,name');

	}
}
