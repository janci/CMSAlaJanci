<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Application\UI
 */



/**
 * Component multiplier.
 *
 * @author     David Grudl
 * @package Nette\Application\UI
 */
class NMultiplier extends NPresenterComponent
{
	/** @var NCallback */
	private $factory;


	public function __construct($factory)
	{
		parent::__construct();
		$this->factory = callback($factory);
	}



	protected function createComponent($name)
	{
		return $this->factory->invoke($name, $this);
	}

}
