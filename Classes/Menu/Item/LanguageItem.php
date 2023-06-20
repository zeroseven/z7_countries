<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Menu\Item;

use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use Zeroseven\Countries\Exception\MenuException;
use Zeroseven\Countries\Model\Country;

class LanguageItem extends AbstractItem
{
    protected array $countries = [];
    protected bool $disabled = false;

    public function __construct(SiteLanguage $language, Country $country = null)
    {
        parent::__construct($language, $country);

        $this->object = $language;
        $this->disabled = $country === null && ($this->getData()['disable_international'] ?? false);
    }

    public function getLanguage(): SiteLanguage
    {
        return $this->object;
    }

    public function getCountries(): array
    {
        return $this->countries;
    }

    /** @throws MenuException */
    public function hasCountry($country): bool
    {
        if ($country instanceof CountryItem || $country instanceof Country) {
            return isset($this->countries[$country->getUid()]);
        }

        throw new MenuException('Value must be type of CountryItem or Country', 1647335434);
    }

    /** @throws MenuException */
    public function addCountryItem(CountryItem $countryItem): self
    {
        if (!$this->hasCountry($countryItem)) {
            $this->countries[$countryItem->getUid()] = $countryItem;
        }

        return $this;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function isEnabled(): bool
    {
        return !$this->isDisabled();
    }

    public function isAvailable(): bool
    {
        return $this->isEnabled() && parent::isAvailable();
    }
}
