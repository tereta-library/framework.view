<?php declare(strict_types=1);

namespace Framework\View\Html\Php\Renderer;

/**
 * @class Framework\View\Html\Php\Renderer\CallFunction
 */
class CallFunction
{
    /**
     * @param string $expression
     * @return array|null
     */
    public static function parseFunction(string $expression): ?array
    {
        if (!preg_match('/^->([a-z0-9A-Z]+)\(/', $expression, $matches)) {
            return null;
        }

        list($part, $function) = $matches;
        $pointer = strlen($part);
        $parameter = substr($expression, $pointer);
        $skipMode = false;
        $brackets = 0;
        for($s=0; $s<strlen($parameter); $s++) {
            $symbol = $parameter[$s];
            if ($skipMode == false && $symbol == "'") {
                $skipMode = "'";
                continue;
            }

            if ($skipMode && $symbol == $skipMode) {
                $skipMode = false;
                continue;
            }

            if ($skipMode) continue;
            if ($symbol == '(') $brackets++;
            if ($symbol == ')') $brackets--;
            if ($brackets < 0) {
                break;
            }
        }

        $pointer = $pointer + $s;

        if ($brackets > 0) {
            throw new Exception("Wrong function call expression ({$expression})");
        }

        $parameter = substr($parameter, 0, $s);

        return [$function, $parameter, $pointer + 1];
    }
}
