<?php

class ThemeHouse_Controllers_PhpFile_ControllerAdmin extends ThemeHouse_PhpFile
{

    protected $_dataWriter = null;

    public function __construct($className, array $options)
    {
        parent::__construct($className);
        
        $this->setExtends('XenForo_ControllerAdmin_Abstract');
        
        $this->_dataWriter = XenForo_DataWriter::create($options['datawriter']);
        
        if (empty($options['name'])) {
            $options['name'] = substr(strrchr($className, '_'), 1);
        }
        
        if (empty($options['name_plural'])) {
            $options['name_plural'] = substr($options['method'], 3);
        }
        
        if (empty($options['view_class'])) {
            $options['view_class'] = substr($className, 0, strpos($className, '_ControllerAdmin_') + 1) . 'View' .
                 substr($className, strpos($className, '_ControllerAdmin_') + 11);
        }
        
        if (in_array('index', $options['actions'])) {
            $this->_createFunctionActionIndex($options);
        }
        
        if (in_array('add', $options['actions']) || in_array('edit', $options['actions'])) {
            $this->_createFunctionGetAddEditResponse($options);
        }
        
        if (in_array('add', $options['actions'])) {
            $this->_createFunctionActionAdd($options);
        }
        
        if (in_array('edit', $options['actions'])) {
            $this->_createFunctionActionEdit($options);
        }
        
        if (in_array('add', $options['actions']) || in_array('edit', $options['actions'])) {
            $this->_createFunctionActionSave($options);
        }
        
        if (in_array('delete', $options['actions'])) {
            $this->_createFunctionActionDelete($options);
        }
        
        if (in_array('edit', $options['actions'])) {
            $this->_createFunctionGetOrError($options);
        }
        
        if (in_array('index', $options['actions'])) {
            $this->_createFunctionGetModel($options);
        }
    }

    protected function _createFunctionActionIndex(array $options)
    {
        $function = $this->createFunction('actionIndex');
        $function->setPhpDoc(
            array(
                '',
                '@return XenForo_ControllerResponse_Abstract'
            ));
        
        $lcFirstName = lcfirst($options['name']);
        $lcFirstNamePlural = lcfirst($options['name_plural']);
        
        $snakeCase = ThemeHouse_Reflection_Helper_Template::camelCaseToSnakeCase($options['name']);
        $snakeCasePlural = ThemeHouse_Reflection_Helper_Template::camelCaseToSnakeCase($options['name_plural']);
        $templateName = ThemeHouse_Reflection_Helper_Template::getTemplateName($snakeCase . '_list', $options['addon_id']);
        
        $name = $options['name'];
        $method = $options['method'];
        $viewClass = $options['view_class'];
        
        $body = array(
            '$' . $lcFirstName . 'Model = $this->_get' . $options['name'] . 'Model();',
            '',
            '$viewParams = array(',
            "\t" . '\'' . $lcFirstNamePlural . '\' => $this->_get' . $name . 'Model()->' . $method . '()',
            ');',
            '',
            'return $this->responseView(\'' . $viewClass . '_List\',',
            "\t" . '\'' . $templateName . '\', $viewParams);'
        );
        $function->setBody($body);
        
        $addOnId = $options['addon_id'];
        
        $titlePhrase = ThemeHouse_Reflection_Helper_Phrase::getPhraseName($snakeCasePlural, $addOnId);
        $createNewPhrase = ThemeHouse_Reflection_Helper_Phrase::getPhraseName('create_new_' . $snakeCase, $addOnId);
        $thereAreNoPhrase = ThemeHouse_Reflection_Helper_Phrase::getPhraseName('there_are_no_' . $snakeCasePlural,
            $addOnId);
        
        $camelCasePrimaryKeyId = ThemeHouse_Reflection_Helper_Template::snakeCaseToCamelCase($options['primary_key_id']);
        $lcFirstPrimaryKeyId = lcfirst($camelCasePrimaryKeyId);
        
        $titleField = $options['title_field'];
        $snippet = $options['subtitle_field'] ? '{$' . $lcFirstName . '.' . $options['subtitle'] . '}' : '';
        
        $routePrefix = $options['route_prefix'];
        
        $template = array(
            '<xen:title>{xen:phrase ' . $titlePhrase . '}</xen:title>',
            '',
            '<xen:h1>{xen:phrase ' . $titlePhrase . '}</xen:h1>',
            '',
            '<xen:topctrl>',
            '    <a href="{xen:adminlink \'' . $routePrefix . '/add\'}" class="button">+ {xen:phrase ' . $createNewPhrase .
                 '}</a>',
                '</xen:topctrl>',
                '',
                '<xen:require css="filter_list.css" />',
                '<xen:require js="js/xenforo/filter_list.js" />',
                '',
                '<xen:form action="{xen:adminlink \'' . $routePrefix . '/toggle\'}" class="section AutoValidator">',
                '',
                '    <xen:if is="{$' . $lcFirstNamePlural . '}">',
                '        <h2 class="subHeading">',
                '            <xen:include template="filter_list_controls" />',
                '            {xen:phrase ' . $titlePhrase . '}',
                '        </h2>',
                '',
                '        <ol class="FilterList">',
                '            <xen:foreach loop="$' . $lcFirstNamePlural . '" key="$' . $lcFirstPrimaryKeyId .
                 '" value="$' . $lcFirstName . '">',
                '                <xen:listitem id="{$' . $lcFirstPrimaryKeyId . '}"',
                '                    label="{$' . $lcFirstName . '.' . $titleField . '}"',
                '                    snippet="' . $snippet . '"',
                '                    href="{xen:adminlink \'' . $routePrefix . '/edit\', $' . $lcFirstName . '}"',
                '                    delete="{xen:adminlink \'' . $routePrefix . '/delete\', $' . $lcFirstName . '}"',
                '                    deletehint="{xen:phrase delete}">',
                '                </xen:listitem>',
                '            </xen:foreach>',
                '        </ol>',
                '',
                '        <p class="sectionFooter">{xen:phrase showing_x_of_y_items, \'count=<span class="FilterListCount">{xen:count $' .
                 $lcFirstNamePlural . '}</span>\', \'total={xen:count $' . $lcFirstNamePlural . '}\'}</p>',
                '',
                '    <xen:else />',
                '        <div class="noResults">{xen:phrase ' . $thereAreNoPhrase . '}</div>',
                '    </xen:if>',
                '',
                '</xen:form>'
        );
        
        ThemeHouse_Reflection_Helper_Template::createAdminTemplate($templateName, implode("\n", $template), $addOnId);
    }

    protected function _createFunctionGetAddEditResponse(array $options)
    {
        $function = $this->createFunction('_get' . $options['name'] . 'AddEditResponse');
        $function->setPhpDoc(
            array(
                '',
                '@return XenForo_ControllerResponse_Abstract'
            ));
        
        $lcFirstName = lcfirst($options['name']);
        
        $function->setSignature(array(
            'array $' . $lcFirstName
        ));
        
        $snakeCase = ThemeHouse_Reflection_Helper_Template::camelCaseToSnakeCase($options['name']);
        $templateName = ThemeHouse_Reflection_Helper_Template::getTemplateName($snakeCase . '_edit', $options['addon_id']);
        
        $viewClass = $options['view_class'];
        
        $body = array(
            '$' . $lcFirstName . 'Model = $this->_get' . $options['name'] . 'Model();',
            '',
            '$viewParams = array(',
            "\t" . '\'' . $lcFirstName . '\' => $' . $lcFirstName,
            ');',
            '',
            'return $this->responseView(\'' . $viewClass . '_Edit\',',
            "\t" . '\'' . $templateName . '\', $viewParams);'
        );
        $function->setBody($body);
        
        $addOnId = $options['addon_id'];
        
        $routePrefix = $options['route_prefix'];
        
        $primaryKeyId = $options['primary_key_id'];
        
        $editPhrase = ThemeHouse_Reflection_Helper_Phrase::getPhraseName('edit_' . $snakeCase, $addOnId);
        $createNewPhrase = ThemeHouse_Reflection_Helper_Phrase::getPhraseName('create_new_' . $snakeCase, $addOnId);
        
        if (in_array('add', $options['actions']) || in_array('edit', $options['actions'])) {
            $title = '{xen:if \'{$' . $lcFirstName . '.' . $primaryKeyId . '}\', \'{xen:phrase ' . $editPhrase . '}: {$' .
                 $lcFirstName . '.title}\', \'{xen:phrase ' . $createNewPhrase . '}\'}';
            $h1 = '{xen:if \'{$' . $lcFirstName . '.' . $primaryKeyId . '}\', \'{xen:phrase ' . $editPhrase . '}: <em>{$' .
                 $lcFirstName . '.title}</em>\', \'{xen:phrase ' . $createNewPhrase . '}\'}';
        } elseif (in_array('add', $options['actions'])) {
            $title = '{xen:phrase ' . $createNewPhrase . '}';
            $h1 = $title;
        } else {
            $title = '{xen:phrase ' . $editPhrase . '}: {$' . $lcFirstName . '.title}';
            $h1 = '{xen:phrase ' . $editPhrase . '}: <em>{$' . $lcFirstName . '.title}</em>';
        }
        
        $header = array(
            '<xen:title>' . $title . '</xen:title>',
            '',
            '<xen:h1>' . $h1 . '</xen:h1>',
            '',
            '<xen:if is="{$' . $lcFirstName . '.' . $primaryKeyId . '}">',
            "\t" . '<xen:navigation>',
            "\t\t" . '<xen:breadcrumb href="{xen:adminlink \'' . $routePrefix . '\'}#{xen:helper listitemid, $' .
                 $lcFirstName . '.' . $primaryKeyId . '}">{$' . $lcFirstName . '.' . $options['title_field'] .
                 '}</xen:breadcrumb>',
                "\t" . '</xen:navigation>',
                '</xen:if>',
                ''
        );
        
        $beginForm = array(
            '<xen:form action="{xen:adminlink \'' . $routePrefix . '/save\', $' . $lcFirstName .
                 '}" class="AutoValidator" data-redirect="on">'
        );
        
        $fields = $this->_prepareFields($options);
        
        $saveAndExitButton = "\t\t" .
             '<input type="submit" name="saveexit" value="{xen:phrase save_and_exit}" accesskey="e" class="button primary" id="saveExitButton" />';
        $saveChangesButton = "\t\t" .
             '<input type="submit" name="reload" value="{xen:phrase save_changes}" accesskey="s" class="button" id="saveReloadButton" data-ajaxvalue="{xen:phrase save_all_changes}" />';
        
        $deletePhrase = '{xen:phrase ' .
             ThemeHouse_Reflection_Helper_Phrase::getPhraseName('delete_' . $snakeCase, $addOnId) . '}...';
        
        $deleteButton = array(
            "\t\t" . '<xen:if is="{$' . $lcFirstName . '.' . $primaryKeyId . '}">',
            "\t\t\t" . '<input type="button" value="' . $deletePhrase . '" accesskey="d" class="button OverlayTrigger"',
            "\t\t\t\t" . 'data-href="{xen:adminlink ' . $routePrefix . '/delete, $' . $lcFirstName . '}" />',
            "\t\t" . '</xen:if>'
        );
        
        if (!in_array('delete', $options['actions'])) {
            $deleteButton = array();
        }
        
        $endForm = array_merge(
            array(
                "\t" . '<xen:submitunit>',
                $saveAndExitButton,
                $saveChangesButton
            ), $deleteButton, 
            array(
                "\t" . '</xen:submitunit>',
                '</xen:form>'
            ));
        
        $template = array_merge($header, $beginForm, $fields, $endForm);
        
        ThemeHouse_Reflection_Helper_Template::createAdminTemplate($templateName, implode("\n", $template), $addOnId);
    }

    protected function _prepareFields(array $options)
    {
        $dataWriter = $this->_dataWriter;
        $dwFields = $dataWriter->getFields();
        
        $snakeCase = ThemeHouse_Reflection_Helper_Template::camelCaseToSnakeCase($options['name']);
        
        $addOnId = $options['addon_id'];
        
        $lcFirstName = lcfirst($options['name']);
        $primaryKeyId = $options['primary_key_id'];
        
        $editPhrase = ThemeHouse_Reflection_Helper_Phrase::getPhraseName('edit_' . $snakeCase, $addOnId);
        $createNewPhrase = ThemeHouse_Reflection_Helper_Phrase::getPhraseName('create_new_' . $snakeCase, $addOnId);
        
        $fields = array();
        
        foreach ($dwFields as $table => $tableFields) {
            foreach ($tableFields as $fieldId => $_field) {
                if (!empty($options['fields'][$table][$fieldId])) {
                    if ($_field['type'] == XenForo_DataWriter::TYPE_STRING) {
                        $phrase = ThemeHouse_Reflection_Helper_Phrase::getPhraseName($fieldId, $addOnId);
                        $explainPhrase = ThemeHouse_Reflection_Helper_Phrase::getPhraseName($fieldId . '_explain',
                            $addOnId);
                        if ($fieldId == $options['title_field']) {
                            $field = array(
                                "\t" . '<xen:textboxunit label="{xen:phrase ' . $phrase . '}:" explain="{xen:phrase ' .
                                     $explainPhrase . '}" name="title" value="{$' . $lcFirstName . '.title}"' .
                                     (!empty($_field['maxLength']) ? ' maxlength="' . $_field['maxLength'] . '"' : '') .
                                     ' data-liveTitleTemplate="{xen:if {$' . $lcFirstName . '.' . $primaryKeyId . '},',
                                    "\t\t" . '\'{xen:phrase ' . $editPhrase . '}: <em>%s</em>\',',
                                    "\t\t" . '\'{xen:phrase ' . $createNewPhrase . '}: <em>%s</em>\'}" />'
                            );
                        } else {
                            $field = array(
                                "\t" . '<xen:textboxunit label="{xen:phrase ' . $phrase . '}:" explain="{xen:phrase ' .
                                     $explainPhrase . '}" name="' . $fieldId . '" value="{$' . $lcFirstName . '.' .
                                     $fieldId . '}"' .
                                     (!empty($_field['maxLength']) ? ' maxlength="' . $_field['maxLength'] . '"' : '') .
                                     ' />'
                            );
                        }
                    }
                    $fields = array_merge($fields, $field, array(
                        ''
                    ));
                }
            }
        }
        
        return $fields;
    }

    protected function _createFunctionActionAdd(array $options)
    {
        $function = $this->createFunction('actionAdd');
        $function->setPhpDoc(
            array(
                '',
                '@return XenForo_ControllerResponse_Abstract'
            ));
        
        $name = $options['name'];
        $lcFirstName = lcfirst($name);
        
        $model = XenForo_Model::create($options['model']);
        
        $modelReflectionClass = new ThemeHouse_Controllers_Reflection_Class_Model(get_class($model));
        
        if ($modelReflectionClass->hasMethod('getDefault' . $name)) {
            $body = array(
                '$' . $lcFirstName . ' = $this->_get' . $name . 'Model()->getDefault' . $name . '();',
                '',
                'return $this->_get' . $name . 'AddEditResponse($' . $lcFirstName . ');'
            );
        } else {
            $body = array(
                '$' . $lcFirstName . ' = array();',
                '',
                'return $this->_get' . $name . 'AddEditResponse($' . $lcFirstName . ');'
            );
        }
        $function->setBody($body);
    }

    protected function _createFunctionActionEdit(array $options)
    {
        $function = $this->createFunction('actionEdit');
        $function->setPhpDoc(
            array(
                '',
                '@return XenForo_ControllerResponse_Abstract'
            ));
        
        $name = $options['name'];
        $lcFirstName = lcfirst($name);
        
        $primaryKeyId = $options['primary_key_id'];
        $camelCasePrimaryKeyId = ThemeHouse_Reflection_Helper_Template::snakeCaseToCamelCase($primaryKeyId);
        $lcFirstPrimaryKeyId = lcfirst($camelCasePrimaryKeyId);
        
        $dataWriter = $this->_dataWriter;
        $dwFields = $dataWriter->getFields();
        
        $tables = array_keys($dwFields);
        $tableName = $tables[0];
        
        $primaryKeyField = $dwFields[$tableName][$primaryKeyId];
        $primaryKeyType = 'XenForo_Input::' . strtoupper($primaryKeyField['type']);
        
        $body = array(
            '$' . $lcFirstPrimaryKeyId . ' = $this->_input->filterSingle(\'' . $primaryKeyId . '\', ' . $primaryKeyType .
                 ');',
                '$' . $lcFirstName . ' = $this->_get' . $name . 'OrError($' . $lcFirstPrimaryKeyId . ');',
                '',
                'return $this->_get' . $name . 'AddEditResponse($' . $lcFirstName . ');'
        );
        $function->setBody($body);
    }

    protected function _createFunctionActionSave(array $options)
    {
        $function = $this->createFunction('actionSave');
        $function->setPhpDoc(
            array(
                '',
                '@return XenForo_ControllerResponse_Abstract'
            ));
        
        $name = $options['name'];
        $lcFirstName = lcfirst($name);
        
        $primaryKeyId = $options['primary_key_id'];
        $camelCasePrimaryKeyId = ThemeHouse_Reflection_Helper_Template::snakeCaseToCamelCase($primaryKeyId);
        $lcFirstPrimaryKeyId = lcfirst($camelCasePrimaryKeyId);
        
        $dataWriter = $options['datawriter'];
        $dw = $this->_dataWriter;
        $dwFields = $dw->getFields();
        
        $tables = array_keys($dwFields);
        $tableName = $tables[0];
        
        $primaryKeyField = $dwFields[$tableName][$primaryKeyId];
        $primaryKeyType = 'XenForo_Input::' . strtoupper($primaryKeyField['type']);
        
        $routePrefix = $options['route_prefix'];
        
        $header = array(
            '$this->_assertPostOnly();',
            '',
            '$' . $lcFirstPrimaryKeyId . ' = $this->_input->filterSingle(\'' . $primaryKeyId . '\', ' . $primaryKeyType .
                 ');'
        );
        
        $dwData = $this->_prepareDwData($options);
        
        $hash = '$this->getLastHash($' . $lcFirstPrimaryKeyId . ')';
        
        $footer = array(
            '$dw = XenForo_DataWriter::create(\'' . $dataWriter . '\');',
            'if ($' . $lcFirstPrimaryKeyId . ') {',
            "\t" . '$dw->setExistingData($' . $lcFirstPrimaryKeyId . ');',
            '}',
            '$dw->bulkSet($dwData);',
            '$dw->save();',
            '',
            '$' . $lcFirstPrimaryKeyId . ' = $dw->get(\'' . $primaryKeyId . '\');',
            '',
            'return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS,',
            "\t" . 'XenForo_Link::buildAdminLink(\'' . $routePrefix . '\') . ' . $hash . ');'
        );
        
        $body = array_merge($header, $dwData, $footer);
        
        $function->setBody($body);
    }

    protected function _prepareDwData(array $options)
    {
        $dwData = array(
            '$dwData = $this->_input->filter(',
            "\t" . 'array('
        );
        
        $dataWriter = $this->_dataWriter;
        $dwFields = $dataWriter->getFields();
        
        $snakeCase = ThemeHouse_Reflection_Helper_Template::camelCaseToSnakeCase($options['name']);
        
        $addOnId = $options['addon_id'];
        
        $lcFirstName = lcfirst($options['name']);
        $primaryKeyId = $options['primary_key_id'];
        
        $editPhrase = ThemeHouse_Reflection_Helper_Phrase::getPhraseName('edit_' . $snakeCase, $addOnId);
        $createNewPhrase = ThemeHouse_Reflection_Helper_Phrase::getPhraseName('create_new_' . $snakeCase, $addOnId);
        
        foreach ($dwFields as $table => $tableFields) {
            foreach ($tableFields as $fieldId => $_field) {
                if (!empty($options['fields'][$table][$fieldId])) {
                    $type = 'XenForo_Input::' . strtoupper($_field['type']);
                    $dwData[] = "\t\t" . '\'' . $fieldId . '\' => ' . $type . ',';
                }
            }
        }
        
        $dwData[] = "\t" . '));';
        $dwData[] = '';
        
        return $dwData;
    }

    protected function _createFunctionActionDelete(array $options)
    {
        $function = $this->createFunction('actionDelete');
        $function->setPhpDoc(
            array(
                '',
                '@return XenForo_ControllerResponse_Abstract'
            ));
        
        $name = $options['name'];
        $lcFirstName = lcfirst($name);
        
        $primaryKeyId = $options['primary_key_id'];
        $camelCasePrimaryKeyId = ThemeHouse_Reflection_Helper_Template::snakeCaseToCamelCase($primaryKeyId);
        $lcFirstPrimaryKeyId = lcfirst($camelCasePrimaryKeyId);
        
        $routePrefix = $options['route_prefix'];
        $dataWriter = $options['datawriter'];
        $viewClass = $options['view_class'];
        
        $addOnId = $options['addon_id'];
        
        $snakeCase = ThemeHouse_Reflection_Helper_Template::camelCaseToSnakeCase($options['name']);
        $templateName = ThemeHouse_Reflection_Helper_Template::getTemplateName($snakeCase . '_delete', $addOnId);
        
        $body = array(
            'if ($this->isConfirmedPost()) {',
            "\t" . 'return $this->_deleteData(\'' . $dataWriter . '\', \'' . $primaryKeyId . '\',',
            "\t\t" . 'XenForo_Link::buildAdminLink(\'' . $routePrefix . '\'));',
            '} else {',
            "\t" . '$' . $lcFirstPrimaryKeyId . ' = $this->_input->filterSingle(\'' . $primaryKeyId .
                 '\', XenForo_Input::UINT);',
                "\t" . '$' . $lcFirstName . ' = $this->_get' . $name . 'OrError($' . $lcFirstPrimaryKeyId . ');',
                '',
                "\t" . '$viewParams = array(',
                "\t\t" . '\'' . $lcFirstName . '\' => $' . $lcFirstName . '',
                "\t" . ');',
                '',
                "\t" . 'return $this->responseView(\'' . $viewClass . '_Delete\',',
                "\t\t" . '\'' . $templateName . '\', $viewParams);',
                '}'
        );
        $function->setBody($body);
        
        $titleField = $options['title_field'];
        
        $title = $lcFirstName . '.' . $titleField;
        
        $confirmDeletionPhrase = ThemeHouse_Reflection_Helper_Phrase::getPhraseName('confirm_deletion_of_' . $snakeCase,
            $addOnId);
        $pleaseConfirmPhrase = ThemeHouse_Reflection_Helper_Phrase::getPhraseName(
            'please_confirm_want_to_delete_' . $snakeCase, $addOnId);
        $deletePhrase = ThemeHouse_Reflection_Helper_Phrase::getPhraseName('delete_' . $snakeCase, $addOnId);
        
        $editLink = '{xen:adminlink \'' . $routePrefix . '/edit\', $' . $lcFirstName . '}';
        $formLink = '{xen:adminlink \'' . $routePrefix . '/delete\', $' . $lcFirstName . '}';
        
        $template = array(
            '<xen:title>{xen:phrase ' . $confirmDeletionPhrase . '}: {$' . $title . '}</xen:title>',
            '',
            '<xen:h1>{xen:phrase ' . $confirmDeletionPhrase . '}</xen:h1>',
            '',
            '<xen:navigation>',
            "\t" . '<xen:breadcrumb href="' . $editLink . '">{$' . $title . '}</xen:breadcrumb>',
            '</xen:navigation>',
            '',
            '<xen:require css="delete_confirmation.css" />',
            '',
            '<xen:form action="' . $formLink . '" class="deleteConfirmForm formOverlay">',
            "\t" . '<p>{xen:phrase ' . $pleaseConfirmPhrase . '}:</p>',
            "\t" . '<strong><a href="' . $editLink . '">{$' . $title . '}</a></strong>',
            '',
            "\t" . '<xen:submitunit save="{xen:phrase ' . $deletePhrase . '}" />',
            '',
            "\t" . '<input type="hidden" name="_xfConfirm" value="1" />',
            '</xen:form>'
        );
        
        ThemeHouse_Reflection_Helper_Template::createAdminTemplate($templateName, implode("\n", $template), $addOnId);
    }

    protected function _createFunctionGetOrError(array $options)
    {
        $name = $options['name'];
        $lcFirstName = lcfirst($name);
        
        $function = $this->createFunction('_get' . $name . 'OrError');
        $function->setPhpDoc(array(
            '',
            '@return array'
        ));
        
        $primaryKeyId = $options['primary_key_id'];
        $camelCasePrimaryKeyId = ThemeHouse_Reflection_Helper_Template::snakeCaseToCamelCase($primaryKeyId);
        $lcFirstPrimaryKeyId = lcfirst($camelCasePrimaryKeyId);
        
        $function->setSignature(array(
            '$' . $lcFirstPrimaryKeyId
        ));
        
        $snakeCase = ThemeHouse_Reflection_Helper_Template::camelCaseToSnakeCase($options['name']);
        
        $addOnId = $options['addon_id'];
        
        $notFoundPhrase = ThemeHouse_Reflection_Helper_Phrase::getPhraseName($snakeCase . '_not_found', $addOnId);
        
        $model = XenForo_Model::create($options['model']);
        
        $modelReflectionClass = new ThemeHouse_Controllers_Reflection_Class_Model(get_class($model));
        
        if ($modelReflectionClass->hasMethod('prepare' . $name)) {
            $body = array(
                '$' . $lcFirstName . 'Model = $this->_get' . $options['name'] . 'Model();',
                '',
                'return $' . $lcFirstName . 'Model->prepare' . $options['name'] . '(',
                "\t" . '$this->getRecordOrError($' . $lcFirstPrimaryKeyId . ', $' . $lcFirstName . 'Model, \'get' .
                     $options['name'] . 'ById\',',
                    "\t\t" . '\'' . $notFoundPhrase . '\'));'
            );
        } else {
            $body = array(
                'return $this->getRecordOrError($' . $lcFirstPrimaryKeyId . ', $this->_get' . $options['name'] .
                     'Model(), \'get' . $options['name'] . 'ById\',',
                    "\t" . '\'' . $notFoundPhrase . '\');'
            );
        }
        
        $function->setBody($body);
    }

    protected function _createFunctionGetModel(array $options)
    {
        $name = $options['name'];
        $model = $options['model'];
        
        $function = $this->createFunction('_get' . $name . 'Model');
        $function->setPhpDoc(array(
            '',
            '@return ' . $model
        ));
        $function->addToBody('return $this->getModelFromCache(\'' . $model . '\');');
    }
}