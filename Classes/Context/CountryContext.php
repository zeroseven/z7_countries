<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Context;

use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\LanguageService;

class CountryContext implements AspectInterface
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
        $configuration['hreflang'] = LanguageService::createHreflang($language, $country);

        return new SiteLanguage(
            $language->getLanguageId(),
            $language->getLocale(),
            LanguageService::createBase($language, $country),
            $configuration
        );
    }

    /**
     * Fetch common information about the user
     *
     * @param string $name
     * @return int|bool|string|array
     * @throws AspectPropertyNotFoundException
     */
    public function get(string $name)
    {
        switch ($name) {
            case 'originalLanguages':
                return (array)$this->originalLanguages;
            case 'manipulatedLanguages':
                return (array)$this->manipulatedLanguages;
        }

        throw new AspectPropertyNotFoundException('Property "' . $name . '" not found in Aspect "' . __CLASS__ . '".', 1621452385);
    }
}
