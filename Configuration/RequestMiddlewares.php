<?php

return [
    'frontend' => [
        'zeroseven/z7_countries/redirect' => [
            'target' => \Zeroseven\Countries\Middleware\Redirect::class,
            'after' => [
                'typo3/cms-frontend/tsfe'
            ]
        ]
    ]
];
