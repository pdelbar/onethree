function MultiDate( iYear, iMonth, container, datecontainer )
{
	this.getDaysInMonth = function( iYear, iMonth )
	{
		return ( 32 - new Date( iYear, iMonth, 32 ).getDate() );
	}

	this.addDateToDates = function( data, isChecked )
	{
		var key = this.dates.indexOf( data );
		if( key == -1 && isChecked )
		{
			this.dates[ this.dates.length ] = data;
		}
		else if( key != -1 && !isChecked )
		{
			this.dates.splice( key, 1 );
		}

		this.dates.sort();

		this.datecontainer.value = this.dates.join( ";" );
	}

	this.addColumnToDates = function( column, isChecked )
	{
		for( key in this.dateCols[ column ] )
		{
			var dateval = this.dateCols[ column ][ key ];
			var regex   = /day((19|20)[0-9]{2}-[0-1][0-9]-[0-3][0-9])/;
			var date    = regex.exec( dateval );


			if( date != null )
			{
				this.addDateToDates( date[ 1 ], isChecked );
				document.getElementById( dateval ).checked = isChecked;
			}
		}
	}

	this.addRowToDates = function( row, isChecked )
	{
		for( key in this.dateRows[ row ] )
		{
			var dateval = this.dateRows[ row ][ key ];
			var regex   = /day((19|20)[0-9]{2}-[0-1][0-9]-[0-3][0-9])/;
			var date    = regex.exec( dateval );

			if( date != null )
			{
				this.addDateToDates( date[ 1 ], isChecked );
				document.getElementById( dateval ).checked = isChecked;
			}
		}
	}

	this.createCalendar = function( iYear, iMonth )
	{
		if( iYear )
			this.year = iYear;
		else
			iYear = this.year;

		if( iMonth )
			this.month = iMonth - 1;
		else
			iMonth = this.month + 1

		var today = new Date();
		this.dateRows = new Array();
		for( var i = 0; i <= 5; i++ )
		{
			this.dateRows[ i ] = new Array();
		}

		this.dateCols = new Array();
		for( var i = 1; i <= 7; i++ )
		{
			this.dateCols[ i ] = new Array();
		}

		var totalDays = this.getDaysInMonth( this.year, this.month );

		var currMonth = new Array();
		for( var i = 1; i <= totalDays; i++ )
		{
			currMonth[ i ] = new Date( this.year, this.month, i );
		}

		var table = document.createElement( 'table' );
		table.setAttribute( 'border', 1 );

		var thead = document.createElement( 'thead' );

		var row  = document.createElement( 'tr' );
		var cell = document.createElement( 'th' );

		var prevMonth = parseInt( iMonth ) - 1;
		var prevYear = iYear;
		if( prevMonth == 0 )
		{
			prevMonth = 12;
			prevYear  = parseInt( iYear ) - 1;
		}
		cell.innerHTML = '<a href="#" onclick="' + this.objectName + '.createCalendar( \'' + prevYear + '\', \'' + prevMonth + '\' ); return false;">&lt;</a>';
		row.appendChild( cell );

		var cell = document.createElement( 'th' );
		cell.colSpan = 6;
		var input = this.months[ this.month ] + ' ' + this.year;
		cell.innerHTML = input;
		row.appendChild( cell );

		var cell = document.createElement( 'th' );
		var nextMonth = parseInt( iMonth ) + 1;
		var nextYear = iYear;
		if( nextMonth > 12 )
		{
			nextMonth = 1;
			nextYear  = parseInt( iYear ) + 1;
		}
		cell.innerHTML = '<a href="#" onclick="' + this.objectName + '.createCalendar( \'' + nextYear + '\', \'' + nextMonth + '\' ); return false;">&gt;</a>';
		row.appendChild( cell );

		thead.appendChild( row );

		var row   = document.createElement( 'tr' );
		var cell  = document.createElement( 'th' );
		var input = '&nbsp;';
		cell.innerHTML = input;
		row.appendChild( cell );

		for( var i = 1; i < 8; i++ )
		{
			var cell  = document.createElement( 'th' );
			var currDay = i;
			if( currDay == 7 ) currDay = 0;
			cell.appendChild( document.createTextNode( this.days[ currDay ] ) );
			row.appendChild( cell );
		}

		thead.appendChild( row );
		table.appendChild( thead );

		var tbody = document.createElement( 'tbody' );

		var row   = document.createElement( 'tr' );
		var cell = document.createElement( 'td' );
		var input = '&nbsp;';
		cell.innerHTML = input;
		row.appendChild( cell );

		for( var i = 1; i <= 7; i++ )
		{
			var cell = document.createElement( 'td' );
			try{
				var input = document.createElement( '<input type="checkbox" onclick="' + this.objectName + '.addColumnToDates( \'' + i + '\', this.checked );" />' );
			}catch(err){
				var input = document.createElement( 'input' );
				input.setAttribute( 'type', 'checkbox' );

				input.setAttribute( 'onclick', this.objectName + '.addColumnToDates( \'' + i + '\', this.checked );' );
				input.onClick = this.objectName + '.addColumnToDates( \'' + i + '\', this.checked );';
			}
			cell.appendChild( input );

			row.appendChild( cell );
		}

		tbody.appendChild( row );

		var row = document.createElement( 'tr' );

		var rowNr = 0;
		var cell = document.createElement( 'td' );
		try{
			var input = document.createElement( '<input type="checkbox" onclick="' + this.objectName + '.addRowToDates( \'0\', this.checked );" />' );
		}catch(err){
			var input = document.createElement( 'input' );
			input.setAttribute( 'type', 'checkbox' );

			input.setAttribute( 'onclick', this.objectName + '.addRowToDates( \'0\', this.checked );' );
			input.onClick = this.objectName + '.addRowToDates( \'0\', this.checked );';
		}
		cell.appendChild( input );
		row.appendChild( cell );

		if( currMonth[ 1 ].getDay() != 1 )
		{
			var empty = 1;
			while( empty != currMonth[ 1 ].getDay() )
			{
				var cell = document.createElement( 'td' );
				cell.innerHTML = '&nbsp;';
				row.appendChild( cell );

				empty++;
				if( empty == 7 )
					empty = 0;
			}
		}

		for( var i = 1; i <= totalDays; i++ )
		{
			if( i != 1 && currMonth[ i ].getDay() == 1 )
			{
				tbody.appendChild( row );
				rowNr++;
				var row   = document.createElement( 'tr' );
				var cell  = document.createElement( 'td' );
				try{
					var input = document.createElement( '<input type="checkbox" onclick="' + this.objectName + '.addRowToDates( \'' + rowNr + '\', this.checked );" />' );
				}catch(err){
					var input = document.createElement( 'input' );
					input.setAttribute( 'type', 'checkbox' );

					input.setAttribute( 'onclick', this.objectName + '.addRowToDates( \'' + rowNr + '\', this.checked );' );
					input.onClick = this.objectName + '.addRowToDates( \'' + rowNr + '\', this.checked );';
				}
				cell.appendChild( input );
				row.appendChild( cell );
			}

			var cell        = document.createElement( 'td' );
			var usedYear    = ( currMonth[ i ].getYear() < 1900 ) ? currMonth[ i ].getYear() + 1900 : currMonth[ i ].getYear();
			var usedMonth   = currMonth[ i ].getMonth() + 1;
			var usedDay     = currMonth[ i ].getDate();
			var isChecked   = false;

			var currDate = usedYear + '-' + ( ( usedMonth < 10 ) ? '0' + usedMonth : usedMonth ) + '-' + ( ( usedDay < 10 ) ? '0' + usedDay : usedDay );
			if( this.dates.indexOf( currDate ) != -1 )
				isChecked = true;

			this.dateRows[ rowNr ][ this.dateRows[ rowNr ].length ] = 'day' + currDate;

			var usedCol = currMonth[ i ].getDay();
			if( usedCol == 0 )
				usedCol = 7;
			this.dateCols[ usedCol ][ this.dateCols[ usedCol ].length ] = 'day' + currDate;

			try{
				var input = document.createElement( '<input type="checkbox" id="day' + currDate + '" onclick="' + this.objectName + '.addDateToDates( \'' + currDate + '\', this.checked );" value="' + currDate + '"' + ( ( isChecked ) ? ' checked="checked"' : '' ) + ' />' );
			}catch(err){
				var input = document.createElement( 'input' );
				input.setAttribute( 'id', 'day' + currDate );
				input.setAttribute( 'type', 'checkbox' );
				input.setAttribute( 'value', currDate );
				input.setAttribute( 'onclick', this.objectName + '.addDateToDates( \'' + currDate + '\', this.checked );' );
				input.onClick = this.objectName + '.addDateToDates( \'' + currDate + '\', this.checked );';
				if( isChecked )
					input.setAttribute( 'checked', 'checked' );
			}
			cell.appendChild( input );

			try{
				var label = document.createElement( '<label for="day' + currDate + '" />' );
			}catch(err){
				var label = document.createElement( 'label' );
				label.setAttribute( 'for', 'day' + currDate );
			}
			label.appendChild( document.createTextNode( usedDay ) );

			var todayYear  = ( ( today.getYear() < 1900 ) ? parseInt( today.getYear() + 1900 ) : today.getYear() );
			var todayMonth = today.getMonth();
			var todayDate  = today.getDate();
			if( this.year == todayYear && this.month == todayMonth && usedDay == todayDate )
			{
				cell.setAttribute( 'class', 'isToday' );
			}

			cell.appendChild( label );
			row.appendChild( cell );
		}

		if( currMonth[ ( i - 1 ) ].getDay() != 0 )
		{
			var empty = currMonth[ ( i - 1 ) ].getDay() + 1;
			if( empty == 7 )
				empty = 0;

			while( empty != 1 )
			{
				var cell = document.createElement( 'td' );
				cell.innerHTML = '&nbsp;';
				row.appendChild( cell );

				empty++;
				if( empty == 7 )
					empty = 0;
			}
		}

		tbody.appendChild( row );
		table.appendChild( tbody );

		var tfoot  = document.createElement( 'tfoot' );
		var row    = document.createElement( 'tr' );
		var cell   = document.createElement( 'td' );
		cell.colSpan = 8;
		cell.setAttribute( 'align', 'center' );
		cell.innerHTML = '<a href="#" onclick="' + this.objectName + '.createCalendar( \'' + ( ( today.getYear() < 1900 ) ? parseInt( today.getYear() + 1900 ) : today.getYear() ) + '\', \'' + ( parseInt( today.getMonth() ) + 1 ) + '\' ); return false;">Today</a>';
		row.appendChild( cell );
		tfoot.appendChild( row );
		table.appendChild( tfoot );

		this.container.innerHTML = '';
		this.container.appendChild( table );
	}

	this.ucfirst = function( str )
	{
		var f = str.charAt(0).toUpperCase();
		return f + str.substr(1);
	}

	this.objectName    = 'multi' + this.ucfirst( datecontainer );
	this.container     = document.getElementById( container );
	this.month         = iMonth - 1;
	this.year          = iYear;
	this.dates         = new Array();
	this.datecontainer = document.getElementById( datecontainer );
	this.dateRows      = new Array();
	this.dateCols      = new Array();

	this.days      = new Array();
	this.days[ 0 ] = 'Sun';
	this.days[ 1 ] = 'Mon';
	this.days[ 2 ] = 'Tue';
	this.days[ 3 ] = 'Wed';
	this.days[ 4 ] = 'Thu';
	this.days[ 5 ] = 'Fri';
	this.days[ 6 ] = 'Sat';

	this.months       = new Array();
	this.months[ 0 ]  = 'January';
	this.months[ 1 ]  = 'February';
	this.months[ 2 ]  = 'March';
	this.months[ 3 ]  = 'April';
	this.months[ 4 ]  = 'May';
	this.months[ 5 ]  = 'June';
	this.months[ 6 ]  = 'Juli';
	this.months[ 7 ]  = 'August';
	this.months[ 8 ]  = 'September';
	this.months[ 9 ]  = 'October';
	this.months[ 10 ] = 'November';
	this.months[ 11 ] = 'December';
}
