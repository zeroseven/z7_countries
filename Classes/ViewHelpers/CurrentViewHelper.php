<?php

declare(strict_types=1);

namespace Zeroseven\Countries\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Zeroseven\Countries\Service\CountryService;

class CurrentViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('name', 'string', 'Name of the variable to represent the current country object', true);
    }

    /**
     * @return mixed
     */
    public function render()
    {
        $country = CountryService::getCountryByUri();
        if ($country) {
            $this->templateVariableContainer->add($this->arguments['name'], $country->toArray());
            $output = $this->renderChildren();
            $this->templateVariableContainer->remove($this->arguments['name']);
            return $output;
        }
        return $this->renderChildren();
    }
}
