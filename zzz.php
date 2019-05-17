<?php

$access_token = "a24f5e868dd6e843f246477c702789fdc5507f46712c2ecc2f3a463bcc3f8200be34cacf43def11292984";
$group_id = "182436277";
$album_id = '262083116';
$image_path = dirname(__FILE__) . '/test_name.jpg';

$vk = new Model_Vk($access_token);

//Загружаем изображение
$upload_img = $vk->uploadImage($image_path,$group_id,$album_id);

class Model_Vk {

    private $access_token;
    private $url = "https://api.vk.com/method/";

    /**
     * Конструктор
     */
    public function __construct($access_token) {

        $this->access_token = $access_token;
    }

    /**
     * Делает запрос к Api VK
     * @param $method
     * @param $params
     */
    public function method($method, $params = null) {

        

        $p = "";
        if( $params && is_array($params) ) {
            foreach($params as $key => $param) {
                $p .= ($p == "" ? "" : "&") . $key . "=" . urlencode($param);
            }
        }
        $response = file_get_contents($this->url . $method . "?" . ($p ? $p . "&" : "") . "access_token=" . $this->access_token."&v=5.90");
         print_r($response);
        // json_decode($response);
       
      

        if( $response ) {
            return json_decode($response);
            // print_r($response);
        }
        return false;
    }









  public function uploadImage($file, $group_id = null, $album_id = null) {

    $params = array();
    if( $group_id ) {
      $params['group_id'] = $group_id;
    }
    if( $album_id ) {
      $params['album_id'] = $album_id;
    }

    //Получаем сервер для загрузки изображения
    $response = $this->method("photos.getUploadServer", $params);


    if( isset($response) == false ) {
      print_r($response);
      exit;
    }
    
    $server = $response->response->upload_url;

    $postparam = Array('file1'=>new \CURLFile($file));
    //$postparam=array("file1"=>"@".$file);
    //Отправляем файл на сервер
    $ch = curl_init($server);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$postparam);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data; charset=UTF-8'));
    $json = json_decode(curl_exec($ch));
    curl_close($ch);

    //  print_r($postparam);


    

    
    //Сохраняем файл в альбом
    $photo = $this->method("photos.save", array(
      "server" => $json->server,
      "photos_list" => $json->photos_list,
      "album_id" => $album_id,
      'gid' => $group_id,
      "hash" => $json->hash
      
    ));
    

    if( isset($photo->response[0]->id) ) {
      return $photo->response[0]->id;
    } else {
      return false;
    }
  }
}
?>