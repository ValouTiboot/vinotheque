<?php 
require_once('dump.php');

// DEMANDE D'UN JETON D'AUTHENTIFICATION

try 
{
	ini_set("soap.wsdl_cache_enabled", "0");
	ini_set("default_socket_timeout", 480);  
	
	// Url du contrat wsdl
	$proxywsdl = "http://api.dolist.net/V2/AuthenticationService.svc?wsdl";
	$location = "http://api.dolist.net/V2/AuthenticationService.svc/soap1.1";
	
	// Génération du proxy
	$client = new SoapClient($proxywsdl, array('trace' => 1, 'location' => $location));            

	// Renseigner la clé d'authentification avec l'identifiant client
	$authenticationInfos	= array('AuthenticationKey' => 'ujnhtgnhqEEiZtWlbezl4+wgrOb2tJtTwz0fPj3RAG0rbqWF/9fwfdfrtKZSsi6mGmI3JMwUycDAeReZvEDkOqg==','AccountID' => 123);
	$authenticationRequest	= array('authenticationRequest' => $authenticationInfos);

	// Demande du jeton d'authentification
	$result = $client->GetAuthenticationToken($authenticationRequest);

	echo "Authentification ok";
	echo "<br>";	
	
	if (!is_null($result->GetAuthenticationTokenResult) and $result->GetAuthenticationTokenResult != '') {
		if ($result->GetAuthenticationTokenResult->Key != '') {
			echo "Informations concernant le token d'authentification :";
			echo "<br>";				
			// Afficher le token
			print "<pre>";
			print_r($result->GetAuthenticationTokenResult);
		}
		else {
			echo "Problème sur le token d'authentification";
			echo "<br>";	
			// Afficher le token
			print "<pre>";
			print_r($result->GetAuthenticationTokenResult);
		}
	}
	else 
	{
		echo "Le token d'authentification est null.";
	}
}
//Gestion d'erreur
catch(SoapFault $fault) 
{

	print dump($fault);

	$Detail = $fault->detail;

	echo "<hr><h3>Error</h3>";
	echo "Message : ".$Detail->ServiceException->Message;
	echo "<br>";
	echo "Description : ".$Detail->ServiceException->Description;
}
?>