<?php

/**
 * The main "program".
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Bcal
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Bcal_XH
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
Bcal_XH detected an unsupported CMSimple_XH version.
Uninstall Bcal_XH or upgrade to a supported CMSimple_XH version!
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
define('BCAL_VERSION', '@BCAL_VERSION@');

/**
 * Renders a calendar.
 *
 * @param int $monthCount A month count.
 *
 * @return string (X)HTML.
 *
 * @global Bcal_Controller The plugin controller.
 */
function bcal($monthCount = 1)
{
    global $_Bcal_controller;

    return $_Bcal_controller->renderCalendar($monthCount);
}

/**
 * The plugin controller.
 *
 * @var Bcal_Controller
 */
$_Bcal_controller = new Bcal_Controller();
$_Bcal_controller->dispatch();

?>
