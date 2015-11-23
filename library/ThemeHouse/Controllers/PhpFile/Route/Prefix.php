<?php

class ThemeHouse_Controllers_PhpFile_Route_Prefix extends ThemeHouse_PhpFile
{

    protected $_options;

    public function __construct($className, array $options)
    {
        parent::__construct($className);
        
        $this->_options = $options;
        
        if (!empty($this->_options['title_plural'])) {
            $this->setPhpDoc(
                array(
                    'Route prefix handler for ' . strtolower($this->_options['title_plural']) . ' in the public system.'
                ));
        }
        $this->setImplements('XenForo_Route_Interface');
        
        $this->_createFunctionMatch();
        $this->_createFunctionBuildLink();
    }

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
        $controller = (!empty($this->_class['controller_public']) ? $this->_class['controller_public'] : str_replace(
            'Route_Prefix', 'ControllerPublic', $this->_className));
        $majorSection = (!empty($this->_options['major_section']) ? $this->_options['major_section'] : 'forum');
        $primaryKeyId = (!empty($this->_options['primary_key_id']) ? $this->_options['primary_key_id'] : 'id');
        $body = array(
            '$action = $router->resolveActionWithIntegerParam($routePath, $request, \'' . $primaryKeyId . '\');',
            '$action = $router->resolveActionAsPageNumber($action, $request);',
            'return $router->getRouteMatch(\'' . $controller . '\', $action, \'' . $majorSection . '\');'
        );
        $function->setBody($body);
        return $function;
    }

    protected function _createFunctionBuildLink()
    {
        $function = $this->createFunction('buildLink');
        $function->setPhpDoc(
            array(
                'Method to build a link to the specified page/action with the provided',
                'data and params.',
                '',
                '@see XenForo_Route_BuilderInterface'
            ));
        $function->setSignature(
            array(
                '$originalPrefix',
                '$outputPrefix',
                '$action',
                '$extension',
                '$data',
                'array &$extraParams'
            ));
        
        $primaryKeyId = (!empty($this->_options['primary_key_id']) ? $this->_options['primary_key_id'] : 'id');
        $body = 'return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, \'' .
             $primaryKeyId . '\', \'title\');';
        $function->addToBody($body);
    }
}