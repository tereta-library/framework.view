<?php declare(strict_types=1);

namespace Framework\View\Html\Php;

use Exception;
use Framework\View\Html\Php\Renderer\CallFunction;

/**
 * @class Framework\View\Html\Php\Renderer
 */
class Renderer
{
    /**
     * @param string $expression
     * @return array|null
     */
    public function verify(string $expression): ?array
    {
        if (!preg_match('/^\s*\$([A-Za-z0-9]+)(((\[[a-zA-Z0-9]+\])|(->[a-zA-Z]+)(\(.*\))?)*)$/', $expression, $matches)) {
            return null;
        }

        $variable = '$' . $matches[1];
        $parts = $matches[2];

        return [$variable, $parts];
    }

    /**
     * @param string $expression
     * @return string
     * @throws Exception
     */
    public function render(string $expression): string
    {
        if (preg_match('/^[1-9]+$/', $expression, $matches)) {
            return $expression;
        }

        if (preg_match('/^"(.*)"$/', $expression, $matches)) {
            return $matches[0];
        }

        if (!$matched = $this->verify($expression)) {
            throw new Exception("Wrong attribute bind expression ({$expression})");
        }

        list ($variable, $parts) = $matched;

        $render = $variable;

        while ($parts) {
            if (preg_match('/^\[([a-z0-9A-Z]+)\]/Usi', $parts, $matches)) {
                $render .= "['{$matches[1]}']";
                $parts = substr($parts, strlen($matches[0]));
                continue;
            }

            if ($function = CallFunction::parseFunction($parts)) {
                list($function, $arguments, $length) = $function;
                try {
                    $parametersRender = $this->renderFunction($arguments);
                } catch (Exception $e) {
                    throw $e;
                }
                $render .= "->{$function}(" . $parametersRender . ")";
                $parts = substr($parts, $length);
                continue;
            }

            if (preg_match('/^->([a-z0-9A-Z]+)/Usi', $parts, $matches)) {
                $render .= "->{$matches[1]}";
                $parts = substr($parts, strlen($matches[0]));
                continue;
            }

            throw new Exception("Wrong attribute bind expression ({$expression})");
        }

        return $render;
    }

    /**
     * @param string $expression
     * @return string
     * @throws Exception
     */
    private function renderFunction(string $expression): string
    {
        $parameters = [];
        $pointer = 0;
        while ($expression) {
            if (substr($expression, 0, 1) == "'") {
                $expression = substr($expression, 1);

                if (!preg_match('/^(.*?[^\\\\])\'(, |$)/', $expression, $matches)) {
                    throw new Exception("Wrong attribute bind expression ({$expression})");
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