<?php
if (false) {

    class XFCP_ThemeHouse_Controllers_Extend_XenForo_DataWriter_RoutePrefix extends XenForo_DataWriter_RoutePrefix
    {
    }
}

class ThemeHouse_Controllers_Extend_XenForo_DataWriter_RoutePrefix extends XFCP_ThemeHouse_Controllers_Extend_XenForo_DataWriter_RoutePrefix
{

    /**
     *
     * @var string
     */
    const OPTION_PRIMARY_KEY_ID = 'primaryKeyId';

    /**
     *
     * @see XenForo_DataWriter_RoutePrefix::_getDefaultOptions()
     */
    protected function _getDefaultOptions()
    {
        $options = parent::_getDefaultOptions();
        
        $options[self::OPTION_PRIMARY_KEY_ID] = '';
        
        return $options;
    }

    protected function _preSave()
    {
        $class = $this->get('route_class');
        if (!XenForo_Application::autoload($class)) {
            $options = array(
                'title_plural' => str_replace('-', ' ', $this->get('original_prefix'))
            );
            if ($this->getOption(self::OPTION_PRIMARY_KEY_ID)) {
                $options['primary_key_id'] = $this->getOption(self::OPTION_PRIMARY_KEY_ID);
            }
            
            $phpFile = null;
            switch ($this->get('route_type')) {
                case 'public':
                    $phpFile = new ThemeHouse_Controllers_PhpFile_Route_Prefix($class, $options);
                    break;
                case 'admin':
                    $phpFile = new ThemeHouse_Controllers_PhpFile_Route_PrefixAdmin($class, $options);
                    break;
            }
            if (!is_null($phpFile)) {
                $phpFile->export(true);
            }
        }
        
        return parent::_preSave();
    }
}