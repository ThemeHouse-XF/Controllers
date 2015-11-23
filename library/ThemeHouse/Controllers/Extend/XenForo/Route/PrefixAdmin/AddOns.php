<?php
if (false) {

    class XFCP_ThemeHouse_Controllers_Extend_XenForo_Route_PrefixAdmin_AddOns extends XenForo_Route_PrefixAdmin_AddOns
    {
    }
}

class ThemeHouse_Controllers_Extend_XenForo_Route_PrefixAdmin_AddOns extends XFCP_ThemeHouse_Controllers_Extend_XenForo_Route_PrefixAdmin_AddOns
{

    /**
     * Match a specific route for an already matched prefix.
     *
     * @see XenForo_Route_Interface::match()
     */
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $xenOptions = XenForo_Application::get('options');
        
        if ($xenOptions->th_controllers_enableAddOnChooser) {
            $action = $router->resolveActionWithStringParam($routePath, $request, 'addon_id');
            
            if ($request->getParam('addon_id') == 'admin-controllers') {
                $action = 'admin-controllers' . $action;
                $request->setParam('addon_id', '');
            } elseif ($request->getParam('addon_id') == 'public-controllers') {
                $action = 'public-controllers' . $action;
                $request->setParam('addon_id', '');
            }
            
            if (strlen($action) >= strlen('admin-controllers') &&
                 substr($action, 0, strlen('admin-controllers')) == 'admin-controllers') {
                return $router->getRouteMatch('ThemeHouse_Controllers_ControllerAdmin_ControllerAdmin',
                    substr($action, strlen('admin-controllers')), 'adminControllers');
            } elseif (strlen($action) >= strlen('public-controllers') &&
                 substr($action, 0, strlen('public-controllers')) == 'public-controllers') {
                return $router->getRouteMatch('ThemeHouse_Controllers_ControllerAdmin_ControllerPublic',
                    substr($action, strlen('public-controllers')), 'publicControllers');
            }
        }
        
        return parent::match($routePath, $request, $router);
    }
}