<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Menu\Item;

use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use Zeroseven\Countries\Exception\MenuException;
use Zeroseven\Countries\Model\Country;

class LanguageItem extends AbstractItem
{
    protected array $countries;

    public function __construct(SiteLanguage $language, Country $country = null)
    {
        parent::__construct($language, $country);

        $this->object = $language;
    }

    public function getLanguage(): SiteLanguage
    {
        return $this->object;
    }

    public function getCountries(): array
    {
        return $this->countries;
    }

    public function hasCountry($country): bool
    {
        if ($country instanceof CountryItem || $country instanceof Country) {
            return isset($this->countries[$country->getUid()]);
        }

        throw new MenuException('Value musst be type of CountryItem or Country', 1647335434);
    }

    public function addCountryItem(CountryItem $countryItem): self
    {
        if (!$this->hasCountry($countryItem)) {
            $this->countries[$countryItem->getUid()] = $countryItem;
        }

        return $this;
    }
}
