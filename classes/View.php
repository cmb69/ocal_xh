<?php

/**
 * Copyright 2016-2017 Christoph M. Becker
 *
 * This file is part of Ocal_XH.
 *
 * Ocal_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Ocal_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Ocal_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Ocal;

class View
{
    /** @var string */
    private $templateFolder;

    /** @var array<string,string> */
    private $lang;

    /**
     * @var array<string,mixed>
     */
    private $data = array();

    /**
     * @param array<string,string> $lang
     */
    public function __construct(string $templateFolder, array $lang)
    {
        $this->templateFolder = $templateFolder;
        $this->lang = $lang;
    }

    /**
     * @param string $name
     * @return string
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * @param string $name
     * @param list<mixed> $args
     * @return string
     */
    public function __call($name, array $args)
    {
        return $this->esc($this->data[$name]);
    }

    /**
     * @param string $key
     * @return string
     */
    protected function text($key)
    {
        $args = func_get_args();
        array_shift($args);
        return $this->esc(vsprintf($this->lang[$key], $args));
    }

    /**
     * @param string $key
     * @param int $count
     * @return string
     */
    protected function plural($key, $count)
    {
        if ($count == 0) {
            $key .= '_0';
        } else {
            $key .= XH_numberSuffix($count);
        }
        $args = func_get_args();
        array_shift($args);
        return $this->esc(vsprintf($this->lang[$key], $args));
    }

    /** @param array<string,mixed> $_data */
    public function render(string $_template, array $_data): string
    {
        $this->data = $_data;
        ob_start();
        echo "<!-- {$_template} -->\n";
        include "{$this->templateFolder}{$_template}.php";
        return (string) ob_get_clean();
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function esc($value)
    {
        if ($value instanceof HtmlString) {
            return $value;
        } else {
            return XH_hsc($value);
        }
    }
}
