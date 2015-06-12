# OpenContent Editorial Stuff

## Introduzione
L'estensione **OpenContent Editorial Stuff** serve ad esporre alla redazione web una dashboard di pubblicazione orientata al workflow. Permette di configurare più dashboard ciascuna orientata a una classe di contenuto.

Di default l'estensione permette:

 * l'asseganzione di stati, 
 * un motore di ricerca filtrabile per stati, 
 * l'accesso alla modifica dei contenuti, 
 * l'inserimento *smart and mobile* dei file media, 
 * l'invio di mail collaborative a uno o più utenti con il contentuto dell'oggetto
 * l'invio di tweet o feed Facebook (se NGPush è stata correttamente configurata)
 * la visualizzazione della cronologia delle modifiche
 * la visualizzazione di commenti interni

## Requisiti
 * eZPublish Legacy versione > 4.7
 * Estensione OCBootstrap
 * Estensione OCSearchTools
 * Estensione OCOperatorsCollection
 * Estensione NGPush (opzionale)
 
## Installazione

 * Clonare l'estensione nella cartella `<ez_legacy_root>/extension`
 * Importare nel database la tabella schema.sql
 * Rigenerare autoloads e svuotare cache ini e templates
 * Customizzare il file `editorialstuff.ini` in un suo override 
 
## Utilizzo e customizzazione

Ciascuna dashboard corrisponde a una configurazione presente nel file `editorialstuff.ini`.

Le dashboard attive sono quelle presenti in `AvailableFactories/Identifiers` ciascuna raggiungibile dal percorso `http://<your_domain>/editorialstuff/dashboard/<identifier>`.

Di default l'estensione viene rilasciata con un dashboard **demo**, raggiungibile da `http://<your_domain>/editorialstuff/dashboard/demo`, le cui impostazioni sono definite nel blocco `demo` (esattemente come l'identifier).


	[demo]	
	ClassIdentifier=folder
	CreationRepositoryNode=2
	CreationButtonText=Crea nuova cartella
	RepositoryNodes[]
	RepositoryNodes[]=1
	AttributeIdentifiers[]
	AttributeIdentifiers[images]=images
	AttributeIdentifiers[videos]=video
	AttributeIdentifiers[audios]=audio
	AttributeIdentifiers[tags]=argomento
	StateGroup=test
	States[draft]=Bozza
	States[published]=Pubblicato
	#ClassName=YourCustomPHPClass

 - in `ClassIdentifier`va definito l'identificatore della classe in cui oggetti saranno visualizzati e gestiti in dashboard
 - in `CreationRepositoryNode` va specificato il nodo parent ove vengono creati nuovi oggetti attraverso il bottone presente in dashbaord 
 - in `CreationButtonText`va definita l'etichetta del bottone di cui al punto precedente
 - in `RepositoryNodes` vanno definiti i nodi parent da cui fecciare gli oggetti in dashboard
 - in `AttributeIdentifiers` vanno mappati gli attributi della classe: images, videos e audios devono essere attributi di tipo ***Relazioni Oggetti (eZObjectRelationList)***, tags di tipo ***eZTags***. Sono opzionali, se non vengono specificati alcune funzionalità non saranno disponibili
 - in `StateGroup`e in `States`vanno specificati il gruppo e gli stati (identificatore=>nome) che la dashboard prenderà in considerazione (se non sono presenti nel sistema, l'estensione provvederà a crearli)

Infine nel parametro opzionale `ClassName` è possibile specificare la classe PHP che si occuperà di gestire ciascun singolo post. Se il parametro non è specificato verrà utilizzata la classe `OCEditorialStuffPostDefaultFactory`

Definire una classe custom serve a customizzare ciò che avviene al passaggio di stato: la classe deve infatti estendere la classe astratta `OCEditorialStuffPostFactory` e perciò implemetare il metodo `onChangeState`, ad esempio:
	
	class MyCustomEditorialStuffPostFactory extends OCEditorialStuffPostFactory
	{
		// se lo stato dell'oggetto viene cambiato da 'foo' a 'bar' 
		// all'oggetto viene aggiunta una collocazione in Media

		public function onChangeState( 
			OCEditorialStuffPost $post, 
			eZContentObjectState $beforeState, 
			eZContentObjectState $afterState 
		)
		{
			$currentObject = $post->getObject();
			if ( $beforeState->attribute( 'identifier' ) == 'foo' 
				 && $afterState->attribute( 'identifier' ) == 'bar' )
			{
				eZContentOperationCollection::addAssignment( 
					$currentObject->attribute( 'main_node_id' ), 
					$currentObject->attribute( 'id' ), 
					array( eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'MediaRootNode' ) ) 
				);
			}	
		}
	}
Da notare che l'oggetto OCEditorialStuffPost $post incapsula un eZContentObject. 
Nella classe di default il metodo onChangeState produce solamente un Notice di esempio.

Per attivare i commenti interni è necessario creare un attributo di tipo ***Commenti (ezcomComments)*** nella classe specificata: l'attributo ***deve*** avere come identificatore `internal_comment`

