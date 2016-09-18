<?php

namespace UKMNorge\APIBundle\Util;

use UKMNorge\APIBundle\Util\AccessInterface;

### DispatcherInterface
## Dette interfacet implementeres av hvert API som skal tilgjengeliggjøres i et system.
## Objektet som implementerer dette kan være en standard SymfonyController, Service eller ren PHP.
interface DispatcherInterface {

	### Setter objektet som brukes for å godkjenne tilgang til APIet.
	public function setAccessInterface(AccessInterface $access);

	### Kaller rett funksjon for dataene satt i construct om den finnes, og returnerer et objekt med følgende data:.
	## Inputs:
	# $version - alfanumerisk, versjonsnummeret på APIet
	# $category - streng
	# $action - streng
	### Outputs:
	## Ved suksess:
	# stdClass
	#	->success = true
	#	->errors = array() - kan være tomt. Inneholder eventuelle feil fra sub-funksjoner.??
	#	->data = object
	## Ved feil, som at funksjonen ikke finnes:
	# stdClass
	#	->success = false
	#	->errors = array() - kan være tomt, men BØR ikke være det.
	public function call($version, $category, $action);	
	
}