<?php
include "html.php";
include "SID-model-operations.php";


function ListPrograms($idAuthor){
	$arr = PROGRAM_COLLECTION($idAuthor);
	$html = "";
	if ($arr != Null) {$html = "<h2>Образовательные программы:</h2><h3>";}
	$n = 1;
	foreach($arr as $row){
		$idStand = $row[idStand];
		$description = GET_STANDART_DESCRIPTION($idAuthor, $idStand);
		$level = GET_STANDART_LEVEL($idAuthor, $idStand);
		$len = GET_STANDART_LENGTH($idAuthor, $idStand);
		$html = $html.<<<EOT
			<p>$n) $description <br> Уровень обучения: $level <br> Срок обучения: $len года <br> 
				<a href="standart.php?idStand=$idStand">Перейти к стандарту </a><br> 
				Перейти к компетенциям: 
				<a href="standart.php?idStand=$idStand&name=$description&type=УК" title="УК">УК</a>&nbsp;
				<a href="standart.php?idStand=$idStand&name=$description&type=ОПК" title="ОПК">ОПК</a>&nbsp;
				<a href="program.php?idProg=$row[idProg]&name=$description&type=ПК" title="ПК">ПК</a>&nbsp; <br> 
				<a href="course.php?idProg=$row[idProg]&name=$description&type=К">Перейти к курсам </a><br>
				<a href="analysis.php?idProg=$row[idProg]&name=$description">Анализировать программу</a><br><br>
			</p><br>
EOT;
		$n +=1;
	}
	$html = $html."</h3><br><h2><a href='program.php?idProg=0'>Создать новую образовательную программу</a></h2>";
	return $html;
}
function CreateForm($idAuthor){
	$arr = STANDART_COLLECTION($idAuthor);
	$select = "";
	foreach ($arr as $row){
		$select = $select."<option value=$row[idStand]>$row[description]</option>";
	}
	$html = <<<EOT
		<h2>Создаем новую образовательную программу</h2>
		<form action="program.php?idProg=0&save=1" method="post">
			<p>Стандарт: 
			<select name="idStand"><option selected disabled>Выберите стандарт</option>$select</select>
			<input type="text" name="year" value="Введите год набора" /></p>
			<p><input type="submit" value="Сохранить" /></p>
		</form>
EOT;
	return $html;
}

echo $top;
$idAuthor = 1;
if (!isset($_GET['idProg'])) { 	// Программа не выбрана - выводим список программ пользователя				
	echo ListPrograms($idAuthor);	
}
	
else if (($_GET['idProg']==0)AND(!isset($_POST[idStand]))){	// обрабатываем запрос на создание новой программы				
	echo CreateForm($idAuthor);	
} 

else if (($_GET['idProg']==0)AND(isset($_POST[idStand]))){ //сохраняем данные
		$year = $_POST[year];
		$idStand = $_POST[idStand];
		$idProg = CREATE_PROGRAM($idAuthor, $idStand, $year);
		echo ListPrograms($idAuthor);
}

else if($_POST[type] == 'УК'){
	echo "УК";
}

echo $bottom;
?>