<?php

include "html.php";
echo $top;

include "SID-model-operations.php";
$idAuthor = 1;

function ListStandarts($idAuthor){
	$arr = STANDART_COLLECTION($idAuthor);
	$html = "";
	if ($arr != Null) { $html = "<h2>Стандарты:</h2><h3>";}
	foreach ($arr as $row){
		$html = $html.<<<EOT
			<p><a href="standart.php?idStand=$row[idStand]">
				<img width="20" src="images/edit.png" alt="Редактировать атрибуты стандарта" title="Редактировать атрибуты стандарта" />
				</a>&nbsp;$row[description]&nbsp;
				<a href="competence.php?idStand=$row[idStand]&name=$row[description]&type=УК" title="УК">УК</a>&nbsp;
				<a href="competence.php?idStand=$row[idStand]&name=$row[description]&type=ОПК" title="ОПК">ОПК</a>
			</p>
EOT;
	}
	$html = $html."</h3><br><h2><a href='standart.php?idStand=0'>Ввести новый стандарт</a></h2>";
	return $html;
}
function CreateForm($idAuthor){
	$idStand = CREATE_STANDART($idAuthor);
	$html = <<<EOT
	<h2>Добавляем новый стандарт</h2><br>
	<form action="standart.php?idStand=$idStand&save=1" method="post">
	<p>Аннотация: <br><textarea name="description" cols="100" rows="10">Введите аннотацию стандарта</textarea></p>
	<p>URL адрес стандарта: <input type="text" name="url" value="Введите url адрес стандарта" /></p>
	<p>Уровень образования: <select name="level">
								<option value="Бакалавриат">Бакалавриат</option>
								<option value="Специалитет">Специалитет</option>
								<option value="Магистратура">Магистратура</option>
								<option value="Аспирантура">Аспирантура</option>
							</select></p>
	<p>Длительность обучения: <input type="text" name="length" value="Длительность" /></p>
	<h3>Ограничения на стандарт</h3>
	<p>Минимальное количество зачетных единиц, отводимых на освоение учебных дисциплин: <input type="text" name="min_stud" value="Количество ЗЕ" /></p>
	<p>Минимальное количество зачетных единиц, отводимых на практики: <input type="text" name="min_pract" value="Количество ЗЕ" /></p>
	<p>Нижняя граница для зачетных единиц, отводимых на государственную итоговую аттестацию: <input type="text" name="min_crt" value="Количество ЗЕ" /></p>
	<p>Верхняя граница для зачетных единиц, отводимых на государственную итоговую аттестацию: <input type="text" name="max_crt" value="Количество ЗЕ" /></p>
	<p>Общее количество зачетных единиц образовательной программы: <input type="text" name="total" value="Количество ЗЕ" /></p>
	<p><input type="submit" value="Сохранить" /></p>
	</form>
EOT;
return $html;
}
function EditForm($idAuthor, $idStand){

	$idStand = $_GET['idStand'];
	$description = GET_STANDART_DESCRIPTION($idAuthor, $idStand);
	$min_stud = GET_STANDART_MIN_STUD($idAuthor, $idStand);
	$min_pract = GET_STANDART_MIN_PRACT($idAuthor, $idStand);
	$min_crt = GET_STANDART_MIN_CRT($idAuthor, $idStand);
	$max_crt = GET_STANDART_MAX_CRT($idAuthor, $idStand);
	$total = GET_STANDART_TOTAL($idAuthor, $idStand);
	$url = GET_STANDART_URL($idAuthor, $idStand);
	$level = GET_STANDART_LEVEL($idAuthor, $idStand);
	$length = GET_STANDART_LENGTH($idAuthor, $idStand);
	$lev = array("Бакалавриат", "Специалитет", "Магистратура", "Аспирантура");
	$select = "";
	for ($i=0;$i<4;$i++){
		if ($lev[$i]==$level){
			$select = $select."<option selected value=\"$lev[$i]\">$lev[$i]</option>";
		}else{
			$select = $select."<option value=\"$lev[$i]\">$lev[$i]</option>";
		}
	}
	$html = <<<EOT
	<h2>Стандарт</h2><br>
	<form action="standart.php?idStand=$idStand&save=1" method="post">
	<p>Аннотация: <br><textarea name="description" cols="100" rows="10">$description</textarea></p>
	<p>URL адрес стандарта: <input type="text" name="url" value="$url" /></p>
	<p>Уровень образования: <select name="level">$select</select></p>
	<p>Длительность обучения: <input type="text" name="length" value="$length" /></p>
	<h3>Ограничения на стандарт</h3>
	<p>Минимальное количество зачетных единиц, отводимых на освоение учебных дисциплин: <input type="text" name="min_stud" value="$min_stud" /></p>
	<p>Минимальное количество зачетных единиц, отводимых на практики: <input type="text" name="min_pract" value="$min_pract" /></p>
	<p>Нижняя граница для зачетных единиц, отводимых на государственную итоговую аттестацию: <input type="text" name="min_crt" value="$min_crt" /></p>
	<p>Верхняя граница для зачетных единиц, отводимых на государственную итоговую аттестацию: <input type="text" name="max_crt" value="$max_crt" /></p>
	<p>Общее количество зачетных единиц образовательной программы: <input type="text" name="total" value="$total" /></p>
	<p><input type="submit" value="Сохранить" /></p>
	</form>
EOT;
return $html;
}
function PrintOK(){
	$html = <<<EOT
		<h2>Изменения были сохранены</h2>
		<form action="standart.php" method="post">
		<p><input type="submit" value="ОК" /></p>
		</form>
EOT;
	return $html;
}
/* function ListUC($idAuthor, $idStand, $name){
	$mysqli = conn();	
	
	RESET_UC($idAuthor, $idStand);
	$id = FETCH_UC($idAuthor, $idStand);
	if ($id!=0){$html = "<h2>Компетенции:</h2><h3>";}
	else{$html = "<h3>Список компетенций еще пуст</h3><h3>";}
	while ($id != 0){
		$number = GET_UC_NUMBER($idAuthor, $idStand);
		$description = GET_UC_DESCRIPTION($idAuthor, $idStand);
		$html = $html.<<<EOT
						<p><a href='competence.php?idCmp=$id&back="standart.php?idStand=$idStand&name=$name&type=УК"'>
						<img width='20' src='images/edit.png' alt='Редактировать компетенцию' title='Редактировать компетенцию' /></a>
						УК-$number. $description 
						<a href='standart.php?idStand=$idStand&name=$name&type=УК&idUC=$id&akt=-1'>
						<img width='20' src='images/delete.png' alt='Удалить компетенцию' title='Удалить компетенцию' /></a>
						</p>
EOT;
		NEXT_UC($idAuthor, $idStand);
		$id = FETCH_UC($idAuthor, $idStand);
	}
	$arr = COMPETENCE_COLLECTION("УК");
	$html = $html."<p>Добавьте компетенцию из списка</p></h3><form method='post' action='standart.php?idStand=$idStand&name=$name&type=УК&akt=0'><select name='idComp'><option selected disabled>Выберите компетенцию</option>";
	foreach($arr as $row){
		$html = $html."<option value='$row[id]'>УК-$row[number]. $row[description]</option>";
	}
	return $html."</select><p><input type='submit' value='Добавить'></p></form>";
}
function ListGPC($idAuthor, $idStand, $name){
	$mysqli = conn();	
	RESET_UC($idAuthor, $idStand);
	$id = FETCH_GPC($idAuthor, $idStand);
	if ($id!=0){$html = "<h2>Компетенции:</h2><h3>";}
	else{$html = "<h3>Список компетенций еще пуст</h3><h3>";}
	while ($id != 0){
		$number = GET_GPC_NUMBER($idAuthor, $idStand);
		$description = GET_GPC_DESCRIPTION($idAuthor, $idStand);
		$html = $html.<<<EOT
						<p><a href='competence.php?idCmp=$id&back="standart.php?idStand=$idStand&name=$name&type=ОПК"'>
						<img width='20' src='images/edit.png' alt='Редактировать компетенцию' title='Редактировать компетенцию' /></a>
						УК-$number. $description 
						<a href='standart.php?idStand=$idStand&name=$name&type=ОПК&idGPC=$id&akt=-1'>
						<img width='20' src='images/delete.png' alt='Удалить компетенцию' title='Удалить компетенцию' /></a>
						</p>
EOT;
		NEXT_GPC($idAuthor, $idStand);
		$id = FETCH_GPC($idAuthor, $idStand);
	}
	$list = COMPETENCE_COLLECTION("ОПК");
	$html = $html."<p>Добавьте компетенцию из списка</p></h3><form method='post' action='standart.php?idStand=$idStand&name=$name&type=ОПК&akt=0'><select name='idComp'><option selected disabled>Выберите компетенцию</option>";
	foreach($list as $row){
		$html = $html."<option value='$row[description]'>ОПК-$row[number]. $row[description]</option>";
	}
	$html = $html."</select><p><input type='submit' value='Добавить'></p></form>";
	return $html;
}
 */

if (!isset($_GET['idStand'])) { 		// Стандарт не выбран - выводим список стандартов						
	echo ListStandarts($idAuthor);					
} 

else if (($_GET['idStand']==0)AND(!isset($_GET['save']))/* AND(!isset($_GET['type'])) */){	// обрабатываем запрос на создание нового стандарта			
	echo CreateForm($idAuthor);		
}

// обрабатываем запросы на редактирование существующего стандарта
else if (($_GET['idStand'] > 0)AND(!isset($_GET['save']))/* AND(!isset($_GET['type'])) */){	//выводим параметры выбранного для редактирования стандарта
	echo EditForm($idAuthor, $idStand);	
}

else if (isset($_GET['save'])){	// сохраняем изменения
	SET_STANDART_DESCRIPTION($idAuthor, $_GET[idStand], $_POST[description]);
	SET_STANDART_MIN_STUD($idAuthor, $_GET[idStand], $_POST[min_stud]);
	SET_STANDART_MIN_PRACT($idAuthor, $_GET[idStand], $_POST[min_pract]);
	SET_STANDART_MIN_CRT($idAuthor, $_GET[idStand], $_POST[min_crt]);
	SET_STANDART_MAX_CRT($idAuthor, $_GET[idStand], $_POST[max_crt]);
	SET_STANDART_TOTAL($idAuthor, $_GET[idStand], $_POST[total]);
	SET_STANDART_URL($idAuthor, $_GET[idStand], $_POST[url]);
	SET_STANDART_LEVEL($idAuthor, $_GET[idStand], $_POST[level]);
	SET_STANDART_LENGTH($idAuthor, $_GET[idStand], $_POST[length]);
	echo PrintOK();
	
}

/* else if (isset($_GET['type'])AND(!isset($_GET['akt']))){ //выводим списки компетенций стандарта
	$idStand = $_GET[idStand];
	$name = $_GET[name];
	if ($_GET['type']=="УК"){
		echo ListUC($idAuthor, $idStand, $name);
	}else if ($_GET['type']=="ОПК"){
		echo ListGPC($idAuthor, $idStand, $name);
	}
}

else if(isset($_GET['akt'])){		// выполняем действия с компетенциями -1 - удалить, 1 - создать, 0 - добавить
	if($_GET['akt']==-1){			// -1 - удалить
		$idStand = $_GET[idStand];
		$name = $_GET[name];
		if ($_GET['type']==УК){
			RESET_UC($idAuthor, $idStand);
			$id = FETCH_UC($idAuthor, $idStand);
			while (($id != 0)AND($id != $_GET['idUC'])){
				NEXT_UC($idAuthor, $idStand);
				$id = FETCH_UC($idAuthor, $idStand);
			}
			if ($id != NULL){
				DELETE_UC_FROM_STANDART($idAuthor, $idStand);
			}
			ListUC($idAuthor, $idStand, $name);
		}else if ($_GET['type']=="ОПК"){		
		
		}
	}

	else if ($_GET['akt']==0){		// 0 - добавить
		$idStand = $_GET[idStand];
		$name = $_GET[name];
		$idComp = $_POST[idComp];
		if ($_GET[type]=="УК"){
			RESET_UC($idAuthor, $idStand);
			$id = FETCH_UC($idAuthor, $idStand);
			while ($id != 0){
				NEXT_UC($idAuthor, $idStand);
				$id = FETCH_UC($idAuthor, $idStand);
				$number = GET_UC_NUMBER($idAuthor, $idStand)+1;
			}
			INSERT_UC_INTO_STANDART($idAuthor, $idStand, $number, $idComp);
			ListUC($idAuthor, $idStand, $name);
		}else if ($_GET['type']=="ОПК"){
			RESET_GPC($idAuthor, $idStand);
			$id = FETCH_GPC($idAuthor, $idStand);
			while ($id != 0){
				NEXT_GPC($idAuthor, $idStand);
				$id = FETCH_GPC($idAuthor, $idStand);
				$number = GET_GPC_NUMBER($idAuthor, $idStand)+1;
			}
			INSERT_GPC_INTO_STANDART($idAuthor, $idStand, $number, $idComp);
			ListGPC($idAuthor, $idStand, $name);
		}
		//виснет и номера неверные выдает!!!
	}	

	else if($_GET['akt']==1){		// 1 - создать
	} */
	
	
/* 	}else if(($_GET['akt']==0)){	// выбираем компетенцию из списка
		$list = COMPETENCE_COLLECTIONS("УК");
		$html = <<<EOT
		  <form method="post" action="standart.php?idStand=$row[idStand]&name=$row[description]&type=УК&akt=1">
		   <p>Выберите компетенцию из списка</p>
		   <p><input type="checkbox" name="" value="a1" checked>Windows 95/98<Br>
		   <input type="checkbox" name="option2" value="a2">Windows 2000<Br>
		   <input type="checkbox" name="option3" value="a3">System X<Br> 
		   <input type="checkbox" name="option4" value="a4">Linux<Br> 
		   <input type="checkbox" name="option5" value="a5">X3-DOS</p>
		   <p><input type="submit" value="Отправить"></p>
		  </form>
EOT;
		foreach($list as $row){
			$html =$html.<<<EOT
			<p>УК-$row[number]. $row[description]</p>
			<a href='competence.php?idCmp=$id&back="standart.php?idStand=$row[idStand]&name=$row[description]&type=УК"'>
							<img width='20' src='images/edit.png' alt='Редактировать компетенцию' title='Редактировать компетенцию' /></a>
EOT;
		} */

	

echo $html;
echo $bottom;
?>