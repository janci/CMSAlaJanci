<?php
/**
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 *
 * Copyright (c) 2012 Ján Švantner (http://www.janci.net)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

class BaseAdminPresenter extends BasePresenter {
        protected $db;
		
        public function startup(){
            parent::startup();
            if(!$this->getUser()->isLoggedIn() && $this->getAction()!='login') {
                $this->redirect('Admin:login');
            }
            if($this->getUser()->isLoggedIn() && $this->getAction()=='login'){
                $this->redirect('Homepage:default');
            }
            
            $this->db = $this->getService('database');
        }
        
        protected function beforeRender() {
            parent::beforeRender();
            $plugins = $this->component_builder->getPlugins();
            $menu = array();
            $iconset = array();
            $check_names=array();
            /* @var $publisher Publisher */
            $publisher = $this->getService('publisher');
            foreach($plugins as $plugin){
                $config = $plugin->getConfig();
                if(isset($check_names[$config['name']])) continue;
                $check_names[$config['name']] = true;
                
                if(isset($config['admin'], $config['admin']['menu'])){
                    foreach($config['admin']['menu'] as $menuitem){
                        if(!isset($menuitem['link'],$menuitem['title'])) {
                            throw new PluginException("Missing configuration `title` or `link` for menu item.");
                        }
                        if(isset($menuitem['weight'])) {
                            if(isset($menu[$menuitem['weight']]))
                                throw new PluginException("Weight with `".$menuitem['weight']."` is ambigious for titles (".$menu[$menuitem['weight']]['title'].", ".$menuitem['title'].")");
                            $menu[$menuitem['weight']] = $menuitem;
                        } else {
                            $menu[] = $menuitem;
                        }
                        
                        if(!isset($menuitem['current'])) $menuitem['current'] = $menuitem['link'];
                    }
                    
                }
                
                
                if(isset($config['admin'], $config['admin']['icon'])){
                    foreach($config['admin']['icon'] as $icon){
                        if(!isset($icon['link'],$icon['title'],$icon['icon'])) {
                            throw new PluginException("Missing configuration `title` or `link` or `icon` for icon item.");
                        }
                        
                        $ds = DIRECTORY_SEPARATOR;
                        $wwwDir = dirname($plugin->getReflection()->getFileName()).$ds.'www';
                        $publisher->publicDirectory($plugin->getName(), $wwwDir);
                        $icon['icon'] = $plugin->getName().'.www:'.$icon['icon'];
                        //$icon['icon'] = 'data:image/png;base64,'.base64_encode(file_get_contents($wwwDir.$ds.$icon['icon']));
                        if(isset($icon['weight'])) {
                            if(isset($iconset[$icon['weight']]))
                                throw new PluginException("Weight with `".$icon['weight']."` is ambigious for titles (".$iconset[$icon['weight']]['title'].", ".$icon['title'].")");
                            $iconset[$icon['weight']] = $icon;
                        } else {
                            $iconset[] = $icon;
                        } 
                    }
                }
            }

            ksort($menu);
            $this->template->menu  = $menu;
            $this->template->icons = $iconset;
        }
        
        public function findLayoutTemplateFile() {
            if($this->getName()=='Admin') return parent::findLayoutTemplateFile ();
            
            $dir = APP_DIR; //dirname(dirname(dirname(dirname($this->getReflection()->getFileName()))));
            $ds = DIRECTORY_SEPARATOR;
            
            return $dir.$ds. 'templates'.$ds.'Admin'.$ds.'@layout.latte';
        }
}

