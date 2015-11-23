<?php

class ThemeHouse_Controllers_ControllerAdmin_ControllerPublic extends ThemeHouse_Controllers_ControllerAdmin_Controller
{

    public function actionIndex()
    {
        $addOns = $this->_getAddOnModel()->getAllAddOns();
        
        $xenOptions = XenForo_Application::get('options');
        
        $addOnSelected = '';
        
        if ($xenOptions->th_controllers_enableAddOnChooser) {
            $addOnId = $this->_input->filterSingle('addon_id', XenForo_Input::STRING);
            
            if (!empty($GLOBALS['ThemeHouse_Controllers_Route_PrefixAdmin_PublicControllers']) && !$addOnId) {
                $addOnId = XenForo_Helper_Cookie::getCookie('edit_addon_id');
            }
            
            if ($addOnId && !empty($addOns[$addOnId])) {
                XenForo_Helper_Cookie::setCookie('edit_addon_id', $addOnId);
                
                $addOn = $addOns[$addOnId];
                
                $addOnSelected = $addOnId;
                
                $this->canonicalizeRequestUrl(XenForo_Link::buildAdminLink('add-ons/public-controllers', $addOn));
            } else {
                $this->canonicalizeRequestUrl(XenForo_Link::buildAdminLink('add-ons/public-controllers'));
                
                XenForo_Helper_Cookie::deleteCookie('edit_addon_id');
            }
        }
        
        $addOns['XenForo'] = array(
            'addon_id' => 'XenForo',
            'active' => true,
            'title' => 'XenForo'
        );
        
        $rootPath = XenForo_Autoloader::getInstance()->getRootDir();
        
        $controllerPublics = array();
        $controllerPublicCount = 0;
        $totalControllerPublics = 0;
        
        foreach ($addOns as $addOnId => $addOn) {
            $controllerPublicPath = $rootPath . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $addOnId) .
                 DIRECTORY_SEPARATOR . 'ControllerPublic';
            
            if (!file_exists($controllerPublicPath)) {
                continue;
            }
            
            $directory = new RecursiveDirectoryIterator($controllerPublicPath);
            $iterator = new RecursiveIteratorIterator($directory);
            $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
            
            foreach ($regex as $fileinfo) {
                $classPath = str_replace($rootPath, '', $fileinfo[0]);
                $classPath = pathinfo($classPath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR .
                     pathinfo($classPath, PATHINFO_FILENAME);
                $dirs = explode(DIRECTORY_SEPARATOR, $classPath);
                $dirs = array_filter($dirs);
                $className = implode('_', $dirs);
                if (!$xenOptions->th_controllers_enableAddOnChooser || !$addOnSelected ||
                     $addOnId == $addOnSelected) {
                    $controllerPublics[$addOnId][$className] = array(
                        'class' => $className,
                        'filename' => pathinfo($classPath, PATHINFO_FILENAME)
                    );
                    $controllerPublicCount++;
                }
                $totalControllerPublics++;
            }
        }
        
        unset($addOns['XenForo']);
        
        $viewParams = array(
            'addOns' => $addOns,
            'addOnSelected' => $addOnSelected,
            
            'controllerPublics' => $controllerPublics,
            'controllerPublicCount' => $controllerPublicCount,
            'totalControllerPublics' => $totalControllerPublics
        );
        
        return $this->responseView('ThemeHouse_Controllers_ViewAdmin_ControllerPublic_List',
            'th_controller_public_list_controllers', $viewParams);
    }

    public function actionView()
    {
        $class = $this->_input->filterSingle('class', XenForo_Input::STRING);
        
        try {
            $response = new Zend_Controller_Response_Http();
            $controllerPublic = new $class($this->getRequest(), $response, $this->getRouteMatch());
        } catch (Exception $e) {
        }
        
        if (empty($controllerPublic) || !$controllerPublic instanceof XenForo_ControllerPublic_Abstract) {
            return $this->responseNoPermission();
        }
        
        $reflectionClass = new ThemeHouse_Reflection_Class(get_class($controllerPublic));
        
        $reflectionMethods = $reflectionClass->getMethods();
        
        $methods = array();
        foreach ($reflectionMethods as $reflectionMethod) {
            /* @var $reflectionMethod ReflectionMethod */
            $methodName = $reflectionMethod->getName();
            $declaringClass = $reflectionMethod->getDeclaringClass();
            $methods[$methodName]['declaringClass'] = $declaringClass->getName();
            $methods[$methodName]['isAbstract'] = $reflectionMethod->isAbstract();
            $methods[$methodName]['isConstructor'] = $reflectionMethod->isConstructor();
            $methods[$methodName]['isDeprecated'] = $reflectionMethod->isDeprecated();
            $methods[$methodName]['isDestructor'] = $reflectionMethod->isDestructor();
            $methods[$methodName]['isFinal'] = $reflectionMethod->isFinal();
            $methods[$methodName]['isInternal'] = $reflectionMethod->isInternal();
            $methods[$methodName]['isPrivate'] = $reflectionMethod->isPrivate();
            $methods[$methodName]['isProtected'] = $reflectionMethod->isProtected();
            $methods[$methodName]['isPublic'] = $reflectionMethod->isPublic();
            $methods[$methodName]['isStatic'] = $reflectionMethod->isStatic();
            $methods[$methodName]['isUserDefined'] = $reflectionMethod->isUserDefined();
        }
        
        $controllerPublic = array(
            'class' => $class
        );
        
        $viewParams = array(
            'controllerPublic' => $controllerPublic,
            
            'methods' => $methods
        );
        
        return $this->responseView('ThemeHouse_Controllers_ViewAdmin_ControllerPublic_View', 'th_controller_public_view_controllers',
            $viewParams);
    }

    public function actionAdd()
    {
        $addOnId = $this->_input->filterSingle('addon_id', XenForo_Input::STRING);
        
        if (!$addOnId) {
            $addOnModel = $this->_getAddOnModel();
            
            $viewParams = array(
                'addOnOptions' => $addOnModel->getAddOnOptionsListIfAvailable()
            );
            
            return $this->responseView('ThemeHouse_Controllers_ViewAdmin_ControllerPublic_Add_ChooseAddOn',
                'th_controller_public_choose_addon_controllers', $viewParams);
        }
        
        $dataWriter = $this->_input->filterSingle('datawriter', XenForo_Input::STRING);
        
        if (!$dataWriter) {
            $dataWriters = array();
            
            $rootPath = XenForo_Autoloader::getInstance()->getRootDir();
            
            $dataWriterPath = $rootPath . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $addOnId) .
                 DIRECTORY_SEPARATOR . 'DataWriter';
            
            if (!file_exists($dataWriterPath)) {
                return $this->responseError(new XenForo_Phrase('th_no_datawriters_in_this_addon_controller'));
            }
            
            $directory = new RecursiveDirectoryIterator($dataWriterPath);
            $iterator = new RecursiveIteratorIterator($directory);
            $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
            
            foreach ($regex as $fileinfo) {
                $classPath = str_replace($rootPath, '', $fileinfo[0]);
                $classPath = pathinfo($classPath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR .
                     pathinfo($classPath, PATHINFO_FILENAME);
                $dirs = explode(DIRECTORY_SEPARATOR, $classPath);
                $dirs = array_filter($dirs);
                $className = implode('_', $dirs);
                $dataWriters[$className] = $className;
            }
            
            $viewParams = array(
                'dataWriters' => $dataWriters,
                
                'addOnSelected' => $addOnId
            );
            
            return $this->responseView('ThemeHouse_Controllers_ViewAdmin_ControllerPublic_Add_ChooseDataWriter',
                'th_controller_public_choose_datawriter_controllers', $viewParams);
        }
        
        $dataWriterClassName = $dataWriter;
        
        if (substr($dataWriterClassName, 0, strlen($addOnId . '_DataWriter_')) != $addOnId . '_DataWriter_') {
            return $this->responseNoPermission();
        }
        
        $class = $addOnId . '_ControllerPublic_' . substr($dataWriterClassName, strlen($addOnId . '_DataWriter_'));
        $name = substr(strrchr($dataWriter, '_'), 1);
        
        $dataWriter = XenForo_DataWriter::create($dataWriter);
        
        if (!$dataWriter || !$dataWriter instanceof XenForo_DataWriter) {
            return $this->responseNoPermission();
        }
        
        $reflectionClass = new ThemeHouse_Reflection_Class(get_class($dataWriter));
        
        $viewParams = array(
            'dataWriter' => $dataWriterClassName,
            'addOnSelected' => $addOnId,
            
            'class' => $class,
        );
        
        return $this->responseView('ThemeHouse_Controllers_ViewAdmin_ControllerPublic_Add',
            'th_controller_public_add_controllers', $viewParams);
    }

    public function actionSave()
    {
        $options = $this->_input->filter(
            array(
                'datawriter' => XenForo_Input::STRING,
                'addon_id' => XenForo_Input::STRING,
                'class' => XenForo_Input::STRING,
            ));
        
        try {
            $dataWriter = XenForo_DataWriter::create($options['datawriter']);
        } catch (Exception $e) {
        }
        
        if (empty($dataWriter) || !$dataWriter instanceof XenForo_DataWriter) {
            return $this->responseNoPermission();
        }
        
        $phpFile = new ThemeHouse_Controllers_PhpFile_ControllerPublic($options['class'], $options);
        $phpFile->export();
        
        return $this->responseRedirect(XenForo_ControllerResponse_Redirect::RESOURCE_CREATED, 
            XenForo_Link::buildAdminLink('public-controllers'));
    }

    public function actionAddMethod()
    {
        $class = $this->_input->filterSingle('class', XenForo_Input::STRING);

        $request = $this->_request;
        $response = $this->_response;
        $routeMatch = $this->_routeMatch;

        $controllerPublic = new $class($request, $response, $routeMatch);

        if (empty($controllerPublic) || !$controllerPublic instanceof XenForo_ControllerPublic_Abstract) {
            return $this->responseNoPermission();
        }

        return $this->_getMethodAddResponse($controllerPublic, 'public-controllers', 'controllerpublic');
    }

    public function actionEditMethod()
    {
        $className = $this->_input->filterSingle('class', XenForo_Input::STRING);

        $request = $this->_request;
        $response = $this->_response;
        $routeMatch = $this->_routeMatch;

        $controllerPublic = new $className($request, $response, $routeMatch);

        if (empty($controllerPublic) || !$controllerPublic instanceof XenForo_ControllerPublic_Abstract) {
            return $this->responseNoPermission();
        }

        return $this->_getMethodEditResponse($controllerPublic, 'public-controllers');
    }

    protected function _getAddOnIdFromClassName($className)
    {
        return ThemeHouse_Controllers_Helper_ControllerPublic::getAddOnIdFromControllerAdminClass($className);
    }

    /**
     * Get the controller public helper.
     *
     * @return ThemeHouse_Controllers_ControllerHelper_ControllerPublic
     */
    protected function _getControllerPublicHelper()
    {
        return $this->getHelper('ThemeHouse_Controllers_ControllerHelper_ControllerPublic');
    }
}