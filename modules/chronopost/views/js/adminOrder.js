/**
  * MODULE PRESTASHOP OFFICIEL CHRONOPOST
  * 
  * LICENSE : All rights reserved - COPY AND REDISTRIBUTION FORBIDDEN WITHOUT PRIOR CONSENT FROM OXILEO
  * LICENCE : Tous droits réservés, le droit d'auteur s'applique - COPIE ET REDISTRIBUTION INTERDITES
* SANS ACCORD EXPRES D'OXILEO
  *
  * @author    Oxileo SAS <contact@oxileo.eu>
  * @copyright 2001-2017 Oxileo SAS
  * @license   Proprietary - no redistribution without authorization
  */

$( document ).ready(function() {
	if(lt) {
		setInactive();
		$("#shipping_table").append("<tr><td></td><td></td><td></td><td></td><td></td>"
		+"<td colspan=\"2\"><a class=\"cancelSkybill\" href=\"\">Annuler cet envoi</a></td></tr>");
	}

	$("#chronoSubmitButton").on('click', function(e) {
		if(lt) {
	  		e.preventDefault();
	  		document.location.href=path+"/skybills/"+lt+".pdf";
	  		return false;
	  	}
      $("#chrono_form").submit();
  		$(this).prop('disabled', true);
  	});

  	$(".cancelSkybill").on('click', function(e) {
  		e.preventDefault();
  		if(confirm("Êtes-vous sûr de vouloir annuler cet envoi ? La lettre de transport associée sera inutilisable.")) {
  			$.get(path+"/async/cancelSkybill.php", { skybill: lt, shared_secret: chronopost_secret, id_order: $("input[name=id_order]").val()}).done( function( data ) {
          alert('Lettre de transport bien annulée.');
  				location.reload();
			});
  		}
  	});
});

function setInactive() {
	$("#chronoSubmitButton").val("Ré-imprimer l'étiquette Chronopost");
}