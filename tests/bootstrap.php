<?php

const CMSIMPLE_XH_VERSION = "CMSimple_XH 1.7.5";
const OCAL_VERSION = "1.3-dev";

require_once './vendor/autoload.php';
require_once '../../cmsimple/classes/CSRFProtection.php';
require_once '../../cmsimple/functions.php';
require_once '../../cmsimple/adminfuncs.php';

require_once "./classes/CalendarController.php";
require_once "./classes/Occupancy.php";
require_once "./classes/Pagination.php";

require_once "./classes/DailyCalendarController.php";
require_once "./classes/DailyOccupancy.php";
require_once "./classes/DailyPagination.php";
require_once "./classes/Db.php";
require_once "./classes/DefaultAdminController.php";
require_once "./classes/HourlyCalendarController.php";
require_once "./classes/HourlyOccupancy.php";
require_once "./classes/HourlyPagination.php";
require_once "./classes/HtmlString.php";
require_once "./classes/ListService.php";
require_once "./classes/Month.php";
require_once "./classes/Response.php";
require_once "./classes/SystemChecker.php";
require_once "./classes/Url.php";
require_once "./classes/Week.php";
require_once "./classes/View.php";
