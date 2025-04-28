# Ocal_XH

Ocal_XH facilitates to present calendars on your website which display the
occupancy of a certain location or other rental, so visitors can easily see
whether the item is available on a certain date or (hourly) interval. Each
calendar is only meant for a single rental; however, you can display multiple
calendars on a single page. Visitors are able to switch between a calendar and a
list view.

The plugin does neither provide any actual booking facilities
(however, you may be able to combine it with a mail form like
[Advancedform_XH](https://github.com/cmb69/advancedform_xh)),
nor can it be used as an event calendar, such as
[Calendar_XH](https://github.com/cmb69/calendar_xh).

## Table of Contents

  - [Requirements](#requirements)
  - [Download](#download)
  - [Installation](#installation)
  - [Settings](#settings)
  - [Usage](#usage)
    - [Administration](#administration)
    - [States](#states)
  - [Troubleshooting](#troubleshooting)
  - [License](#license)
  - [Credits](#credits)

## Requirements

Ocal_XH is a plugin for [CMSimple_XH](https://cmsimple-xh.org/).
It requires CMSimple_XH ≥ 1.7.0, and PHP ≥ 7.1.0.
Ocal_XH also requires [Plib_XH](https://github.com/cmb69/plib_xh) ≥ 1.6;
if that is not already installed (see `Settings` → `Info`),
get the [lastest release](https://github.com/cmb69/plib_xh/releases/latest),
and install it.

## Download

The [lastest release](https://github.com/cmb69/ocal_xh/releases/latest)
is available for download on Github.

## Installation

The installation is done as with many other CMSimple_XH plugins. See the
[CMSimple_XH Wiki](https://wiki.cmsimple-xh.org/?for-users/working-with-the-cms/plugins#id3_install-plugin)
for further details.

1. **Backup the data on your server.**
1. Unzip the distribution on your computer.
1. Upload the whole directory `ocal/` to your server into
   the `plugins/` directory of CMSimple_XH.
1. Set write permissions for the subdirectories `css/`, `config/` and
   `languages/`.
1. Navigate `Plugins` → `Ocal` to check if all requirements are
   fulfilled.

## Settings

The configuration of the plugin is done as with many other CMSimple_XH plugins in
the back-end of the Website. Select `Plugins` → `Ocal`.

You can change the default settings of Ocal_XH under `Config`. Hints for the
options will be displayed when hovering over the help icon with your mouse.

Localization is done under `Language`. You can translate the character
strings to your own language (if there is no appropriate language file
available), or customize them according to your needs.

The look of Ocal_XH can be customized under `Stylesheet`.

## Usage

To display a *daily* occupancy calendar on a *page*, use:

    {{{ocal('%OCCUPANCY_NAME%', %NUMBER_OF_MONTHS%)}}}

To display an *hourly* occupancy calendar on a *page*, use:

    {{{ocal_hourly('%OCCUPANCY_NAME%', %NUMBER_OF_WEEKS%)}}}

To display a *daily* occupancy calendar in the *template*, use:

    <?=ocal('%OCCUPANCY_NAME%', %NUMBER_OF_MONTHS%)?>

To display an *hourly* occupancy calendar in the *template*, use:

    <?=ocal_hourly('%OCCUPANCY_NAME%', %NUMBER_OF_WEEKS%)?>

`%OCCUPANCY_NAME%` is an arbitrary name, that must only consist of the letters
`a`-`z`, the digits `0`-`9` and hyphens (`-`).
Daily and hourly occupancy calendars must *not* have the same name.

`%NUMBER_OF_MONTHS%` and `%NUMBER_OF_WEEKS%` specify the number of calendars
that shall be displayed simultaneously.

Note that you can put an arbitrary amount of occupancy calendars on a single
page, even for the same occupancy (what might make sense if you like to present
a large interval, say a year, on a page, and only a small interval, say a single
month, in the template).

Some examples:

    {{{ocal('holiday-appartment', 12)}}}
    <?=ocal('holiday-appartment', 1)?>
    {{{ocal_hourly('rickshaw', 2)}}}
    {{{ocal('rubber-raft-1', 6)}}}
    {{{ocal('rubber-raft-2', 6)}}}

### Administration

The administration of the occupancy calendars happens on the pages where they
are displayed. When you are logged in as administrator, you will see a toolbar
where you can pick one of the available states by clicking it. Then you can
click individual dates in the displayed calendars to assign them this state.
When you are finished, you have to save your modifications.

By default, four states are available which have the following meaning:

- `0` (white): non applicable
- `1` (green): available
- `2` (yellow): reserved or partly available
- `3` (red): (fully) booked out

Note that the number and meaning of the states [is customizable](#states).

Also note that the administration requires JavaScript and a somewhat
contemporary browser (e.g. IE < 8 is not supported).

### States

It is possible to have an arbitrary amount of states, each with its own
meaning. The maximum state that is available has to be set in the
configuration (`State` → `Max`). Note that in addition to that many states,
there is also state `0`, which is the default state that is not shown in the
list views.

You can customize the state labels in the language settings of the plugin.
If you have increased `State` → `Max`, you have to manually edit the respective
language files in `plugins/ocal/languages/`, and have to add
further state labels, like so:

    $plugin_tx['ocal']['label_state_4']="extra state";

You can customize the state colors in the stylesheet of the plugin,
and add rules for additional states like so:

    *[data-ocal_state="4"] {
        background: blue;
        color: white;
    }

To summarize: set the maximum state in the configuration, add (or remove)
the state labels in the language file and add (or remove) state color rules
in the stylesheet.

## Troubleshooting

Report bugs and ask for support either on [Github](https://github.com/cmb69/ocal_xh/issues)
or in the [CMSimple_XH Forum](https://cmsimpleforum.com/).

## License

Ocal_XH is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Ocal_XH is distributed in the hope that it will be useful,
but *without any warranty*; without even the implied warranty of
*merchantibility* or *fitness for a particular purpose*.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Ocal_XH.  If not, see <https://www.gnu.org/licenses/>.

© 2014-2023 Christoph M. Becker

## Credits

Ocal_XH is inspired by the occcal plugin of CMSimple-Styles.
Many thanks to Jens Broecher for having this nice idea.

The plugin icon is designed by [deviantdark](https://www.deviantart.com/deviantdark).
Many thanks for publishing the icon under LGPL.

Many thanks to the community at the [CMSimple_XH Forum](https://www.cmsimpleforum.com/)
for tips, suggestions and testing.
Especially I like to thank *rothom* for being the first tester and
*hixi* for suggesting the hourly calendars.
And many thanks to *lck* who found and reported serious bugs right after the
release of version 1.0.
Also thanks to *Tata* who inspired a couple of enhancements.

And last but not least many thanks to [Peter Harteg](https://www.harteg.dk/),
the “father” of CMSimple, and all developers of [CMSimple_XH](https://www.cmsimple-xh.org/)
without whom this amazing CMS would not exist.
