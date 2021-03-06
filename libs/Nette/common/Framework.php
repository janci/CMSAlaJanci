<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette
 */



/**
 * The Nette Framework (http://nette.org)
 *
 * @author     David Grudl
 * @package Nette
 */
final class NFramework
{

	/** Nette Framework version identification */
	const NAME = 'Nette Framework',
		VERSION = '2.1-dev',
		REVISION = 'b8c66ea released on 2012-04-28';

	/** @var bool set to TRUE if your host has disabled function ini_set */
	public static $iAmUsingBadHost = FALSE;



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new NStaticClassException;
	}

}
