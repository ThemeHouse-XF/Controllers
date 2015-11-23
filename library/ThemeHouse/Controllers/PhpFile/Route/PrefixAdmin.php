<?php

class ThemeHouse_Controllers_PhpFile_Route_PrefixAdmin extends ThemeHouse_RoutePrefixes_PhpFile_Route_Prefix
{

    public function __construct($className, array $options)
    {
        parent::__construct($className, $options);
        
        if (!empty($this->_options['title_plural'])) {
            $this->setPhpDoc(
                array(
                    'Route prefix handler for ' . strtolower($this->_options['title_plural']) .
                         ' in the admin control panel.'
                ));
        }
    }

    /**
     *
     * @see ThemeHouse_Controllers_PhpFile_Route_Prefix::_createFunctionMatch()
     */
    protected function _createFunctionMatch()
    {
        $function = $this->createFunction('match');
        $function->setPhpDoc(
            array(
                'Match a specific route for an already matched prefix.',
                '',
                '@see XenForo_Route_Interface::match()'
            ));
        $function->setSignature(
            array(
                '$routePath',
                'Zend_Controller_Request_Http $request',
                'XenForo_Router $router'
            ));
        $controller = (!empty($this->_options['class']) ? $this->_options['class'] : str_replace(
            'Route_Prefix', 'Controller', $this->_className));
        $majorSection = (!empty($this->_options['major_section']) ? $this->_options['major_section'] : 'applications');
        $primaryKeyId = (!empty($this->_options['primary_key_id']) ? $this->_options['primary_key_id'] : 'id');
        $body = array(
            '$action = $router->resolveActionWithIntegerParam($routePath, $request, \'' . $primaryKeyId . '\');',
            '$action = $router->resolveActionAsPageNumber($action, $request);',
            'return $router->getRouteMatch(\'' . $controller . '\', $action, \'' . $majorSection . '\');'
        );
        $function->setBody($body);
        return $function;
    }
}