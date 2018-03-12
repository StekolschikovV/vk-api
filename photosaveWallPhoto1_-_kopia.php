<?php
$pic;
 if (!empty($_GET["name"]))
 {
     echo " Получены новые вводные: имя - ".$_GET["name"];
     $pic= "/pic1/".$_GET["name"];
 }
 else {
     echo "Переменные не дошли. Проверьте все еще раз.";
 }



$token = '';
$group_id = '';
$album_id = '';
$v = '5.62'; //версия vk api
$image_path = dirname(__FILE__). $pic;// путь до картинки
$post_data = array("file1" => '@'.$image_path);

// получаем урл для загрузки
$url = file_get_contents("https://api.vk.com/method/photos.getWallUploadServer?group_id=".$group_id."&v=".$v."&access_token=".$token); //
$url = json_decode($url)->response->upload_url;
//print_r($url);
//// отправка post картинки
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
$result = json_decode(curl_exec($ch),true);

//// сохраняем
$safe = file_get_contents("https://api.vk.com/method/photos.saveWallPhoto?server=".$result['server']
    ."&photo=".$result['photo']
    ."&hash=".$result['hash']
    ."&group_id=".$group_id
    ."&caption=".""
    ."&access_token=".$token);


$safe = json_decode($safe,true);

echo "</br></br>=". $safe['response'][0]['id'];
$message = "";

//
$query=file_get_contents("https://api.vk.com/method/wall.post?owner_id=-".$group_id."&from_group=1&attachments=".$safe['response'][0]['id'] ."&message=".urlencode($message)."&access_token=".$token);
    ?>