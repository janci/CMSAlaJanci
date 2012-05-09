<?php

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
