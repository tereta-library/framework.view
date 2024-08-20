<?php declare(strict_types=1);

namespace Framework\View\Php\Abstract;

use Exception;

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

        $this->construct();
    }

    protected function construct(): void
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
    public function assign(string $variable, mixed $value = null): static
    {
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
        if (!is_file("{$this->themeDirectory}/{$this->template}")) {
            throw new Exception("The \"{$this->themeDirectory}/{$this->template}\" template file does not exist.");
        }

        require "{$this->themeDirectory}/{$this->template}";
        return ob_get_clean();
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
}