<?php
/**
 * Just register `lib/` path, and now all classes in lib are available
 *
 * @author Sasha Simkin <sashasimkin@gmail.com>
 */

include dirname(__FILE__) . '/lib/Autoload.php';
Autoload::registerPath(dirname(__FILE__) . '/lib');