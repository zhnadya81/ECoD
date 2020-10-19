<?php

require "dbconnect.php";
require "db.php";

function func($num, $dir, $content){  
// $num 	- номер модуля = имя папки для файлов модуля
// $dir 	- имя директории для всего курса
// $content	- массив с данными для файлов модуля $num

	$modNum = $num;
	$modDir = $dir.$modNum."/";
	$tempDir = "templates/";
	$components = array("theory","theory_control","practice","practice_control", "literature", "test", "test_result", "page_q");
	$count = 8; 	// количество дидактических слоев

	mkdir($modDir, 0777, true);

	$filename = realpath($tempDir."SCO.html"); 
	$f = fopen($filename, 'r');
	$text = fread($f, filesize($filename));
	fclose($f);
	$f = fopen($modDir."SCO.html", 'wb');
	$text = str_replace("<!--title-->", $content[0]["title"], $text);
	fwrite($f, $text);
	fclose($f);

	$filename = realpath($tempDir."menu.html"); 
	$f = fopen($filename, 'r');
	$text = fread($f, filesize($filename));
	fclose($f);
	$f = fopen($modDir."menu.html", 'wb');
	$text = str_replace("<!--title-->", $content[0]["title"], $text);
	fwrite($f, $text);
	fclose($f);

	//$content = $contentS[$num];
	//echo $contentS[$num];

	// для каждого компонента кроме теста
	$cCount = 2; // количество вопросов в тесте
	for($i=0; $i<$count; $i++){
		if ($i==7){
			for($k=0; $k<$cCount; $k++){					//  для каждого вопроса теста
				$filename = realpath($tempDir.$components[$i].".html"); 
				$f = fopen($filename, 'r');
				$text = fread($f, filesize($filename));
				fclose($f);
				$f = fopen($modDir.$components[$i]."_".$k."_.html", 'wb');
				$text = str_replace("<!--title-->", $content[$i]["title"], $text);
				$text = str_replace("<!--script-->", $content[$i]["script"], $text);
				$text = str_replace("<!--content-->", $content[$i]["content"], $text);
				$text = str_replace("<!--j-->", "".($k)."", $text);
				$text = str_replace("<!--i-->", "".($k+1)."", $text);
				fwrite($f, $text);
				fclose($f);
			}
		}
		else if($i==6){
			$filename = realpath($tempDir.$components[$i].".html"); 
			$f = fopen($filename, 'r');
			$text = fread($f, filesize($filename));
			fclose($f);
			$f = fopen($modDir.$components[7]."_".$cCount."_.html", 'wb');
			$text = str_replace("<!--title-->", $content[$i]["title"], $text);
			$text = str_replace("<!--content-->", $content[$i]["content"], $text);
			$text = str_replace("<!--script-->", $content[$i]["script"], $text);
			fwrite($f, $text);
			fclose($f);
		}
		else {
			$filename = realpath($tempDir.$components[$i].".html"); 
			$f = fopen($filename, 'r');
			$text = fread($f, filesize($filename));
			fclose($f);
			$f = fopen($modDir.$components[$i].".html", 'wb');
			$text = str_replace("<!--title-->", $content[$i]["title"], $text);
			$text = str_replace("<!--content-->", $content[$i]["content"], $text);
			$text = str_replace("<!--script-->", $content[$i]["script"], $text);
			fwrite($f, $text);
			fclose($f);
		}
//print_r($content);
//	echo var_dump($content);
	}

}

/////////////////////////////////////////////////////////
//               main program          /////////////////

$dir = "square/";	// папка для всего курса

if (!mkdir($dir, 0777, true)) {	// создаем папку для курса
	echo "Не удалось создать директорию ".$dir;
	exit();
}else{
	mkdir($dir."/res/css/", 0777, true);	// сюда копируем стили
	mkdir($dir."/res/img/", 0777, true);	// сюда копируем логотип
	mkdir($dir."/res/js/", 0777, true);		// сюда копируем скрипты
}
copy("res/css/style.css", $dir."res/css/style.css");
copy("res/img/logo.png", $dir."res/img/logo.png");
copy("res/js/tests.js", $dir."res/js/tests.js");
copy("res/js/mysco.js", $dir."res/js/mysco.js");

// $num 	- номер модуля = имя папки для файлов модуля
// $dir 	- имя директории для всего курса
// $content	- массив с данными для файлов модуля $num

$mod=1;
$query = "SELECT * FROM component INNER JOIN scheme ON component.idSchem=scheme.idSchem WHERE idMod=".$mod;
$res = $mysqli->query($query);
$content = $content0;
for ($row_no = 0; $row_no < $res->num_rows; $row_no++) {

    $res->data_seek($row_no);
    $row = $res->fetch_assoc();
	$cont = $row['content'];
	if($row['idSchem']==1){
		$content[0] = array("title" => $row['name'], "content" => $row['content'], "script" => "");
	}
}

func("0", $dir, $content0); 		// Площадь многоугольника
func("00", $dir, $content00);		// Площадь квадрата
func("000", $dir, $content000);	// Площадь прямоугольника
func("001", $dir, $content001);		// Теорема Пифагора
func("0010", $dir, $content0010);	// Теорема, обратная теореме Пифагора
func("0011", $dir, $content0011);	// Формула Герона


$f = fopen($dir."imsmanifest.xml", 'wb');
fwrite($f, $manifest);			// пока берем из файла db.php как константу
fclose($f);

echo <<<EOT
в папке square создан курс <br>
<ul>
	<li><a target="_blank" href="./square/0/SCO.html">Площадь многоугольника</a></li>
	<li><a  target="_blank" href="./square/00/SCO.html">Площадь квадрата</a></li>
	<li><a target="_blank"  href="./square/000/SCO.html">Площадь прямоугольника</a></li>
	<li><a  target="_blank" href="./square/001/SCO.html">Теорема Пифагора</a></li>
	<li><a  target="_blank" href="./square/0010/SCO.html">Теорема, обратная теореме Пифагора</a></li>
	<li><a  target="_blank" href="./square/0011/SCO.html">Формула Герона</a></li>
</ul>

EOT;
?>