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

use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    /** @dataProvider dataForUrlHasDesiredStringRepresentation */
    public function testUrlHasDesiredStringRepresentation(Url $url, string $expected): void
    {
        $actual = (string) $url;
        $this->assertEquals($expected, $actual);
    }

    public function dataForUrlHasDesiredStringRepresentation(): array
    {
        return [
            'no_page' => [new Url("/", "", []), "/"],
            'with_page' => [new Url("/", "Page", []), "/?Page"],
            'with_params' => [new Url("/", "Page", ["foo" => "bar", "qux" => "bar"]), "/?Page&foo=bar&qux=bar"],
            'params_but_no_page' => [new Url("/", "", ["foo" => "bar"]), "/?&foo=bar"],
            'gh-35' => [new Url("/", "Ocal-1.0", []), "/?Ocal-1.0"],
            'get_with_list' => [
                new Url("/", "", ["foo" => ["bar", "baz"]]),
                "/?&foo%5B0%5D=bar&foo%5B1%5D=baz"
            ],
            'get_with_dict' => [
                new Url("/", "", ["foo" => ["one" => "bar", "two" => "baz"]]),
                "/?&foo%5Bone%5D=bar&foo%5Btwo%5D=baz"
            ],
        ];
    }

    /**
     * @param array<string,string> $params
     * @dataProvider dataForUrlCanBeModified
     */
    public function testUrlCanBeModified(Url $url, array $params, string $expected): void
    {
        $actual = (string) $url->replace($params);
        $this->assertEquals($expected, $actual);
    }

    public function dataForUrlCanBeModified(): array
    {
        return [
            [
                new Url("/", "Page", []),
                [],
                "/?Page",
            ],
            [
                new Url("/", "", []),
                ["foo" => "bar"],
                "/?&foo=bar",
            ],
            [
                new Url("/", "Page", ["foo" => "bar"]),
                [],
                "/?Page&foo=bar",
            ],
            [
                new Url("/", "Page", ["foo" => "bar"]),
                ["baz" => "qux"],
                "/?Page&foo=bar&baz=qux",
            ],
            [
                new Url("/", "Page", ["foo" => "bar"]),
                ["foo" => "baz"],
                "/?Page&foo=baz",
            ],
        ];
    }
}
