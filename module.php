<?php

include "html.php";
include "SID-model-operations.php";

function PrintListMod($idAuthor, $idEnc, $name, $type){
	$html = "<h2>Энциклопедия '$_GET[name]'</h2><table border=0>";
	RESET_MODULE($idAuthor, $idEnc);
	$idMod = FETCH_MODULE($idAuthor, $idEnc);
	$one = True;
	while ($idMod != 0){
		if ($one) {$html = $html."<h3>Выберите модуль:</h3><h3>"; $one = False;}
		$title = GET_MODULE_TITLE($idAuthor, $idMod);
		$html = $html."<form action='components.php?idEnc=$idEnc&idMod=idMod&name=$name&nameM=$title'  method='post'>";
		$html = $html.<<<EOT
		<tr><td><a href='module.php?idEnc=$idEnc&name=$name&idMod=$idMod'>
				<img width='20' src='images/edit.png' alt='Редактировать параметры модуля' title='Редактировать параметры модуля' />
			</a></td>
			<td>&nbsp;$title&nbsp;<td>
			<td><select name='type'>
				<option value=1>$type[1]</option>
				<option value=2>$type[2]</option>
				<option value=3>$type[3]</option>
				<option value=4>$type[4]</option>
				<option value=5>$type[5]</option>
				<option value=6>$type[6]</option>
				<option value=7>$type[7]</option>
			</select></td>
			<td><input type='submit' value='Отобразить компоненты'></td>
		</tr></form>
EOT;
		NEXT_MODULE($idAuthor, $idEnc);
		$idMod = FETCH_MODULE($idAuthor, $idEnc);
	}
	$html = $html."</table></h3><br><h2><a href='module.php?idEnc=$idEnc&name=$name&idMod=0'>Создать новый модуль</a></h2>";
	return $html;
}

function PrintError(){
	$html = <<<EOT
	<h2>Ошибка! Энциклопедия не выбрана</h2>
	<form action="encyclopedia.php" method="post">
	<p><input type="submit" value="Перейти к выбору энциклопедии" /></p>
	</form>
EOT;
	return $html;
}

function PrintCreateForm($idAuthor, $idEnc, $name){
	$idEnc = $_GET['idEnc'];
	$idMod = INSERT_MODULE($idAuthor, $idEnc);
	$html = <<<EOT
		<h2>Создаем новый модуль для энциклопедии '$name'</h2><br>
		<form action="module.php?idEnc=$idEnc&name=$name&idMod=$idMod&save=1" method="post">
		<p>Название: <input type="text" name="title" value="Введите название модуля" /></p>
		<p>Аннотация: <br><textarea name="annotation" cols="100" rows="10">Введите описание модуля</textarea></p>
		<p><input type="submit" value="Сохранить" /></p>
		</form>
EOT;
	return $html;
}

function PrintEditForm($idAuthor, $idEnc, $name, $idMod){
		$html = "<h2>Энциклопедия '$name' <br>Модуль</h2>";
		$title = GET_MODULE_TITLE($idAuthor, $idMod);
		$annotation = GET_MODULE_ANNOTATION($idAuthor, $idMod);
		$html = $html.<<<EOT
		<form action="module.php?idEnc=$idEnc&name=$name&idMod=$idMod&save=1" method="post">
			<p>Название: <input type="text" name="title" value="$title" /></p>
			<p>Аннотация: <br><textarea name="annotation" cols="100" rows="10">$annotation</textarea></p>
			<p><input type="submit" value="Применить изменения" /></p>
		</form>
EOT;
	return $html;
}


$idAuthor = 1;
echo $top;
$type = array(1 => "Теория", 2 => "Вопрос для самопроверки", 3 => "Пример", 4 => "Упражнение", 5 => "Тестовое задание", 6 => "Практическое задание", 7 => "Библиографическая ссылка");

if (!isset($_GET['idMod']) and isset($_GET['idEnc'])) { // модуль энциклопедии не выбран - выводим список модулей
	$idEnc = $_GET['idEnc'];
	$name = $_GET[name];
	echo PrintListMod($idAuthor, $idEnc, $name, $type);
} 

else if(!isset($_GET['idEnc'])){		// не определена энциклопедия
	echo PrintError();
} 

else if (($_GET['idMod']==0)AND(!isset($_GET['save']))){ // запросили создание нового модуля
		echo PrintCreateForm($idAuthor, $_GET['idEnc'], $_GET[name]);
}

else if (($_GET['idMod']>0)AND(!isset($_GET['save']))){	//выводим атрибуты выбранного модуля 				
		echo PrintEditForm($idAuthor, $_GET['idEnc'], $_GET[name], $_GET[idMod]);
}

else if (isset($_GET['save'])){							// сохранить изменения
		$idMod = $_GET['idMod'];
		SET_MODULE_TITLE($idAuthor, $idMod, $_POST['title']);
		SET_MODULE_ANNOTATION($idAuthor, $idMod, $_POST['annotation']);
		echo PrintListMod($idAuthor, $_GET['idEnc'], $_GET[name], $type);
}


echo $bottom;
?>
