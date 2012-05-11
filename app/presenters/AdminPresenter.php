<?php
/**
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 *
 * Copyright (c) 2012 Ján Švantner (http://www.janci.net)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

class AdminPresenter extends BaseAdminPresenter {
    public function signInFormSubmitted($form)
	{
		try {
			$values = $form->getValues();
			if ($values->remember) {
				$this->getUser()->setExpiration('+ 14 days', FALSE);
			} else {
				$this->getUser()->setExpiration('+ 20 minutes', TRUE);
			}
			$this->getUser()->login($values->username, $values->password);
			$this->redirect('default');

		} catch (NAuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}



	public function actionLogout()
	{
		$this->getUser()->logout();
		$this->flashMessage('You have been signed out.');
		$this->redirect('login');
	}
        
         /**
	 * Sign in form component factory.
	 * @return NAppForm
	 */
	protected function createComponentSignInForm()
	{
		$form = new NAppForm;
		$form->addText('username', 'Username:')
			->setRequired('Please provide a username.');

		$form->addPassword('password', 'Password:')
			->setRequired('Please provide a password.');

		$form->addCheckbox('remember', 'Remember me on this computer');

		$form->addSubmit('send', 'Sign in');

		$form->onSuccess[] = callback($this, 'signInFormSubmitted');
		return $form;
	}

}

