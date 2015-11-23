<?php

class ThemeHouse_Controllers_Reflection_Class_Model extends ThemeHouse_Reflection_Class
{

    public function findGetAllMethod($pluralName = null, $checkExists = true)
    {
        if ($pluralName === null) {
            $pluralName = $this->getPluralName();
        }

        if (!$checkExists) {
            return 'get' . $pluralName;
        }
        
        if ($this->hasMethod('get' . $pluralName)) {
            return 'get' . $pluralName;
        }
        
        return false;
    }
    
    public function getPluralName()
    {
        $className = $this->getName();
        
        $name = substr(strrchr($className, '_'), 1);
        
        return $name . 's';
    }
}