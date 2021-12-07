<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Xclass;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Context\CountryContext;

class Site extends \TYPO3\CMS\Core\Site\Entity\Site
{
    public function __construct(string $identifier, int $rootPageId, array $configuration)
    {
        parent::__construct($identifier, $rootPageId, $configuration);

        // Sore languages in context
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('country', GeneralUtility::makeInstance(CountryContext::class, $this));

        // Manipulate languages
        if (!empty($manipulatedLanguages = $context->getPropertyFromAspect('country', 'manipulatedLanguages'))) {
            $this->languages = $manipulatedLanguages;
        }
    }
}
