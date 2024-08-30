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

//        if (!preg_match_all('/(((attribute):([a-z0-9]+):((\$[a-z0-9]+)(\[\'[a-z0-9]+\'\])*))(; +|$))/i', $bind, $matches)) {
//            return;
//        }

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

        $render = $this->processBindAttributeVariable($variable);
        $render = "<?php echo {$render} ?>";
        $node->getTag()->setAttribute($attribute, $render);
    }

    private function processBindAttributeVariable(string $variable)
    {
        if (preg_match('/^[1-9]+$/', $variable, $matches)) {
            return $variable;
        }

        if (!preg_match('/^\$([A-Za-z0-9]+)(((\[[a-zA-Z]+\])|(->[a-zA-Z]+)(\(.*\))?)*)$/', $variable, $matches)) {
            throw new Exception('Wrong attribute bind variable');
        }

        $variable = '$' . $matches[1];
        $parts = $matches[2];
        $render = $variable;

        while ($parts) {
            if (preg_match('/^\[([a-z0-9A-Z]+)\]/Usi', $parts, $matches)) {
                $render .= "['{$matches[1]}']";
                $parts = substr($parts, strlen($matches[0]));
                continue;
            }

            if (preg_match('/^->([a-z0-9A-Z]+)\((.*)\)/', $parts, $matches)) {
                $render .= "->{$matches[1]}(" . $this->processBindAttributeVariable($matches[2]) . ")";
                $parts = substr($parts, strlen($matches[0]));
                continue;
            }

            if (preg_match('/^->([a-z0-9A-Z]+)/Usi', $parts, $matches)) {
                $render .= "->{$matches[1]}";
                $parts = substr($parts, strlen($matches[0]));
            }
        }

        return $render;
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