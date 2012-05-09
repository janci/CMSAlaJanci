<?php
class AdminPhotosPresenter extends BaseAdminPresenter {
        public function renderUploadPhotos($id){
            // list of valid extensions, ex. array("jpeg", "xml", "bmp")
            $allowedExtensions = array('jpeg','jpg','png');
            // max file size in bytes
            $sizeLimit = 10 * 1024 * 1024;

            $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
            $result = $uploader->handleUpload('gallery/');
            
            if(isset($result, $result['success']) && $result['success']==true){
                $arr = array(
                    'filename'=>$result['filename'],
                    'created'=>new DateTime,
                    'album_id'=>$id
                    
                );
                $this->db->table('photo')->insert($arr);
            }
            
            // to pass data through iframe you will need to encode all html tags
            echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
            $this->terminate();
        }
        
        
        public function renderDefault(){
            $this->template->gallery = $this->db->table('album')->where('remove',0);
            
        }
        
        public function handleRemove($id){
            $a = $this->db->table('photo')->where('id',$id)->update(array('remove'=>1));
            $record = $this->db->table('photo')->where('id',$id)->fetch();
            if($record!=false)
                @unlink('gallery'.DIRECTORY_SEPARATOR.$record->filename);
            
            if (isset($a) && is_numeric($a)) 
                $this->flashMessage('Dokument bol úspešne vymazaný', 'success');
            else
                $this->flashMessage('Dokument sa nepodarilo vymazať', 'error');
            $this->redirect('default');
        }
        
        
        
       
}
