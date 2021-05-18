<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:tx_z7countries_country',
        'label' => 'title',
        'default_sortby' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'rootLevel' => 1,
        'adminOnly' => true,
        'enablecolumns' => [
            'disabled' => 'hidden'
        ],
        'searchFields' => 'title, iso_code',
        'typeicon_classes' => [
            'default' => 'flags-multiple',
            'mask' => 'flags-###TYPE###'
        ],
        'typeicon_column' => 'flag'
    ],
    'types' => [
        '1' => [
            'showitem' => 'hidden, title, iso_code, flag'
        ]
    ],
    'columns' => [
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'items' => [
                    '1' => [
                        '0' => 'LLL:EXT:lang/locallang_core.xlf:labels.enabled'
                    ]
                ]
            ]
        ],
        'title' => [
            'exclude' => false,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:tx_z7countries_country.title',
            'config' => [
                'type' => 'input',
                'eval' => 'trim,required',
                'default' => ''
            ]
        ],
        'iso_code' => [
            'exclude' => false,
            'label' => 'LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:tx_z7countries_country.iso_code',
            'config' => [
                'type' => 'input',
                'eval' => 'trim,required,alpha,nospace,unique',
                'default' => ''
            ]
        ],
        'flag' => [
            'exclude' => true,
            'label' => 'LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:tx_z7countries_country.flag',
            'config' => $GLOBALS['TCA']['sys_language']['columns']['flag']['config']
        ]
    ]
];
