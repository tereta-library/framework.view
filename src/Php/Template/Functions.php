<?php declare(strict_types=1);

namespace Framework\View\Php\Template;

use Framework\View\Php\Template\Functions as TemplateFunctions;
use Framework\Dictionary\Service\Translation;

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
 * @class Framework\View\Php\Template
 * @package Framework\View\Php
 * @link https://tereta.dev
 * @since 2020-2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 * @copyright 2020-2024 Tereta Alexander
 */
class Functions
{
    /**
     * @var Functions|null $instance
     */
    private static ?TemplateFunctions $instance = null;

    /**
     * @var Translation|null $vocabulary
     */
    private static ?Translation $translation = null;

    /**
     * @return $this
     */
    public static function getInstance(): static
    {
        if (self::$instance) {
            return self::$instance;
        }

        return self::$instance = new static();
    }

    /**
     * @param string $prefix
     * @param string $test
     * @return string
     */
    public function concat(string $prefix, string $test): string
    {
        return $prefix . $test;
    }

    /**
     * @param string $string
     * @return string
     */
    public function capitalUpperCase(string $string): string
    {
        return ucfirst($string);
    }

    /**
     * @param string $string
     * @return string
     */
    public function translate(string $string): string
    {
        if (!static::$translation) {
            return $string;
        }

        return static::$translation->translate($string);
    }

    /**
     * @param Translation $translation
     * @return void
     */
    public static function setTranslation(Translation $translation): void
    {
        static::$translation = $translation;
    }
}


