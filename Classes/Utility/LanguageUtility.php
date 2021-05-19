<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Utility;

use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use Zeroseven\Countries\Service\CountryService;

class LanguageUtility
{
    /** @var array */
    protected $originalLanguages;

    /** @var array */
    protected $manipulatedLanguages;

    public function __construct(array $originalLanguages)
    {
        $this->originalLanguages = $originalLanguages;

        if (!empty($country = CountryService::getCountryByUri())) {
            foreach ($originalLanguages as $originalLanguage) {
                $this->manipulatedLanguages[] = $this->manipulateLanguage($originalLanguage, (int)$country['uid']);
            }
        }
    }

    protected function manipulateLanguage(SiteLanguage $language, int $country): SiteLanguage
    {
        $configuration = $language->toArray();
        $configuration['hreflang'] = $this->createLanguageHreflang($language, $country);

        return new SiteLanguage(
            $language->getLanguageId(),
            $language->getLocale(),
            $this->createLanguageBase($language, $country),
            $configuration
        );
    }

    protected function createLanguageBase(SiteLanguage $language, int $countryUid): UriInterface
    {
        if ($country = CountryService::getCountryByUid($countryUid)) {
            return $language->getBase()->withPath('/' . $language->getTwoLetterIsoCode() . CountryService::DELIMITER . $country['iso_code'] . '/');
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

    public function getOriginalLanguages(): array
    {
        return (array)$this->originalLanguages;
    }

    public function getManipulatedLanguages(): array
    {
        return (array)$this->manipulatedLanguages;
    }
}
