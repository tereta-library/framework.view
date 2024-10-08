<?php declare(strict_types=1);

namespace Framework\View\Html\Block;

use Framework\Dom\Document;
use Framework\Dom\Node as DomNode;
use Framework\View\Php\Abstract\Block as AbstractBlock;

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
 * @class Framework\View\Html\Block\Node
 * @package Framework\View\Html\Block
 * @link https://tereta.dev
 * @since 2020-2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 * @copyright 2020-2024 Tereta Alexander
 */
class Node extends DomNode
{
    /**
     * @var string|null $class
     */
    private ?string $class = null;

    /**
     * @var string|null $template
     */
    private ?string $template = null;

    /**
     * @var string|null $blockIdentifier
     */
    private ?string $blockIdentifier = null;

    /**
     * @var AbstractBlock|null $block
     */
    private ?AbstractBlock $block = null;

    /**
     * @var array $registeredBlock
     */
    private static array $registeredBlock = [];

    /**
     * @var int $blockCounter
     */
    private static int $blockCounter = 0;

    /**
     * @param Document $document
     */
    public function __construct(Document &$document)
    {
        parent::__construct($document);
    }

    /**
     * @return string
     */
    public function renderContent(): string
    {
        if ($this->getName() != 'backend:container') {
            return parent::render();
        }

        $return = '';
        foreach ($this->getChildren() as $child) {
            $return .= $child->render();
        }
        return $return;
    }

    /**
     * @param AbstractBlock $block
     * @return void
     */
    public function setBlock(AbstractBlock $block): void {
        self::$blockCounter++;
        $this->block = $block;
        $this->blockIdentifier = $blockIdentifier = get_class($block);

        if (isset(static::$registeredBlock[$blockIdentifier])) {
            $this->blockIdentifier = $blockIdentifier = get_class($block) . '#' . self::$blockCounter;
        }

        static::$registeredBlock[$blockIdentifier] = $block;
    }

    /**
     * @return AbstractBlock
     */
    public function getBlock(): AbstractBlock {
        return $this->block;
    }

    /**
     * @return string
     */
    public function getBlockIdentifier(): string {
        return $this->blockIdentifier;
    }

    /**
     * @param string $block
     * @return AbstractBlock
     */
    static public function getRegisteredBlock(string $block): AbstractBlock
    {
        return static::$registeredBlock[$block];
    }

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate(string $template): static
    {
        $this->block->setTemplate($template);
        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $blockClass = str_replace('\\', '\\\\', $this->blockIdentifier);
        return "<?php echo \Framework\View\Html\Block\Node::getRegisteredBlock(\"{$blockClass}\")->render() ?>";
    }
}