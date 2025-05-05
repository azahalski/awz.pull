<?php
return [
    'ui.entity-selector' => [
        'value' => [
            'entities' => [
                [
                    'entityId' => 'awzpull-user',
                    'provider' => [
                        'moduleId' => 'awz.pull',
                        'className' => '\\Awz\\Pull\\Access\\EntitySelectors\\User'
                    ],
                ],
                [
                    'entityId' => 'awzpull-group',
                    'provider' => [
                        'moduleId' => 'awz.pull',
                        'className' => '\\Awz\\Pull\\Access\\EntitySelectors\\Group'
                    ],
                ],
            ]
        ],
        'readonly' => true,
    ]
];