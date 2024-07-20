<?php declare(strict_types=1);

namespace Framework\View\Php\Abstract;

use Exception;

/**
 * Framework\View\Php\Abstract\Block
 */
abstract class Block
{
    protected ?string $template = null;

    /**
     * @param string $themeDirectory
     * @param array $data
     * @param string|null $template
     */
    public function __construct(
        private string $themeDirectory,
        private array $data = [],
        string $template = null
    ) {
        if ($template !== null) {
            $this->template = $template;
        }
    }

    public function setTemplate(string $template): static
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function render(): string
    {
        ob_start();
        extract($this->data);
        if (!is_file("{$this->themeDirectory}/{$this->template}")) {
            throw new Exception("The \"{$this->themeDirectory}/{$this->template}\" template file does not exist.");
        }

        require "{$this->themeDirectory}/{$this->template}";
        return ob_get_clean();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }
}