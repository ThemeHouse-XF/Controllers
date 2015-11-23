<?php

/**
 * Route prefix handler for admin controllers in the admin control panel.
 */
class ThemeHouse_Controllers_Route_PrefixAdmin_AdminControllers implements XenForo_Route_Interface
{

    /**
     * Match a specific route for an already matched prefix.
     *
     * @see XenForo_Route_Interface::match()
     */
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $GLOBALS['ThemeHouse_Controllers_Route_PrefixAdmin_AdminControllers'] = $this;
        
        $action = $router->resolveActionWithStringParam($routePath, $request, 'class');
        
        return $router->getRouteMatch('ThemeHouse_Controllers_ControllerAdmin_ControllerAdmin', $action,
            'adminControllers');
    }

    /**
     * Method to build a link to the specified page/action with the provided
     * data and params.
     *
     * @see XenForo_Route_BuilderInterface
     */
    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        return XenForo_Link::buildBasicLinkWithStringParam($outputPrefix, $action, $extension, $data, 'class');
    }
}