<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Xclass;

use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\LanguageService;

class Site extends \TYPO3\CMS\Core\Site\Entity\Site
{

    protected function createLanguageBase(SiteLanguage $language, int $countryUid): UriInterface
    {
        if ($country = CountryService::getCountryByUid($countryUid)) {
            return $language->getBase()->withPath($language->getTwoLetterIsoCode() . CountryService::DELIMITER . $country['iso_code']);
        }

        return $language->getBase();
    }

    protected function createLanguageHreflang(SiteLanguage $language, int $countryUid): string
    {
        if ($country = CountryService::getCountryByUid($countryUid)) {
            return $language->getTwoLetterIsoCode() . '_' . strtoupper($country['iso_code']);
        }

        return $language->getHreflang();
    }

    public function __construct(string $identifier, int $rootPageId, array $configuration)
    {
        parent::__construct($identifier, $rootPageId, $configuration);

        // Manipulate languages
        if (($countryUid = CountryService::getCountryByUri()) !== null) {
            foreach ($this->languages as $i => $language) {

                $configuration = $language->toArray();

                $configuration['hreflang'] = $this->createLanguageHreflang($language, $countryUid);

                $this->languages[$i] = new SiteLanguage(
                    $language->getLanguageId(),
                    $language->getLocale(),
                    $this->createLanguageBase($language, $countryUid),
                    $configuration
                );
            }
        }
    }
}
