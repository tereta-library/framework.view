<?php declare(strict_types=1);

namespace Framework\View\Php;

use Framework\View\Php\Abstract\Block as AbstractBlock;
use Framework\Pattern\Factory as PatternFactory;
use ReflectionException;

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
 * @class Framework\View\Php\Factory
 * @package Framework\View\Php
 * @link https://tereta.dev
 * @since 2020-2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 * @copyright 2020-2024 Tereta Alexander
 */
class Factory
{
    /**
     * @var PatternFactory $factory
     */
    private PatternFactory $factory;

    /**
     * @var string|null
     */
    public static ?string $themeDirectory = null;

    /**
     * @param string|null $instanceThemeDirectory
     */
    public function __construct(
        protected ?string $instanceThemeDirectory = null
    ) {
        $this->factory = (new PatternFactory(AbstractBlock::class));
    }

    /**
     * @param string $class
     * @param array $data
     * @return AbstractBlock
     * @throws ReflectionException
     */
    public function create(string $class, array $data = []): AbstractBlock
    {
        return $this->factory->create($class, [$this->themeDirectory ?? static::$themeDirectory, $data]);
    }
}