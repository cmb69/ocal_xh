<?php

const CMSIMPLE_XH_VERSION = "CMSimple_XH 1.7.5";
const OCAL_VERSION = "1.5-dev";

require_once './vendor/autoload.php';
require_once '../../cmsimple/functions.php';
require_once '../../cmsimple/adminfuncs.php';

require_once "../plib/classes/CsrfProtector.php";
require_once "../plib/classes/Document.php";
require_once "../plib/classes/DocumentStore.php";
require_once "../plib/classes/Request.php";
require_once "../plib/classes/Response.php";
require_once "../plib/classes/SystemChecker.php";
require_once "../plib/classes/Url.php";
require_once "../plib/classes/View.php";
require_once "../plib/classes/FakeRequest.php";
require_once "../plib/classes/FakeSystemChecker.php";

spl_autoload_register(function (string $className) {
    $parts = explode("\\", $className);
    if ($parts[0] !== "Ocal") {
        return;
    }
    if (count($parts) === 3) {
        $parts[1] = strtolower($parts[1]);
    }
    $filename = implode("/", array_slice($parts, 1)) . ".php";
    if (is_readable("./classes/$filename")) {
        include_once "./classes/$filename";
    } elseif (is_readable("./tests/$filename")) {
        include_once "./tests/$filename";
    }
});
