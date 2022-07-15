<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Event;

final class RedirectEvent
{
    private array $languageMenu;
    private ?string $url;
    private int $status;

    public function __construct(array $languageMenu, string $url = null, int $status = null)
    {
        $this->languageMenu = $languageMenu;
        $this->url = $url;
        $this->status = $status ?: 307;
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

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }
}
