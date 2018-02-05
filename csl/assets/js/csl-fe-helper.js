var MAP_CENTER_DEFAULT_LAT = 40.41678;
var MAP_CENTER_DEFAULT_LON = -3.70379;
var googleLoaded;

jQuery(document).ready(function($) {
	console.log(feTXT.document_ready);
	
    // Cookies Law activation
	C.init();
	
	$('#loginData').popover();

    // Bootstrap and Thickbox functionalities activation
    $("img:not(.logofooter)").parent("a").addClass("thickbox");
    
    $("img:not(.avatar,.leaflet-tile,.logofooter)").addClass("img-shadow1");
    $("img:not(.avatar,.leaflet-tile,.logofooter)").addClass("img-responsive");
    $("img:not(.avatar,.leaflet-tile,.logofooter)").addClass("img-thumbnail");
    
    //Tooltip activation
    $('[data-toggle="tooltip"]').tooltip()
    
	if ($('#table')) {
		// *** To review
		/*
		$('#table').pointer({
			content: '<h3>wpsnipp.com | Notice</h3><p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>',
			position: 'right',
			close: function() {
				// Once the close button is hit
			}
		}).pointer('open');
		*/
	}

    // Tours
	if ($('#tour-ef').length > 0) {
        var $demo, duration, remaining, tour;
        $demo = $("#tour-ef");
        duration = 5000;
        remaining = duration;
        tour = new Tour({
            onStart: function() {
                return $demo.addClass("disabled", true);
            },
            onEnd: function() {
                return $demo.removeClass("disabled", true);
            },
            debug: true,
            steps: tourSteps,
            template: tourTemplate,
        }).init();
        if (tour.ended()) {
            $(tourEnded).prependTo(".content").alert();
        }
        $("#tour-ef").on("click", function(e) {
            e.preventDefault();
            if ($(this).hasClass("disabled")) {
                return;
            }
            tour.restart();
            return $(".alert").alert("close");
        });
    }
        
	if (typeof isCH !== 'undefined') {
	    var tblData = $('#cqry').DataTable({
		    ajax: {
			    url: datU,
	    	},
			colReorder: true,
			rowReorder: true,
			responsive: true,
			language: {
			    url: "//cdn.datatables.net/plug-ins/1.10.10/i18n/Spanish.json",
			    buttons: {
			        copyTitle: feTXT.copyTitle,
			        copySuccess: {
			            _: feTXT.copySuccess1,
			            1: feTXT.copySuccess2,
			        }
				}
			},
			dom: "<'row'<'col-md-3'l><'col-md-6'B><'col-md-3'f>>" +
				"<'row'<'col-md-12't>>" +
				"<'row'<'col-md-6'ir><'col-md-6'p>><'clear'>",
			colVis: {
	            exclude: [ 0 ]
	        },
			buttons: [
			    {
			        extend: 'copy',
			        text: '<i class="fa fa-clipboard"></i> ' + feTXT.copyText,
			        key: {
			            key: feTXT.copyHotKey,
			            altKey: true
			        }
			    },
			    {
			        extend: 'csv',
			        text: '<i class="fa fa-download"></i> ' + feTXT.csvText,
			    },
			    {
			        extend: 'excel',
			        text: '<i class="fa fa-download"></i> ' + feTXT.excelText
			    },
			    {
			        extend: 'pdf',
			        text: '<i class="fa fa-download"></i> ' + feTXT.pdfText
			    },
			    {
			        extend: 'print',
			        text: '<i class="fa fa-download"></i> ' + feTXT.printText
			    },
			],
	        rowCallback: function( row, data, index ) {
	            for(i = 0; i < data.length; i++) {
	                if( !isNaN(parseFloat(data[i])) && isFinite(data[i]) ) {
	                    $('td:eq('+i+')', row).addClass( 'text-right' );
	                    $("table thead tr th").eq(i).addClass( 'text-right' );
	                    $("table tfoot tr th").eq(i).addClass( 'text-right' );
	                }
	            }
	        },
	        initComplete: function(settings, json) {
			}
		});

	    var derivers = $.pivotUtilities.derivers;
	
	    var renderers = $.extend(
	        $.pivotUtilities.renderers, 
	        $.pivotUtilities.gchart_renderers, 
	        $.pivotUtilities.c3_renderers, 
	        $.pivotUtilities.d3_renderers 
	    );
	
		
	    $.getJSON(pivU, function(mps) {
	        var aDerived = {};
	        if(mps[0].hasOwnProperty('Año')) {
		    	aDerived['Quinquenios'] = derivers.bin("Año", 5);	    
		    	aDerived['Décadas'] = derivers.bin("Año", 10);	    
	        }
	        $("#pivotTable").pivotUI(mps, {
	            hiddenAttributes: ["ID"],
	            renderers: renderers,
	            derivedAttributes: aDerived,
	        }, true, pivL);
	    });
	    
	    $.getJSON(chaU, function(mps) {
			var chart = c3.generate({
				data: mps,
				axis: {
					x: {
						type: 'category'
            		},
				},
				subchart: {
					show: true,
				},
				legend: {
					position: 'inset',
					inset: {
						anchor: 'top-right',
						x: 20,
						y: 10,
						step: undefined
					}
				}
			});
	    });
	}
});

/* Table to JSON */
/* From http: / / lightswitch05.github.io/table-to-json/ */

(function( $ ) {
  'use strict';

  $.fn.tableToJSON = function(opts) {

    // Set options
    var defaults = {
      ignoreColumns: [],
      onlyColumns: null,
      ignoreHiddenRows: true,
      ignoreEmptyRows: false,
      headings: null,
      allowHTML: false,
      includeRowId: false,
      textDataOverride: 'data-override',
      textExtractor: null
    };
    opts = $.extend(defaults, opts);

    var notNull = function(value) {
      return value !== undefined && value !== null;
    };

    var ignoredColumn = function(index) {
      if( notNull(opts.onlyColumns) ) {
        return $.inArray(index, opts.onlyColumns) === -1;
      }
      return $.inArray(index, opts.ignoreColumns) !== -1;
    };

    var arraysToHash = function(keys, values) {
      var result = {}, index = 0;
      $.each(values, function(i, value) {
        // when ignoring columns, the header option still starts
        // with the first defined column
        if ( index < keys.length && notNull(value) ) {
          result[ keys[index] ] = value;
          index++;
        }
      });
      return result;
    };

    var cellValues = function(cellIndex, cell, isHeader) {
      var $cell = $(cell),
        // textExtractor
        extractor = opts.textExtractor,
        override = $cell.attr(opts.textDataOverride);
      // don't use extractor for header cells
      if ( extractor === null || isHeader ) {
        return $.trim( override || ( opts.allowHTML ? $cell.html() : cell.textContent || $cell.text() ) || '' );
      } else {
        // overall extractor function
        if ( $.isFunction(extractor) ) {
          return $.trim( override || extractor(cellIndex, $cell) );
        } else if ( typeof extractor === 'object' && $.isFunction( extractor[cellIndex] ) ) {
          return $.trim( override || extractor[cellIndex](cellIndex, $cell) );
        }
      }
      // fallback
      return $.trim( override || ( opts.allowHTML ? $cell.html() : cell.textContent || $cell.text() ) || '' );
    };

    var rowValues = function(row, isHeader) {
      var result = [];
      var includeRowId = opts.includeRowId;
      var useRowId = (typeof includeRowId === 'boolean') ? includeRowId : (typeof includeRowId === 'string') ? true : false;
      var rowIdName = (typeof includeRowId === 'string') === true ? includeRowId : 'rowId';
      if (useRowId) {
        if (typeof $(row).attr('id') === 'undefined') {
          result.push(rowIdName);
        }
      }
      $(row).children('td,th').each(function(cellIndex, cell) {
        result.push( cellValues(cellIndex, cell, isHeader) );
      });
      return result;
    };

    var getHeadings = function(table) {
      var firstRow = table.find('tr:first').first();
      return notNull(opts.headings) ? opts.headings : rowValues(firstRow, true);
    };

    var construct = function(table, headings) {
      var i, j, len, len2, txt, $row, $cell,
        tmpArray = [], cellIndex = 0, result = [];
      table.children('tbody,*').children('tr').each(function(rowIndex, row) {
        if( rowIndex > 0 || notNull(opts.headings) ) {
          var includeRowId = opts.includeRowId;
          var useRowId = (typeof includeRowId === 'boolean') ? includeRowId : (typeof includeRowId === 'string') ? true : false;

          $row = $(row);

          var isEmpty = ($row.find('td').length === $row.find('td:empty').length) ? true : false;

          if( ( $row.is(':visible') || !opts.ignoreHiddenRows ) && ( !isEmpty || !opts.ignoreEmptyRows ) && ( !$row.data('ignore') || $row.data('ignore') === 'false' ) ) {
            cellIndex = 0;
            if (!tmpArray[rowIndex]) {
              tmpArray[rowIndex] = [];
            }
            if (useRowId) {
              cellIndex = cellIndex + 1;
              if (typeof $row.attr('id') !== 'undefined') {
                tmpArray[rowIndex].push($row.attr('id'));
              } else {
                tmpArray[rowIndex].push('');
              }
            }

            $row.children().each(function(){
              $cell = $(this);
              // skip column if already defined
              while (tmpArray[rowIndex][cellIndex]) { cellIndex++; }

              // process rowspans
              if ($cell.filter('[rowspan]').length) {
                len = parseInt( $cell.attr('rowspan'), 10) - 1;
                txt = cellValues(cellIndex, $cell);
                for (i = 1; i <= len; i++) {
                  if (!tmpArray[rowIndex + i]) { tmpArray[rowIndex + i] = []; }
                  tmpArray[rowIndex + i][cellIndex] = txt;
                }
              }
              // process colspans
              if ($cell.filter('[colspan]').length) {
                len = parseInt( $cell.attr('colspan'), 10) - 1;
                txt = cellValues(cellIndex, $cell);
                for (i = 1; i <= len; i++) {
                  // cell has both col and row spans
                  if ($cell.filter('[rowspan]').length) {
                    len2 = parseInt( $cell.attr('rowspan'), 10);
                    for (j = 0; j < len2; j++) {
                      tmpArray[rowIndex + j][cellIndex + i] = txt;
                    }
                  } else {
                    tmpArray[rowIndex][cellIndex + i] = txt;
                  }
                }
              }

              txt = tmpArray[rowIndex][cellIndex] || cellValues(cellIndex, $cell);
              if (notNull(txt)) {
                tmpArray[rowIndex][cellIndex] = txt;
              }
              cellIndex++;
            });
          }
        }
      });
      $.each(tmpArray, function( i, row ){
        if (notNull(row)) {
          // remove ignoredColumns / add onlyColumns
          var newRow = notNull(opts.onlyColumns) || opts.ignoreColumns.length ?
            $.grep(row, function(v, index){ return !ignoredColumn(index); }) : row,

            // remove ignoredColumns / add onlyColumns if headings is not defined
            newHeadings = notNull(opts.headings) ? headings :
              $.grep(headings, function(v, index){ return !ignoredColumn(index); });

          txt = arraysToHash(newHeadings, newRow);
          result[result.length] = txt;
        }
      });
      return result;
    };

    // Run
    var headings = getHeadings(this);
    return construct(this, headings);
  };
})( jQuery );

/*
 * Creare's 'Implied Consent' EU Cookie Law Banner v:2.4
 * Conceived by Robert Kent, James Bavington & Tom Foyster
 * Modified by Simon Freytag for syntax, namespace, jQuery and Bootstrap.
 *
 * Configuration variables:
 * cookieDuration	= Number of days before the cookie expires, and the banner reappears
 * cookieName		= Name of our cookie
 * cookieValue		= Value of cookie
 * bannerTitle		= Message banner title
 * bannerMessage	= Message banner message
 * bannerButton		= Message banner dismiss button
 * bannerLinkURL	= Link to your cookie policy
 * bannerLinkText	= Link text
 *
 */

C = {
    cookieDuration:	14,
    cookieName:		feTXT.cookieName,
    cookieValue:	feTXT.cookieValue,
    bannerTitle:	feTXT.bannerTitle,
    bannerMessage:	feTXT.bannerMessage,
    bannerButton:	feTXT.bannerButton,
    bannerLinkURL:	feTXT.bannerLinkURL,
    bannerLinkText:	feTXT.bannerLinkText,

    createDiv: function () {
        var banner = jQuery(
            '<div class="alert alert-success alert-dismissible fade in" ' +
            'role="alert" style="position: fixed; bottom: 0; width: 100%; ' +
            'margin-bottom: 0">' + 
            '<div class="row"><div class="col-md-11">' + 
            '<strong>' + this.bannerTitle + '</strong><br />' +
            this.bannerMessage + ' <a href="' + this.bannerLinkURL + '">' +
            this.bannerLinkText + '</a>' + 
            '</div><div class="col-md-1"><span class="pull-right">' + 
            '<button type="button" class="btn ' +
            'btn-success btn-sm" onclick="C.createCookie(C.cookieName, C.cookieValue' +
            ', C.cookieDuration)" data-dismiss="alert" aria-label="Close">' +
            this.bannerButton + '</button></span></div></div></div>'
        )
        jQuery("body").append(banner)
    },

    createCookie: function(name, value, days) {
        console.log("Create cookie")
        var expires = ""
        if (days) {
            var date = new Date()
            date.setTime(date.getTime() + (days*24*60*60*1000))
            expires = "; expires=" + date.toGMTString()
        }
        document.cookie = name + "=" + value + expires + "; path=/";
    },

    checkCookie: function(name) {
        var nameEQ = name + "="
        var ca = document.cookie.split(';')
        for(var i = 0; i < ca.length; i++) {
            var c = ca[i]
            while (c.charAt(0)==' ')
                c = c.substring(1, c.length)
            if (c.indexOf(nameEQ) == 0) 
                return c.substring(nameEQ.length, c.length)
        }
        return null
    },

    init: function() {
        if (this.checkCookie(this.cookieName) != this.cookieValue)
            this.createDiv()
    }
}

