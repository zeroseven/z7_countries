<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Xclass;

use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use Zeroseven\Countries\Service\ParameterService;

class Site extends \TYPO3\CMS\Core\Site\Entity\Site
{
    public function __construct(string $identifier, int $rootPageId, array $configuration)
    {
        parent::__construct($identifier, $rootPageId, $configuration);

        // Manipulate languages
        if ($country = ParameterService::getCountry()) {
            foreach ($this->languages as $i => $language) {
                $this->languages[$i] = new SiteLanguage(
                    $language->getLanguageId(),
                    $language->getLocale(),
                    ParameterService::addCountry($country, $language->getBase()),
                    $language->toArray()
                );
            }
        }
    }
}
