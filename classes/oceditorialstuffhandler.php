<?php

class OCEditorialStuffHandler implements OCEditorialStuffHandlerInterface
{
    const INTERVAL_MONTH = 'P1M';
    const PICKER_DATE_FORMAT = 'd-m-Y';
    const FULLDAY_IDENTIFIER_FORMAT = 'Y-n-j';
    const DAY_IDENTIFIER_FORMAT = 'j';
    const MONTH_IDENTIFIER_FORMAT = 'n';
    const YEAR_IDENTIFIER_FORMAT = 'Y'; 
    
    protected $interval;
    
    protected $startDateArray;
    
    protected $facets = null;
    
    protected $filters = array();

    protected $sortArray = array( 'published' => 'desc' );
    
    protected $query = '';

    protected $factoryIdentifier;

    /**
     * @var OCEditorialStuffPostFactoryInterface
     */
    protected $factory;

    private static $instances = array();

    /**
     * @return OCEditorialStuffHandlerInterface[]
     */
    final public static function instances()
    {
        $data = array();
        $ini = eZINI::instance( 'editorialstuff.ini' );
        $availableFactories = $ini->variable( 'AvailableFactories', 'Identifiers' );
        foreach( $availableFactories as $identifier )
        {
            $data[$identifier] = self::instance( $identifier );
        }
        return $data;
    }

    /**
     * @param string $factoryIdentifier
     * @param array $factoryParameters
     *
     * @return OCEditorialStuffHandlerInterface
     */
    final public static function instance( $factoryIdentifier, $factoryParameters = array() )
    {
        ksort( $factoryParameters );
        $key = md5( $factoryIdentifier . json_encode( $factoryParameters ) );
        if ( !isset( self::$instances[$key] ) )
        {
            $handlerClassName = 'OCEditorialStuffHandler';
            $factory = OCEditorialStuffPostFactory::instance(
                $factoryIdentifier,
                $factoryParameters
            );
            $factoryConfiguration = $factory->getConfiguration();
            if ( isset( $factoryConfiguration['HandlerClassName'] ) )
            {
                $handlerClassName = $factoryConfiguration['HandlerClassName'];
            }

            self::$instances[$key] = new $handlerClassName( $factoryIdentifier, $factory );
        }
        return self::$instances[$key];
    }

    final public static function instanceFromFactory( OCEditorialStuffPostFactoryInterface $factory )
    {
        $factoryConfiguration = $factory->getConfiguration();
        return self::instance( $factoryConfiguration['identifier'], $factoryConfiguration['RuntimeParameters'] );

    }

    protected function __construct( $factoryIdentifier, $factory )
    {
        $this->factoryIdentifier = $factoryIdentifier;
        $this->factory = $factory;
        $this->setStartDate( date( self::YEAR_IDENTIFIER_FORMAT ), date( self::MONTH_IDENTIFIER_FORMAT ), date( self::DAY_IDENTIFIER_FORMAT ) );
    }

    /**
     * @return OCEditorialStuffPostFactoryInterface
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @param $id
     * @return OCEditorialStuffPost
     * @throws Exception
     */
    public function fetchByObjectId( $id )
    {
        $this->setFilters( array( 'meta_id_si:' . $id ) );
        $results = $this->fetchResult( 1, 0 );
        if( count( $results ) > 0 )
        {
            return $results[0];
        }
        throw new Exception( "Post $id not found" );
    }

    public function fetchByNodeId( $id )
    {
        $this->setFilters( array( 'meta_node_id_si:' . $id ) );
        $results = $this->fetchResult( 1, 0 );
        if( count( $results ) > 0 )
        {
            return $results[0];
        }
        throw new Exception( "Post node $id not found" );
    }

    protected function parseParameters( $parameters )
    {
        if ( isset( $parameters['interval'] ) && $parameters['interval'] )
        {
            $this->setInterval( $parameters['interval'] );
        }
        if ( isset( $parameters['state'] ) && $parameters['state']  )
        {
            $this->setState( $parameters['state'] );
        }
        if ( isset( $parameters['query'] ) && $parameters['query']  )
        {
            $this->setQuery( $parameters['query'] );
        }
        if ( isset( $parameters['tag'] ) && $parameters['tag']  )
        {
            $this->setTag( $parameters['tag'] );
        }
        if ( isset( $parameters['sort'] ) && $parameters['sort']  )
        {
            $this->setSortArray( $parameters['sort'] );
        }
        if ( isset( $parameters['filters'] ) && $parameters['filters']  )
        {
            $this->setFilters( $parameters['filters'] );
        }
    }

    public function fetchItems( $parameters )
    {
        $this->parseParameters( $parameters );
        $result = (array) $this->fetchResult( $parameters['limit'], $parameters['offset'] );
        return $result;
    }

    public function fetchItemsCount( $parameters )
    {
        $this->parseParameters( $parameters );
        $result = intval( $this->fetchCount() );
        return $result;
    }

    protected function setFilters( $array )
    {
        $this->filters = $array;
    }

    protected function setSortArray( $array )
    {
        $this->sortArray = $array;
    }

    protected function setQuery( $query )
    {
        $this->query = $query;
        return $this;
    }

    protected function setState( $state )
    {
        if ( is_array( $state ) )
        {
            $stateFilter = array();
            foreach( $state as $s )
            {
                $stateFilter[] = 'meta_object_states_si:' . $s;
            }
            $this->filters[] = $stateFilter;
        }
        else
        {
            $this->filters[] = 'meta_object_states_si:' . $state;
        }
        return $this;
    }

    protected function setTag( $tag )
    {        
        $solrIdentifier = false;
        foreach( $this->factory->fields() as $field )
        {
            if ( $field['object_property'] == 'tags' )
            {
                $solrIdentifier = $field['solr_identifier'];
                break;
            }
        }
        
        if ( $solrIdentifier )
        {
            if ( is_array( $tag ) )
            {
                $tagFilter = array();
                foreach( $tag as $s )
                {
                    $tagFilter[] = $solrIdentifier . ':' . $s;
                }
                $this->filters[] = $tagFilter;
            }
            else
            {
                $this->filters[] = $solrIdentifier . ':' . $tag;
            }
        }
        return $this;
    }

    protected function setInterval( $interval )
    {        
        $this->interval = $interval;
        $this->filters[] = $this->getDateFilter();
        return $this;
    }

    protected function setStartDate( $year, $month, $day )
    {
        $this->startDateArray = array(
            'hour' => '00',
            'minute' => '00',
            'second' => '00',
            'month' => $month,
            'day' => $day,
            'year' => $year
        );
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return OCEditorialStuffPost[]
     */
    protected function fetchResult( $limit = 10, $offset = 0 )
    {
        $solrResult = $this->fetch( $limit, $offset );
        $result = array();
        if ( $solrResult['SearchCount'] > 0 )
        {
            foreach( $solrResult['SearchResult'] as $item )
            {
                $result[] = $this->getFactory()->instanceFromEzfindResultArray( $item );
            }
        }
        
        return $result;   
    }

    protected function fetchCount()
    {
        $solrResult = $this->fetch( 1, 0 );
        return $solrResult['SearchCount'];   
    }

    protected function fetch( $limit = 10, $offset = 0 )
    {        
        $fieldsToReturn = array();
        foreach( $this->getFactory()->fields() as $field )
        {
            $fieldsToReturn[] = $field['solr_identifier'];
        }

        $fullAccess = eZUser::currentUser()->hasAccessTo( 'editorialstuff', 'full_dashboard' );
        if ( $fullAccess['accessWord'] != 'yes' )
        {
            $this->filters[] = 'meta_owner_id_si:' . eZUser::currentUserID();
        }
        
        $fullAccess = eZUser::currentUser()->hasAccessTo( 'editorialstuff', 'full_dashboard' );
        if ( $fullAccess['accessWord'] != 'yes' )
        {
            $this->filters[] = 'meta_owner_id_si:' . eZUser::currentUserID();
        }
        
        $solrFetchParams = array(
            'SearchOffset' => $offset,
            'SearchLimit' => $limit,
            'Facet' => $this->facets,
            'SortBy' => $this->sortArray,
            'Filter' => $this->filters,
            'SearchContentClassID' => array( $this->getFactory()->classIdentifier() ),
            'SearchSectionID' => null,
            'SearchSubTreeArray' => $this->getFactory()->repositoryRootNodes(),
            'AsObjects' => false,
            'SpellCheck' => null,
            'IgnoreVisibility' => null,
            'Limitation' => null,
            'BoostFunctions' => null,
            'QueryHandler' => 'ezpublish',
            'EnableElevation' => true,
            'ForceElevation' => true,
            'SearchDate' => null,
            'DistributedSearch' => null,
            'FieldsToReturn' => $fieldsToReturn,
            'SearchResultClustering' => null,
            'ExtendedAttributeFilter' => array()
        );        
        $solrSearch = new OCSolr();
        $solrResult = $solrSearch->search( $this->query, $solrFetchParams );
        return $solrResult;
    }
        
    protected function getDateFilter()
    {
        $startDateTime = DateTime::createFromFormat( 'H i s n j Y', implode( ' ', $this->startDateArray ), self::timezone() );
        $startTimeStamp = $startDateTime->format( 'U' );
        
        $endDateTime = clone $startDateTime;
        $this->addInterval( $endDateTime );
        $endTimeStamp = $endDateTime->format( 'U' );

        $startDate = ezfSolrDocumentFieldBase::preProcessValue( $startTimeStamp, 'date' );        
        $endDate = ezfSolrDocumentFieldBase::preProcessValue( $endTimeStamp , 'date' );        
       
        if ( $endTimeStamp > $startTimeStamp )
            return array( 'meta_published_dt:[' . $startDate . ' TO ' . $endDate . ']' );
        else
            return array( 'meta_published_dt:[' . $endDate . ' TO ' . $startDate . ']' );
    }
    
    protected function addInterval( DateTime $date )
    {        
        if ( strpos( $this->interval, '-' ) !== false )
        {
            $interval = substr( $this->interval, 1 );
            $interval = new DateInterval( $interval );
            if ( !$interval instanceof DateInterval )
            {
                throw new Exception( "Intervallo non valido: {$this->interval}" );
            }
            $date->sub( $interval );
        }
        else
        {
            $interval = new DateInterval( $this->interval );
            if ( !$interval instanceof DateInterval )
            {
                throw new Exception( "Intervallo non valido: {$this->interval}" );
            }
            $date->add( $interval );
        }
        return $date;
    }
    
    public static function timezone()
    {
        //@todo
        return new DateTimeZone( 'Europe/Rome' );
    }

}