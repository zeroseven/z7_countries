<?php

return [
    'frontend' => [
        'zeroseven/z7_countries/redirect' => [
            'target' => \Zeroseven\Countries\Middleware\Redirect::class,
            'before' => [
                'typo3/cms-frontend/tsfe'
            ],
            'after' => [
                'typo3/cms-frontend/base-redirect-resolver'
            ]
        ]
    ]
];
