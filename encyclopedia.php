<?php
include "html.php";
include "SID-model-operations.php";

function ListForm($idAuthor){
	$list = ENCYCLOPEDIA_COLLECTION($idAuthor); 
	if($list!=NULL) {echo "<h2>Энциклопедии:</h2><h3>";}
	$html = "";
	foreach ($list as $row) {
		$html = $html. <<<EOT
			<p><a href='encyclopedia.php?idEnc=$row[idEnc]'>
			<img width='20' src='images/edit.png' alt='Редактировать параметры энциклопедии' title='Редактировать параметры энциклопедии' /></a>&nbsp;
			<a href='module.php?idEnc=$row[idEnc]&name=$row[title]' title='Отобразить модули энциклопедии'>$row[title]</a>&nbsp;</p>
EOT;
	}
	$html = $html."</h3><br><h2><a href='encyclopedia.php?idEnc=0'>Создать новую энциклопедию</a></h2>";
	return $html;
}

function CreateForm($idAuthor){
	$idEnc = CREATE_ENCYCLOPEDIA($idAuthor);
	$html = <<<EOT
	<h2>Создаем новую энциклопедию</h2><br>
	<form action="encyclopedia.php?idEnc=$idEnc&save=1" method="post">
	<p>Название: <input type="text" name="title" value="Введите название энциклопедии" /></p>
	<p>Аннотация: <br><textarea name="annotation" cols="100" rows="10">Введите аннотацию энциклопедии</textarea></p>
	<p><input type="submit" value="Сохранить" /></p>
	</form>
EOT;
	return $html;
}

function EditForm($idAuthor, $idEnc){
		$html = "<h2>Энциклопедия </h2>";
		$title = GET_ENCYCLOPEDIA_TITLE($idAuthor, $idEnc);
		$annotation = GET_ENCYCLOPEDIA_ANNOTATION($idAuthor, $idEnc);
		$html = $html.<<<EOT
		<form action="encyclopedia.php?idEnc=$idEnc&save=1" method="post">
			<p>Название: <input type="text" name="title" value="$title" /></p>
			<p>Аннотация: <br><textarea name="annotation" cols="100" rows="10">$annotation</textarea></p>
			<p><input type="submit" value="Обновить параметры энциклопедии" /></p>
		</form>
EOT;
	return $html;
}


echo $top;
$idAuthor = 1;

if (!isset($_GET['idEnc'])) { 	// Энциклопедия не выбрана - выводим список энциклопедий пользователя
	echo ListForm($idAuthor); 
} 

else if ($_GET['idEnc']==0){	// отображаем форму для создания новой энциклопедии					
	echo CreateForm($idAuthor);	
} 

// обрабатываем запросы на редактирование существующей энциклопедии
else if (($_GET['idEnc']>0)AND(!isset($_GET['save']))){		//выводим параметры выбранной энциклопедии				
	$idEnc = $_GET['idEnc'];
	echo EditForm($idAuthor, $idEnc);
		
}

else if (isset($_GET['save'])){		// сохраняем изменения
	$idEnc = $_GET['idEnc'];
	SET_ENCYCLOPEDIA_TITLE($idAuthor, $idEnc, $_POST['title']);
	SET_ENCYCLOPEDIA_ANNOTATION($idAuthor, $idEnc, $_POST['annotation']);
	echo ListForm($idAuthor); 
}

echo $bottom;
?>