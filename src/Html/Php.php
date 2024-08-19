<?php declare(strict_types=1);

namespace Framework\View\Html;

use Framework\Dom\Document;
use Framework\Dom\Node;
use Framework\View\Html\Php\Node as PhpNode;
use Exception;

/**
 * @class Framework\View\Html\Php
 */
class Php
{
    public function __construct(private Document &$document)
    {
    }

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

        if (!preg_match_all('/(((attribute):([a-z0-9]+):((\$[a-z0-9]+)(\[\'[a-z0-9]+\'\])*))(; +|$))/i', $bind, $matches)) {
            return;
        }

        $node->getTag()->setAttribute('data-backend-bind', null);

        $binds = [];
        foreach ($matches[3] as $index => $type) {
            $binds[] = [
                'type' => $type,
                'parameter' => $matches[4][$index],
                'variable' => $matches[5][$index],
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
        while(substr($variable, 0, 1) == '$') {
            $variable = substr($variable, 1);
        }

        $node->getTag()->setAttribute($attribute, "<?php echo isset(\${$variable}) ? \${$variable} : '' ?>");
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