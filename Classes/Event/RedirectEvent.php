<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Event;

final class RedirectEvent
{
    private array $languageMenu;
    private ?string $url;

    public function __construct(array $languageMenu, string $url = null)
    {
        $this->languageMenu = $languageMenu;
        $this->url = $url;
    }

    public function getLanguageMenu(): array
    {
        return $this->languageMenu;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }
}
