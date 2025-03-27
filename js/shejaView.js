$(function ()
{
    // -- books list -- //

    function setULStatus($ul)
	{
        var omniTrue = true, omniFalse = false;
        $ul.find('div > div > input:checkbox').each(function () {
            var b = $(this).prop('checked');
            omniTrue &= b;
            omniFalse |= b;
        });
        var $menu = $ul.children('input:checkbox').first();

        $menu.prop('indeterminate', false);
        if ( omniTrue ) $menu.prop('checked', true);
        else if ( !omniFalse ) $menu.prop('checked', false);
        else $menu.prop('indeterminate', true);
    }

    function init()
	{
        $('div.book-list > ul > div')
            .hide()
            .addClass('bg-dark text-white')
            .css('position', 'absolute')
            .css('padding', 10)
            .css('overflow', 'hidden');

        $('div.book-list > ul').each(function () {
            setULStatus( $(this) );
        });
    }

    $('div.book-list > ul > input:checkbox').click(function() {
        $(this).siblings('div').first().find('input:checkbox').prop('checked', ($(this).is(':checked')));
    });

    $('div.book-list > ul > div > div > input:checkbox').click(function() {
        setULStatus( $(this).closest('ul') );
    });

    $('div.book-list > ul a').click(function (e) {
        e.preventDefault();
        $d = $(this).closest('ul').children('div').first();
        if( $d.css('display') === "block" ) {
            $d.css("z-index", 1 );
        } else {
            $d.css("z-index", 1000 );
        }
        $d.toggle("fast");
    });

    $('div.book-list > ul').mouseleave(function () {
        $d = $(this).children('div').first();
        if( $d.css('display') === "block" ) {
            $d.toggle("fast").css("z-index", 1 );
        }
    });

    init();

    // -- search form -- //

    $('#wholeWord').click(function () {
        $('#wylie').prop("disabled", !($(this).prop("checked")));
    });

    $('form').on('submit', function (e) {
        var search = $("input[name='q']");
        var t = search.val().trim();
        var books = $('.book-list').find('input:checked');
        if (t.length === 0 || books.length === 0) {
            e.preventDefault();
        }
    });

	function insertAtCursor(myField, myValue)
	{
		//IE support
		if (document.selection)
		{
			myField.focus();
			var sel = document.selection.createRange();
			sel.text = myValue;
		}
		//MOZILLA and others
		else if (myField.selectionStart || myField.selectionStart == '0')
		{
			var startPos = myField.selectionStart;
			var endPos = myField.selectionEnd;
			myField.value = myField.value.substring(0, startPos)
				+ myValue
				+ myField.value.substring(endPos, myField.value.length);
			myField.setSelectionRange(startPos, startPos + myValue.length);
		}
		else
		{
			myField.value += myValue;
		}
	}
	
	function to_qwerty(str)
	{
		var kb_map = {
            "й":"q", "ц":"w", "у":"e", "к":"r", "е":"t", "н":"y", "г":"u", "ш":"i", "щ":"o", "з":"p", "х":"[", "ъ":"]",
			"ф":"a", "ы":"s", "в":"d", "а":"f", "п":"g", "р":"h", "о":"j", "л":"k", "д":"l", "ж":";", "э":"'",
			"я":"z", "ч":"x", "с":"c", "м":"v", "и":"b", "т":"n", "ь":"m", "б":",", "ю":".", ".":"/"
        };       

		var s = "";
		
        for(i = 0; i < str.length; i++)
		{
			var ch = str.charAt(i);
            if( kb_map.hasOwnProperty(ch.toLowerCase()) )
			{
                if( ch === ch.toLowerCase() )
				{
                    ch = kb_map[ ch.toLowerCase() ];    
                }
				else if( ch === ch.toUpperCase() )
				{
                    ch = kb_map[ ch.toLowerCase() ].toUpperCase();
                } 
            }
			s += ch;
        }
		return s;
	}
	
	$("input[type='search']").keydown( function(e)
	{
		if (e.ctrlKey && e.shiftKey && e.keyCode === 73)
		{
			e.preventDefault();
			// inserts ".{1,10}?" at Ctrl+Shift+I keydown
			insertAtCursor(this, '.{1,10}?');
			document.getElementById("regEx").checked = true;
		}
		else if (e.ctrlKey && e.shiftKey && e.keyCode === 76)
		{
			e.preventDefault();
			// йцукен -> qwerty at Ctrl+Shift+L keydown
			this.value = to_qwerty(this.value);
		}
	});
	
    // -- appendices table -- //

    var sort_order = [];

    function table_init()
    {
        var ths = $('table.table > thead > tr > th');
        ths.css( 'cursor', 'pointer' );
        sort_order[0] = 'asc';
        for(var i = 1; i < ths.length; i++) sort_order[i] = 'no';
    }

    $('table.table > thead > tr > th').click(function ()
    {
        var idx = $(this).index();
        if (idx === 1) idx = 0;

        var t = sort_order[idx];
        var ths = $(this).closest('tr').children();
        for(var i = 0; i < ths.length; i++) sort_order[i] = 'no';
        sort_order[idx] = ( t === 'asc') ? 'desc' : 'asc';

        var $table = $(this).closest('table');
        $table.addClass("while-progress-bg");
        $p = $(".while-progress");
        $p.find('label').text( $(this).text() + " sorting (" + sort_order[idx] + ")");
        $p.removeClass("d-none");

        setTimeout(function() {
            sortTable( $table, idx, sort_order[idx] );
            $p.addClass("d-none");
            $table.removeClass("while-progress-bg");
        }, 0);
    });

    function sortTable(table, column, order) {
        var asc   = order === 'asc';
        var tbody = table.find('tbody');

        var s = 'td:eq(' + column  + ')';

        tbody
            .find('tr')
            .sort( function(a, b) {
                if (asc) {
                    return $(s, a).text().localeCompare($(s, b).text());
                } else {
                    return $(s, b).text().localeCompare($(s, a).text());
                }
            })
            .each( function() {
                $(this).removeClass('d-none');
                if ( $(s, this).text().trim() === "" ) {
                    $(this).addClass('d-none');
                }
            })
            .appendTo(tbody);
    }

    table_init();

    // -- nav bar appendices ajax -- //

    $('.nav-link').click(function (e) {
        e.preventDefault();

        if ($(this).attr('class').indexOf('disabled') !== -1) return false;

        table_init();

        $('div.container').addClass("while-progress-bg");
        $p = $(".while-progress");
        $p.find('label').text( "Loading...");
        $p.removeClass("d-none");

        var t = $(this).text().trim().toLowerCase();
        setTimeout(function() {
            provideRows(t);
            $p.addClass("d-none");
            $('div.container').removeClass("while-progress-bg");
        }, 0);
    });

});

