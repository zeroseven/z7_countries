<?php

return [
    'frontend' => [
        'zeroseven/z7_countries/redirect' => [
            'target' => \Zeroseven\Countries\Middleware\Redirect::class,
            'after' => [
                'typo3/cms-frontend/tsfe'
            ]
        ],
        'zeroseven/z7_countries/preview' => [
            'target' => \Zeroseven\Countries\Middleware\Preview::class,
            'before' => [
                'typo3/cms-frontend/preview-simulator'
            ],
            'after' => [
                'typo3/cms-frontend/page-resolver'
            ]
        ]
    ]
];
