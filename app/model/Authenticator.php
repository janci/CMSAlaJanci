<?php
/**
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 *
 * Copyright (c) 2012 Ján Švantner (http://www.janci.net)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */


/**
 * Users authenticator.
 */
class Authenticator extends NObject implements IAuthenticator
{
	/** @var NConnection */
	private $database;



	public function __construct(NConnection $database)
	{
		$this->database = $database;
	}



	/**
	 * Performs an authentication
	 * @param  array
	 * @return NIdentity
	 * @throws NAuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		$row = $this->database->table('user')->where('username', $username)->fetch();

		if (!$row) {
			throw new NAuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
		}

		if ($row->password !== $this->calculateHash($password)) {
			throw new NAuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
		}

		unset($row->password);
		return new NIdentity($row->id, $row->role, $row->toArray());
	}



	/**
	 * Computes salted password hash.
	 * @param  string
	 * @return string
	 */
	public function calculateHash($password)
	{
		return md5($password . str_repeat('*cms_janci_net*', 10));
	}

}
