<?php
require LIBS_DIR . '/Texy/texy.min.php';

/**
 * Homepage presenter.
 */
class PagePresenter extends BasePresenter
{
        private $db;
        public function startup() {
            parent::startup();
            $this->db = $this->getService('database');
            
            $action = ($this->getAction()!='default')? $this->getAction():'index';
            $this->template->page = $this->db->table('page')->where('url',$action)->where('remove',0)->fetch();
            if($this->template->page==false) throw new NBadRequestException("Missing page with name $action",404);
            
            $texy = new Texy();
            $texy->headingModule->top = 3;
            $this->template->page->content = $texy->process($this->template->page->content);
            
            $this->component_builder->setContent($this->template->page->content);
            $this->setView('default');
        }
        
        protected function beforeRender() {
            parent::beforeRender();
            $this->template->page->content = $this->component_builder->getContent();
        }

}
