<?php

class ThemeHouse_Controllers_Listener_LoadClass extends ThemeHouse_Listener_LoadClass
{

    protected function _getExtendedClasses()
    {
        return array(
            'ThemeHouse_Controllers' => array(
                'route_prefix' => array(
                    'XenForo_Route_PrefixAdmin_AddOns'
                ),
                'datawriter' => array(
                    'XenForo_DataWriter_RoutePrefix'
                ),
            ),
        );
    }

    public static function loadClassRoutePrefix($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_Controllers_Listener_LoadClass', $class, $extend, 'route_prefix');
    }

    public static function loadClassDataWriter($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_Controllers_Listener_LoadClass', $class, $extend, 'datawriter');
    }
}