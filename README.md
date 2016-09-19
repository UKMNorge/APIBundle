APIBundle
=========

Symfony-Bundle for å autorisere tilgang til og eksponere data fra et system som et API.

APIBundle spesifiserer to interface det er viktig å vite om, [`AccessInterface`](src/Util/AccessInterface.php) og [`DispatcherInterface`](src/Util/DispatcherInterface.php). `DispatcherInterface` beskriver hvilke funksjoner som må implementeres i Entry-pointet til APIet.

```
interface DispatcherInterface {
	### Setter objektet som brukes for å godkjenne tilgang til APIet.
	public function setAccessInterface(AccessInterface $access);

	### Kaller rett funksjon for dataene satt i construct om den finnes, og returnerer et objekt med følgende data:.
	public function call($version, $category, $action);	
}
```

`AccessInterface` beskriver funksjonene som må implementeres i en Access-klasse, som validerer om en tjeneste har rettigheter til å hente ut informasjonen.

Eksempel på bruk
----------------
RSVP er et godt eksempel på bruk av dette systemet. Alt som kreves for å eksponere data til et API er å legge til bundlen i composer.json, opprette parameterne ukmapi_system og ukmapi_dispatcher, og opprette en klasse som implementerer `DispatcherInterface`.

```
class APIService implements DispatcherInterface {

	private $access;

	public function __construct($container) {
		$this->container = $container;
	}

	public function setAccessInterface(AccessInterface $access) {
		$this->access = $access;
	}

	# Returnerer et stdClass-objekt.
	public function call($version, $category, $action) {
		$res = new stdClass();
		// Vi har bare en versjon for tiden:
		switch($version) {
			case 'v1':
				$res = $this->callV1($category, $action);
				break;
			default:
				$res->success = false;
				$res->errors[] = 'UKMRSVPBundle:APIService: API-versjonen du spurte etter finnes ikke!';
		}
		return $res;
	}
[...]
}
```
*(Utdrag fra [APIService fra RSVP](https://github.com/UKMNorge/UKMRSVP/blob/master/src/UKMNorge/RSVPBundle/Services/APIService.php))*

APIBundle må også legges til i routing.yml, helst med prefixet /api/:
```
ukmapi:
    resource: "@UKMAPIBundle/Resources/config/routing.yml"
    prefix:   /api/
```

Når dette er gjort finner man APIet på http://*tjeneste*/api/. En vanlig adresse for å f.eks hente ut alle events i RSVP er `http://rsvp.ukm.no/api/v1/events/all/`. Dispatcheren må kalle AccessInterface->got($permission) for alle handlinger som krever autentisering. Eksempelkode fra [APIService](https://github.com/UKMNorge/UKMRSVP/blob/master/src/UKMNorge/RSVPBundle/Services/APIService.php):
```
private function events($action) {
	$res = new stdClass();
	switch($action) {
		### List alle Events.
		case 'all':
			// Ensure that the requester is allow this data
			if($this->access->got('readEvents')){
				$res->success = true;
				$res->data = $this->listAllEventsAction();
			} else {
				$res->success = false;
				$res->errors[] = 'UKMRSVPBundle:APIService: Du har ikke tilgangen som kreves for denne handlingen!';
			}
			break;
		default:
			$res->success = false;
			$res->errors[] = 'UKMRSVPBundle:APIService: Dataene du spurte etter finnes ikke!';
	}

	return $res;
}
```