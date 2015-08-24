<?php

class One_Filter_Scheme_Extension_Enabled implements One_Filter_Interface
{

	public function affect(One_Query $query)
	{
    $query->where('enabled','eq',1);

	}
}
