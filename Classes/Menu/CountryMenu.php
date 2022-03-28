<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Menu;

use Zeroseven\Countries\Service\CountryService;

class CountryMenu extends AbstractMenu
{
    public function render(): array
    {
        $menu = [];

        foreach ($this->site->getLanguages() as $language) {
            foreach (CountryService::getCountriesByLanguageUid($language->getLanguageId()) as $country) {
                if (!isset($menu[$country->getUid()])) {
                    $menu[$country->getUid()] = $this->getCountryMenuItem($language, $country);
                }

                if (!$menu[$country->getUid()]->hasLanguage($language)) {
                    $menu[$country->getUid()]->addLanguageItem($this->getLanguageMenuItem($language, $country, $menu[$country->getUid()]->isAvailable()));
                }
            }
        }

        return $menu;
    }
}
