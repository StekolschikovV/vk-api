 <?php
require_once 'pr.php';

class vk extends pr{
    
    private function send_vk($method, $params, $url = '') { // основная функция, выполняющая запросы к серверу ВК, (метод вк, передаваеммые параметры, ссылка запроса (в случае отличия от стандартной апи))
        if($url == ''){ // если не подано внешнего урм идём по стандартному апи урл
            $vk_url = "https://api.vk.com/method/" . $method . "?access_token=" . $this->conf_w('token') . "&v=" . $this->conf_w('v');
        } else {
            $vk_url = $url;
        }
echo "<pre><h1>---------DEBUG---------</h1><h2>>>> Request to the server vk.com:</h2><b>URL:<br></b>$vk_url";
        $ch = curl_init($vk_url); // оттправляем запрос средствами curl
        curl_setopt ( $ch, CURLOPT_HEADER, false );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_HEADER, 0);
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params ); // передаваемые данные
//        $info = curl_getinfo($ch); // информация по выполнению курл запроса
        $data = curl_exec($ch);
        curl_close($ch);
echo "<b><br><br>DATA:</b><pre>";print_r($params);echo"</pre>";
//echo "<b><br><br>INFO CURL:</b><pre>";print_r($info);echo"</pre>";
        return $data; //возвращаем страницу в сроке 
    }
    
    public function post($mess, $img_mas, $video_url = '') { // публикует на стену вк, (текст сообщения, массив ссылок на картинки(url), полная ссылка на видео)
            $photo_link = $this->img_group($img_mas); // формирует строку вк образных ссылок из обычных ссылок (url) на изображения.
            if(!$video_url == ''){
                $video_link = $this->video_link($video_url); //д елает из url на видео(с внешних серверов, не вк), вк образную ссылку на видео    
            }
            $data = json_decode(
                            $this->send_vk(
                                        'wall.post',
                                        array(
                                            'owner_id' => -$this->conf_w('groupid'), // owner_id для групп типа "группа" ставить '-'
                                            'from_group' => 1, // 1 от имени сообщества, 0 от имени админа
                                            'message' => $mess,
                                            'attachments' => $photo_link.$video_link
                                        )
                            )
                    );
echo "<h2><<< The response from the server:</h2><pre>";print_r($data);echo "</pre><h1>----------END----------</h1><br>";
            return $data;
    }
    
    public function url_upload() { // запрос ссылки для загрузки изображения на сервер вк.
        $url_upload = json_decode( // получаемм ссылку для отправки от vk api
            $this->send_vk(
                'photos.getWallUploadServer', // метод получения ссылки
                array(
                    'group_id' => $this->conf_w('groupid') // id группы
                )
            )
        );
echo "<h2><<< The response from the server:</h2><pre>";print_r($url_upload);echo "</pre><h1>----------END----------</h1><br>";
        return $url_upload->response->upload_url; //возвращаем ссылку на которую будем слать img в строку; 
    }
    public function imgch_del($img_arr_url = '') { // Удаление изображение из imgch (собственный сервер), на вход подается массив ссылок
        if(!$img_arr_url == ''){
            foreach($img_arr_url as $key => $value){ // перебераем массив ссылок
                $file_name = pathinfo($value)['basename']; // получаем имя файла
                $uri_file = dirname(__FILE__).DS.$this->conf_w('imgch').DS.$file_name; // формируем путь сохранения файла у себя на сервере
                $del[] = unlink($uri_file); // удаляем фаил
            }
        }
        return $del; // возвращает массив с результатами публикаций 1 - успех, 0 - неудача.
    }
    public function img_upload($url_img) { // загрузка изображения на сервер вк. (прямой url на img)
//echo "<pre><hr><br> --- Формируем ссылку --- ";
//echo "<br>url_img = ".$url_img;
        $file_name = pathinfo($url_img)['basename']; // получаем имя файла
//echo "<br>file_name = ".$file_name;
        $uri_file = dirname(__FILE__).DS.$this->conf_w('imgch').DS.$file_name; // формируем путь сохранения файла у себя на сервере
//echo "<br>uri_file = ".$uri_file;
        $copy = copy($url_img, $uri_file); // копируем изображение себе на сервер
//echo "<br>copy = "; print_r($copy);
        $file_up = curl_file_create($uri_file, 'image/jpeg', $file_name); // формируем данные по изображению для отправки через curl
//echo "<br>file_up = "; print_r($file_up);
        $file_mas = array('file' => $file_up); // пихаем в массив с ключём file нужно curl
//echo "<br>file_mas = "; print_r($file_mas);    
        $upload_url = $this->url_upload(); // получаем ссылку на которую будем слать img в строку

        $img_upload = json_decode(
            $this->send_vk('', $file_mas, $upload_url) // шлём всё через curl
        );
//echo "<br>img_upload = "; print_r($img_upload); echo "<br><hr></pre>";
//echo "<pre><hr><br> --- Формируем ссылку конец --- ";
echo "<h2><<< The response from the server:</h2><pre>";print_r($img_upload);echo "</pre><h1>----------END----------</h1><br>";
        return $img_upload; // ответ сервера после отправки файла в массиве
    }
    
    public function img_save($url_img) { // сохранение (закрепление, присвоение id) изображения на сервере
        $img_upload = $this->img_upload($url_img); // загрузка изображения на сервер вк. (прямой url на img)
        $img_save = json_decode(
            $this->send_vk(
                'photos.saveWallPhoto', // сохранение (закрепление, присвоение id) изображения на сервере
                    array(
                        'group_id' => $this->conf_w('groupid'),
                        //'user_id' => $this->conf_w('userid'),
                        'photo' => stripslashes($img_upload->photo),
                        'server' => $img_upload->server,
                        'hash' => $img_upload->hash                                             
                    )
            )
        );
echo "<h2><<< The response from the server:</h2><pre>";print_r($img_save);echo "</pre><h1>----------END----------</h1><br>";
        return $img_save; // массив с данными ответа сервера
    }
    
    public function img_group($img_mas) { // формирует строку вк образных ссылок из обычных ссылок (url) на изображения.
        $img_group = ''; // объявляем переменную
        foreach($img_mas as $key => $value){
            $img_group .= 'photo'.$this->conf_w('userid').'_'.$this->img_save($value)->response[0]->id.',';
        }
        return $img_group; // сформированная строка вк образных ссылок
    }
    
    public function video_link($url_video) { //делает из url на видео(с внешних серверов, не вк), вк образную ссылку на видео
        $video_link = json_decode( // 
            $this->send_vk(
                'video.save', // метод сохранения по ссылке
                    array(
                        'link' => $url_video,
                        'group_id' => $this->conf_w('groupid'),
                        'is_private' => '1' // не публиковать в альбом                                       
                    )
            )
        );
        $addvideo = json_decode(file_get_contents($video_link->response->upload_url)); // идём по ссылке полученой в ответ на пред запрос, для активации закрепления, возвращает массив с video_id
echo '<pre><h1>---------DEBUG FL: '.__FILE__.' FU: '.__FUNCTION__.' LI: '.__LINE__.'---------</h1><h2>>>> Request to the server vk.com:</h2><b>URL:<br></b>$vk_url';

        return 'video'.-$this->conf_w('groupid').'_'.$video_link->response->video_id; // формируем и возвращаем vk образную ссылку на видосик.
    }     
}

// Пример использования класса:
//$vk = new vk();
//$test = array('http://rumedia.ws/uploads/posts/2015-06/1434139308_861614.jpg','http://rumedia.ws/uploads/posts/2015-06/1434139415_08a894f54f66.png');
//$vk_post = $vk->post('text', $test, 'https://www.youtube.com/watch?v=8AGgCV9vS9Y');

// Получить токен: https://oauth.vk.com/authorize?client_id=5594065-----id приложения------&scope=groups,wall,offline,photos,video&redirect_uri=https://oauth.vk.com/blank.html&display=page&v=5.21&response_type=token
//https://oauth.vk.com/blank.html#access_token=5292382fcf9792f2e0045987b9cea22a23445934214fafcb1442edc2aa6bf375aec85f73122c39b4ca61e&expires_in=0&user_id=379793163
?> 
