<?php

/**
 * The main "program".
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Ocal
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Ocal_XH
 */

/*
 * Prevent direct access and usage from unsupported CMSimple_XH versions.
 */
if (!defined('CMSIMPLE_XH_VERSION')
    || strpos(CMSIMPLE_XH_VERSION, 'CMSimple_XH') !== 0
    || version_compare(CMSIMPLE_XH_VERSION, 'CMSimple_XH 1.6', 'lt')
) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: text/plain; charset=UTF-8');
    die(<<<EOT
Ocal_XH detected an unsupported CMSimple_XH version.
Uninstall Ocal_XH or upgrade to a supported CMSimple_XH version!
EOT
    );
}

/**
 * The data source layer.
 */
require_once $pth['folder']['plugin_classes'] . 'DataSource.php';

/**
 * The domain layer.
 */
require_once $pth['folder']['plugin_classes'] . 'Domain.php';

/**
 * The presentation layer.
 */
require_once $pth['folder']['plugin_classes'] . 'Presentation.php';

/**
 * The plugin version.
 */
define('OCAL_VERSION', '@OCAL_VERSION@');

/**
 * Renders a calendar.
 *
 * @param string $name       A calendar name.
 * @param int    $monthCount A month count.
 *
 * @return string (X)HTML.
 *
 * @global Ocal_Controller The plugin controller.
 */
function ocal($name, $monthCount = 1)
{
    global $_Ocal_controller;

    return $_Ocal_controller->renderCalendar($name, $monthCount);
}

/**
 * The plugin controller.
 *
 * @var Ocal_Controller
 */
$_Ocal_controller = new Ocal_Controller();
$_Ocal_controller->dispatch();

?>
