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

        if (preg_match('/^"(.*)"$/', $expression, $matches)) {
            return $matches[0];
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
                $parametersRender = $this->renderFunction($matches[2]);
                $render .= "->{$matches[1]}(" . $parametersRender . ")";
                $parts = substr($parts, strlen($matches[0]));
                continue;
            }

            if (preg_match('/^->([a-z0-9A-Z]+)/Usi', $parts, $matches)) {
                $render .= "->{$matches[1]}";
                $parts = substr($parts, strlen($matches[0]));
                continue;
            }

            throw new Exception("Wrong attribute bind variable ({$expression})");
        }

        return $render;
    }

    private function renderFunction(string $expression): string
    {
        $parameters = [];
        $pointer = 0;
        while ($expression) {
            if (substr($expression, 0, 1) == "'") {
                $expression = substr($expression, 1);

                if (!preg_match('/^(.*?[^\\\\])\'(, |$)/', $expression, $matches)) {
                    throw new Exception("Wrong attribute bind variable ({$expression})");
                }

                $value = $matches[1];
                $value = str_replace('"', '\\"', $value);
                $value = str_replace("\\", "\\\\", $value);
                $parameters[$pointer] = '"' . $value . '"';
                $expression = substr($expression, strlen($matches[0]));

                if ($matches[2] == ', ') {
                    $pointer++;
                }
                continue;
            }

            $separator = strpos($expression, ', ');
            if ($separator !== false) {
                $parameters[$pointer] = substr($expression, 0, $separator);
                $expression = substr($expression, $separator + 1);
                $pointer++;
                continue;
            }

            if ($separator === false) {
                $parameters[$pointer] = $expression;
                break;
            };
        }
        $render = [];

        foreach ($parameters as $parameter) {
            $render[] = $this->render($parameter);
        }

        return implode(', ', $render);
    }
}