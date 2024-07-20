<?php declare(strict_types=1);

namespace Framework\View\Php;

use Framework\View\Php\Abstract\Block as AbstractBlock;
use Framework\Pattern\Factory as PatternFactory;

/**
 * class Framework\View\Php\Factory
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
     * @param string $instanceThemeDirectory
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
     */
    public function create(string $class, array $data = []): AbstractBlock
    {
        return $this->factory->create($class, [$this->themeDirectory ?? static::$themeDirectory, $data]);
    }
}