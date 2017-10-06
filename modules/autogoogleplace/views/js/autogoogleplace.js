/**
 * NOTICE OF LICENSE.
 *
 * @file Get Google Maps Place API and select your path 
 *
 * This source file is subject to a commercial license from Agence Malttt SAS
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the Agence Malttt SAS is strictly forbidden.
 * INFORMATION SUR LA LICENCE D'UTILISATION
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Agence Malttt SAS
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part d'Agence Malttt SAS est expressement interdite.
 *
 * @author    Matthieu Deroubaix
 * @copyright Copyright (c) 2015-2016 Agence Malttt SAS - 90 Rue faubourg saint martin - 75010 Paris
 * @license   Commercial license
 * Support by mail  :  support@agence-malttt.fr
 * Phone : +33.972535133
 */

		var placeSearch, autocomplete;
		var componentForm = {
		  street_number: 'short_name',
		  route: 'short_name',
		  locality: 'long_name',
		  postal_code: 'short_name',
		  country: 'long_name'
		};
		function initialize(id_address_input) {
			if(document.getElementById(id_address_input)){

				if(typeof google == "undefined"){
					return false;
				}
				autocomplete = new google.maps.places.Autocomplete(
				    (document.getElementById(id_address_input)),
				    { types: ['geocode'] }
				);				

				google.maps.event.addListener(autocomplete, 'place_changed', function() {
				    

				    	var inputAutocomplete = document.getElementById(id_address_input);
					
				    	if($('#'+id_address_input).closest('form').find('input[type="text"][id*="city"]').length > 0){
				    		var city_input = $('#'+id_address_input).closest('form').find('input[type="text"][id*="city"]').eq(0).attr('id');
				    	}

				    	if($('#'+id_address_input).closest('form').find('input[type="text"][id*="postcode"]').length > 0){
				    		var postcode_input = $('#'+id_address_input).closest('form').find('input[type="text"][id*="postcode"]').eq(0).attr('id');
				    	}

				    	if($('#'+id_address_input).closest('form').find('select[id*="country"]').length > 0){
				    		var country_input = $('#'+id_address_input).closest('form').find('select[id*="country"]').eq(0).attr('id');
				    	}

				    	var returnFill = fillInAddress(city_input,postcode_input);
						google.maps.event.clearListeners(inputAutocomplete, "focus");
						google.maps.event.clearListeners(inputAutocomplete, "blur");

						// Bugfix uniform.js
						if (typeof $.uniform == 'object') {
							$.uniform.update();
						}

						// Bugfix
						// google.maps.event.clearListeners(inputAutocomplete, "keydown");
						// $('#'+id_address_input).unbind("keypress");
						
						document.getElementById(id_address_input).value = "";
				    	
				    	if(typeof returnFill !== 'undefined') {

					    	for (var i = 0; i < returnFill.length; i++) {
					    		if(typeof returnFill[i] !== "undefined"){
									document.getElementById(id_address_input).value = document.getElementById(id_address_input).value + returnFill[i] ;
					    		}
					    	}

				    	}
				
				    	$('#'+id_address_input+',#'+city_input+',#'+postcode_input).addClass('input-success');

				});

			  }
		}

		// [START region_fillform]
		function fillInAddress(city_input,postcode_input) {

		  var place = autocomplete.getPlace();
		  
		  for (var component in componentForm) {
		  	if(document.getElementById(component)){
		    	document.getElementById(component).value = '';
		    	document.getElementById(component).disabled = false;
		  	}
		  }

		  if(typeof place !== 'undefined' && typeof place.address_components !== 'undefined'){
				 
			  for (var i = 0; i < place.address_components.length; i++) {
			   
			    var addressType = place.address_components[i].types[0];
			   
			    if (componentForm[addressType]) {
							    
			      var val = place.address_components[i][componentForm[addressType]];

			      if (typeof val != "undefined" && val){

				      if (addressType == "street_number") {
				      	var street_number = val + " " ;
				      }  

				      if (addressType == "route") {
				      	var route = val;
				      }

				      if (typeof city_input !== 'undefined' && addressType == "locality") {
				      	document.getElementById(city_input).value = val; 
				      	$(city_input).trigger('keyup').trigger('blur');
				      }

				      if (typeof postcode_input !== 'undefined' && addressType == "postal_code") {
				      	document.getElementById(postcode_input).value = val; 
				      	$(postcode_input).trigger('keyup').trigger('blur');
				      }

				      if (typeof country_input !== 'undefined' && addressType == "country") {
						$(country_input)
    					.filter(function(i, e) { return $(e).text() == val}).prop('selected', true);
				      }
				      
			      }
			    }
			  }

			var ret = [street_number,route];
		  }
		
		return ret;

		}
		// [END region_fillform]

	function loadAddressHelper(){
		if($('#address1').length > 0){
			var id_address_input = 'address1';
		}else if($('input#address').length > 0){
			var id_address_input = 'address';
		}else if($('#street').length > 0){
			var id_address_input = 'street';
		}else if($('#road').length > 0){
			var id_address_input = 'road';
		}else if($('input[type="text"][id*="address"]').length > 0){
			var id_address_input = $('input[type="text"][id*="address"]').eq(0).attr('id');
		}else if($('textarea[id*="address"]').length > 0){
			var id_address_input = $('textarea[id*="address"]').eq(0).attr('id');
		}

		if(typeof id_address_input != 'undefined'){
			initialize(id_address_input);

			var gmap_input_selected = document.getElementById(id_address_input);
			google.maps.event.addDomListener(gmap_input_selected, 'keydown', function(e) { 
			    if (e.keyCode == 13) { 
			        e.preventDefault(); 
			    }
			  }); 
			// Prevent some misunderstanding
			$(id_address_input).attr('autocomplete','off');
		}
	}

	if(typeof google !== 'undefined' && typeof google.maps !== 'undefined' && typeof google.maps.places !== 'undefined' && typeof google.maps.places.Autocomplete !== 'undefined'){

		loadAddressHelper();

	}else{

        (function(app) {

	      app.loadMap = function() {
			loadAddressHelper();
	      };

	      app.loadGoogleMapsScript = function () {

	      	if(typeof mapsapikey !== 'undefined') {		      		

		        var script = document.createElement('script');
		        script.type = 'text/javascript';
		        script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&' +
		          'callback=app.loadMap' +
		          '&key='+mapsapikey;
		        document.body.appendChild(script);

	      	}else{
	      		alert('Please define your Google Maps API Key in Autogooglesuggest module backoffice.');
	      	}

	      };

	    }(window.app = window.app || {}));

	    window.onload = app.loadGoogleMapsScript;

	}
