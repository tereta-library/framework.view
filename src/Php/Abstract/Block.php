<?php declare(strict_types=1);

namespace Framework\View\Php\Abstract;

use Exception;
use Framework\View\Html as Layout;

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
 * @class Framework\View\Php\Abstract\Block
 * @package Framework\View\Php\Abstract
 * @link https://tereta.dev
 * @since 2020-2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 * @copyright 2020-2024 Tereta Alexander
 */
abstract class Block
{
    /**
     * @var string|null $template
     */
    protected ?string $template = null;

    /**
     * @var array $themeDirectory
     */
    private array $themeDirectory = [];

    /**
     * @param string|array|null $themeDirectory
     * @param array $data
     * @param string|null $template
     */
    public function __construct(
        null|string|array $themeDirectory,
        private array $data = [],
        string $template = null
    ) {
        if (is_array($themeDirectory)) {
            $this->themeDirectory = $themeDirectory;
        } else {
            $this->themeDirectory = [$themeDirectory];
        }

        if ($template !== null) {
            $this->template = $template;
        }

        $this->construct();
    }

    /**
     * Additional constructor for child classes
     *
     * @return void
     */
    protected function construct(): void
    {
    }

    /**
     * Initialisation before rendering to set necessary relations beetling blocks
     *
     * @param Layout $layout
     * @return void
     */
    public function initialize(Layout $layout): void
    {
    }

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate(string $template): static
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @param string $variable
     * @param mixed $value
     * @return $this
     */
    public function assign(array|string $variable, mixed $value = null): static
    {
        if (is_array($variable)) {
            foreach ($variable as $key => $value) {
                $this->data[$key] = $value;
            }

            return $this;
        }

        $this->data[$variable] = $value;

        return $this;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function render(): string
    {
        extract($this->data);

        ob_start();
        extract($this->data);
        $file = null;
        foreach ($this->themeDirectory ?? [] as $themeDirectory) {
            if (is_file("{$themeDirectory}/{$this->template}")) {
                $file = "{$themeDirectory}/{$this->template}";
                break;
            }
        }

        if (!$this->themeDirectory) {
            $file = $this->template;
        }

        if (!is_file($file)) {
            $themeDirectoryImploded = implode('", "', $this->themeDirectory);
            throw new Exception("The \"{$this->template}\" template file does not exist in the \"$themeDirectoryImploded\" directories.");
        }

        require $file;
        return ob_get_clean();
    }

    /**
     * @param string $value
     * @return string
     */
    public function quoteAttribute(string $value): string
    {
        return '"' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
    }

    /**
     * @return string
     * @throws Exception
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set(string $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    public function __sleep()
    {
        return ['template'];
    }
}