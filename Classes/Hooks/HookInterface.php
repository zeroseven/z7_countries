<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Hooks;

interface HookInterface
{
    /** To register hook in ext_localconf.php */
    public static function register(): void;
}
