<?php
class AdminGalleryPresenter extends BaseAdminPresenter {
    private $gallery_id;
    public function renderDefault(){
        $this->template->albums = $this->db->table('album')
                                    ->select('COUNT(photo:id) AS photos')
                                    ->select('name, album.id')
                                    ->group('album.id')
                                    ->where('album.remove=?',0);

    }

    public function actionAdd(){
        $this->view = 'edit';
    }

    public function actionEdit($id){
        $this->gallery_id = $id;
    }


    public function createComponentEditGalleryForm(){
        $gallery = $this->db->table('album')->where(array('id'=>$this->gallery_id))->fetch();
        $gallery = ($gallery)? $gallery->toArray():array();
        $form = new NAppForm; 
        $form->addGroup('Galéria');
        $form->addText('name','Názov:');
        if (isset($this->gallery_id)) {
            $form->addHidden('operation','edit');
            $form->addSubmit('odoslat','Upraviť album');
        } else {
            $form->addHidden('operation','insert');
            $form->addSubmit('odoslat','Pridať album');
        }
        if (isset($this->gallery_id)) $form->setValues($gallery);

        $form->onSuccess[] = array($this,'onSubmitEditGalleryForm');
        return $form;
    }

        public function onSubmitEditGalleryForm(NAppForm $form){
        $val = $form->values;
        $up_pages = array('name'  => $val['name']);

        $dbq = $this->db->table('album')->where(array('id'=>$this->gallery_id));
        if ($val['operation']=='insert'){
            $up_pages['created'] = new DateTime();
            $dbq->insert($up_pages);
        } else {
            $dbq->update($up_pages);
        }

        $this->flashMessage('Uloženie prebehlo v poriadku!','success');
        $this->redirect('default');
    }

        public function handleRemove($id){
        $a = $this->db->table('album')->where('id',$id)->update(array('remove'=>1));
        if (isset($a) && is_numeric($a)) 
            $this->flashMessage('Galéria bola úspešne vymazaná', 'success');
        else
            $this->flashMessage('Galériu sa nepodarilo vymazať', 'error');
        $this->redirect('default');
    }
}
