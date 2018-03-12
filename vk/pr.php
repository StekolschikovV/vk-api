<?php
define('DS', DIRECTORY_SEPARATOR);
class pr {
// запись в фаил конфигураций
    function conf_r($val_cr){
        $file = fopen('db/conf.ini','a');
        if(empty($file)){
            $error = $error."<LI>Не удалось создать файл conf";
        }
            // записываем информацию в файл, по одной строчке на каждое поле
            fputs($file, $value);
            // закрываем файл
            fclose($file);
    }
//* запись/чтение в фаилы csv. 2 массив который пишем, 1 место куда пишем, по умолчанию conf.ini>uridb
    function db_rw_csv($uri = 'uridb', $db_rw = ''){
        $uri = $this->conf_w($uri); // получаем путь из conf.ini по параметру
        $file = fopen($uri,'r+'); // открываем для записи и чтения
                if(empty($file)){
            $error = $error."<LI>Не удалось создать файл db";
        }
        if($db_rw != ''){ // если данные для записи поданы, то записываем их
            ftruncate($file, 0); // очистеть фаил
            fputs($file, date("d-m-Y H:i:s", time())."\n");
            foreach ($db_rw as $line) {
                fputcsv($file, explode(',', $line), ';');
            }
        }        
        
    }
// запись/чтение в фаилы строкой. 1 место куда пишем, по умолчанию conf.ini>uridb, 2 массив который пишем, 3 проверочный код (пример: для сравнения) 4.нужно ли чистить фаил, если да то параметр 'a', запись в конец файла
    function db_rw($uri = 'uridb', $db_rw = '', $bit = '', $mode = 'r+'){
        $uri = $this->conf_w($uri); // получаем путь из conf.ini по параметру
        $file = fopen($uri, $mode); // открываем для записи и чтения
        if(empty($file)){
            $error = $error."<LI>Не удалось создать файл db";
        }
        if($db_rw != ''){ // если данные для записи поданы, то записываем их
            if($mode == 'r+'){ 
                ftruncate($file, 0);  // очистеть фаил, если в функцию не передан 4тый параметр
                fputs($file, $bit.';'.date("d-m-Y H:i:s", time())."\n");
            }
            foreach($db_rw as $key => $value){
                // записываем информацию в файл, по одной строчке на каждое поле
                $write = fputs($file, $value."\n");
            }
            // закрываем файл
            fclose($file);
            return 1;
        } else { // если данные не поданы то читаем из файла
            if($bit == 1){ // проверяем не запрашивается ли bit
                $m_data = fgets($file, 33); // вытягиваем из файла bit в строку
            } else {
                $m_data_no = file($uri); // возвращаем содержимое файла в массив как есть
                $i = 0;
                foreach($m_data_no as $key => $value){ // перебераем массив
                    if($key != 0) // исключаем дату создания из массива
                    $m_data[$i++] = trim($value); // чистим элементы массива от переносов в конце строки                            
                }
            }
            if(!isset($m_data))$m_data = '';
            return $m_data;
        }
    }
    //функция чтения регулярных выражений, для получения вызвать функцию с именем ключа регулярки, и ключа нужного массива
    function preg_w($mas = 'a', $val_pw = ''){
        require_once 'db/preg.php';
        if($val_pw != ''){
            return $preg_arr[$mas][$val_pw];    
        } else {
            return $preg_arr[$mas];
        }
         
    }
    //функция чтения конфигураций, если в качестве параметра не задан ключ элемента массива, вернёт весь массив
    function conf_w($val_cw = ''){
        $mas_conf = parse_ini_file('db/conf.ini', true);
        if($val_cw != '')
            return $mas_conf[$val_cw];
        else
            return $mas_conf; 
    }

    //функция запроса по регулярке с кешированием в фаил, 1 регулярка, 2 выбранный массив (если не задан вернёт весь массив), 3 урл запроса. 
    function reg_data($regexp, $nm = '', $url = ''){
        $s = 0; // разрешаем кеширование
        if($url == ''){
            $url = $this->conf_w('url');
            $s = 1; // ключь для отмены кеширования
            $page = file_get_contents($url); // получаем код страници в строку с сайта
        }
        if($s != 1){ // отменяем кеширование если url запрашивается из конфига
            $uri = $this->conf_w('html'); // получаем путь к html_ch
            $file = fopen($uri,'r+'); // открываем фаил
            if(empty($file)){
                $error = $error."<LI>Не удалось создать файл html_ch";
            }
            $file_url_str = fgets($file); // читаем первую строку
            if(trim($file_url_str) != $url){ //сравниваем урл с файла и переданный в качестве парам, если совпадают значит берём из кеша
                    ftruncate($file, 0); // очистеть фаил
                    $page = file_get_contents($url); // получаем страницу в строку
                    fputs($file, $url."\n".$page); // пишем в фаил
                    fclose($file);    
            } else {
                $page = file_get_contents($uri); // получаем данные из кеша html_ch
            }
        }
    //* при проблемах с кодировкой раскоментировать    
            $page = iconv("WINDOWS-1251", "UTF-8//IGNORE", $page);
    //*
        preg_match_all($regexp, $page, $buffer); // проводим поиск по регулярке, и пишем в $buffer
        if($nm == '')
            return $buffer; // возвращаем как есть, без обработки
        else
        // чистим полученные данные от html
            foreach($buffer[$nm] as $key => $value){
                $buffer[$nm][$key] = strip_tags($value);
            }
            return $buffer[$nm]; 
    }
    
    function debug($data, $text1, $text2, $text3){ // не допиленая функция дебага
        echo '<pre><h1>---------DEBUG FL: '.__FILE__.' FU: '.__FUNCTION__.' LI: '.__LINE__.'---------</h1><h2>>>> Request to the server vk.com:</h2><b>URL:<br></b>$vk_url';
        echo "<b><br><br>DATA:</b><pre>";print_r($params);echo"</pre>";
        echo "<h2><<< The response from the server:</h2><pre>";print_r($video_link);echo "</pre><h1>----------END----------</h1><br>";
        echo __FUNCTION__.__LINE__;   
    }
}