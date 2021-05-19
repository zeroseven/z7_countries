<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Xclass;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Utility\LanguageUtility;

class Site extends \TYPO3\CMS\Core\Site\Entity\Site
{
    /** @var LanguageUtility */
    protected $languageUtility;

    public function __construct(string $identifier, int $rootPageId, array $configuration)
    {
        parent::__construct($identifier, $rootPageId, $configuration);

        // Create instance of LanguageUtility
        $this->languageUtility = GeneralUtility::makeInstance(LanguageUtility::class, $this->languages);

        // Manipulate languages
        if (!empty($manipulatedLanguages = $this->languageUtility->getManipulatedLanguages())) {
            $this->languages = $manipulatedLanguages;
        }
    }
}
