<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Model;

use Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Country
{
    /** @var int */
    protected $uid;

    /** @var string */
    protected $title;

    /** @var string */
    protected $isoCode;

    /** @var string */
    protected $flag;

    /** @var string */
    protected $parameter;

    public function __construct(array $row)
    {
        foreach (['uid', 'title', 'iso_code', 'parameter'] as $property) {
            if (empty($row[$property])) {
                throw new Exception('Required property "' . $property . '" is missing. Object of country cannot be created.', 1622057576);
            }
        }

        $this->setUid((int)$row['uid']);
        $this->setTitle($row['title']);
        $this->setIsoCode($row['iso_code']);
        $this->setParameter($row['parameter']);
        $this->setFlag($row['flag']);
    }

    public static function makeInstance(array $row): self
    {
        return GeneralUtility::makeInstance(self::class, $row);
    }

    public function toArray(): array
    {
        return [
            'uid' => $this->getUid(),
            'title' => $this->getTitle(),
            'iso_code' => $this->getIsoCode(),
            'parameter' => $this->getParameter(),
            'flag' => $this->getFlag()
        ];
    }

    public function getUid(): int
    {
        return $this->uid;
    }

    public function setUid(int $uid): void
    {
        $this->uid = $uid;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getIsoCode(): string
    {
        return $this->isoCode;
    }

    public function setIsoCode(string $isoCode): void
    {
        $this->isoCode = $isoCode;
    }

    public function getFlag(): string
    {
        return $this->flag;
    }

    public function setFlag(string $flag): void
    {
        $this->flag = $flag;
    }

    public function getParameter(): string
    {
        return $this->parameter;
    }

    public function setParameter(string $parameter): void
    {
        $this->parameter = $parameter;
    }
}
