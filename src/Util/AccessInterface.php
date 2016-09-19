<?php

namespace UKMNorge\APIBundle\Util;

# AccessInterface
# Interface for en Access-klasse, som skal hente rettigheter fra UKM.no
# for å bestemme om en API-forespørsel tillates eller ikke.
interface AccessInterface {

	### Oppretter Access-objektet
	# Tar i mot 2 argumenter:
	# $api_key er en streng, API-nøkkelen til systemet som spør om tilgang.
	# $system er en streng, system-nøkkelen i API_PERM-tabellen i SS3.
	public function __construct($api_key, $sys_key, $sys_secret);

	### Setter interne variabler som got() bruker til å sjekke om spørringen er gyldig. 
	# Får UKMno til å signere request og sammenligner den med den signerte versjonen.
	## Input:
	# $request er et array med alle argumentene som brukes til signeringen.
	# $signed_request er en streng, den signerte versjonen av $request som kommer fra spørrende system.
	public function validate($data);

	### Sjekker om systemet har denne rettigheten.
	## Input:
	# $permission er en streng, navnet på tillatelsen det bes om.
	# Returnerer en boolsk true/false hvis tillatelsen blir henholdsvis godtatt eller nektet.
	public function got($permission);

	### Returnerer et array med eventuelle feil som har oppstått.
	public function errors();
}