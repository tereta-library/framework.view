<?php declare(strict_types=1);

namespace Framework\View\Html\Php\Interpolation;

/**
 * @class Framework\View\Html\Php\Interpolation\Variable
 */
class Variable
{
    private ?int $position;
    private ?int $length;

    public function __construct()
    {
    }

    public function find(string $content): bool
    {
        if (!preg_match('/\{\$([A-Za-z0-9_]+)(}|->|\[\'|\[\")/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            return false;
        }

        $position = $matches[0][1];
        $length = 0;
        $this->name = $matches[1][0];

        while(true) {
            if ($matches[2][0] == '}') {
                $length = $length + strlen($matches[0][0]);
                break;
            }

            break;
        }

        $this->position = (int) $position;
        $this->length = $length;
        return true;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function render()
    {
        return "<?= \${$this->name} ?>";
    }
}