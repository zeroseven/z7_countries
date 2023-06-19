<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Context;

use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class CountryContext implements AspectInterface
{
    /** @var SiteLanguage[] */
    protected ?array $originalLanguages = null;

    /** @var SiteLanguage[] */
    protected ?array $manipulatedLanguages = null;

    public function __construct(array $originalLanguages, array $manipulatedLanguages = null)
    {
        $this->originalLanguages = $originalLanguages;
        $this->manipulatedLanguages = $manipulatedLanguages;
    }

    public function get(string $name)
    {
        switch ($name) {
            case 'originalLanguages':
                return $this->originalLanguages;
            case 'manipulatedLanguages':
                return $this->manipulatedLanguages;
        }

        throw new AspectPropertyNotFoundException('Property "' . $name . '" not found in Aspect "' . __CLASS__ . '".', 1621452385);
    }
}
