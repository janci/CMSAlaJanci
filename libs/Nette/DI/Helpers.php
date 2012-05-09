<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\DI
 */



/**
 * The DI helpers.
 *
 * @author     David Grudl
 * @package Nette\DI
 */
final class NDIHelpers
{

	/**
	 * Expands %placeholders%.
	 * @param  mixed
	 * @param  array
	 * @param  bool
	 * @return mixed
	 * @throws InvalidArgumentException
	 */
	public static function expand($var, array $params, $recursive = FALSE)
	{
		if (is_array($var)) {
			$res = array();
			foreach ($var as $key => $val) {
				$res[$key] = self::expand($val, $params, $recursive);
			}
			return $res;

		} elseif ($var instanceof NDIStatement) {
			return new NDIStatement(self::expand($var->entity, $params, $recursive), self::expand($var->arguments, $params, $recursive));

		} elseif (!is_string($var)) {
			return $var;
		}

		$parts = preg_split('#%([\w.-]*)%#i', $var, -1, PREG_SPLIT_DELIM_CAPTURE);
		$res = '';
		foreach ($parts as $n => $part) {
			if ($n % 2 === 0) {
				$res .= $part;

			} elseif ($part === '') {
				$res .= '%';

			} elseif (isset($recursive[$part])) {
				throw new InvalidArgumentException('Circular reference detected for variables: ' . implode(', ', array_keys($recursive)) . '.');

			} else {
				$val = NArrays::get($params, explode('.', $part));
				if ($recursive) {
					$val = self::expand($val, $params, (is_array($recursive) ? $recursive : array()) + array($part => 1));
				}
				if (strlen($part) + 2 === strlen($var)) {
					return $val;
				}
				if (!is_scalar($val)) {
					throw new InvalidArgumentException("Unable to concatenate non-scalar parameter '$part' into '$var'.");
				}
				$res .= $val;
			}
		}
		return $res;
	}



	/**
	 * Expand counterpart.
	 * @param  mixed
	 * @return mixed
	 */
	public static function escape($value)
	{
		if (is_array($value)) {
			array_walk_recursive($value, create_function('&$val', '
				$val = is_string($val) ? str_replace(\'%\', \'%%\', $val) : $val;
			'));
		} elseif (is_string($value)) {
			$value = str_replace('%', '%%', $value);
		}
		return $value;
	}



	/**
	 * Generates list of arguments using autowiring.
	 * @param  NFunctionReflection|NMethodReflection
	 * @return array
	 */
	public static function autowireArguments(ReflectionFunctionAbstract $method, array $arguments, $container)
	{
		$optCount = 0;
		$num = -1;
		$res = array();

		foreach ($method->getParameters() as $num => $parameter) {
			if (array_key_exists($num, $arguments)) {
				$res[$num] = $arguments[$num];
				unset($arguments[$num]);
				$optCount = 0;

			} elseif (array_key_exists($parameter->getName(), $arguments)) {
				$res[$num] = $arguments[$parameter->getName()];
				unset($arguments[$parameter->getName()]);
				$optCount = 0;

			} elseif ($class = $parameter->getClassName()) { // has object typehint
				$res[$num] = $container->getByType($class, FALSE);
				if ($res[$num] === NULL) {
					if ($parameter->allowsNull()) {
						$optCount++;
					} else {
						throw new InvalidArgumentException("No service of type {$class} found");
					}
				} else {
					if ($container instanceof NDIContainerBuilder) {
						$res[$num] = '@' . $res[$num];
					}
					$optCount = 0;
				}

			} elseif ($parameter->isOptional()) {
				// PDO::__construct has optional parameter without default value (and isArray() and allowsNull() returns FALSE)
				$res[$num] = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : NULL;
				$optCount++;

			} else {
				throw new InvalidArgumentException("$parameter is missing.");
			}
		}

		// extra parameters
		while (array_key_exists(++$num, $arguments)) {
			$res[$num] = $arguments[$num];
			unset($arguments[$num]);
			$optCount = 0;
		}
		if ($arguments) {
			throw new InvalidArgumentException("Unable to pass specified arguments to $method.");
		}

		return $optCount ? array_slice($res, 0, -$optCount) : $res;
	}

}
