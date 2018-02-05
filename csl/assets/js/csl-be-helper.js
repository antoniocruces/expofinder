var siriusm_val 			= [];

var ICON_SUCCESS_LEFT 		= '<i class="fa fa-check-circle statusMark-OK" style="margin-right: 5px;"></i>';
var ICON_ERROR_LEFT 		= '<i class="fa fa-times-circle statusMark-ERROR" style="margin-right: 5px;"></i>';
var ICON_SUCCESS_RIGHTT 	= '<i class="fa fa-check-circle statusMark-OK" style="margin-left: 5px;"></i>';
var ICON_ERROR_RIGHT 		= '<i class="fa fa-times-circle statusMark-ERROR" style="margin-left: 5px;"></i>';
var MAP_CENTER_DEFAULT_LAT 	= 40.41678;
var MAP_CENTER_DEFAULT_LON 	= -3.70379;
//var GLOBAL_NONCE            = beTXT.gnonce;	

var GCHART_UN_REGIONS       = {
	'South America':				'005',
	'Western Africa':				'011',
	'Central America':				'013',
	'Eastern Africa':				'014',
	'Northern Africa':				'015',
	'Middle Africa':				'017',
	'Southern Africa':				'018',
	'Caribbean':					'029',
	'Eastern Asia':					'030',
	'Southern Asia':				'034',
	'South-Eastern Asia':			'035',
	'Southern Europe':				'039',
	'Australia and New Zealand':	'053',
	'Melanesia':					'054',
	'Micronesia':					'057',
	'Polynesia':					'061',
	'Central Asia':					'143',
	'Western Asia':					'145',
	'Eastern Europe':				'151',
	'Northern Europe':				'154',
	'Western Europe':				'155',
	'Northern America':				'021',
};

jQuery( document ).ready( function( $ ) {
	console.log( beTXT.document_ready );
	
    if(typeof($('#wp-version-message').html()) !== 'undefined') {
        $('#wp-version-message').html( $('#wp-version-message').html().replace('WordPress', 'WPAF Engine')).addClass('line-top-margin');
    }    

    $('.howto').append('. ' + beTXT.separate_comma);
    
    if (typeof $.fn.datepicker !== 'undefined') { 
    	$.datepicker.regional[csl_LANG_S] = {
    		closeText: 'Cerrar',
    		prevText: '<Ant',
    		nextText: 'Sig>',
    		currentText: 'Hoy',
    		monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    		monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
    		dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
    		dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
    		dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
    		weekHeader: 'Sm',
    		dateFormat: 'yy-mm-dd',
    		firstDay: 1,
    		isRTL: false,
    		showMonthAfterYear: false,
    		yearSuffix: ''
    	};
    	$.datepicker.setDefaults($.datepicker.regional[csl_LANG_S]);
    }
 
	total_rank_SIRIUSM();

    synchronize_child_and_parent_category($);
	
	if((adminpage == 'post-php' || adminpage == 'post-new-php') && (typenow == 'exhibition')) {
        $.get(beTXT.kwURI, function(data) { 
	        //console.log(jQuery(tmce_getContent())) 
	        //var tmcec = tmce_getContent();
	        //tmcec.each()
	        //$('#tinymce p').unhighlight();
            //$('#tinymce p').highlight(jQuery.unique(data.split("\n")));
        }, 'text');
    }
	
	
    if (typeof $.fn.locationpicker !== 'undefined') {
        // Spain capital city geographical center by default: 40.41678, -3.70379
        var frmMapCenterLAT = $('.aad-coordinates').val() ? $('.aad-coordinates').val().split(',')[0] : MAP_CENTER_DEFAULT_LAT;
        frmMapCenterLON = $('.aad-coordinates').val() ? $('.aad-coordinates').val().split(',')[1] : MAP_CENTER_DEFAULT_LON;  
        $('#us3').locationpicker({
            location: {latitude: frmMapCenterLAT, longitude: frmMapCenterLON},
            radius: 100,
            onchanged: function(currentLocation, radius, isMarkerDropped) {
            	$('.aad-coordinates').val(currentLocation.latitude + "," + currentLocation.longitude + ",0");
            },
        	inputBinding: {
                locationNameInput: $('.aad-address')
            },
            enableAutocomplete: true
        });
    }
    
	$("#btnSrchURL").click(function(){
    	findFeeds();
        findRelated();
	});

    $('#btnExportXLS').on('click', function(e) {
        e.preventDefault();
        window.location = ajaxurl + '?action=csl_generic_ajax_call&q=exportentitiesxls';        
    });
    
    if (typeof jeoquery !== 'undefined') {
        jeoquery.defaultData.userName = 'iarthis_lab';
        $(".jeoquery").jeoCityAutoComplete({callback: function(city) { console.log(city)  }});
        $(".jeoquerycountries").jeoCountryAutoComplete({callback: function(city) { console.log(city) }});
    
    	$('.opentab').on('click', function (e){
    		e.preventDefault();
    		var url = $(this).attr('href');
    		var iConf = confirm(beTXT.confirm_new_tab);
    		if(iConf) {
    			window.open(url,'_blank');		
    		} else {
    			window.open(url,'_self');	
    		}
    	});
    }

    if($('#statusTable1').length) {
        if (typeof googleLoaded !== 'undefined') {
            googleLoaded.done( function() {
                $('#statusTable1').gvChart({
	 				hideTable: false,
	 				chartType: 'ComboChart',
	 				swap: true,
                    gvSettings: {
                        vAxis: {title: beTXT.quantity_of_records, logScale: true},
                        hAxis: {title: beTXT.record_status},
                        width: '100%',
                        height: 500,
						seriesType: "bars",
						legend: { position: 'top', maxLines: 3 },
				        bar: { groupWidth: '75%' },
						isStacked: false,
						series: {0: {type: "area"}},
						backgroundColor: 'transparent'
                    },
                    excludedColumns: [4,5]
                });
            });
        }
    }
    
    if($('#statusTableUE').length) {
        if (typeof googleLoaded !== 'undefined') {
            googleLoaded.done( function() {
                $('#statusTableUE').gvChart({
	 				hideTable: false,
	 				chartType: 'ColumnChart',
	 				swap: true,
                    gvSettings: {
                        vAxis: {title: beTXT.users},
                        hAxis: {title: beTXT.percent_of_records},
                        width: '100%',
                        height: 500,
						legend: { position: 'top', maxLines: 3 },
				        bar: { groupWidth: '75%' },
						isStacked: 'percent',
						colors: ['red', 'green'],
						backgroundColor: 'transparent',
                    },
                    excludedColumns: [1,3]
                });
            });
        }
    }

    if($('#statusTable4').length) {
        if (typeof googleLoaded !== 'undefined') {
            googleLoaded.done( function() {
                $('#statusTable4').gvChart({
	 				hideTable: false,
	 				chartType: 'AreaChart',
	 				swap: true,
                    gvSettings: {
                        vAxis: {
	                        title: beTXT.quantity_log, 
	                        logScale: true, 
	                        /*
	                        viewWindow: {
		                        max: 0.5, 
		                        min: 0
		                    }
		                    */
		                },
                        hAxis: {title: beTXT.dates},
                        width: '100%',
                        height: 500,
                        isStacked: true,
          				legend: { position: 'top', maxLines: 3 },
				        bar: { groupWidth: '95%' },
						isStacked: false,
						colors: ['blue', 'gray', 'red', 'green'],
						backgroundColor: 'transparent',
                    },
                    excludedColumns: [1,2,3,4]
                });
            });
        }
    }
    
    if($('#geoTable1').length) {
        if (typeof googleLoaded !== 'undefined') {
            googleLoaded.done( function() {
                $('#geoTable1').gvChart({
	 				hideTable: false,
	 				chartType: 'PieChart',
	 				swap: true,
                    gvSettings: {
                        vAxis: {title: beTXT.quantity},
                        hAxis: {title: beTXT.countries},
                        width: '100%',
                        height: 300,
                        is3D: true,
						backgroundColor: 'transparent',
                    },
                    excludedColumns: [1]
                });
            });
        }
    }
    
    if($('#geoTable2').length) {
        if (typeof googleLoaded !== 'undefined') {
            googleLoaded.done( function() {
                $('#geoTable2').gvChart({
	 				hideTable: false,
	 				chartType: 'PieChart',
	 				swap: true,
                    gvSettings: {
                        vAxis: {title: beTXT.quantity},
                        hAxis: {title: beTXT.regions},
                        width: '100%',
                        height: 300,
                        is3D: true,
						backgroundColor: 'transparent'
                    },
                    excludedColumns: [1]
                });
            });
        }
    }
    
    if($('#geoTable3').length) {
        if (typeof googleLoaded !== 'undefined') {
            googleLoaded.done( function() {
                $('#geoTable3').gvChart({
	 				hideTable: false,
	 				chartType: 'PieChart',
	 				swap: true,
                    gvSettings: {
                        vAxis: {title: beTXT.quantity},
                        hAxis: {title: beTXT.cities},
                        width: '100%',
                        height: 300,
                        is3D: true,
						backgroundColor: 'transparent'
                    },
                    excludedColumns: [1]
                });
            });
        }
    }
    
    if($('#userTimetable').length) {
        if (typeof googleLoaded !== 'undefined') {
            googleLoaded.done( function() {
                $('#userTimetable').gvChart({
	 				hideTable: false,
	 				chartType: 'ComboChart',
	 				swap: true,
                    gvSettings: {
                        vAxis: {title: beTXT.quantity_of_records, logScale: true},
                        hAxis: {title: beTXT.record_status},
                        width: '100%',
                        height: 500,
						seriesType: "bars",
						legend: { position: 'top', maxLines: 3 },
				        bar: { groupWidth: '75%' },
						isStacked: false,
						series: {0: {type: "area"}},
						backgroundColor: 'transparent'
                    },
                    excludedColumns: [3]
                });
            });
        }
    }
    
    if($('#userActivityStats').length) {
        var nColumns = $('#userActivityStats thead th').not('.manage-column').length + 2;
        for(var i = 1; i < nColumns; i++) {
            var counts = $('#userActivityStats tbody td:nth-child('+i+')').not('.manage-column').map(function() {
                return parseInt($(this).text().replace(':',''));
            }).get();
            var max = Array.max(counts);
            n  = 500;   // Declare the number of groups
            xr = 255;  // Red value
            xg = 255;  // Green value
            xb = 255;  // Blue value
            yr = 75;   // Initial color. Red value
            yg = 187;  // Initial color. Green value
            yb = 30;   // Initial color. Blue value
            $('#userActivityStats tbody td:nth-child('+i+')').not('.manage-column').each(function(){
                var val = parseInt($(this).text().replace(':','').replace('.','').replace(',','.'));
                var pos = parseInt((Math.round((val/max)*100)).toFixed(0));
                red = parseInt((xr + (( pos * (yr - xr)) / (n-1))).toFixed(0));
                green = parseInt((xg + (( pos * (yg - xg)) / (n-1))).toFixed(0));
                blue = parseInt((xb + (( pos * (yb - xb)) / (n-1))).toFixed(0));
                clr = 'rgb('+red+','+green+','+blue+')';
                var ltrclr = (red < 60 && green < 60 && blue < 60) ? 'rgb(255,255,255)' : 'rgb(0,0,0)'; 
                $(this).css({
                	backgroundColor: clr,
                    color: ltrclr
            	});
            });
        }			
    }

    if($('#userTimetable').length) {
        var nColumns = $('#userTimetable thead th').not('.manage-column').length + 2;
        for(var i = 1; i < nColumns; i++) {
            var counts = $('#userTimetable tbody td:nth-child('+i+')').not('.manage-column').map(function() {
                return parseInt($(this).text().replace(':',''));
            }).get();
            var max = Array.max(counts);
            n  = 500;   // Declare the number of groups
            xr = 255;  // Red value
            xg = 255;  // Green value
            xb = 255;  // Blue value
            yr = 75;   // Initial color. Red value
            yg = 187;  // Initial color. Green value
            yb = 30;   // Initial color. Blue value
            $('#userTimetable tbody td:nth-child('+i+')').not('.manage-column').each(function(){
                var val = parseInt($(this).text().replace(':','').replace('.','').replace(',','.'));
                var pos = parseInt((Math.round((val/max)*100)).toFixed(0));
                red = parseInt((xr + (( pos * (yr - xr)) / (n-1))).toFixed(0));
                green = parseInt((xg + (( pos * (yg - xg)) / (n-1))).toFixed(0));
                blue = parseInt((xb + (( pos * (yb - xb)) / (n-1))).toFixed(0));
                clr = 'rgb('+red+','+green+','+blue+')';
                var ltrclr = (red < 60 && green < 60 && blue < 60) ? 'rgb(255,255,255)' : 'rgb(0,0,0)'; 
                $(this).css({
                	backgroundColor: clr,
                    color: ltrclr
            	});
            });
        }			
    }

    if($('#rtServerChart').length) {
		var smoothie = new SmoothieChart({interpolation:'bezier',timestampFormatter:SmoothieChart.timeFormatter});
		smoothie.streamTo(document.getElementById("rtServerChart"));
		var line1 = new TimeSeries();
		var line2 = new TimeSeries();
		var line3 = new TimeSeries();
		smoothie.addTimeSeries(line1, { strokeStyle:'rgb(0, 255, 0)', fillStyle:'rgba(0, 255, 0, 0.3)', lineWidth:3 });
		smoothie.addTimeSeries(line2, { strokeStyle:'rgb(255, 0, 255)', fillStyle:'rgba(255, 0, 255, 0.3)', lineWidth:3 });
		smoothie.addTimeSeries(line3, { strokeStyle:'rgb(0, 82, 255)', fillStyle:'rgba(0, 82, 255, 0.3)', lineWidth:3 });
		
		function captureData() {
			$.ajax({
				url: ajaxurl + "?action=csl_generic_ajax_call&q=sinfo&s=" + beTXT.gnonce,
				type: "GET",
				dataType: "json",
				success: function(data) {
					line1.append(new Date().getTime(), data.cpu_usage_1);
					line2.append(new Date().getTime(), data.cpu_usage_5);
					line3.append(new Date().getTime(), data.cpu_usage_15);
					$('#txtCPU1').html(data.cpu_usage_1.toLocaleString() + '%');
					$('#txtCPU5').html(data.cpu_usage_5.toLocaleString() + '%');
					$('#txtCPU15').html(data.cpu_usage_15.toLocaleString() + '%');
					var nProc = parseFloat($('#txtPROC').html());
					var sSuff = null;
					if(nProc > data.processes) {
						sSuff = '<span class="dashicons dashicons-arrow-down statusMark-OK"></span>';
					} else if(nProc < data.processes) {
						sSuff = '<span class="dashicons dashicons-arrow-up statusMark-ERROR"></span>';
					} else {
						sSuff = '<span class="dashicons dashicons-leftright"></span>';
					}
					$('#txtPROC').html(data.processes.toLocaleString() + '&nbsp;' + sSuff);
				}
			});
			setTimeout(captureData, 5000);
		}
		captureData();
    }
    
    $('.autolookup').each(function(index, v) {
        $(v).suggest(ajaxurl + '?action=csl_self_ajax_lookup&q=' + $(v).val() + '&pt=' + ($(v).attr('data-pt') ? $(v).attr('data-pt') : 'post'));
    });               
            
	$("input[name$='_rng']").on('change', function(){
        $('#' + $(this).attr('name').slice(0, -4)).val($(this).val()); 
		$('#' + $(this).attr('name').slice(0, -4) + '_val').val($(this).val());
        total_rank_SIRIUSM();
	});

	$("input[name$='_val']").on('change', function(){
        $('#' + $(this).attr('name').slice(0, -4)).val($(this).val()); 
		$('#' + $(this).attr('name').slice(0, -4) + '_rng').val($(this).val());
        total_rank_SIRIUSM();
	});

    $(".verifyURL").each( function(index, value) {
        sURL = $(value).val();
        verifyURLGeneral(sURL, $(value));        
    });

    $(".verifyRSS").each( function(index, value) {
        sURL = $(value).val();
        verifyURLRSS(sURL, $(value));        
    });
    
    $(".verifyEMail").each( function(index, value) {
        if($(value).val()) {
            $(value).removeClass('field-verifyOK');
            $(value).removeClass('field-verifyERROR');
            if($(value).val())
                $(value).addClass(isValidEmailAddress($(this).val()) ? 'field-verifyOK' : 'field-verifyERROR');
        }
    });

    $(".verifyPhone").each( function(index, value) {
        if($(value).val()) {
            $(value).removeClass('field-verifyOK');
            $(value).removeClass('field-verifyERROR');
            var phone = $(value).val(),
                intRegex = /[0-9 -()+]+$/;
            if((phone.length < 6) || (!intRegex.test(phone))) {
                 $(value).addClass('field-verifyERROR');
            } else {
                 $(value).addClass('field-verifyOK');
            } 
        }  
    });

    $(".jeoquery").each( function(index, value) {
        if($(value).val()) {
            $(value).removeClass('field-verifyOK');
            $(value).removeClass('field-verifyERROR');
            var city = $(value).val();
            if(city.split('; ').length !== 3) {
                 $(value).addClass('field-verifyERROR');
            } else {
                 $(value).addClass('field-verifyOK');
            } 
        }  
    });
    
    $(".jeoquerycountries").each( function(index, value) {
        if($(value).val()) {
            $(value).removeClass('field-verifyOK');
            $(value).removeClass('field-verifyERROR');
            var city = $(value).val();
            if(city.length == 0) {
                 $(value).addClass('field-verifyERROR');
            } else {
                 $(value).addClass('field-verifyOK');
            } 
        }  
    });

    $(".autolookup").each( function(index, value) {
        if($(value).val()) {
            $(value).removeClass('field-verifyOK');
            $(value).removeClass('field-verifyERROR');
            var element = $(value).val();
            var aElm = element.split(': ');
            if(aElm.length !== 2) {
                $(value).addClass('field-verifyERROR');
            } else {
	            if(isNaN(aElm[0])) {
                	$(value).addClass('field-verifyERROR');
	            } else {
                	$(value).addClass('field-verifyOK');
	            }
            } 
        }  
    });
    
    $(".mandatory").each( function(index, value) {
        if(!$(value).val()) {
        	$(value).removeClass('field-verifyEMPTY').addClass('field-verifyERROR')        
        } else {
        	$(value).addClass('field-verifyOK')        	        
        }
    });
    
	$(".verifyURL").on('change', function(){
        var elnam = '#waitSign' + $(this).attr('name');
        $(elnam).fadeIn('slow').animate({opacity: 1.0}, 100).effect("pulsate", { times: 2 }, 500).fadeOut('slow');
        verifyURLGeneral($(this).val(), $(this));
	});
    
	$(".verifyRSS").on('change', function(){
        var elnam = '#waitSign' + $(this).attr('name');
        $(elnam).fadeIn('slow').animate({opacity: 1.0}, 100).effect("pulsate", { times: 2 }, 500).fadeOut('slow');
        verifyURLRSS($(this).val(), $(this));
	});

	$(".verifyEMail").on('change', function(){
        $(this).removeClass('field-verifyOK');
        $(this).removeClass('field-verifyERROR');
        if($(this).val())
            $(this).addClass(isValidEmailAddress($(this).val()) ? 'field-verifyOK' : 'field-verifyERROR');
	});

    $(".verifyPhone").on("change", function() {
        $(this).removeClass('field-verifyOK');
        $(this).removeClass('field-verifyERROR');
        var phone = $(this).val(),
            intRegex = /[0-9 -()+]+$/;
        if((phone.length < 6) || (!intRegex.test(phone))) {
             $(this).addClass('field-verifyERROR');
        } else {
             $(this).addClass('field-verifyOK');
        }   
    });

    $(".jeoquery").on("change", function(e) {
        if($(this).val()) {
            $(this).removeClass('field-verifyOK');
            $(this).removeClass('field-verifyERROR');
            var city = $(this).val();
            if(city.split('; ').length !== 3) {
                 $(this).addClass('field-verifyERROR');
            } else {
                 $(this).addClass('field-verifyOK');
            } 
        }  
    });

    $(".autolookup").on("change", function() {
        if($(this).val()) {
            $(this).removeClass('field-verifyOK');
            $(this).removeClass('field-verifyERROR');
            var element = $(this).val();
            var aElm = element.split(': ');
            if(aElm.length !== 2) {
                $(this).addClass('field-verifyERROR');
            } else {
	            if(isNaN(aElm[0])) {
                	$(this).addClass('field-verifyERROR');
	            } else {
                	$(this).addClass('field-verifyOK');
	            }
            } 
        }  
    });
    
    $(".mandatory").on("keyup", function() {
        //console.log(this.value.length)
        if(this.value.length < 1) {
        	$(this).removeClass('field-verifyEMPTY').removeClass('field-verifyOK').addClass('field-verifyERROR');        
        } else {
        	$(this).removeClass('field-verifyEMPTY').removeClass('field-verifyERROR').addClass('field-verifyOK');        
        }
    });
    
    if($('.avoc').length) {
	    $(".avoc").autocomplete({
	        source: function(request, response) {
	            $.ajax({
	                url: "http://es.wikipedia.org/w/api.php",
	                dataType: "jsonp",
	                data: {
	                    'action': "opensearch",
	                    'format': "json",
	                    'search': request.term
	                },
	                success: function(data) {
	                    response(data[1]);
	                }
	            });
	        }
	    });
    }

	$("#printDomainHelpBtn").on('click', function(){
        $("#help-valuation-domains").printThis({
            debug: false,               
            importCSS: true,            
            importStyle: false,         
            printContainer: true,       
            pageTitle: "",             
            removeInline: false,       
            printDelay: 333,           
            header: null,               
            formValues: true            
        });
	});
  
	$("#printDomainModelBtn").on('click', function(){
        $("#help-valuation-models").printThis({
            debug: false,               
            importCSS: true,            
            importStyle: false,         
            printContainer: true,       
            pageTitle: "",              
            removeInline: false,        
            printDelay: 333,            
            header: null,               
            formValues: true           
        });
	});
  
	$("#printImportantQuestionsBtn").on('click', function(){
        $("#importantQuestions").printThis({
            debug: false,               
            importCSS: true,            
            importStyle: false,        
            printContainer: true,       
            pageTitle: "",              
            removeInline: false,        
            printDelay: 333,           
            header: null,               
            formValues: true            
        });
	});
  
	$("#printSessionsBtn").on('click', function(){
        $("#div_user_sessions").printThis({
            debug: false,              
            importCSS: true,            
            importStyle: false,        
            printContainer: true,       
            pageTitle: "",              
            removeInline: false,        
            printDelay: 333,            
            header: null,               
            formValues: true            
        });
	});
  
	$("#printPostsBtn").on('click', function(){
        $("#div_user_posts").printThis({
            debug: false,               
            importCSS: true,            
            importStyle: false,        
            printContainer: true,       
            pageTitle: "",              
            removeInline: false,       
            printDelay: 333,            
            header: null,               
            formValues: true            
        });
	});
	
	/*** FORM VERIFICATION START ***/
	$("form#post #publish").on("click", function( event ) {
		//event.preventDefault();
		
		if($('.field-verifyERROR').length < 1) {
			$("#post").submit();
		} else {
			var msgtxt = '';
			$('.field-verifyERROR').each( function(index, value) {
				msgtxt += "- " + $("label[for='"+$(value).attr('id')+"']").text() + "\n";
		    });
			if(confirm(beTXT.verif_error_text + "\n\n" + msgtxt + "\n\n" + beTXT.verif_error_ques)) {
				$("#post").submit();	
			} else {
				return false;	
			}	
		}
		
	});
	/*** FORM VERIFICATION END ***/

});

/**
 * Automark parent-children terms in hierarchical custom taxonomies
 */

function synchronize_child_and_parent_category($) {
    $('#tax_topicchecklist, #tax_artwork_typechecklist').find('input').each(function(index, input) {
        $(input).bind('change', function() {
            var checkbox = $(this);
            var is_checked = $(checkbox).is(':checked');
            if(is_checked) {
                $(checkbox).parents('li').children('label').children('input').attr('checked', 'checked');
            } else {
                $(checkbox).parentsUntil('ul').find('input').removeAttr('checked');
            }
        });
    });
}
 

/**
 * Similar post title search
 */
 
jQuery( '<div id="stresults"></div>' ).insertAfter( "input#title[type='text']" );

/**
 * Similar post title search
 * -------------------------
 * highlight v4
 * Highlights arbitrary terms.
 * <http://johannburkard.de/blog/programming/javascript/highlight-javascript-text-higlighting-jquery-plugin.html>
 * MIT license.
 * Johann Burkard
 * <http://johannburkard.de>
 * <mailto:jb@eaio.com>
 */

jQuery.fn.highlight = function(pat) {
    function innerHighlight(node, pat) {
        var skip = 0;
        if (node.nodeType == 3) {
            var pos = node.data.toUpperCase().indexOf(pat);
            if (pos >= 0) {
                var spannode = document.createElement('span');
                spannode.className = 'highlight';
                var middlebit = node.splitText(pos);
                var endbit = middlebit.splitText(pat.length);
                var middleclone = middlebit.cloneNode(true);
                spannode.appendChild(middleclone);
                middlebit.parentNode.replaceChild(spannode, middlebit);
                skip = 1;
            }
        } else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
            for (var i = 0; i < node.childNodes.length; ++i) {
                i += innerHighlight(node.childNodes[i], pat);
            }
        }
        return skip;
    }
    return this.length && pat && pat.length ? this.each(function() {
        innerHighlight(this, pat.toUpperCase());
    }) : this;
};

jQuery.fn.removeHighlight = function() {
    return this.find("span.highlight").each(function() {
        this.parentNode.firstChild.nodeName;
        with (this.parentNode) {
            replaceChild(this.firstChild, this);
            normalize();
        }
    }).end();
};

/*
 * jQuery ajax actions
 */
 
jQuery("#st-screen-options-apply").on('click', function(){
	var stlimit   = jQuery('#_csl_st_screen_options_limit').val();
	var stminchar = jQuery('#_csl_st_screen_options_minchar').val();
	jQuery.post(ajaxurl,{stlimit: stlimit, stminchar: stminchar, action:'csl_st_ajax_hook_sc'}, function(t){
		var e=t.substr(0,t.length-1);
	})
	.done(function() { jQuery(".metabox-prefs .success").fadeIn(500).delay(1000).fadeOut(1500); })
	.fail(function() { jQuery(".metabox-prefs .error").fadeIn(500).delay(1000).fadeOut(1500); });
});

jQuery("#title").on('keyup', function(){
	jQuery("#stresults").html('<div class="spinner"></div>');
	var sttitle = jQuery(this).val();
	jQuery.post(ajaxurl,{sttitle: sttitle, action:'csl_st_ajax_hook'},function(t){
		var e=t.substr(0,t.length-1);
		jQuery("#stresults").html(e);
		jQuery('#stresults').highlight(sttitle);
	})
});


// Start CP Helper

jQuery(function(jQuery) {
	
	jQuery('#media-items').bind('DOMNodeInserted',function(){
		jQuery('input[value="Insert into Post"]').each(function(){
				jQuery(this).attr('value','Use This Image');
		});
	});
	
	jQuery('.custom_upload_image_button').click(function() {
		postID = jQuery(this).data('cp-post-id');
		formfield = jQuery(this).siblings('.custom_upload_image');
		preview = jQuery(this).siblings('.custom_preview_image');
		tb_show('', 'media-upload.php?post_id='+postID+'&type=image&TB_iframe=true');
		window.send_to_editor = function(html) {
			imgurl = jQuery('img',html).attr('src');
			classes = jQuery('img', html).attr('class');
			id = classes.replace(/(.*?)wp-image-/, '');
			formfield.val(id);
			preview.attr('src', imgurl);
			tb_remove();
		};
		return false;
	});
	
	jQuery('.custom_clear_image_button').click(function() {
		var defaultImage = jQuery(this).parent().siblings('.custom_default_image').text();
		jQuery(this).parent().siblings('.custom_upload_image').val('');
		jQuery(this).parent().siblings('.custom_preview_image').attr('src', defaultImage);
		return false;
	});
	
	jQuery('.repeatable-add').click(function() {
		field = jQuery(this).closest('td').find('.custom_repeatable li:last').clone(true);
		fieldLocation = jQuery(this).closest('td').find('.custom_repeatable li:last');
		jQuery('input', field).val('').removeClass('field-verifyOK').removeClass('field-verifyERROR').addClass('field-verifyEMPTY').attr('name', function(index, name) {
			return name.replace(/(\d+)/, function(fullMatch, n) {
				return Number(n) + 1;
			});
		});
		field.insertAfter(fieldLocation, jQuery(this).closest('td'));
		
		var inputField = jQuery('input', field);
		if(inputField.hasClass('verifyRSS')) {
			inputField.on('change', function(){
				verifyURLRSS(inputField.val(), inputField);
			});		
		}
		if(inputField.hasClass('verifyHTML')) {
			inputField.on('change', function(){
				verifyURLGeneral(jQuery(this).val(), jQuery(this));
			});		
		}
		if(inputField.hasClass('autolookup')) {
            inputField.suggest(ajaxurl + '?action=csl_self_ajax_lookup&q=' + inputField.val() + '&pt=' + (inputField.attr('data-pt') ? inputField.attr('data-pt') : 'post'));
		}

        jQuery( document ).ready.apply();

		if(jQuery(this).closest('td').find('.custom_repeatable li').length > 1) {
			jQuery(this).closest('td').find('.custom_repeatable li').each( function(i, e) {
				jQuery(e).find('.repeatable-remove').each( function(idx, elm) {
					jQuery(elm).removeClass('ahref-disabled').addClass('ahref-enabled');	
				});	
			});	
		}
		return false;
		
	});
	
	jQuery('.repeatable-remove').click(function(){
		if(jQuery(this).parent().index() > 0) {
			jQuery(this).parent().remove();
		} else {
			jQuery(this).removeClass('ahref-enabled').addClass('ahref-disabled');
			alert(cpTXT.cant_delete_only_one_field);
		}
		return false;
	});
		
	jQuery('.custom_repeatable').sortable({
		opacity: 0.6,
		revert: true,
		cursor: 'move',
		handle: '.sort'
	});

    jQuery(".datepicker").datepicker();
    jQuery(".datepicker").on("click", function(e) { /* console.log(jQuery(this).datepicker()) */ })

});

// End CP Helper

function setColor(p){
    var red = p<50 ? 255 : Math.round(256 - (p-50)*5.12);
    var green = p>50 ? 255 : Math.round((p)*5.12);
    return "rgb(" + red + "," + green + ",0)";
}

function total_rank_SIRIUSM() {
    var sTOT = 0;
    var nMAX = jQuery("input[name$='_rng']").length;
	jQuery("input[name$='_rng']").each(function(i, selected){ 
		sTOT += parseInt(jQuery(selected).val()); 
	});
    var sPER = Math.round(sTOT / nMAX);
	var color = setColor(sPER * nMAX);
    var numStar = 0;
	jQuery("span[id^='sta_']").each(function(i, selected){
        jQuery(selected).removeClass('dashicons-star-empty');
        jQuery(selected).removeClass('dashicons-star-filled');
		jQuery(selected).css({ "color": color}); 
        jQuery(selected).addClass('dashicons-star-' + (numStar < sPER ? 'filled' : 'empty'));
        numStar++;
	});
}

jQuery(function($) {
	$("#exportTXT").click(function(){
    	$("#txt-container").table2excel({
    		exclude: ".noExl",
    		name: bTXT.report
    	}); 
	});
});

jQuery(function($) {
	$("#exportRPT").click(function(){
    	$("#rpt-container").table2excel({
    		exclude: ".noExl",
    		name: bTXT.report
    	}); 
	});
});

jQuery(function($) {
	$("#exportSTA").click(function(){
    	$("#sta-container").table2excel({
    		exclude: ".noExl",
    		name: bTXT.report
    	}); 
	});
});

jQuery(function($) {
	$("#exportPRP").click(function(){
    	$("#prp-container").table2excel({
    		exclude: ".noExl",
    		name: bTXT.report
    	}); 
	});
});

function findFeeds() {
    jQuery('#btnSrchURL').html('<i id="statusMark" class="fa fa-cog fa-spin statusMark-WORKING" style="margin-right: 10px;"></i>' + beTXT.searching);
    jQuery('#btnSrchURL').attr('disabled','disabled');    
    // Direct URIs
	var xQRY	= jQuery("input[name='_cp__ent_url']")[0].value;
	var sURL	= 'http://ajax.googleapis.com/ajax/services/feed/lookup?v=1.0&q=' + xQRY;
	var isOK	= true;
	var aFee	= [];
	var jqxhr	= jQuery.ajax({
		url: sURL,
		dataType: 'jsonp'
	})
	.done(function(data, textStatus, jqXHR) {
		if(data.responseData) {
			isOK = true;
			aFee.push(data.responseData.url);		
		} else {
			isOK = false;
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		isOK = false;	
	})
	.always(function(a, b, c) {
		if(isOK) {
            var sTXT = ICON_SUCCESS_LEFT + beTXT.linked_rss_text + '<br />' + beTXT.query + ': <strong>' + xQRY + '</strong>. <strong>' + aFee.length.toLocaleString() + '</strong> ' + beTXT.results;
            sTXT += '<ol>';
			jQuery.each(aFee, function(index, value) {
				sTXT += '<li><a href="' + value + '" target="_blank">' + value + '</a>.</li>' + PHP_EOL;
			});
			sTXT += '</ol>' + PHP_EOL;
		} else {
			var sTXT = xQRY == '' ? 
                ICON_ERROR_LEFT + beTXT.not_enough_rss_info : 
                ICON_ERROR_LEFT + beTXT.not_linked_rss_info + '<br />' + beTXT.query + ': ' +  xQRY;
		}
        jQuery("#vrfyRSSStatus").html(sTXT); 
	});			
    jQuery('#btnSrchURL').html('<i id="statusMark" class="fa fa-check-circle statusMark-OK" style="margin-right: 10px;"></i>' + beTXT.record_checked);
    jQuery('#btnSrchURL').removeAttr('disabled');
}

function findRelated() {
    jQuery('#btnSrchURL').html('<i id="statusMark" class="fa fa-cog fa-spin statusMark-WORKING" style="margin-right: 10px;"></i>' + beTXT.searching);
    jQuery('#btnSrchURL').attr('disabled','disabled');
    // Related URIs
	var xQRY	= jQuery("input[name='_cp__ent_town']")[0].value + ' ' + jQuery("input[name='post_title']").val();
	var sURL	= 'https://ajax.googleapis.com/ajax/services/feed/find?v=1.0&q=' + xQRY;
	var isOK	= true;
	var aFee	= [];
	var jqxhr	= jQuery.ajax({
		url: sURL,
		dataType: 'jsonp'
	})
	.done(function(data, textStatus, jqXHR) {
		if(data.responseData) {
			isOK = true;
            jQuery.each(data.responseData.entries, function(i, v) {
    			aFee.push(v);		
            });
		} else {
			isOK = false;
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		isOK = false;	
	})
	.always(function(a, b, c) {
		if(isOK) {
			var sTXT = xQRY == ' ' ? 
                ICON_ERROR_LEFT + beTXT.not_enough_related_info : 
                ICON_SUCCESS_LEFT + beTXT.related_text + '<br />' + beTXT.query + ': <strong>' + xQRY + '</strong>. <strong>' + aFee.length.toLocaleString() + '</strong> ' + beTXT.results;
            sTXT += '<ol>';
			jQuery.each(aFee, function(index, value) {
				sTXT += '<li><i class="fa fa-external-link"></i>&nbsp;' + 
                '<a href="' + value.link + '" target="_blank">' + cleanHTMLTags(value.title) + '</a>.&nbsp;<small>' + cleanHTMLTags(value.contentSnippet).substr(0, 50) + '</small>' + 
                PHP_EOL;
			});
			sTXT += '</ol>' + PHP_EOL;
		} else {
			var sTXT = ICON_ERROR_LEFT + beTXT.not_related_rss_info + '<br />' + beTXT.query + ': ' +  xQRY;
		}
        var recErrors = 0;
        jQuery('.verifyURL, .verifyRSS, verifyPhone, .verifyEMail').each(function(key, val) {
            recErrors += jQuery(this).hasClass('field-verifyERROR') ? 1 : 0;            
        });
        jQuery("#vrfyFieldsStatus").html( recErrors > 0 ? 
            ICON_ERROR_LEFT + beTXT.verified_fields_error + ': ' + recErrors.toLocaleString() : 
            ICON_SUCCESS_LEFT + beTXT.verified_fields_no_error);
        jQuery("#vrfyRelatedStatus").html(sTXT);       
	});
    jQuery('#btnSrchURL').html('<i id="statusMark" class="fa fa-check-circle statusMark-OK" style="margin-right: 10px;"></i>' + beTXT.record_checked);
    jQuery('#btnSrchURL').removeAttr('disabled')
}    

function verifyURLGeneral(sURL, oField) {
    var elnam = '#waitSign' + oField.attr('name');
    var isOK = isUrl(sURL);
    if(isOK) {
    	var jqxhr	= jQuery.ajax({
    		url: sURL,
    		type: 'GET'
    	})
    	.done(function(data, textStatus, jqXHR) {
            if(data.results) {
        		isOK = (textStatus == 'success' && data.results.length > 0 && data.results[0].toLowerCase().indexOf('<html') > -1);
            } else {
                isOK = false;
            }
    	})
    	.fail(function(jqXHR, textStatus, errorThrown) {
    		isOK = false;	
    	})
    	.always(function(a, b, c) {
	    	oField.removeClass('field-verifyEMPTY');
            oField.removeClass('field-verifyOK');
            oField.removeClass('field-verifyERROR');
            oField.addClass(isOK ? (sURL.toLowerCase().indexOf('https://') > -1 ? 'field-verifyNOTVERYFIABLE' : 'field-verifyOK') : (sURL.toLowerCase().indexOf('https://') > -1 ? 'field-verifyNOTVERYFIABLE' : 'field-verifyEROR'));
            jQuery(elnam).hide();
    	});			
    } else {
    	oField.removeClass('field-verifyEMPTY');
        oField.removeClass('field-verifyOK');
        oField.removeClass('field-verifyERROR');
        if(sURL == "") {
            oField.addClass('field-verifyEMPTY');
        } else {
            if(sURL.toLowerCase().indexOf('https://') > -1) {
                oField.addClass('field-verifyNOTVERYFIABLE');
            } else {
                oField.addClass('field-verifyERROR');
            }
        }
    }
    jQuery(elnam).hide();
}

function verifyURLRSS(sURL, oField) {
    var elnam = '#waitSign' + oField.attr('name');
    var isOK = isUrl(sURL);
    var sCompleteURL = 'https://ajax.googleapis.com/ajax/services/feed/load?v=1.0&q=' + sURL;
    if(isOK) {
    	var jqxhr	= jQuery.ajax({
    		url: sCompleteURL,
			dataType: 'jsonp'
    	})
		.done(function(data, textStatus, jqXHR) {
			isOK = (data.responseStatus == '200');
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			isOK = false;	
		})
    	.always(function(a, b, c) {
            console.log(a)
            console.log(b)
            console.log(c)
	    	oField.removeClass('field-verifyEMPTY');
            oField.removeClass('field-verifyOK');
            oField.removeClass('field-verifyERROR');
            oField.addClass(isOK ? 'field-verifyOK' : 'field-verifyERROR');
            jQuery(elnam).hide();
    	});			
    } else {
    	oField.removeClass('field-verifyEMPTY');
        oField.removeClass('field-verifyOK');
        oField.removeClass('field-verifyERROR');
        if(sURL == "") {
            oField.addClass('field-verifyEMPTY');
        } else {
            if(sURL.toLowerCase().indexOf('.facebook.com') > -1) {
                oField.addClass('field-verifyNOTVERYFIABLE');
            } else {
                oField.addClass('field-verifyERROR');
            }
        }
    }
    jQuery(elnam).hide();
}

function verifyHTML(data) {
   return data.toLowerCase().indexOf('<html') > -1; 
}

function cleanHTMLTags(sTXT) {
    return jQuery('<p>' + sTXT + '</p>').text();
}

function isUrl(s) {
    var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
    return regexp.test(s);
}

function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(emailAddress);
};

/*
 * LIBRARIES 
 * -------------------------------------------------------------------------*/
 
/**
 * jQuery.ajax mid - CROSS DOMAIN AJAX 
 * ---
 * @author James Padolsey (http://james.padolsey.com)
 * @version 0.11
 * @updated 12-JAN-10
 * ---
 * Note: Read the README!
 * ---
 * @info http://james.padolsey.com/javascript/cross-domain-requests-with-jquery/
 */

jQuery.ajax = (function(_ajax){
    
    var protocol = location.protocol,
        hostname = location.hostname,
        exRegex = RegExp(protocol + '//' + hostname),
        YQL = 'http' + (/^https/.test(protocol)?'s':'') + '://query.yahooapis.com/v1/public/yql?callback=?',
        query = 'select * from html where url="{URL}" and xpath="*"';
    
    function isExternal(url) {
        return !exRegex.test(url) && /:\/\//.test(url);
    }
    
    return function(o) {
        
        var url = o.url;
        
        if ( /get/i.test(o.type) && !/json/i.test(o.dataType) && isExternal(url) ) {
            
            // Manipulate options so that JSONP-x request is made to YQL
            
            o.url = YQL;
            o.dataType = 'json';
            
            o.data = {
                q: query.replace(
                    '{URL}',
                    url + (o.data ?
                        (/\?/.test(url) ? '&' : '?') + jQuery.param(o.data)
                    : '')
                ),
                format: 'xml'
            };
            
            // Since it's a JSONP request
            // complete === success
            if (!o.success && o.complete) {
                o.success = o.complete;
                delete o.complete;
            }
            
            o.success = (function(_success){
                return function(data) {
                    
                    if (_success) {
                        // Fake XHR callback.
                        _success.call(this, {
                            responseText: (data.results[0] || '')
                                // YQL screws with <script>s
                                // Get rid of them
                                .replace(/<script[^>]+?\/>|<script(.|\s)*?\/script>/gi, '')
                        }, 'success');
                    }
                    
                };
            })(o.success);
            
        }
        
        return _ajax.apply(this, arguments);
        
    };
    
})(jQuery.ajax);

 
/* @license 
 * jQuery.print, version 1.3.0
 *  (c) Sathvik Ponangi, Doers' Guild
 * Licence: CC-By (http://creativecommons.org/licenses/by/3.0/)
 *--------------------------------------------------------------------------*/
(function ($) {
    "use strict";
    // A nice closure for our definitions
    function getjQueryObject(string) {
        // Make string a vaild jQuery thing
        var jqObj = $("");
        try {
            jqObj = $(string)
                .clone();
        } catch (e) {
            jqObj = $("<span />")
                .html(string);
        }
        return jqObj;
    }

    function printFrame(frameWindow) {
        // Print the selected window/iframe
        var def = $.Deferred();
        try {
            setTimeout(function () {
                // Fix for IE : Allow it to render the iframe
                frameWindow.focus();
                try {
                    // Fix for IE11 - printng the whole page instead of the iframe content
                    if (!frameWindow.document.execCommand('print', false, null)) {
                        // document.execCommand returns false if it failed -http://stackoverflow.com/a/21336448/937891
                        frameWindow.print();
                    }
                } catch (e) {
                    frameWindow.print();
                }
                frameWindow.close();
                def.resolve();
            }, 250);
        } catch (err) {
            def.reject(err);
        }
        return def;
    }

    function printContentInNewWindow(content) {
        // Open a new window and print selected content
        var w = window.open();
        w.document.write(content);
        w.document.close();
        return printFrame(w);
    }

    function isNode(o) {
        /* http://stackoverflow.com/a/384380/937891 */
        return !!(typeof Node === "object" ? o instanceof Node : o && typeof o === "object" && typeof o.nodeType === "number" && typeof o.nodeName === "string");
    }
    $.print = $.fn.print = function () {
        // Print a given set of elements
        var options, $this, self = this;
        // console.log("Printing", this, arguments);
        if (self instanceof $) {
            // Get the node if it is a jQuery object
            self = self.get(0);
        }
        if (isNode(self)) {
            // If `this` is a HTML element, i.e. for
            // $(selector).print()
            $this = $(self);
            if (arguments.length > 0) {
                options = arguments[0];
            }
        } else {
            if (arguments.length > 0) {
                // $.print(selector,options)
                $this = $(arguments[0]);
                if (isNode($this[0])) {
                    if (arguments.length > 1) {
                        options = arguments[1];
                    }
                } else {
                    // $.print(options)
                    options = arguments[0];
                    $this = $("html");
                }
            } else {
                // $.print()
                $this = $("html");
            }
        }
        // Default options
        var defaults = {
            globalStyles: true,
            mediaPrint: false,
            stylesheet: null,
            noPrintSelector: ".no-print",
            iframe: true,
            append: null,
            prepend: null,
            manuallyCopyFormValues: true,
            deferred: $.Deferred()
        };
        // Merge with user-options
        options = $.extend({}, defaults, (options || {}));
        var $styles = $("");
        if (options.globalStyles) {
            // Apply the stlyes from the current sheet to the printed page
            $styles = $("style, link, meta, title");
        } else if (options.mediaPrint) {
            // Apply the media-print stylesheet
            $styles = $("link[media=print]");
        }
        if (options.stylesheet) {
            // Add a custom stylesheet if given
            $styles = $.merge($styles, $('<link rel="stylesheet" href="' + options.stylesheet + '">'));
        }
        // Create a copy of the element to print
        var copy = $this.clone();
        // Wrap it in a span to get the HTML markup string
        copy = $("<span/>")
            .append(copy);
        // Remove unwanted elements
        copy.find(options.noPrintSelector)
            .remove();
        // Add in the styles
        copy.append($styles.clone());
        // Appedned content
        copy.append(getjQueryObject(options.append));
        // Prepended content
        copy.prepend(getjQueryObject(options.prepend));
        if (options.manuallyCopyFormValues) {
            // Manually copy form values into the HTML for printing user-modified input fields
            // http://stackoverflow.com/a/26707753
            copy.find("input, select, textarea")
                .each(function () {
                    var $field = $(this);
                    if ($field.is("[type='radio']") || $field.is("[type='checkbox']")) {
                        if ($field.prop("checked")) {
                            $field.attr("checked", "checked");
                        }
                    } else if ($field.is("select")) {
                        $field.find(":selected")
                            .attr("selected", "selected");
                    } else {
                        $field.attr("value", $field.val());
                    }
                });
        }
        // Get the HTML markup string
        var content = copy.html();
        // Notify with generated markup & cloned elements - useful for logging, etc
        try {
            options.deferred.notify('generated_markup', content, copy);
        } catch (err) {
            console.warn('Error notifying deferred', err);
        }
        // Destroy the copy
        copy.remove();
        if (options.iframe) {
            // Use an iframe for printing
            try {
                var $iframe = $(options.iframe + "");
                var iframeCount = $iframe.length;
                if (iframeCount === 0) {
                    // Create a new iFrame if none is given
                    $iframe = $('<iframe height="0" width="0" border="0" wmode="Opaque"/>')
                        .prependTo('body')
                        .css({
                            "position": "absolute",
                            "top": -999,
                            "left": -999
                        });
                }
                var w, wdoc;
                w = $iframe.get(0);
                w = w.contentWindow || w.contentDocument || w;
                wdoc = w.document || w.contentDocument || w;
                wdoc.open();
                wdoc.write(content);
                wdoc.close();
                printFrame(w)
                    .done(function () {
                        // Success
                        setTimeout(function () {
                            // Wait for IE
                            if (iframeCount === 0) {
                                // Destroy the iframe if created here
                                $iframe.remove();
                            }
                        }, 100);
                    })
                    .fail(function () {
                        // Use the pop-up method if iframe fails for some reason
                        console.error("Failed to print from iframe", e.stack, e.message);
                        printContentInNewWindow(content);
                    })
                    .always(function () {
                        try {
                            options.deferred.resolve();
                        } catch (err) {
                            console.warn('Error notifying deferred', err);
                        }
                    });
            } catch (e) {
                // Use the pop-up method if iframe fails for some reason
                console.error("Failed to print from iframe", e.stack, e.message);
                printContentInNewWindow(content)
                    .always(function () {
                        try {
                            options.deferred.resolve();
                        } catch (err) {
                            console.warn('Error notifying deferred', err);
                        }
                    });
            }
        } else {
            // Use a new window for printing
            printContentInNewWindow(content)
                .always(function () {
                    try {
                        options.deferred.resolve();
                    } catch (err) {
                        console.warn('Error notifying deferred', err);
                    }
                });
        }
        return this;
    };
})(jQuery);

/*
 *  jQuery table2excel - v1.0.1
 *  jQuery plugin to export an .xls file in browser from an HTML table
 *  https://github.com/rainabba/jquery-table2excel
 *
 *  Made by rainabba
 *  Under MIT License
 */
//table2excel.js
;(function ( $, window, document, undefined ) {
		var pluginName = "table2excel",
				defaults = {
				exclude: ".noExl",
                name: "Table2Excel"
		};

		// The actual plugin constructor
		function Plugin ( element, options ) {
				this.element = element;
				// jQuery has an extend method which merges the contents of two or
				// more objects, storing the result in the first object. The first object
				// is generally empty as we don't want to alter the default options for
				// future instances of the plugin
				this.settings = $.extend( {}, defaults, options );
				this._defaults = defaults;
				this._name = pluginName;
				this.init();
		}

		Plugin.prototype = {
			init: function () {
				var e = this;
				e.template = "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\"><head><!--[if gte mso 9]><xml>";
				e.template += "<x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions>";
				e.template += "<x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>";
				e.tableRows = "";

				// get contents of table except for exclude
				$(e.element).find("tr").not(this.settings.exclude).each(function (i,o) {
					e.tableRows += "<tr>" + $(o).html() + "</tr>";
				});
				this.tableToExcel(this.tableRows, this.settings.name);
			},
			tableToExcel: function (table, name) {
				var e = this;
				e.uri = "data:application/vnd.ms-excel;base64,";
				e.base64 = function (s) {
					return window.btoa(unescape(encodeURIComponent(s)));
				};
				e.format = function (s, c) {
					return s.replace(/{(\w+)}/g, function (m, p) {
						return c[p];
					});
				};
				e.ctx = {
					worksheet: name || "Worksheet",
					table: table
				};
				window.location.href = e.uri + e.base64(e.format(e.template, e.ctx));
			}
		};

		$.fn[ pluginName ] = function ( options ) {
				this.each(function() {
						if ( !$.data( this, "plugin_" + pluginName ) ) {
								$.data( this, "plugin_" + pluginName, new Plugin( this, options ) );
						}
				});

				// chain jQuery functions
				return this;
		};

})( jQuery, window, document );


/*
 * printThis v1.5
 * @desc Printing plug-in for jQuery
 * @author Jason Day
 *
 * Resources (based on) :
 *              jPrintArea: http://plugins.jquery.com/project/jPrintArea
 *              jqPrint: https://github.com/permanenttourist/jquery.jqprint
 *              Ben Nadal: http://www.bennadel.com/blog/1591-Ask-Ben-Print-Part-Of-A-Web-Page-With-jQuery.htm
 *
 * Licensed under the MIT licence:
 *              http://www.opensource.org/licenses/mit-license.php
 *
 * (c) Jason Day 2014
 *
 * Usage:
 *
 *  $("#mySelector").printThis({
 *      debug: false,               * show the iframe for debugging
 *      importCSS: true,            * import page CSS
 *      importStyle: false,         * import style tags
 *      printContainer: true,       * grab outer container as well as the contents of the selector
 *      loadCSS: "path/to/my.css",  * path to additional css file - us an array [] for multiple
 *      pageTitle: "",              * add title to print page
 *      removeInline: false,        * remove all inline styles from print elements
 *      printDelay: 333,            * variable print delay
 *      header: null,               * prefix to html
 *      formValues: true            * preserve input/form values
 *  });
 *
 * Notes:
 *  - the loadCSS will load additional css (with or without @media print) into the iframe, adjusting layout
 */
;
(function($) {
    var opt;
    $.fn.printThis = function(options) {
        opt = $.extend({}, $.fn.printThis.defaults, options);
        var $element = this instanceof jQuery ? this : $(this);
        var strFrameName = "printThis-" + (new Date()).getTime();

        if (window.location.hostname !== document.domain && navigator.userAgent.match(/msie/i)) {
            // Ugly IE hacks due to IE not inheriting document.domain from parent
            // checks if document.domain is set by comparing the host name against document.domain
            var iframeSrc = "javascript:document.write(\"<head><script>document.domain=\\\"" + document.domain + "\\\";</script></head><body></body>\")";
            var printI = document.createElement('iframe');
            printI.name = "printIframe";
            printI.id = strFrameName;
            printI.className = "MSIE";
            document.body.appendChild(printI);
            printI.src = iframeSrc;

        } else {
            // other browsers inherit document.domain, and IE works if document.domain is not explicitly set
            var $frame = $("<iframe id='" + strFrameName + "' name='printIframe' />");
            $frame.appendTo("body");
        }

        var $iframe = $("#" + strFrameName);

        // show frame if in debug mode
        if (!opt.debug) $iframe.css({
            position: "absolute",
            width: "0px",
            height: "0px",
            left: "-600px",
            top: "-600px"
        });


        // $iframe.ready() and $iframe.load were inconsistent between browsers    
        setTimeout(function() {

            var $doc = $iframe.contents(),
                $head = $doc.find("head"),
                $body = $doc.find("body");

            // add base tag to ensure elements use the parent domain
            $head.append('<base href="' + document.location.protocol + '//' + document.location.host + '">');

            // import page stylesheets
            if (opt.importCSS) $("link[rel=stylesheet]").each(function() {
                var href = $(this).attr("href");
                if (href) {
                    var media = $(this).attr("media") || "all";
                    $head.append("<link type='text/css' rel='stylesheet' href='" + href + "' media='" + media + "'>")
                }
            });
            
            // import style tags
            if (opt.importStyle) $("style").each(function() {
                $(this).clone().appendTo($head);
                //$head.append($(this));
            });

            //add title of the page
            if (opt.pageTitle) $head.append("<title>" + opt.pageTitle + "</title>");

            // import additional stylesheet(s)
            if (opt.loadCSS) {
               if( $.isArray(opt.loadCSS)) {
                    jQuery.each(opt.loadCSS, function(index, value) {
                       $head.append("<link type='text/css' rel='stylesheet' href='" + this + "'>");
                    });
                } else {
                    $head.append("<link type='text/css' rel='stylesheet' href='" + opt.loadCSS + "'>");
                }
            }

            // print header
            if (opt.header) $body.append(opt.header);

            // grab $.selector as container
            if (opt.printContainer) $body.append($element.outer());

            // otherwise just print interior elements of container
            else $element.each(function() {
                $body.append($(this).html());
            });

            // capture form/field values
            if (opt.formValues) {
                // loop through inputs
                var $input = $element.find('input');
                if ($input.length) {
                    $input.each(function() {
                        var $this = $(this),
                            $name = $(this).attr('name'),
                            $checker = $this.is(':checkbox') || $this.is(':radio'),
                            $iframeInput = $doc.find('input[name="' + $name + '"]'),
                            $value = $this.val();

                        //order matters here
                        if (!$checker) {
                            $iframeInput.val($value);
                        } else if ($this.is(':checked')) {
                            if ($this.is(':checkbox')) {
                                $iframeInput.attr('checked', 'checked');
                            } else if ($this.is(':radio')) {
                                $doc.find('input[name="' + $name + '"][value=' + $value + ']').attr('checked', 'checked');
                            }
                        }

                    });
                }

                //loop through selects
                var $select = $element.find('select');
                if ($select.length) {
                    $select.each(function() {
                        var $this = $(this),
                            $name = $(this).attr('name'),
                            $value = $this.val();
                        $doc.find('select[name="' + $name + '"]').val($value);
                    });
                }

                //loop through textareas
                var $textarea = $element.find('textarea');
                if ($textarea.length) {
                    $textarea.each(function() {
                        var $this = $(this),
                            $name = $(this).attr('name'),
                            $value = $this.val();
                        $doc.find('textarea[name="' + $name + '"]').val($value);
                    });
                }
            } // end capture form/field values

            // remove inline styles
            if (opt.removeInline) {
                // $.removeAttr available jQuery 1.7+
                if ($.isFunction($.removeAttr)) {
                    $doc.find("body *").removeAttr("style");
                } else {
                    $doc.find("body *").attr("style", "");
                }
            }

            setTimeout(function() {
                if ($iframe.hasClass("MSIE")) {
                    // check if the iframe was created with the ugly hack
                    // and perform another ugly hack out of neccessity
                    window.frames["printIframe"].focus();
                    $head.append("<script>  window.print(); </script>");
                } else {
                    // proper method
                    $iframe[0].contentWindow.focus();
                    $iframe[0].contentWindow.print();
                }

                //remove iframe after print
                if (!opt.debug) {
                    setTimeout(function() {
                        $iframe.remove();
                    }, 1000);
                }

            }, opt.printDelay);

        }, 333);

    };

    // defaults
    $.fn.printThis.defaults = {
        debug: false,           // show the iframe for debugging
        importCSS: true,        // import parent page css
        importStyle: false,     // import style tags
        printContainer: true,   // print outer container/$.selector
        loadCSS: "",            // load an additional css file - load multiple stylesheets with an array []
        pageTitle: "",          // add title to print page
        removeInline: false,    // remove all inline styles
        printDelay: 333,        // variable print delay
        header: null,           // prefix to html
        formValues: true        // preserve input/form values
    };

    // $.selector container
    jQuery.fn.outer = function() {
        return $($("<div></div>").html(this.clone().css('display', 'block'))).html()
    }
})(jQuery);


/*
 * jQuery gvChart plugin
 * This plugin was created to simplify things when using Google Visualisation Charts.
 * All examples you will find on http://www.ivellios.toron.pl/technikalia/demos/gvChart/
 * @name jquery.gvChart.min.js
 * @author Janusz Kamieński - http://www.ivellios.toron.pl/technikalia
 * @category jQuery plugin google charts
 * @copyright (c) 2010 Janusz Kamieński (www.ivellios.toron.pl)
 * @license CC Attribution Works 3.0 Poland - http://creativecommons.org/licenses/by/3.0/pl/deed.en_US
 * @example Visit http://www.ivellios.toron.pl/technikalia/demos/gvChart/ for more informations about this jQuery plugin
 * @June 2012 Added swapping of tables columns and rows by Glenn Wilton
 * @March 2013 Added asynchronous loading with callback by Jason Gill
 * Use googleLoaded.done(function(){ //charts here }); for deferred usage.
 */
 var googleLoaded = jQuery.Deferred();
 (function ($){
 	$.getScript("http://www.google.com/jsapi", function() {
 		var gvChartCount = 0;
 		google.load('visualization', '1.1', {packages: ['corechart', 'geochart', 'treemap', 'gantt'], callback:function(){window.googleLoaded.resolve()}});
 		$.fn.gvChart = function(settings){
 			var $this = $(this),
	 			defaults={
	 				hideTable: true,
	 				chartType: 'AreaChart',
	 				chartDivID: 'gvChartDiv',
	 				gvSettings: null,
	 				swap: false,
	 				excludedColumns: null
	 			},
	 			el = $('<div>'),
	 			gvChartID = defaults.chartDivID+gvChartCount++,
	 			data,
	 			headers,
	 			rows;

 			el.attr('id',gvChartID);
 			el.addClass('gvChart');
 			el.attr('aria-hidden','true');

 			$.extend(defaults,settings);

            if(defaults.chartType == 'GeoChart') {
     			$this.before(el);
            } else {
     			$this.after(el);
            }

 			if(defaults.hideTable){
 				$this.hide();
 				$this.attr('aria-hidden','false');
 			}

 			data = new google.visualization.DataTable();

			// add X label
			data.addColumn('string','X labels');

			var headers = $this.find('thead th');
			var rows = $this.find('tbody tr');

			if(defaults.swap){
				headers.each(function(index){
					if(index){
    				    data.addColumn('number',$(this).text());
					}
				});
				data.addRows(rows.length);

				rows.each(function(index){
					data.setCell(index, 0, $(this).find('th').text());	   
				});

				rows.each(function(index){
					$(this).find('td').each(function(index2){
						data.setCell(index, index2+1 , parseFloat($(this).text().replace((csl_LANG_D == 'es_ES' ? '.' : ','),'').replace((csl_LANG_D == 'es_ES' ? ',' : '.'),'.').replace('%', ''),10) );
					});
				});		
			} else {
				// Add Columns
				rows.each(function(index){
					data.addColumn('number',$(this).find('th').text());
				});

				// Create data size
				data.addRows(headers.length-1);

				// Set the TITLE of each row
				headers.each(function(index){
					if(index){
						data.setCell(index-1, 0, $(this).text());
					}
				});

				// Populate with data
				rows.each(function(index){
					$(this).find('td').each(function(index2){
						data.setCell(index2, index+1, parseFloat($(this).text().replace((csl_LANG_D == 'es_ES' ? '.' : ','),'').replace((csl_LANG_D == 'es_ES' ? ',' : '.'),'.').replace('%', ''),10) );
					});
				});
			}

			if(defaults.excludedColumns) {
				nIterations = 0;
				$.each(defaults.excludedColumns, function( index, value ) {
					data.removeColumn(value - nIterations);
					nIterations++;
				});
			}
			defaults.gvSettings.title = $(this).find('caption').text();
			var chart = new google.visualization[defaults.chartType](document.getElementById(gvChartID));
			chart.draw(data, defaults.gvSettings);
            
			$(window).resize(function(){
	        	chart.draw(data, defaults.gvSettings);
	    	});
            
		}
	});
})(jQuery);

/* 
Based on: http: // wordpress . stackexchange . com / questions / 42652 / #answer-42729
These functions provide a simple way to interact with TinyMCE (wp_editor) visual editor.
This is the same thing that WordPress does, but a tad more intuitive.
Additionally, this works for any editor - not just the "content" editor.
Usage:
0) If you are not using the default visual editor, make your own in PHP with a defined editor ID:
  wp_editor( $content, 'tab-editor' );
  
1) Get contents of your editor in JavaScript:
  tmce_getContent( 'tab-editor' )
  
2) Set content of the editor:
  tmce_setContent( content, 'tab-editor' )
Note: If you just want to use the default editor, you can leave the ID blank:
  tmce_getContent()
  tmce_setContent( content )
  
Note: If using a custom textarea ID, different than the editor id, add an extra argument:
  tmce_getContent( 'visual-id', 'textarea-id' )
  tmce_getContent( content, 'visual-id', 'textarea-id')
  
Note: An additional function to provide "focus" to the displayed editor:
  tmce_focus( 'tab-editor' )
=========================================================
*/
function tmce_getContent(editor_id, textarea_id) {
  if ( typeof editor_id == 'undefined' ) editor_id = wpActiveEditor;
  if ( typeof textarea_id == 'undefined' ) textarea_id = editor_id;
  
  if ( jQuery('#wp-'+editor_id+'-wrap').hasClass('tmce-active') && tinyMCE.get(editor_id) ) {
    return tinyMCE.get(editor_id).getContent();
  }else{
    return jQuery('#'+textarea_id).val();
  }
}

function tmce_setContent(content, editor_id, textarea_id) {
  if ( typeof editor_id == 'undefined' ) editor_id = wpActiveEditor;
  if ( typeof textarea_id == 'undefined' ) textarea_id = editor_id;
  
  if ( jQuery('#wp-'+editor_id+'-wrap').hasClass('tmce-active') && tinyMCE.get(editor_id) ) {
    return tinyMCE.get(editor_id).setContent(content);
  }else{
    return jQuery('#'+textarea_id).val(content);
  }
}

function tmce_focus(editor_id, textarea_id) {
  if ( typeof editor_id == 'undefined' ) editor_id = wpActiveEditor;
  if ( typeof textarea_id == 'undefined' ) textarea_id = editor_id;
  
  if ( jQuery('#wp-'+editor_id+'-wrap').hasClass('tmce-active') && tinyMCE.get(editor_id) ) {
    return tinyMCE.get(editor_id).focus();
  }else{
    return jQuery('#'+textarea_id).focus();
  }
}

jQuery.fn.highlight = function (str, className) {
    var regex = new RegExp(str, "gi");
    return this.each(function () {
        jQuery(this).contents().filter(function() {
            return this.nodeType == 3 && regex.test(this.nodeValue);
        }).replaceWith(function() {
            return (this.nodeValue || "").replace(regex, function(match) {
                return "<span class=\"" + className + "\">" + match + "</span>";
            });
        });
    });
};

//////////////////////////////////////////////////////////////////////////////////////////////////
// Generic functions for users activity table heatmap
Array.max = function(array){
    return Math.max.apply(Math,array);
};
Array.min = function( array ){
    return Math.min.apply( Math, array );
};

