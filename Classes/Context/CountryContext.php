<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Context;

use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class CountryContext implements AspectInterface
{
    /** @var SiteLanguage[] */
    protected $originalLanguages;

    /** @var SiteLanguage[] */
    protected $manipulatedLanguages;

    public function __construct(array $originalLanguages, array $manipulatedLanguages)
    {
        $this->originalLanguages = $originalLanguages;
        $this->manipulatedLanguages = $manipulatedLanguages;
    }

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
