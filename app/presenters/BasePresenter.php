<?php
/**
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 *
 * Copyright (c) 2012 Ján Švantner (http://www.janci.net)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends NPresenter
{
    protected $component_builder;
    protected function startup() {
        parent::startup();
        $this->component_builder = new ComponentBuilder($this, $this->getService('publisher'));
    }
    
    protected function beforeRender() {
        parent::beforeRender();
        $this->component_builder->attachComponents();
        $this->component_builder->filter();
    }
    
    protected function afterRender() {
        $this->getService('publisher')->publish();
        parent::afterRender();
    }
}
