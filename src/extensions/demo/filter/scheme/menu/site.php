<?php

class One_Filter_Scheme_Menu_Site implements One_Filter_Interface
{

	public function affect(One_Query $query)
	{
    $query->where('published','eq',1);
	}
}
