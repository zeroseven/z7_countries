<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Xclass;

use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class Site extends \TYPO3\CMS\Core\Site\Entity\Site
{
    public function __construct(string $identifier, int $rootPageId, array $configuration)
    {
        parent::__construct($identifier, $rootPageId, $configuration);

        // Manipulate languages
        foreach ($this->languages as $i => $language) {
            $this->languages[$i] = new SiteLanguage(
                $language->getLanguageId(),
                $language->getLocale(),
                $language->getBase()->withPath(rtrim($language->getBase()->getPath(), '/') . '_de/'),
                $language->toArray()
            );
        }
    }
}
