function equalTxt(obj1, obj2)
{
	if(obj1.value != obj2.value)
	{
		document.getElementById(obj2.id + 'Error').display = 'block';
		document.getElementById(obj2.id + 'Error').innerHTML = 'error';
	}
	else
	{
		document.getElementById(obj2.id + 'Error').display = 'none';
		document.getElementById(obj2.id + 'Error').innerHTML = '';
	}
}