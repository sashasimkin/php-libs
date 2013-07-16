<?php
/**
 * Just register `lib/` path, and now all classes in lib are available
 *
 * @author Sasha Simkin <sashasimkin@gmail.com>
 */

define('DIR', dirname(__FILE__));
include DIR . '/lib/Autoload.php';

Autoload::registerPath(DIR . '/lib');