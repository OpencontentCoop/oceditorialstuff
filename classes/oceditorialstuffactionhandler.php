<?php

class OCEditorialStuffActionHandler
{

    /**
     * @var OCEditorialStuffPostFactoryInterface
     */
    protected $factory;

    protected function __construct( OCEditorialStuffPostFactoryInterface $factory )
    {
        $this->factory = $factory;
        $factoryConfiguration = $this->factory->getConfiguration();
        
    }

    public static function instance( OCEditorialStuffPostFactoryInterface $factory )
    {
        return new OCEditorialStuffActionHandler( $factory );
    }

    public function handleChangeState( OCEditorialStuffPost $post, eZContentObjectState $beforeState, eZContentObjectState $afterState )
    {

    }

    // out of the box actions
    public static function addLocation( OCEditorialStuffPost $post,
                                        $addLocationId )
    {

    }

    public static function removeLocation( OCEditorialStuffPost $post,
                                           $removeLocationId )
    {

    }

    public static function move( OCEditorialStuffPost $post, $locationId )
    {

    }
}