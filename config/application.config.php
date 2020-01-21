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

        'BjyProfiler',
        'ZendDeveloperTools',

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
        'config_cache_key' => 'app-config.cache',
        'module_map_cache_enabled' => false,
        'module_map_cache_key' => 'app-module.cache',
        'cache_dir' => 'data/cache/',
    ],
    'view_manager' => [
        'display_exceptions' => true,
    ],
];