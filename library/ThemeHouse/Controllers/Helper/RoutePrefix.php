<?php

class ThemeHouse_Controllers_Helper_RoutePrefix
{

    public static function camelCaseToHyphenCase($camelCase)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $camelCase, $matches);
        $hypenCase = $matches[0];
        foreach ($hypenCase as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('-', $hypenCase);
    }

    public static function hyphenCaseToCamelCase($snakeCase, $lcFirst = false)
    {
        $snakeCase = str_replace(' ', '', ucwords(str_replace('-', ' ', $snakeCase)));
        
        if ($lcFirst) {
            if (PHP_VERSION_ID < 50300) {
                $snakeCase = strtolower(substr($snakeCase, 0, 1)) . substr($snakeCase, 1);
            } else {
                $snakeCase = lcfirst($snakeCase);
            }
        }
        
        return $snakeCase;
    }
}