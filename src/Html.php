<?php declare(strict_types=1);

namespace Framework\View;

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
 * @class Framework\View\Html
 * @package Framework\View
 * @link https://tereta.dev
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 */
class Html
{
    /**
     * @var string
     */
    private string $document = '';

    /**
     * @param string $theme Theme path
     */
    public function __construct(private string $theme)
    {
    }

    /**
     * @param string $template
     * @return $this
     */
    public function load(string $template): static
    {
        $this->document = file_get_contents($this->theme . '/' . $template . '.html');

        return $this;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        return $this->document;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }
}