<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Context;

use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use Zeroseven\Countries\Model\Country;

final readonly class CountryRequestContext implements AspectInterface
{
    public function __construct(private ?Country $country, private bool $pageResolved = false) {}

    public function get(string $name): mixed
    {
        if ($name === 'country') {
            return $this->country;
        }
        if ($name === 'pageResolved') {
            return $this->pageResolved;
        }

        throw new AspectPropertyNotFoundException('Property "' . $name . '" not found in Aspect "' . __CLASS__ . '".', 1753193297);
    }
}
