<?php

/*вот токен
https://oauth.vk.com/authorize?client_id=4995526&scope=groups,wall,offline,photos&redirect_uri=https://oauth.vk.com/blank.html&display=page&v=5.73&response_type=token


https://oauth.vk.com/authorize?client_id=6404521&scope=groups,wall,offline,photos&redirect_uri=https://oauth.vk.com/blank.html&display=page&v=5.73&response_type=token

Где client_id - это id твоего ПРИЛОЖЕНИЯ
*/
$token = '54f62041e432ac446cb6f1c724f455ba7fde3c2350123612d1e3530bace55d647e0d1036fbf0cd6c12d7d';
//$token = '980be43837f0e504c3f01ed57ff4ce5b76194deb150958a2b9fd85f1141b6ea91820f79d94b9c2d618e9f';
$group_id = '163307003';
$album_id = '254222855';
$v = '5.73';
$image_path = __DIR__ . "/img3333333.jpg";//поправь тут как надо

$server = file_get_contents("https://api.vk.com/method/photos.getUploadServer?album_id=".$album_id."&group_id=".$group_id."&v=".$v."&access_token=".$token);

$url = json_decode($server);
print_r($url);
echo "<br><br><br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url->response->upload_url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => new \CurlFile($image_path)]);
$output = curl_exec($ch);
print_r($output);
echo "<br><br><br>";


if($output === false){
    $output = curl_error($ch);
    var_dump($output);
    //  если этот вардамп исполняется, скорей всего виноват хостер, который зарубил allow_url_fopen
};
$response = json_decode($output);
print_r($response);
echo "<br><br><br>";

// сохраняем
$safe = file_get_contents("https://api.vk.com/method/photos.save?".
    "server=" . $response->server
    ."&album_id=" . $album_id
    ."&photos_list=".$response->photos_list
    ."&hash=".$response->hash
    ."&group_id=".$group_id
    ."&access_token=".$token
    ."&photo=".$image_path
    ."&v=".$v);

$safe = json_decode($safe);
print_r($safe);
echo "<br><br><br>";
///////////////////////////////////////////////////////////






$user_id = "13897175";
$access_token = "980be43837f0e504c3f01ed57ff4ce5b76194deb150958a2b9fd85f1141b6ea91820f79d94b9c2d618e9f";
$group_name = "162294758";
$url = 'https://api.vk.com/method/messages.send';
$params = array(
    'user_id' => $user_id,
    'message' => trim("5555"),
//    'attachment' => "http://worldwideshop.ru/mes/img.jpg",
 //   'attachment' => "photo-162045882_456239080,photo-162045882_456239080",
//    'attachment' => "photo13897175_456239304",
    'attachment' => "",
    'access_token' => trim($access_token),
    'v' => '5.37',
);
$result = file_get_contents($url, false, stream_context_create(array(
    'http' => array(
        'method' => 'POST',
        'header' => 'Content-type: application/x-www-form-urlencoded',
        'content' => http_build_query($params)
    )
)));
var_dump($result);
