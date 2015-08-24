<?php
interface One_Query_Renderer_Interface
{
	public function render(One_Query $query);
	public function formatAttribute(One_Scheme_Attribute $attribute, $value);
}