<?php declare(strict_types=1);

namespace Framework\View\Html\Php;

use Exception;

/**
 * @class Framework\View\Html\Php\Renderer
 */
class Renderer
{
    public function verify(string $expression): ?array
    {
        if (!preg_match('/^\$([A-Za-z0-9]+)(((\[[a-zA-Z]+\])|(->[a-zA-Z]+)(\(.*\))?)*)$/', $expression, $matches)) {
            return null;
        }

        $variable = '$' . $matches[1];
        $parts = $matches[2];

        return [$variable, $parts];
    }

    public function render(string $expression): string
    {
        if (preg_match('/^[1-9]+$/', $expression, $matches)) {
            return $expression;
        }

        if (!$matched = $this->verify($expression)) {
            throw new Exception("Wrong attribute bind variable ({$expression})");
        }

        list ($variable, $parts) = $matched;

        $render = $variable;

        while ($parts) {
            if (preg_match('/^\[([a-z0-9A-Z]+)\]/Usi', $parts, $matches)) {
                $render .= "['{$matches[1]}']";
                $parts = substr($parts, strlen($matches[0]));
                continue;
            }

            if (preg_match('/^->([a-z0-9A-Z]+)\((.*)\)/', $parts, $matches)) {
                $render .= "->{$matches[1]}(" . $this->render($matches[2]) . ")";
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
}