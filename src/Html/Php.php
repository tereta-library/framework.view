<?php declare(strict_types=1);

namespace Framework\View\Html;

use Framework\Dom\Document;
use Framework\Dom\Node;
use Framework\View\Html\Php\Node as PhpNode;
use Framework\View\Html\Php\Renderer as PhpRenderer;
use Exception;

/**
 * @class Framework\View\Html\Php
 */
class Php
{
    /**
     * @var PhpRenderer
     */
    private PhpRenderer $phpRenderer;

    /**
     * @param Document $document
     */
    public function __construct(private Document &$document)
    {
        $this->phpRenderer = new PhpRenderer;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function process() {
        foreach($this->document->getNodeList() as $node) {
            $this->processComment($node);
            $this->processBind($node);
        }
    }

    /**
     * @param $node
     * @return void
     */
    private function processBind($node)
    {
        if ($node->getType() != Node::TYPE_TAG) {
            return;
        }

        $bind = $node->getTag()->getAttribute('data-backend-bind');
        if (!$bind) {
            return;
        }

        if (!preg_match_all('/((attribute):([a-z0-9]+):((.+)(; +|$)))/Usi', $bind, $matches)) {
            return;
        }

        $node->getTag()->setAttribute('data-backend-bind', null);

        $binds = [];
        foreach ($matches[2] as $index => $type) {
            $binds[] = [
                'type' => $type,
                'parameter' => $matches[3][$index],
                'variable' => $matches[4][$index],
            ];
        }

        foreach ($binds as $bind) {
            switch($bind['type']) {
                case 'attribute':
                    $this->processBindAttribute($node, $bind);
                    break;
            }
        }
    }

    private function processBindAttribute($node, $bind)
    {
        $attribute = $bind['parameter'];
        $variable = $bind['variable'];

        $render = $this->phpRenderer->render($variable);
        $render = "<?php if ({$render}) : ?>{$attribute}=<?php echo \$this->quoteAttribute({$render}) ?><?php endif ?>";
        $node->getTag()->setAttribute($attribute, null);
        $node->getTag()->setAttributeScript($render);
    }


    /**
     * @param $node
     * @return void
     * @throws Exception
     */
    private function processComment($node)
    {
        if ($node->getType() != Node::TYPE_COMMENT) {
            return;
        }

        $tag = $node->getTag();
        $content = $tag->getContent();

        if (substr($content, 0, 2) != ': ') {
            return;
        }

        $phpNode = (new PhpNode($this->document))->import($node->export());
        $node->getParent()->replaceChild($node, $phpNode);
    }
}