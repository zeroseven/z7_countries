<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Menu\Item;

use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use Zeroseven\Countries\Exception\MenuException;
use Zeroseven\Countries\Model\Country;

class CountryItem extends AbstractItem
{
    protected array $languages;

    public function __construct(SiteLanguage $language, Country $country = null)
    {
        parent::__construct($language, $country);

        $this->object = $country;
    }

    public function getCountry(): Country
    {
        return $this->object;
    }

    public function getLanguages(): array
    {
        return $this->languages;
    }

    public function hasLanguage($language): bool
    {
        if ($language instanceof LanguageItem || $language instanceof SiteLanguage) {
            return isset($this->languages[$language->getLanguageId()]);
        }

        throw new MenuException('Value musst be type of LanguageItem or SiteLanguage', 1647335435);
    }

    public function addLanguageItem(LanguageItem $languageItem): self
    {
        if (!$this->hasLanguage($languageItem)) {
            $this->languages[$languageItem->getLanguageId()] = $languageItem;
        }

        return $this;
    }
}
