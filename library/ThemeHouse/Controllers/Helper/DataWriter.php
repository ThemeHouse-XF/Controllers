<?php

class ThemeHouse_Controllers_Helper_DataWriter
{

    public static function getPrimaryKey(XenForo_DataWriter $dataWriter, $tableName = null)
    {
        $fields = $dataWriter->getFields();
        
        if (!$tableName) {
            $tables = array_keys($fields);
            if (isset($tables[0])) {
                $tableName = $tables[0];
            } else {
                return false;
            }
        }
        
        $firstField = null;

        foreach ($fields[$tableName] as $field => $fieldData) {
            if ($firstField === null) {
                $firstField = $field;
            }
            if (!empty($fieldData['autoIncrement'])) {
                return $field;
            }
        }
        
        return $firstField;
    }
}