<?php

class ThemeHouse_Controllers_ControllerAdmin_ControllerAdmin extends ThemeHouse_Controllers_ControllerAdmin_Controller
{

    public function actionIndex()
    {
        $addOns = $this->_getAddOnModel()->getAllAddOns();
        
        $xenOptions = XenForo_Application::get('options');
        
        $addOnSelected = '';
        
        if ($xenOptions->th_controllers_enableAddOnChooser) {
            $addOnId = $this->_input->filterSingle('addon_id', XenForo_Input::STRING);
            
            if (!empty($GLOBALS['ThemeHouse_Controllers_Route_PrefixAdmin_AdminControllers']) && !$addOnId) {
                $addOnId = XenForo_Helper_Cookie::getCookie('edit_addon_id');
            }
            
            if ($addOnId && !empty($addOns[$addOnId])) {
                XenForo_Helper_Cookie::setCookie('edit_addon_id', $addOnId);
                
                $addOn = $addOns[$addOnId];
                
                $addOnSelected = $addOnId;
                
                $this->canonicalizeRequestUrl(XenForo_Link::buildAdminLink('add-ons/admin-controllers', $addOn));
            } else {
                $this->canonicalizeRequestUrl(XenForo_Link::buildAdminLink('add-ons/admin-controllers'));
                
                XenForo_Helper_Cookie::deleteCookie('edit_addon_id');
            }
        }
        
        $addOns['XenForo'] = array(
            'addon_id' => 'XenForo',
            'active' => true,
            'title' => 'XenForo'
        );
        
        $rootPath = XenForo_Autoloader::getInstance()->getRootDir();
        
        $controllerAdmins = array();
        $controllerAdminCount = 0;
        $totalControllerAdmins = 0;
        
        foreach ($addOns as $addOnId => $addOn) {
            $controllerAdminPath = $rootPath . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $addOnId) .
                 DIRECTORY_SEPARATOR . 'ControllerAdmin';
            if (!file_exists($controllerAdminPath)) {
                continue;
            }
            
            $directory = new RecursiveDirectoryIterator($controllerAdminPath);
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
                    $controllerAdmins[$addOnId][$className] = array(
                        'class' => $className,
                        'filename' => pathinfo($classPath, PATHINFO_FILENAME)
                    );
                    $controllerAdminCount++;
                }
                $totalControllerAdmins++;
            }
        }
        
        unset($addOns['XenForo']);
        
        $viewParams = array(
            'addOns' => $addOns,
            'addOnSelected' => $addOnSelected,
            
            'controllerAdmins' => $controllerAdmins,
            'controllerAdminCount' => $controllerAdminCount,
            'totalControllerAdmins' => $totalControllerAdmins
        );
        
        return $this->responseView('ThemeHouse_Controllers_ViewAdmin_ControllerAdmin_List',
            'th_controller_admin_list_controllers', $viewParams);
    }

    public function actionView()
    {
        $class = $this->_input->filterSingle('class', XenForo_Input::STRING);
        
        try {
            $response = new Zend_Controller_Response_Http();
            $controllerAdmin = new $class($this->getRequest(), $response, $this->getRouteMatch());
        } catch (Exception $e) {
        }
        
        if (empty($controllerAdmin) || !$controllerAdmin instanceof XenForo_ControllerAdmin_Abstract) {
            return $this->responseNoPermission();
        }
        
        $reflectionClass = new ThemeHouse_Reflection_Class(get_class($controllerAdmin));
        
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
        
        $controllerAdmin = array(
            'class' => $class
        );
        
        $viewParams = array(
            'controllerAdmin' => $controllerAdmin,
            
            'methods' => $methods
        );
        
        return $this->responseView('ThemeHouse_Controllers_ViewAdmin_ControllerAdmin_View',
            'th_controller_admin_view_controllers', $viewParams);
    }

    public function actionAdd()
    {
        $addOnId = $this->_input->filterSingle('addon_id', XenForo_Input::STRING);
        
        if (!$addOnId) {
            $addOnModel = $this->_getAddOnModel();
            
            $viewParams = array(
                'addOnOptions' => $addOnModel->getAddOnOptionsListIfAvailable()
            );
            
            return $this->responseView('ThemeHouse_Controllers_ViewAdmin_ControllerAdmin_Add_ChooseAddOn',
                'th_controller_admin_choose_addon_controllers', $viewParams);
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
            
            return $this->responseView('ThemeHouse_Controllers_ViewAdmin_ControllerAdmin_Add_ChooseDataWriter',
                'th_controller_admin_choose_dw_controllers', $viewParams);
        }
        
        $dataWriterClassName = $dataWriter;
        
        if (substr($dataWriterClassName, 0, strlen($addOnId . '_DataWriter_')) != $addOnId . '_DataWriter_') {
            return $this->responseNoPermission();
        }
        
        $class = $addOnId . '_ControllerAdmin_' . substr($dataWriterClassName, strlen($addOnId . '_DataWriter_'));
        
        $name = substr(strrchr($dataWriter, '_'), 1);
        
        $dataWriter = XenForo_DataWriter::create($dataWriter);
        
        if (!$dataWriter || !$dataWriter instanceof XenForo_DataWriter) {
            return $this->responseNoPermission();
        }
        
        $fields = $dataWriter->getFields();
        
        $selectedFields = array();
        $titleField = '';
        foreach ($fields as $table => $tableFields) {
            foreach ($tableFields as $fieldId => $field) {
                $titleField = $fieldId;
                if (empty($field['autoIncrement'])) {
                    $selectedFields[$table][$fieldId] = 1;
                    break 2;
                }
            }
        }
        
        $dataWriterReflectionClass = new ThemeHouse_Reflection_Class(get_class($dataWriter));
        
        $getModelMethod = '_get' . $name . 'Model';
        /* @var $reflectionMethod ThemeHouse_Reflection_Method */
        $dataWriterReflectionMethod = $dataWriterReflectionClass->getMethod($getModelMethod, 
            'ThemeHouse_Reflection_Method');
        
        $modelClassName = $dataWriterReflectionMethod->getReturnTag();
        
        $model = XenForo_Model::create($modelClassName);
        
        if (!$model || !$model instanceof XenForo_Model) {
            return $this->responseNoPermission();
        }
        
        $modelReflectionClass = new ThemeHouse_Controllers_Reflection_Class_Model(get_class($model));
        
        $pluralName = $modelReflectionClass->getPluralName();
        $routePrefix = ThemeHouse_Controllers_Helper_RoutePrefix::camelCaseToHyphenCase($pluralName);
        
        $method = $modelReflectionClass->findGetAllMethod($pluralName, false);
        
        $viewParams = array(
            'dataWriter' => $dataWriterClassName,
            'model' => $modelClassName,
            
            'addOnSelected' => $addOnId,
            
            'method' => $method,
            'routePrefix' => $routePrefix,
            
            'fields' => $fields,
            'selectedFields' => $selectedFields,
            'titleField' => $titleField,
            
            'class' => $class
        );
        
        return $this->responseView('ThemeHouse_Controllers_ViewAdmin_ControllerAdmin_Add',
            'th_controller_admin_add_controllers', $viewParams);
    }

    public function actionSave()
    {
        $options = $this->_input->filter(
            array(
                'datawriter' => XenForo_Input::STRING,
                'fields' => XenForo_Input::ARRAY_SIMPLE,
                'actions' => XenForo_Input::ARRAY_SIMPLE,
                'addon_id' => XenForo_Input::STRING,
                'class' => XenForo_Input::STRING,
                'method' => XenForo_Input::STRING,
                'route_prefix' => XenForo_Input::STRING,
                'title_field' => XenForo_Input::STRING,
                'subtitle_field' => XenForo_Input::STRING
            ));
        
        try {
            $dataWriter = XenForo_DataWriter::create($options['datawriter']);
        } catch (Exception $e) {
        }
        
        if (empty($dataWriter) || !$dataWriter instanceof XenForo_DataWriter) {
            return $this->responseNoPermission();
        }
        
        $options['primary_key_id'] = ThemeHouse_Controllers_Helper_DataWriter::getPrimaryKey($dataWriter);
        
        $name = substr(strrchr($options['class'], '_'), 1);
        
        $dataWriterReflectionClass = new ThemeHouse_Reflection_Class(get_class($dataWriter));
        
        $getModelMethod = '_get' . $name . 'Model';
        /* @var $reflectionMethod ThemeHouse_Reflection_Method */
        $dataWriterReflectionMethod = $dataWriterReflectionClass->getMethod($getModelMethod, 
            'ThemeHouse_Reflection_Method');
        
        $options['model'] = $dataWriterReflectionMethod->getReturnTag();
        
        $camelCaseRoutePrefix = ThemeHouse_Controllers_Helper_RoutePrefix::hyphenCaseToCamelCase($options['route_prefix']);
        $routePrefixClass = $options['addon_id'] . '_Route_PrefixAdmin_' . $camelCaseRoutePrefix;
        
        /* @var $routePrefixModel XenForo_Model_RoutePrefix */
        $routePrefixModel = $this->getModelFromCache('XenForo_Model_RoutePrefix');
        $routePrefix = $routePrefixModel->getPrefixByOriginal($options['route_prefix'], 'admin');
        
        if (!$routePrefix) {
            /* @var $routePrefixDw XenForo_DataWriter_RoutePrefix */
            $routePrefixDw = XenForo_DataWriter::create('XenForo_DataWriter_RoutePrefix');
            $routePrefixDw->bulkSet(
                array(
                    'route_type' => 'admin',
                    'original_prefix' => $options['route_prefix'],
                    'route_class' => $routePrefixClass,
                    'build_link' => 'data_only',
                    'addon_id' => $options['addon_id']
                ));
            $routePrefixDw->setOption(ThemeHouse_Controllers_Extend_XenForo_DataWriter_RoutePrefix::OPTION_PRIMARY_KEY_ID,
                $options['primary_key_id']);
            $routePrefixDw->save();
        }
        
        $phpFile = new ThemeHouse_Controllers_PhpFile_ControllerAdmin($options['class'], $options);
        $phpFile->export();
        
        return $this->responseRedirect(XenForo_ControllerResponse_Redirect::RESOURCE_CREATED, 
            XenForo_Link::buildAdminLink('admin-controllers'));
    }

    public function actionAddMethod()
    {
        $class = $this->_input->filterSingle('class', XenForo_Input::STRING);
        
        $request = $this->_request;
        $response = $this->_response;
        $routeMatch = $this->_routeMatch;
        
        $controllerAdmin = new $class($request, $response, $routeMatch);
        
        if (empty($controllerAdmin) || !$controllerAdmin instanceof XenForo_ControllerAdmin_Abstract) {
            return $this->responseNoPermission();
        }
        
        return $this->_getMethodAddResponse($controllerAdmin, 'admin-controllers', 'controlleradmin');
    }

    public function actionEditMethod()
    {
        $className = $this->_input->filterSingle('class', XenForo_Input::STRING);
        
        $request = $this->_request;
        $response = $this->_response;
        $routeMatch = $this->_routeMatch;
        
        $controllerAdmin = new $className($request, $response, $routeMatch);
        
        if (empty($controllerAdmin) || !$controllerAdmin instanceof XenForo_ControllerAdmin_Abstract) {
            return $this->responseNoPermission();
        }
        
        return $this->_getMethodEditResponse($controllerAdmin, 'admin-controllers');
    }

    public function actionDeleteMethod()
    {
        $className = $this->_input->filterSingle('class', XenForo_Input::STRING);
        
        $request = $this->_request;
        $response = $this->_response;
        $routeMatch = $this->_routeMatch;
        
        $controllerAdmin = new $className($request, $response, $routeMatch);
        
        if (empty($controllerAdmin) || !$controllerAdmin instanceof XenForo_ControllerAdmin_Abstract) {
            return $this->responseNoPermission();
        }
        
        return $this->_getMethodDeleteResponse($controllerAdmin, 'admin-controllers');
    }

    protected function _getAddOnIdFromClassName($className)
    {
        return ThemeHouse_Controllers_Helper_ControllerAdmin::getAddOnIdFromControllerAdminClass($className);
    }

    /**
     * Get the controller admin helper.
     *
     * @return ThemeHouse_Controllers_ControllerHelper_ControllerAdmin
     */
    protected function _getControllerAdminHelper()
    {
        return $this->getHelper('ThemeHouse_Controllers_ControllerHelper_ControllerAdmin');
    }
}