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
        if (($countryUid = ParameterService::getCountry()) !== null) {
            foreach ($this->languages as $i => $language) {

                $configuration = $language->toArray();

                $configuration['hreflang'] = ParameterService::createLanguageHreflang($language, $countryUid);

                $this->languages[$i] = new SiteLanguage(
                    $language->getLanguageId(),
                    $language->getLocale(),
                    ParameterService::createLanguageBase($language, $countryUid),
                    $configuration
                );
            }
        }

    }
}
