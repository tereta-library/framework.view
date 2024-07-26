<?php declare(strict_types=1);

namespace Framework\View\Html\Node;

/**
 * ···························WWW.TERETA.DEV······························
 * ·······································································
 * : _____                        _                     _                :
 * :|_   _|   ___   _ __    ___  | |_    __ _        __| |   ___  __   __:
 * :  | |    / _ \ | '__|  / _ \ | __|  / _` |      / _` |  / _ \ \ \ / /:
 * :  | |   |  __/ | |    |  __/ | |_  | (_| |  _  | (_| | |  __/  \ V / :
 * :  |_|    \___| |_|     \___|  \__|  \__,_| (_)  \__,_|  \___|   \_/  :
 * ·······································································
 * ·······································································
 *
 * @class Framework\View\Html\Node\Tag
 * @package Framework\View\Html\Node
 * @link https://tereta.dev
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 */
class Tag
{
    private string $name;
    private int $positionStart;
    private int $positionEnd;
    private array $attributes;

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function setPosition(int $start, int $end): static
    {
        $this->positionStart = $start;
        $this->positionEnd = $end;
        return $this;
    }
}