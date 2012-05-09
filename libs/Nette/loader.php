<?php

/**
 * Nette Framework (version 2.1-dev released on 2012-04-28, http://nette.org)
 *
 * Copyright (c) 2004, 2012 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */



/**
 * Check and reset PHP configuration.
 */
if (!defined('PHP_VERSION_ID')) {
	$tmp = explode('.', PHP_VERSION);
	define('PHP_VERSION_ID', ($tmp[0] * 10000 + $tmp[1] * 100 + $tmp[2]));
}

if (PHP_VERSION_ID < 50200) {
	throw new Exception('Nette Framework requires PHP 5.2.0 or newer.');
}

error_reporting(E_ALL | E_STRICT);
@set_magic_quotes_runtime(FALSE); // @ - deprecated since PHP 5.3.0
iconv_set_encoding('internal_encoding', 'UTF-8');
extension_loaded('mbstring') && mb_internal_encoding('UTF-8');
umask(0);
@header('X-Powered-By: Nette Framework'); // @ - headers may be sent
@header('Content-Type: text/html; charset=utf-8'); // @ - headers may be sent



/**
 * Load and configure Nette Framework.
 */

/** @internal */
class NCFix
{
	static $vars = array();

	static function uses($args)
	{
		self::$vars[] = $args;
		return count(self::$vars)-1;
	}
}

define('NETTE', TRUE);
define('NETTE_DIR', dirname(__FILE__));
define('NETTE_VERSION_ID', 20100); // v2.1.0
define('NETTE_PACKAGE', 'PHP 5.2 prefixed');



require_once dirname(__FILE__) . '/common/exceptions.php';
require_once dirname(__FILE__) . '/common/Object.php';
require_once dirname(__FILE__) . '/Utils/LimitedScope.php';
require_once dirname(__FILE__) . '/Loaders/AutoLoader.php';
require_once dirname(__FILE__) . '/Loaders/NetteLoader.php';


NNetteLoader::getInstance()->register();

require_once dirname(__FILE__) . '/Diagnostics/Helpers.php';
require_once dirname(__FILE__) . '/Diagnostics/shortcuts.php';
require_once dirname(__FILE__) . '/Utils/Html.php';
NDebugger::_init();

NSafeStream::register();



/**
 * NCallback factory.
 * @param  mixed   class, object, callable
 * @param  string  method
 * @return NCallback
 */
function callback($callback, $m = NULL)
{
	if ($m === NULL) {
		return $callback instanceof NCallback ? $callback : new NCallback($callback);
	}
	return new NCallback(array($callback, $m));
}
