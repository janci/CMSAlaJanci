<?php
/**
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 *
 * Copyright (c) 2012 Ján Švantner (http://www.janci.net)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

class AdminPagesPresenter extends BaseAdminPresenter {
    private $page_id;
    public function renderDefault(){
        $this->template->pages = $this->db->table('page')->where('remove',0);
    }

    public function handleRemove($id){
        $a = $this->db->table('page')->where('id',$id)->update(array('remove'=>1, 'url'=> uniqid()));
        if (isset($a) && is_numeric($a)) 
            $this->flashMessage('Stránka bola úspešne vymazaná', 'success');
        else
            $this->flashMessage('Stránku sa nepodarilo vymazať', 'error');
        $this->redirect('default');
    }

    public function actionAdd(){
        $this->setView('edit');
    }
    public function actionEdit($id){
        $this->page_id = $id;
    }

    public function createComponentEditPageForm(){
        $page = $this->db->table('page')->where(array('url'=>$this->page_id))->fetch();
        $page = $page->toArray();
        $form = new NAppForm;
        $form->addGroup('Nastavenie stránky');
        $form->addText('title','Názov:');
        $form->addText('url','URL adresa:');
        /*if (Plugins::isActive('pages-parent')) {
            $opages['null']='Nie je';
            $opages += DbStorage::notorm()->pages()->where(array('url!=?'=>$this->id,'remove'=>0))->fetchPairs('id','title');
            if ($page['parent']==null)$page['parent'] = 'null';
            $form->addSelect('parent','Rodič:',$opages);
        }*/

        /*if (Plugins::isActive('seo')) {
            $form->addGroup('Nastavenie SEO parametrov stránky');
            $form->addText('seo_title','Titulok stránky:');
            $form->addText('seo_description','Popis stránky:');
            $form->addText('seo_keywords','Kľúčové slová:');
            if (isset($this->id)) {
                $seo = $this->seo = DbStorage::notorm()->seo()->where(array('pages_id'=>$page['id']))->fetch();
                $form['seo_title']->value = $seo['title'];
                $form['seo_description']->value = $seo['description'];
                $form['seo_keywords']->value = $seo['keywords'];
            }

        }*/
        $form->addGroup('Obsah stránky');
        $form->addTextarea('content','Obsah stránky:');
        if (isset($this->page_id)) {
            $form->addHidden('operation','edit');
            $form->addSubmit('odoslat','Upraviť stránku');
        } else {
            $form->addHidden('operation','insert');
            $form->addSubmit('odoslat','Pridať stránku');
        }
        if (isset($this->page_id)) $form->setValues($page);

        $form->onSuccess[] = array($this,'onSubmitEditForm');
        return $form;
    }

    public function onSubmitEditForm(NAppForm $form){
        $val = $form->values;
        $up_pages = array(
            'title'  => $val['title'],
            'url'    => $val['url'],
            'content'=> $val['content']
        );
        /*if (Plugins::isActive('pages-parent')){
            $up_pages['parent']=($val['parent']=='null'? null:$val['parent']);
        }*/

        $dbq = $this->db->table('page')->where(array('url'=>$this->page_id));
        //if ($val['operation']=='insert' && $this->allowAdd)
        //    $dbq->insert($up_pages);
        // else 
            $dbq->update($up_pages);

        $id = $this->db->table('page')->where(array('url'=>$val['url']))->select('id')->fetch();

        if (isset($id)){
            $id = $id['id'];
            /*if (Plugins::isActive('seo')){
                $up_seo = array(
                    'title'       => $val['seo_title'],
                    'description' => $val['seo_description'],
                    'keywords'    => $val['seo_keywords']
                );
            } else {*/
                $up_seo = array(
                    'title'       => $val['title'],
                    'description' => $val['title'],
                    'keywords'    => str_replace(' ', ',', trim($val['title']))
                );
            /*}
            $dbq = DbStorage::notorm()->seo->where(array('pages_id'=>$id));
            if ($val['operation']=='insert' && $this->allowAdd)
                $dbq->insert($up_seo);
            else
                $dbq->update($up_seo);*/
        }

        $this->flashMessage('Uloženie prebehlo v poriadku!','success');
        $this->redirect('default');
    }
    
}

