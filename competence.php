<?php

include "html.php";
include "SID-model-operations.php";

function ListUC($idAuthor, $idStand, $description){
	$html = "<h2>$description</h2><br><h2>Универсальные компетенции:</h2><h3>";
	$arr = UC_COLLECTION($idAuthor, $idStand);
//	if ($arr != Null){$html =$html."";}
	foreach ($arr as $row){
		$html = $html. "<p><a href='competence.php?idCmp=$row[idCompetence]&idStand=$idStand&type=УК'><img width='20' src='images/edit.png' alt='Редактировать компетенцию' title='Редактировать компетенцию' /></a>УК-".$row[number].". ".$row[description]."&nbsp;</p>";
	}
	$html = $html. "</h3><br><h2><a href='competence.php?idCmp=0&idStand=$idStand&type=УК'>Создать новую компетенцию</a></h2>";
	return $html;
}

function ListGPC($idAuthor, $idStand, $description){
	$html = "<h2>$description</h2><br>";
	$arr = GPC_COLLECTION($idAuthor, $idStand);
	if ($arr != Null){$html =$html."<h2>Общепрофессиональные компетенции:</h2><h3>";}
	foreach ($arr as $row){
		$html = $html. "<p><a href='competence.php?idCmp=$row[idCompetence]&idStand=$idStand&type=ОПК'><img width='20' src='images/edit.png' alt='Редактировать компетенцию' title='Редактировать компетенцию' />ОПК-".$row[number].". ".$row[description]."</a>&nbsp;</p>";
	}
	$html = $html. "</h3><br><h2><a href='competence.php?idCmp=0&idStand=$idStand&type=ОПК'>Создать новую компетенцию</a></h2>";
	return $html;
}

function ListPC($idAuthor, $idProg, $description){
	$html = "<h2>$description</h2><br>";
	$arr = PC_COLLECTION($idAuthor, $idProg);
	if ($arr != Null){$html =$html."<h2>Профессиональные компетенции:</h2><h3>";}
	foreach ($arr as $row){
		$html = $html. "<p><a href='competence.php?idCmp=$row[idCompetence]&idProg=$idProg&type=ПК'><img width='20' src='images/edit.png' alt='Редактировать компетенцию' title='Редактировать компетенцию' />ПК-".$row[number].". ".$row[description]."</a>&nbsp;</p>";
	}
	$html = $html. "</h3><br><h2><a href='competence.php?idCmp=0&idProg=$idProg&type=ПК'>Создать новую компетенцию</a></h2>";
	return $html;
}

function EditForm($idAuthor, $id, $idComp){
	$mysqli = conn();
	$html = "<h2>Компетенция </h2>";
	$query = "SELECT * FROM `competence` WHERE `idCompetence`='$idComp'";
	$res = $mysqli->query($query);
	for ($row_no = 0; $row_no < $res->num_rows; $row_no++) {
		$res->data_seek($row_no);
		$row = $res->fetch_assoc();
		if ($row[type]=="ПК"){$txt = "&idProg=$id";}
		else{$txt = "&idStand=$id";}
$html = $html.<<<EOT
<form action="competence.php?idCmp=$idComp&save=1&$txt" method="post">
<p>Компетенция: $row[type]-$row[number]</p>
<p>Описание: <br><textarea name="description" cols="100" rows="10">$row[description]</textarea></p>
	<p><input type="submit" value="Обновить информацию о компетенции" /></p>
</form>
EOT;
	return $html;
	}
}

function CreateForm($idAuthor,$id,$type){
	if ($type=="ПК"){$txt = "idProg=$id";}
	else{$txt = "idStand=$id";}

	$html = <<<EOT
	<h2>Компетенция </h2>
	<form action="competence.php?idCmp=0&save=1&$txt" method="post">
	<p>Компетенция: $type-<input type="text" name="number/> </p>
	<p>Описание: <br><textarea name="description" cols="100" rows="10">Введите данные</textarea></p>
	<p><input type="submit" value="Добавить компетенцию" /></p>
</form>
EOT;
	return $html;
}

$idAuthor = 1;
echo $top;
if (!isset($_GET[idCmp]) AND isset($_GET[idStand]) AND isset($_GET[type])) { 							
	if ($_GET[type] == "УК"){echo ListUC($idAuthor, $_GET[idStand], $_GET[name]);}
	else {echo ListGPC($idAuthor, $_GET[idStand], $_GET[name]);}
}

else if(!isset($_GET[idCmp]) AND isset($_GET[idProg])AND isset($_GET[type])){
	echo ListPC($idAuthor, $_GET[idProg], $_GET[name]);
}

else if(isset($_GET[idCmp]) AND $_GET[idCmp]>0 AND !isset($_GET[save])){
	echo EditForm($idAuthor,$id,$_GET['idCmp']);
}

else if(isset($_GET[idCmp]) AND $_GET[idCmp]==0 AND !isset($_GET[save])){
	echo CreateForm($idAuthor,$id,$_GET[type]);
	
	if ($_GET[type]=="ПК") {$id = INSERT_PC_INTO_PROGRAM($idAuthor, $idProg, $number, $idComp);}
	else if ($_GET[type]=="ОПК"){$id = INSERT_GPC_INTO_STANDART($idAuthor, $idStand, $number, $idComp);}
	else {$id = INSERT_UC_INTO_STANDART($idAuthor, $idStand, $number, $idComp);}
}

else if(isset($_GET[save]) AND $_GET[idCmp]==0 ){
	$id = INSERT_UC_INTO_STANDART($idAuthor, $idStand, $number, $idComp);
}

else if(isset($_GET[save]) AND $_GET[idCmp]>0 ){
	if($_GET[type]=="УК"){
		SET_UC_NUMBER($idAuthor, $idStand, $value);
		SET_UC_DESCRIPTION($idAuthor, $idStand, $value);
		echo ListUC($idAuthor, $idStand, $description);
	}else if(($_GET[type]=="ОПК")){
		SET_GPC_NUMBER($idAuthor, $idStand, $value);
		SET_GPC_DESCRIPTION($idAuthor, $idStand, $value);
		echo ListGPC($idAuthor, $idStand, $description);
	}else{
		SET_PC_NUMBER($idAuthor, $idProg, $value);
		SET_PC_DESCRIPTION($idAuthor, $idProg, $value);
		echo ListPC($idAuthor, $idProg, $description);		
	}
}

// работаем с новой компетенцией
else if ($_GET['idCmp']==0 AND !isset($_GET['save'])){	// работаем с новой компетенцией					
	// запрашиваем данные для новой компетенции
	$mysqli = conn();	
	$query = "SELECT * FROM source;";
		$res = $mysqli->query($query);
		$source =<<<EOT
<select name="source">
EOT;
		for ($row_no = 0; $row_no < $res->num_rows; $row_no++) {
			$res->data_seek($row_no);
			$row = $res->fetch_assoc();
			$source = $source."<option value='".$row[idSour]."'>".$row[title]." (".$row[url].")</option>";
		}
		$source = $source."</select><br>";
		if ($row_no==0){
			$source = "Источников компетенций в базе не найдено <br>";
		}
		echo <<<EOT
<h2>Создаем новую компетенцию</h2><br>
<form action="competence.php?idCmp=0&save=1" method="post">
<p>Источник: $source <a href='competence.php?idCmp=0&idSour=0&save=1'>Добавить источник компетенций</a></p>
<p>Компетенция <select name="type"> <option>УК</option><option>ОПК</option><option>ПК</option></select>-<input type="text" name="number" value="Номер компетенции" /></p>
<p>Описание: <br><textarea name="description" cols="100" rows="10">Описание компетенции</textarea></p>
<p><input type="submit" value="Сохранить"/></p>
</form>
EOT;

// работаем с новой компетенцией
	// 	сохраняем параметры новой компетенции
	}else if(!isset($_GET['idSour'])){								 
		$query = <<<EOT
INSERT INTO competence (idSour,type,description,number)
VALUES ('$_POST[source]', '$_POST[type]', '$_POST[description]', '$_POST[number]')
EOT;
		$res = $mysqli->query($query);	// добавляем запись о новой компетенции
		$last_cmp = $mysqli->insert_id;	// идентификатор новой компетенции
//echo $query."<br>";
		
echo <<<EOT
<h2>Изменения были сохранены</h2>
<form action="competence.php" method="post">
<p><input type="submit" value="ОК" /></p>
</form>
EOT;
// работаем с новой компетенцией
	// запрашиваем новый источник компетенций
	}else if($_GET['idSour']==0){ 
		echo <<<EOT
<h2>Создаем новый источник компетенций</h2><br>
<form action="competence.php?idCmp=0&idSour=-1&save=1" method="post">
<p>Описание источника: <input type="text" size="50" name="title" value="Введите описание (например, ФГОС+++ 02.03.02 ФИИТ" /></p>
<p>URL: <input type="text" size="50" name="url" value="Введите гиперссылку на документ" /></p>
<p><input type="submit" value="Сохранить"/></p>
</form>
EOT;

// работаем с новой компетенцией
	// сохраняем новый источник компетенций
	}else if($_GET['idSour']==-1){			 
		$query = <<<EOT
INSERT INTO source (title, url) 
VALUES ('$_POST[title]', '$_POST[url]');
EOT;
		$res = $mysqli->query($query);	// добавляем запись о новом источнике
		$last_sour = $mysqli->insert_id;	// идентификатор нового источника

		$query = "SELECT * FROM source;";
		$res = $mysqli->query($query);
		$source =<<<EOT
<select name="source">
EOT;
		for ($row_no = 0; $row_no < $res->num_rows; $row_no++) {
			$res->data_seek($row_no);
			$row = $res->fetch_assoc();
			if ($row[idSour]==$last_sour){$k = " selected ";}
			else $k = "";
			$source = $source."<option".$k." value='".$row[idSour]."'>".$row[title]."(".$row[url].")</option>";
		}
		$source = $source."</select>";

		echo <<<EOT
<h2>Создаем новую компетенцию</h2><br>
<form action="competence.php?idCmp=0&idSour=1&save=1" method="post">
<p>Источник: $source <input type="submit" value=" Добавить источник " ></p>
<p>Компетенция <select name="type"> <option>УК</option><option>ОПК</option><option>ПК</option></select>-<input type="text" name="number" value="Номер компетенции" /></p>
<p>Описание: <br><textarea name="description" cols="100" rows="10">Описание компетенции</textarea></p>
<p><input type="submit" value="Сохранить"/></p>
</form>
EOT;

	}


else {								// редактируем существующую компетенцию									
	if (!isset($_GET['save'])){				//выводим параметры выбранной энциклопедии				
	echo "<h2>Компетенция </h2>";
	$query = "SELECT * FROM competence WHERE idCompetence='".$_GET['idCmp']."'";
	$res = $mysqli->query($query);
	for ($row_no = 0; $row_no < $res->num_rows; $row_no++) {
		$res->data_seek($row_no);
		$row = $res->fetch_assoc();
echo <<<EOT
<form action="competence.php?idCmp=$_GET[idCmp]&save=1" method="post">
<p>Компетенция: $row[type]-$row[number]</p>
<p>Описание: <br><textarea name="description" cols="100" rows="10">$row[description]</textarea></p>
	<p><input type="submit" value="Обновить информацию о компетенции" /></p>
</form>
EOT;
		}
	}else {										// сохранить изменения
	$query = "UPDATE competence SET description='".$_POST['description']."' WHERE idCompetence='".$_GET['idCmp']."'";
	$res = $mysqli->query($query);
echo <<<EOT
<h2>Изменения были сохранены</h2>
<form action="competence.php" method="post">
<p><input type="submit" value="ОК" /></p>
</form>
EOT;
	}
}


echo $bottom;
?>