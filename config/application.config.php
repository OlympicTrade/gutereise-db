<?php
return [
    'modules' => [
        'Application',
        'Clients',
        'Documents',
        'Drivers',
        'Excursions',
        'Guides',
        'Hotels',
        'Clients',
        'Managers',
        'Museums',
        'Orders',
        'Users',
        'Translator',
        'Transports',
        'Users',
        'Sync',

        /*'Zend\Hydrator',
        'Zend\InputFilter',
        'Zend\Paginator',
        'Zend\I18n',
        'Zend\Filter',
        'Zend\Router',
        'Zend\Validator',*/

        'Zend\Router',
        'Zend\Cache',
        'Zend\Form',
    ],
    'module_listener_options' => [
        'module_paths' => [
            './local',
            './module',
            './vendor',
        ],
        'config_glob_paths' => [
            realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php',
        ],
        'config_cache_enabled' => false,
        //'config_cache_enabled' => true,
        'config_cache_key' => 'app-config.cache',
        'module_map_cache_enabled' => false,
        //'module_map_cache_enabled' => true,
        'module_map_cache_key' => 'app-module.cache',
        'cache_dir' => 'data/cache/',
    ],
];