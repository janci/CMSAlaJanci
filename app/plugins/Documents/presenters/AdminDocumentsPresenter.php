<?php
class AdminDocumentsPresenter extends BaseAdminPresenter {
    private $doc_directory;

    public function startup() {
        parent::startup();
        $this->doc_directory = WWW_DIR.DIRECTORY_SEPARATOR. "documents";
    }
    
    public function renderDefault(){
        $this->template->documents = $this->db->table('document')->where('remove',0);
    }
        
    public function actionAdd(){
        $this->view = 'edit';
    }
        
        
    public function createComponentEditDocumentForm(){
            $form = new NAppForm;
            $form->addText('description','Popis súboru:')
                    ->setRequired('Popis súboru je povinný údaj!');
            $form->addUpload('file','Súbor:')
                    ->setRequired('Je potrebné aby ste nahrali na server súbor!');
            $form->addSubmit('send','Pridať súbor');
            $form->onSuccess[] = callback($this, 'addDocumentFormSubmit');
            return $form;
    }
        
    public function addDocumentFormSubmit(NAppForm $form){
            $values = $form->values;
            $file = $values['file'];
            if(!file_exists($this->doc_directory))
                    mkdir($this->doc_directory);
            if ($file->isOK()) {
                    $file->move($this->doc_directory.DIRECTORY_SEPARATOR.$file->getSanitizedName());
                    $arg = array(
                        'file'=>$file->getSanitizedName(),
                        'description'=>$values->description,
                        'created'=>new DateTime()
                    );
                    $this->getService('database')->table('document')->insert($arg);
                    $this->flashMessage('Uloženie prebehlo v poriadku!','success');
                    $this->redirect('default');
            } else {
                    $form->addError('Upload suboru sa nepodaril!');
            }
    }
        
    public function handleRemove($id){
        $a = $this->db->table('document')->where('id',$id)->update(array('remove'=>1));
        $record = $this->db->table('document')->where('id',$id)->fetch();
        if($record!=false)
            @unlink($this->doc_directory.DIRECTORY_SEPARATOR.$record->file);

        if (isset($a) && is_numeric($a)) 
            $this->flashMessage('Dokument bol úspešne vymazaný', 'success');
        else
            $this->flashMessage('Dokument sa nepodarilo vymazať', 'error');
        $this->redirect('default');
    }
}

