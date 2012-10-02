<?php
/**
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 *
 * Copyright (c) 2012 Ján Švantner (http://www.janci.net)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

class AdminBannerPresenter extends BaseAdminPresenter {
    public function handleRemove($id){
        $a = $this->db->table('bannerwt')->where('id',$id)->update(array('remove'=>1));
        if (isset($a) && is_numeric($a)) 
            $this->flashMessage('Banner bol úspešne vymazaný', 'success');
        else
            $this->flashMessage('Banner sa nepodarilo vymazať', 'error');
        $this->redirect('default');
    }

    public function actionAdd(){
        $this->setView('edit');
    }    
    
    public function actionEdit($id){
    }
}

