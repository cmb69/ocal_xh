<?php

/**
 * Copyright 2023 Christoph M. Becker
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

class Response
{
    /** @var string */
    private $output;

    /** @var string|null */
    private $contentType;

    public function __construct(string $output, ?string $contentType = null)
    {
        $this->output = $output;
        $this->contentType = $contentType;
    }

    /** @return string|never */
    public function trigger()
    {
        if ($this->contentType !== null) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header("Content-Type: {$this->contentType}; charset=UTF-8");
            echo $this->output;
            exit;
        }
        return $this->output;
    }

    public function output(): string
    {
        return $this->output;
    }
}
