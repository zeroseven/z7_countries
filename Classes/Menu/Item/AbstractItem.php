<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Menu\Item;

use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use Zeroseven\Countries\Model\Country;
use Zeroseven\Countries\Service\LanguageManipulationService;

abstract class AbstractItem
{
    /** @var SiteLanguage|Country */
    protected $object;

    protected string $link;

    protected string $hreflang;

    protected bool $available;

    protected bool $active;

    protected bool $current;

    public function __construct(SiteLanguage $language, Country $country = null)
    {
        $this->hreflang = LanguageManipulationService::getHreflang($language, $country);
    }

    /** @return SiteLanguage|Country */
    public function getObject()
    {
        return $this->object;
    }

    public function getData(): ?array
    {
        return method_exists($this->object, 'toArray') ? $this->object->toArray() : [];
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getHreflang(): string
    {
        return $this->hreflang;
    }

    public function setHreflang(string $hreflang): self
    {
        $this->hreflang = $hreflang;

        return $this;
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): self
    {
        $this->available = $available;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function isCurrent(): bool
    {
        return $this->current;
    }

    public function setCurrent(bool $current): self
    {
        $this->current = $current;

        return $this;
    }

    /**
     * Pass methods to the object
     *
     * @param $action
     * @param $arguments
     */
    public function __call($action, $arguments)
    {
        if (is_callable([$this->object, $action]) && preg_match('/^(get|has|is)/', $action)) {
            return $this->getObject()->$action();
        }
    }
}
