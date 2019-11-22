<?php

include "html.php";
include "dbconnect.php";
include "SID-model-operations.php";

function PrintListComp($idAuthor, $idEnc, $name, $idMod, $nameM, $type, $t){
	$html = "<h2>Энциклопедия '$name' </h2><h3><br>Модуль '$nameM' &#8594; $type[$t]</h3><br><br>";
	$arr = COMPONENT_COLLECTION($idAuthor, $idMod, $t);
	if ($arr==NULL) {$html = $html."Пока в коллекции нет элементов<br><br>"; }
	$html = $html.<<<EOT
	<form action="components.php?idEnc=$idEnc&idMod=$idMod&name=$name&nameM=$nameM&idComp=0&type=$t" method="post">
		<table border=0>
		<tr><td>Содержимое нового элемента коллекции:</td><td>Поведение нового элемента коллекции:</td></tr>
		<tr><td><textarea name="asset" cols="60" rows="6">Введите содержимое</textarea></td>
		<td><textarea name="javascript" cols="60" rows="6">Введите JavaScript</textarea></td></tr>
		<tr><td><input type="submit" value="Добавить новый элемент в коллекцию" /></td><td></td></tr>
		</table>
	</form><br><br><br>
EOT;
	foreach ($arr as $comp) {
		$html = $html.<<<EOT
		<form action="components.php?idEnc=$idEnc&idMod=$idMod&name=$name&nameM=$nameM&idComp=$comp[0]&type=$t>" method="post">
			<table border=0>
			<tr><td>Содержимое компоненты:</td><td>Поведение компоненты:</td></tr>
			<tr><td><textarea name="asset" cols="60" rows="6">$comp[1]</textarea></td>
			<td><textarea name="javascript" cols="60" rows="6">$comp[2]</textarea></td></tr>
			<tr><td>Номер компоненты: $comp[3]</td><td><input type="submit" value="Применить изменения" /></td></tr>
			</table>
		</form><br>
EOT;
	}

//	$html = $html. "</h3><br><h3><a href='components.php?idEnc=$idEnc&idMod=$idMod&name=$name&nameM=$nameM&idComp=0&type=$t'>Добавить новый элемент</a></h3>";
	return $html;
}

function PrintError($idEnc){
	$html = <<<EOT
	<h2>Ошибка! Модуль не выбран</h2>
	<form action="module.php?idEnc=$idEnc" method="post">
	<p><input type="submit" value="Перейти к выбору энциклопедии" /></p>
	</form>
EOT;
	return $html;
}






echo $top;
$idAuthor = 1;

$type = array(1 => "Теория", 2 => "Вопрос для самопроверки", 3 => "Пример", 4 => "Упражнение", 5 => "Тестовое задание", 6 => "Практическое задание", 7 => "Библиографическая ссылка");

if (!isset($_GET['idComp']) and isset($_GET['idMod'])) { // компонент модуля не выбран - выводим список компонентов
	echo PrintListComp($idAuthor, $_GET[idEnc], $_GET[name], $_GET[idMod], $_GET[nameM], $type, $_POST['type']);
} 

else if(!isset($_GET['idMod'])){		// не определен модуль
	echo PrintError($_POST[idEnc]);
}

else if ($_GET['idComp']==0){			// работаем с новым элементом коллекции
/* 		$mysqli = conn();
		$d = date("Y-m-d");
		$idMod = $_GET[idMod];
		$t = $_GET[type];
		$query = "SELECT idSchem FROM scheme WHERE (idMod=$idMod) AND (idType=$t)";
		$res = $mysqli->query($query);
		$res->data_seek(0);
		$row = $res->fetch_assoc();
		$idSchem = $row['idSchem'];
		$query = "INSERT INTO component (idSchem, number, asset, javascript, created) VALUES ($idSchem, 0,'$_POST[asset]', '$_POST[javascript]','$d')";
		$res = $mysqli->query($query);
		$id = $mysqli->insert_id;
		InsertCursor($idAuthor, "Mod$t", $idMod, $id);
 */
	$id = ADD_COMPONENT_COLLECTION($idAuthor, $_GET[idMod], $_GET[type], $_POST[asset], $_POST[javascript]);
	echo PrintListComp($idAuthor, $_GET[idEnc], $_GET[name], $_GET[idMod], $_GET[nameM], $type, $_GET[type]);
}

else if ($_GET['idComp']>0){										// отредактировать существующий модуль энциклопедии			// сохранить изменения
	SET_COMPONENT($idAuthor, $_GET[idComp], $_POST[asset], $_POST[javascript]);
echo <<<EOT
<h2>Изменения были сохранены</h2>
<form action="module.php?idEnc=$_GET[idEnc]&name=$_GET[name]" method="post">
<p><input type="submit" value="ОК" /></p>
</form>
EOT;
}

echo $bottom;
?>