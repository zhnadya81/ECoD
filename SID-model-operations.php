 <?php

function conn(){
//	$mysqli = new mysqli("localhost", "root", "", "unicst2");
	$mysqli = new mysqli("localhost", "root", "", "ecod_db");
	if ($mysqli->connect_errno) {
		echo "Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}
	return $mysqli;
}

// ----  операции над курсорами  ---- //

function CreateCursor($idAuthor, $type, $idCur){
//$idAuthor, $type - тип курсора, $id - с чем связан курсор

	$handle = fopen("$idAuthor\\$type.txt", "a");	
	fwrite($handle, "$idCur:0:0:0\n");
	fclose($handle);   // идентификатор в БД : указатель на текущий : кол-во элементов : список элементов через пробел
	return 0;
}

function InsertCursor($idAuthor, $type, $idCur, $idObj){
// $idAuthor, $type - тип курсора, $idCur - с чем связан курсор, $idObj	- что добавляем в коллекцию
	$handle = fopen("$idAuthor\\$type.txt", "r");	// обновляем курсор
	$tmp = "";
	$id = 0;
	while (($buffer = fgets($handle))!= false) {
		list($id, $cur, $qty, $numbers) = explode(":", $buffer);
		if($id == $idCur){
			$arr = explode(" ", $numbers); 	// коллекция индексов
			$n = count($arr);	//$n--;		// отбрасываем признак конца
			$numbers = '';
			for ($i=0;$i<$cur;$i++){
				$numbers = $numbers.$arr[$i]." ";
			}
			$numbers = $numbers.$idObj." ";
			$qty++;
			for ($i=$cur;$i<$n-1;$i++){
				$numbers = $numbers.$arr[$i]." ";
			}
			$buffer = "$id:$cur:$qty:$numbers".$arr[$n-1];
		} 
		$tmp = $tmp.$buffer;
	}
	fclose($handle);

	$handle = fopen("$idAuthor\\$type.txt", "w");	
	fwrite($handle, $tmp);	
	fclose($handle);
	return 0;
}

function DeleteCursor($idAuthor, $type, $idCur){
// $idAuthor, $type - тип курсора, $idCur - с чем связан курсор
	$handle = fopen("$idAuthor\\$type.txt", "r");	// обновляем курсор
	$tmp = "";
	$id = 0;
	$idObj = 0;
	while (($buffer = fgets($handle))!= false) {
		list($id, $cur, $qty, $numbers) = explode(":", $buffer);
		if(($id == $idCur)and($qty > 0)and($cur < $qty)){
			$arr = explode(" ", $numbers); 	// коллекция индексов
			$n = count($arr);	//$n--;		// отбрасываем признак конца
			$numbers = '';
			for ($i=0;$i<$cur;$i++){
				$numbers = $numbers.$arr[$i]." ";
			}
			$qty--; $cur;
			for ($i=$cur+1;$i<$n-1;$i++){
				$numbers = $numbers.$arr[$i]." ";
			}
			$buffer = "$id:$cur:$qty:$numbers".$arr[$n-1];
			$idObj=$id;
		} 
		$tmp = $tmp.$buffer;
	}
	fclose($handle);

	$handle = fopen("$idAuthor\\$type.txt", "w");	
	fwrite($handle, $tmp);	
	fclose($handle);
	
	return $idObj;	//идентификатор удаленного объекта
}

function FetchCursor($idAuthor, $type, $idCur){
// $idAuthor, $type - тип курсора, $idCur - с чем связан курсор
	$handle = fopen("$idAuthor\\$type.txt", "r");	// обновляем курсор
	$id = 0;
	$idObj = 0;
	while (($buffer = fgets($handle))!= false) {
		list($id, $cur, $qty, $numbers) = explode(":", $buffer);
		if($id == $idCur){
			$arr = explode(" ", $numbers); 	// коллекция индексов
			$idObj = $arr[$cur];
			break;
		} 
	}
	fclose($handle);

	return $idObj;	//идентификатор найденного объекта
}

function ResetCursor($idAuthor, $type, $idCur){
// $idAuthor, $type - тип курсора, $idCur - с чем связан курсор
	$handle = fopen("$idAuthor\\$type.txt", "r");	// обновляем курсор
	$id = 0;
	$tmp = "";
	while (($buffer = fgets($handle))!= false) {
		list($id, $cur, $qty, $numbers) = explode(":", $buffer);
		if($id == $idCur){
			$buffer = "$id:0:$qty:$numbers";
		} 
		$tmp = $tmp.$buffer;
	}
	fclose($handle);

	$handle = fopen("$idAuthor\\$type.txt", "w");	
	fwrite($handle, $tmp);	
	fclose($handle);

	return 0;	
}

function NextCursor($idAuthor, $type, $idCur){
// $idAuthor, $type - тип курсора, $idCur - с чем связан курсор
	$handle = fopen("$idAuthor\\$type.txt", "r");	// обновляем курсор
	$id = 0;
	$tmp = "";
	while (($buffer = fgets($handle))!= false) {
		list($id, $cur, $qty, $numbers) = explode(":", $buffer);
		if($id == $idCur){
			if($cur < $qty){$cur++;}
			$buffer = "$id:$cur:$qty:$numbers";
		} 
		$tmp = $tmp.$buffer;
	}
	fclose($handle);

	$handle = fopen("$idAuthor\\$type.txt", "w");	
	fwrite($handle, $tmp);	
	fclose($handle);

	return 0;	
}

function PriorCursor($idAuthor, $type, $idCur){
// $idAuthor, $type - тип курсора, $idCur - с чем связан курсор
	$handle = fopen("$idAuthor\\$type.txt", "r");	// обновляем курсор
	$id = 0;
	$tmp = "";
	while (($buffer = fgets($handle))!= false) {
		list($id, $cur, $qty, $numbers) = explode(":", $buffer);
		if($id == $idCur){
			if($cur > 0){$cur--;}
			$buffer = "$id:$cur:$qty:$numbers";
		} 
		$tmp = $tmp.$buffer;
	}
	fclose($handle);

	$handle = fopen("$idAuthor\\$type.txt", "w");	
	fwrite($handle, $tmp);	
	fclose($handle);

	return 0;	
}


// ----  операции над энциклопедиями  ---- //

function CREATE_ENCYCLOPEDIA($idAuthor){
	$mysqli = conn();	
	
	$d = date("Y-m-d");				// текущая дата

	$query = "INSERT INTO encyclopedia (Author, created) VALUES ($idAuthor, '$d')";

	$res = $mysqli->query($query);	// добавляем запись о новой энциклопедии
	$idEnc = $mysqli->insert_id;	// идентификатор новой энциклопедии

	CreateCursor($idAuthor,"Enc",$idEnc);
		
	return $idEnc;
}

function SET_ENCYCLOPEDIA_TITLE($idAuthor, $idEnc, $value){
	$mysqli = conn();	

	$query = "UPDATE encyclopedia SET `title`='$value' WHERE idEnc = $idEnc";
	
	return $mysqli->query($query);
}

function SET_ENCYCLOPEDIA_ANNOTATION($idAuthor, $idEnc, $value){
	$mysqli = conn();	

	$query = "UPDATE encyclopedia SET annotation='$value' WHERE idEnc = $idEnc";
		
	return $mysqli->query($query);
}

function GET_ENCYCLOPEDIA_TITLE($idAuthor, $idEnc){
	$mysqli = conn();	
	
	$query = "SELECT `title` FROM encyclopedia WHERE idEnc = $idEnc";

	$res = $mysqli->query($query);	
	$res->data_seek(0);
	$row = $res->fetch_assoc();
		
	return $row[title];
}

function GET_ENCYCLOPEDIA_ANNOTATION($idAuthor, $idEnc){
	$mysqli = conn();	

	$query = "SELECT `annotation` FROM encyclopedia WHERE idEnc = $idEnc";

	$res = $mysqli->query($query);	
	$res->data_seek(0);
	$row = $res->fetch_assoc();
		
	return $row[annotation];
}

function ENCYCLOPEDIA_COLLECTION($idAuthor) {
	// $idMod - идентификатор модуля
	$mysqli = conn();	

	$query = "SELECT * FROM encyclopedia WHERE Author=$idAuthor";
	$res = $mysqli->query($query);	
	$arr = array();
	for ($row_no = 0; $row_no < $res->num_rows; $row_no++) {
		$res->data_seek($row_no);
		$row = $res->fetch_assoc();
		$arr[$row_no] = array(idEnc => $row[idEnc],title => $row[title], annotation => $row[annotation],created => $row[created]);
	}
	return $arr;
}

function INSERT_MODULE($idAuthor, $idEnc){
	$mysqli = conn();	
	
	$d = date("Y-m-d");				// текущая дата

	$query = "INSERT INTO module (idEnc, created) VALUES ($idEnc,'$d')";

	$res = $mysqli->query($query);	// добавляем запись о новом модуле
	$idMod = $mysqli->insert_id;	// идентификатор нового модуля

	InsertCursor($idAuthor, "Enc", $idEnc, $idMod);
	for ($i=1; $i<8; $i++){
		CreateCursor($idAuthor, "Mod".$i, $idMod);
	}
	
	// вставляем компоненты (пустые коллекции)
	for ($i=1; $i<8; $i++){
		$query = "INSERT INTO scheme(idMod, idType, modified, credit) VALUES ($idMod,$i,'$d', 0)";
		$res = $mysqli->query($query);	// добавляем запись о новой пустой компоненте
	}
	
	return $idMod;
}

function SET_MODULE_ANNOTATION($idAuthor, $idMod, $value){
	$mysqli = conn();	
	
	$query = "UPDATE `module` SET `annotation`='$value' WHERE idMod = $idMod";
	
	return $mysqli->query($query);
}

function SET_MODULE_TITLE($idAuthor, $idMod, $value){
	$mysqli = conn();	

	$query = "UPDATE `module` SET `title`='$value' WHERE idMod = $idMod";
	
	return $mysqli->query($query);
}

function GET_MODULE_ANNOTATION($idAuthor, $idMod){
	$mysqli = conn();	

	$query = "SELECT `annotation` FROM module WHERE idMod = $idMod";
		
	$res = $mysqli->query($query);	
	$res->data_seek(0);
	$row = $res->fetch_assoc();
		
	return $row[annotation];
}

function GET_MODULE_TITLE($idAuthor, $idMod){
	$mysqli = conn();	

	$query = "SELECT `title` FROM module WHERE idMod = $idMod";
	
	$res = $mysqli->query($query);	
	$res->data_seek(0);
	$row = $res->fetch_assoc();
		
	return $row[title];
}

function DELETE_MODULE($idAuthor, $idEnc){
	$mysqli = conn();	
	
	$idMod = DeleteCursor($idAuthor, "Enc", $idEnc);
	
	if($idMod != 0){
//удалим модуль из таблицы компонентов
		$query = "DELETE FROM `component` WHERE idSchem IN (SELECT idSchem FROM `scheme` WHERE idMod=$idMod)";
		$res = $mysqli->query($query);
// удалим модуль из таблицы схема
		$query = "DELETE FROM `scheme` WHERE idMod=$idMod";
		$res = $mysqli->query($query);
// удалим модуль из таблицы модулей
		$query = "DELETE FROM module WHERE idMod=$idMod";
		$res = $mysqli->query($query);	
	}

	return 0;
}

function FETCH_MODULE($idAuthor, $idEnc){
// $idEnc - идентификатор энциклопедии

	$idMod = FetchCursor($idAuthor, "Enc", $idEnc);
	
	return $idMod;
}

function COPY_MODULE($idAuthor, $idMod1, $idMod2){	
// $idMod1 - идентификатор модуля-донора
// $idMod2 - идентификатор модуля-реципиента	

	$mysqli = conn();	
	$d = date("Y-m-d");				// текущая дата

	$query = "SELECT * FROM module WHERE idMod=$idMod1";
	$res = $mysqli->query($query);	
	$res->data_seek(0);
	$row = $res->fetch_assoc();
	$title = $row[title];
	$annotation = $row[annotation];
	
	$query = "UPDATE module SET (title=$title, annotation=$annotation, created='$d') WHERE idMod=$idMod2";
	$res = $mysqli->query($query);	
	
//скопировать компоненты!!!
	//удалим модуль из таблицы компонентов
	$query = "DELETE FROM `component` WHERE idSchem IN (SELECT idSchem FROM `scheme` WHERE idMod=$idMod2)";
	$res = $mysqli->query($query);

	$query = "SELECT idSchem, type FROM scheme WHERE idMod=$idMod1";
	$res = $mysqli->query($query);	
	for ($row_no = 0; $row_no < $res->num_rows; $row_no++) {
		$res->data_seek($row_no);
		$row = $res->fetch_assoc();
		$type = $row[type];
		$idSchem = $row[idSchem];
		$query1 = "SELECT * FROM component WHERE idSchem=$idSchem";
		$res1 = $mysqli->query($query1);	
		for ($row_no1 = 0; $row_no1 < $res1->num_rows; $row_no1++) {
			$res1->data_seek($row_no1);
			$row1 = $res1->fetch_assoc();
			$number = $row1[number];
			$asset = $row1[asset];
			$javascript = $row1[javascript];
			$query2 = "SELECT idSchem FROM scheme WHERE idMod=$idMod2 AND type=$type";
			$res2 = $mysqli->query($query2);
			$res2 -> data_seek(0);
			$row2 = $res2->fetch_assoc();
			$id = $row2[idSchem];
			//скопируем компоненты модуля 1 в модуль 2
			$query2 = "INSERT INTO component(idSchem, number, created, asset, javascript) VALUES ($id, $number, '$d',$asset, $javascript)";
			$res2 = $mysqli->query($query2);
		}
	}
return 0;	
}

function RESET_MODULE($idAuthor, $idEnc){
	// $idEnc - идентификатор энциклопедии

	return ResetCursor($idAuthor, "Enc", $idEnc);
}

function NEXT_MODULE($idAuthor, $idEnc){
	// $idEnc - идентификатор энциклопедии

	NextCursor($idAuthor, "Enc", $idEnc);
	return 0;
}

function PRIOR_MODULE($idAuthor, $idEnc) {
	// $idEnc - идентификатор энциклопедии

	PriorCursor($idAuthor, "Enc", $idEnc);
	return 0;
}

function COMPONENT_COLLECTION($idAuthor, $idMod, $type) {
	// $idMod - идентификатор модуля
	$mysqli = conn();	

	$query = "SELECT * FROM `component` WHERE idSchem IN (SELECT idSchem FROM `scheme` WHERE idMod=$idMod AND idType=$type) ORDER BY `number`";
	$res = $mysqli->query($query);	
	$arr = array();
	for ($row_no = 0; $row_no < $res->num_rows; $row_no++) {
		$res->data_seek($row_no);
		$row = $res->fetch_assoc();
		$arr[$row_no] = array($row[idComp], $row[asset],$row[javascript], $row[number]);
	}
	return $arr;
}

function CONCEPT_COLLECTION($idAuthor, $idMod) {
	// $idMod - идентификатор модуля

	return COMPONENT_COLLECTION($idAuthor, $idMod,1);
}

function OPEN_QUESTION_COLLECTION($idAuthor, $idMod) {
	// $idMod - идентификатор модуля

	return COMPONENT_COLLECTION($idAuthor, $idMod,2);
}

function EXAMPLE_COLLECTION($idAuthor, $idMod) {
	// $idMod - идентификатор модуля

	return COMPONENT_COLLECTION($idAuthor, $idMod,3);
}

function EXERCISE_COLLECTION($idAuthor, $idMod) {
	// $idMod - идентификатор модуля

	return COMPONENT_COLLECTION($idAuthor, $idMod,4);
}

function CLOSE_QUESTION_COLLECTION($idAuthor, $idMod) {
	// $idMod - идентификатор модуля

	return COMPONENT_COLLECTION($idAuthor, $idMod,5);
}

function PROBLEM_COLLECTION($idAuthor, $idMod) {
	// $idMod - идентификатор модуля

	return COMPONENT_COLLECTION($idAuthor, $idMod,6);
}

function BIBITEM_COLLECTION($idAuthor, $idMod) {
	// $idMod - идентификатор модуля

	return COMPONENT_COLLECTION($idAuthor, $idMod,7);
} 

function COMPONENT_CREDIT($idAuthor, $idMod,$type) {
	// $idMod - идентификатор модуля
	$mysqli = conn();	

	$query = "SELECT * FROM `scheme` WHERE (idMod=$idMod AND idType=$type)";
	$res = $mysqli->query($query);	
	$res->data_seek(0);
	$row = $res->fetch_assoc();
	return $row[credit];
}

function CONCEPT_CREDIT($idAuthor, $idMod) {
	// $idMod - идентификатор модуля

	return COMPONENT_CREDIT($idAuthor, $idMod,1);
}

function OPEN_QUESTION_CREDIT($idAuthor, $idMod) {
	// $idMod - идентификатор модуля

	return COMPONENT_CREDIT($idAuthor, $idMod,2);
}

function EXAMPLE_CREDIT($idAuthor, $idMod) {
	// $idMod - идентификатор модуля

	return COMPONENT_CREDIT($idAuthor, $idMod,3);
}

function EXERCISE_CREDIT($idAuthor, $idMod) {
	// $idMod - идентификатор модуля

	return COMPONENT_CREDIT($idAuthor, $idMod,4);
}

function CLOSE_QUESTION_CREDIT($idAuthor, $idMod) {
	// $idMod - идентификатор модуля

	return COMPONENT_CREDIT($idAuthor, $idMod,5);
}

function PROBLEM_CREDIT($idAuthor, $idMod) {
	// $idMod - идентификатор модуля

	return COMPONENT_CREDIT($idAuthor, $idMod,6);
}

function BIBITEM_CREDIT($idAuthor, $idMod) {
	// $idMod - идентификатор модуля

	return COMPONENT_CREDIT($idAuthor, $idMod,7);
} 

function ADD_COMPONENT_COLLECTION($idAuthor, $idMod, $type, $asset, $javascript) {
	// $idMod - идентификатор модуля
	$mysqli = conn();	

	$d = date("Y-m-d");
	$query = "SELECT idSchem FROM scheme WHERE (idMod=$idMod) AND (idType=$type)";
	$res = $mysqli->query($query);
	$res->data_seek(0);
	$row = $res->fetch_assoc();
	$idSchem = $row['idSchem'];
	$query = "INSERT INTO component (idSchem, asset, javascript, created) VALUES ($idSchem, '$asset', '$javascript','$d')";
	$res = $mysqli->query($query);
	$id = $mysqli->insert_id;
	InsertCursor($idAuthor, "Mod$t", $idMod, $id);

	return $id;
}

function SET_COMPONENT($idAuthor, $idComp, $asset, $javascript) {
	// $idMod - идентификатор модуля
	$mysqli = conn();	

	$query = "UPDATE component SET asset='$asset', javascript='$javascript' WHERE idComp='$idComp'";

	return $mysqli->query($query);
}

function ADD_CONCEPT_COLLECTION($idAuthor, $idMod, $asset, $javascript) {
	// $idMod - идентификатор модуля

	return ADD_COMPONENT_COLLECTION($idAuthor, $idMod,1, $asset, $javascript);
}

function ADD_OPEN_QUESTION_COLLECTION($idAuthor, $idMod, $asset, $javascript) {
	// $idMod - идентификатор модуля

	return ADD_COMPONENT_COLLECTION($idAuthor, $idMod,2, $asset, $javascript);
}

function ADD_EXAMPLE_COLLECTION($idAuthor, $idMod, $asset, $javascript) {
	// $idMod - идентификатор модуля

	return ADD_COMPONENT_COLLECTION($idAuthor, $idMod,3, $asset, $javascript);
}

function ADD_EXERCISE_COLLECTION($idAuthor, $idMod, $asset, $javascript) {
	// $idMod - идентификатор модуля

	return ADD_COMPONENT_COLLECTION($idAuthor, $idMod,4, $asset, $javascript);
}

function ADD_CLOSE_QUESTION_COLLECTION($idAuthor, $idMod, $asset, $javascript) {
	// $idMod - идентификатор модуля

	return ADD_COMPONENT_COLLECTION($idAuthor, $idMod,5, $asset, $javascript);
}

function ADD_PROBLEM_COLLECTION($idAuthor, $idMod, $asset, $javascript) {
	// $idMod - идентификатор модуля

	return ADD_COMPONENT_COLLECTION($idAuthor, $idMod,6, $asset, $javascript);
}

function ADD_BIBITEM_COLLECTION($idAuthor, $idMod, $asset, $javascript) {
	// $idMod - идентификатор модуля

	return ADD_COMPONENT_COLLECTION($idAuthor, $idMod,7, $asset, $javascript);
} 

function SET_COMPONENT_CREDIT($idAuthor, $idMod, $type, $credit){
	$mysqli = conn();	

	$query = "UPDATE `scheme` SET `credit` = $credit WHERE (idMod=$idMod) AND (idType=$type)";
	
	return $mysqli->query($query);
}

function SET_CONCEPT_CREDIT($idAuthor, $idMod, $credit){
		return SET_COMPONENT_CREDIT($idAuthor, $idMod, 1, $credit);
}

function SET_OPEN_QUESTION_CREDIT($idAuthor, $idMod, $credit){
		return SET_COMPONENT_CREDIT($idAuthor, $idMod, 2, $credit);
}

function SET_EXAMPLE_CREDIT($idAuthor, $idMod, $credit){
		return SET_COMPONENT_CREDIT($idAuthor, $idMod, 3, $credit);
}

function SET_EXERCISE_CREDIT($idAuthor, $idMod, $credit){
		return SET_COMPONENT_CREDIT($idAuthor, $idMod, 4, $credit);
}

function SET_CLOSE_QUESTION_CREDIT($idAuthor, $idMod, $credit){
		return SET_COMPONENT_CREDIT($idAuthor, $idMod, 5, $credit);
}

function SET_PROBLEM_CREDIT($idAuthor, $idMod, $credit){
		return SET_COMPONENT_CREDIT($idAuthor, $idMod, 6, $credit);
}

function SET_BIBITEM_CREDIT($idAuthor, $idMod, $credit){
		return SET_COMPONENT_CREDIT($idAuthor, $idMod, 7, $credit);
}

// ----  операции над стандартами  ---- //

function CREATE_STANDART($idAuthor){

	$mysqli = conn();	
	$txt = 'Введите данные';
	$query = "INSERT INTO `standart`(`description`, `minstud`, `minpract`, `mincrt`, `maxcrt`, `total`, `url`, `level`, `length`, `Author`) VALUES ('$txt', '0', '0', '0', '0', '0', '$txt', '$txt', '0', '$idAuthor')";
	$res = $mysqli->query($query);	// добавляем запись о новом стандарте
	$id = $mysqli->insert_id;	// идентификатор нового стандарта

	CreateCursor($idAuthor,"UC",$id);
	CreateCursor($idAuthor,"GPC",$id);
	
	return $id;
}

function SET_STANDART_ATTR($idAuthor, $idStand, $attr, $value){
	$mysqli = conn();	

	$query = "UPDATE `standart` SET `$attr` = '$value' WHERE idStand = $idStand";
	
	return $mysqli->query($query);
}

function SET_STANDART_DESCRIPTION($idAuthor, $idStand, $value){
	return SET_STANDART_ATTR($idAuthor, $idStand, "description", $value);
}

function SET_STANDART_MIN_STUD($idAuthor, $idStand, $value){
	return SET_STANDART_ATTR($idAuthor, $idStand, "minstud", $value);
}

function SET_STANDART_MIN_PRACT($idAuthor, $idStand, $value){
	return SET_STANDART_ATTR($idAuthor, $idStand, "minpract", $value);
}

function SET_STANDART_MIN_CRT($idAuthor, $idStand, $value){
	return SET_STANDART_ATTR($idAuthor, $idStand, "mincrt", $value);
}

function SET_STANDART_MAX_CRT($idAuthor, $idStand, $value){
	return SET_STANDART_ATTR($idAuthor, $idStand, "maxcrt", $value);
}

function SET_STANDART_TOTAL($idAuthor, $idStand, $value){
	return SET_STANDART_ATTR($idAuthor, $idStand, "total", $value);
}

function SET_STANDART_URL($idAuthor, $idStand, $value){
	return SET_STANDART_ATTR($idAuthor, $idStand, "url", $value);
}

function SET_STANDART_LEVEL($idAuthor, $idStand, $value){
	return SET_STANDART_ATTR($idAuthor, $idStand, "level", $value);
}

function SET_STANDART_LENGTH($idAuthor, $idStand, $value){
	return SET_STANDART_ATTR($idAuthor, $idStand, "length", $value);
}

function GET_STANDART_ATTR($idAuthor, $idStand, $attr){
	$mysqli = conn();	

	$query = "SELECT $attr FROM standart WHERE idStand = $idStand";
	$res = $mysqli->query($query);	
	$res->data_seek(0);
	$row = $res->fetch_assoc();

	return $row[$attr];
}

function GET_STANDART_DESCRIPTION($idAuthor, $idStand){
	return GET_STANDART_ATTR($idAuthor, $idStand, "description");
}

function GET_STANDART_MIN_STUD($idAuthor, $idStand){
	return GET_STANDART_ATTR($idAuthor, $idStand, "minstud");
}

function GET_STANDART_MIN_PRACT($idAuthor, $idStand){
	return GET_STANDART_ATTR($idAuthor, $idStand, "minpract");
}

function GET_STANDART_MIN_CRT($idAuthor, $idStand){
	return GET_STANDART_ATTR($idAuthor, $idStand, "mincrt");
}

function GET_STANDART_MAX_CRT($idAuthor, $idStand){
	return GET_STANDART_ATTR($idAuthor, $idStand, "maxcrt");
}

function GET_STANDART_TOTAL($idAuthor, $idStand){
	return GET_STANDART_ATTR($idAuthor, $idStand, "total");
}

function GET_STANDART_URL($idAuthor, $idStand){
	return GET_STANDART_ATTR($idAuthor, $idStand, "url");
}

function GET_STANDART_LEVEL($idAuthor, $idStand){
	return GET_STANDART_ATTR($idAuthor, $idStand, "level");
}

function GET_STANDART_LENGTH($idAuthor, $idStand){
	return GET_STANDART_ATTR($idAuthor, $idStand, "length");
}

function STANDART_COLLECTION($idAuthor){
	$mysqli = conn();	

	$query = "SELECT * FROM `standart` WHERE `Author` = $idAuthor";
	$res = $mysqli->query($query);	
	$arr = array();
	for ($row_no = 0; $row_no < $res->num_rows; $row_no++) {
		$res->data_seek($row_no);
		$row = $res->fetch_assoc();
		$arr[$row_no] = array(idStand => $row[idStand], description => $row[description], minstud => $row[minstud], minpract => $row[minpract], mincrt => $row[mincrt], maxcrt => $row[maxcrt], total => $row[total], url => $row[url], level => $row[level], length => $row[length] );
	}
	return $arr;
}

// ---- операции над компетенциями ---- //

/* function COMPETENCE_COLLECTION($type){
	$mysqli = conn();	

	$query = "SELECT * FROM `competence` WHERE `type` = '$type'";
	$res = $mysqli->query($query);	
	$arr = array();
	for ($row_no = 0; $row_no < $res->num_rows; $row_no++) {
		$res->data_seek($row_no);
		$row = $res->fetch_assoc();
		$arr[$row_no] = array(id => $row[idCompetence], source => $row[source], description => $row[description], number => $row[number]);
	}
	return $arr;
} */

function COMPETENCE_COLLECTION($idAuthor, $id, $type){
	$mysqli = conn();	
	
	if ($type=="PC"){$atr = "idProg";}
	else {$atr = "idStand";}

	$query = "SELECT * FROM `$type` INNER JOIN `competence` WHERE `$atr` = '$id' AND `$type`.`idCompetence` = `competence`.`idCompetence`";
	$res = $mysqli->query($query);	
	$arr = array();
	$atr1 = "id".$type;
	for ($row_no = 0; $row_no < $res->num_rows; $row_no++) {
		$res->data_seek($row_no);
		$row = $res->fetch_assoc();
		$id = $row[$atr1];
		$arr[$row_no] = array(id => $id, idCompetence => $row[idCompetence], number => $row[number], description => $row[description]);
	}
	return $arr;
}

function UC_COLLECTION($idAuthor, $idStand){
	return COMPETENCE_COLLECTION($idAuthor, $idStand, "UC");
}

function GPC_COLLECTION($idAuthor, $idStand){
	return COMPETENCE_COLLECTION($idAuthor, $idStand, "GPC");
}

function PC_COLLECTION($idAuthor, $idProg){
	return COMPETENCE_COLLECTION($idAuthor, $idProg, "PC");
}

function RESET_UC($idAuthor, $idStand){
	// $idEnc - идентификатор энциклопедии

	return ResetCursor($idAuthor, "UC", $idStand);
}

function RESET_GPC($idAuthor, $idStand){
	// $idEnc - идентификатор энциклопедии

	return ResetCursor($idAuthor, "GPC", $idStand);
}

function RESET_PC($idAuthor, $idProg){
	// $idEnc - идентификатор энциклопедии

	return ResetCursor($idAuthor, "PC", $idProg);
}

function NEXT_UC($idAuthor, $idStand){
	NextCursor($idAuthor, "UC", $idStand);
	return 0;
}

function NEXT_GPC($idAuthor, $idStand){
	NextCursor($idAuthor, "GPC", $idStand);
	return 0;
}

function NEXT_PC($idAuthor, $idProg){
	NextCursor($idAuthor, "PC", $idProg);
	return 0;
}

function PRIOR_UC($idAuthor, $idStand) {
	PriorCursor($idAuthor, "UC", $idStand);
	return 0;
}

function PRIOR_GPC($idAuthor, $idStand) {
	PriorCursor($idAuthor, "GPC", $idStand);
	return 0;
}

function PRIOR_PC($idAuthor, $idProg) {
	PriorCursor($idAuthor, "PC", $idProg);
	return 0;
}

function FETCH_UC($idAuthor, $idStand){
	return FetchCursor($idAuthor, "UC", $idStand);
}

function FETCH_GPC($idAuthor, $idStand){
	return FetchCursor($idAuthor, "GPC", $idStand);
}

function FETCH_PC($idAuthor, $idProg){
	return FetchCursor($idAuthor, "PC", $idProg);
}

function INSERT_UC_INTO_STANDART($idAuthor, $idStand, $number, $idComp){
	$mysqli = conn();	
	
/* 	$query = "SELECT * FROM `competence` WHERE `type`='УК' AND `description`='$description'";
	$res = $mysqli->query($query);	
	$res->data_seek(0);
	$row = $res->fetch_assoc();
	$idComp = $row[idCompetence]; */
	
	if ($idComp != 0){
		$query = "INSERT INTO `UC` (`idStand`, `idCompetence`, `number`) VALUES ($idStand, $idComp, $number)";

		$res = $mysqli->query($query);	// добавляем запись о новой универсальной компетенции
		$idUC = $mysqli->insert_id;	// идентификатор нового модуля

		InsertCursor($idAuthor, "UC", $idStand, $idUC);
	}
	return $idUC;
}

function INSERT_GPC_INTO_STANDART($idAuthor, $idStand, $number, $idComp){
	$mysqli = conn();	
	
/* 	$query = "SELECT * FROM competence WHERE type=`ОПК` AND description=$description";
	$res = $mysqli->query($query);	
	$res->data_seek(0);
	$row = $res->fetch_assoc();
	$idComp = $row[idComp]; */
	
	if ($idComp != 0){
		$query = "INSERT INTO `GPC` (idStand, idCompetence, number) VALUES ($idStand, $idComp, $number)";

		$res = $mysqli->query($query);	// добавляем запись о новой универсальной компетенции
		$idGPC = $mysqli->insert_id;	// идентификатор нового модуля

		InsertCursor($idAuthor, "GPC", $idStand, $idGPC);
	}
	return $idGPC;
}

function INSERT_PC_INTO_PROG($idAuthor, $idProg, $number, $idComp){
	$mysqli = conn();	
	
/* 	$query = "SELECT * FROM competence WHERE type=`ОПК` AND description=$description";
	$res = $mysqli->query($query);	
	$res->data_seek(0);
	$row = $res->fetch_assoc();
	$idComp = $row[idComp];
	 */
	$query = "INSERT INTO `PC` (idProg, idCompetence, number) VALUES ($idStand, $idComp, $number)";

	$res = $mysqli->query($query);	// добавляем запись о новой универсальной компетенции
	$idPC = $mysqli->insert_id;	// идентификатор нового модуля

	InsertCursor($idAuthor, "PC", $idProg, $idPC);

	return $idPC;
}

function DELETE_UC_FROM_STANDART($idAuthor, $idStand){
	$mysqli = conn();	
	
	$id = DeleteCursor($idAuthor, "UC", $idStand);
	
	if($id != 0){
		$query = "DELETE FROM `UC` WHERE idUC=$id";
		$res = $mysqli->query($query);
	}

	return $id;
}

function DELETE_GPC_FROM_STANDART($idAuthor, $idStand){
	$mysqli = conn();	
	
	$id = DeleteCursor($idAuthor, "GPC", $idStand);
	
	if($id != 0){
		$query = "DELETE FROM `GPC` WHERE idGPC=$id";
		$res = $mysqli->query($query);
	}

	return $id;
}

function DELETE_PC_FROM_PROGRAM($idAuthor, $idProg){
	$mysqli = conn();	
	
	$id = DeleteCursor($idAuthor, "PC", $idProg);
	
	if($id != 0){
		$query = "DELETE FROM `PC` WHERE idPC=$id";
		$res = $mysqli->query($query);
	}

	return $id;
}


function GET_UC_NUMBER($idAuthor, $idStand){
	$mysqli = conn();	
	
	$idUC = FetchCursor($idAuthor, "UC", $idStand);
	
	if ($idUC != 0){
		$query = "SELECT `number` FROM `UC` WHERE `idUC` = $idUC";
		$res = $mysqli->query($query);	
		$res->data_seek(0);
		$row = $res->fetch_assoc();
		return $row['number'];
	}else return 0;
}

function GET_UC_DESCRIPTION($idAuthor, $idStand){
	$mysqli = conn();	
	
	$idUC = FetchCursor($idAuthor, "UC", $idStand);
	
	if ($idUC != 0){
		$query = "SELECT `description` FROM `competence` WHERE `idCompetence` IN (SELECT `idCompetence` FROM `uc` WHERE `idUC`=$idUC)";
		$res = $mysqli->query($query);	
		$res->data_seek(0);
		$row = $res->fetch_assoc();
		return $row[description];
	}else return 0;	
}

function GET_GPC_NUMBER($idAuthor, $idStand){
	$mysqli = conn();	
	
	$idGPC = FetchCursor($idAuthor, "GPC", $idStand);
	
	if ($idGPC != 0){
		$query = "SELECT `number` FROM `GPC` WHERE `idGPC` = $idGPC";
		$res = $mysqli->query($query);	
		$res->data_seek(0);
		$row = $res->fetch_assoc();
		return $row[number];
	}else return 0;	
}

function GET_GPC_DESCRIPTION($idAuthor, $idStand){
	$mysqli = conn();	
	
	$idGPC = FetchCursor($idAuthor, "GPC", $idStand);
	
	if ($idGPC != 0){
		$query = "SELECT `description` FROM `GPC` WHERE `idGPC`=$idGPC)";
		$res = $mysqli->query($query);	
		$res->data_seek(0);
		$row = $res->fetch_assoc();
		return $row[description];
	}else return 0;	
}

function GET_PC_NUMBER($idAuthor, $idProg){
	$mysqli = conn();	
	
	$idPC = FetchCursor($idAuthor, "PC", $idProg);
	
	if ($idPC != 0){
		$query = "SELECT `number` FROM `PC` WHERE `idPC` = $idPC";
		$res = $mysqli->query($query);	
		$res->data_seek(0);
		$row = $res->fetch_assoc();
		return $row[number];
	}else return 0;	
}

function GET_PC_DESCRIPTION($idAuthor, $idProg){
	$mysqli = conn();	
	
	$idPC = FetchCursor($idAuthor, "PC", $idProg);
	
	if ($idPC != 0){
		$query = "SELECT `description` FROM `PC` WHERE `idPC`=$idPC)";
		$res = $mysqli->query($query);	
		$res->data_seek(0);
		$row = $res->fetch_assoc();
		return $row[description];
	}else return 0;	
}

function SET_UC_NUMBER($idAuthor, $idStand, $value){
	$mysqli = conn();	
	
	$idUC = FetchCursor($idAuthor, "UC", $idStand);
	
	if ($idUC != 0){
		$query = "UPDATE `UC` SET `number` = '$value' WHERE `idUC` = $idUC";
		return $mysqli->query($query);
	}else return 0;
}

function SET_UC_DESCRIPTION($idAuthor, $idStand, $value){
	$mysqli = conn();	
	
	$idUC = FetchCursor($idAuthor, "UC", $idStand);
	
	if ($idUC != 0){
		$query = "UPDATE `UC` SET `description` = '$value' WHERE `idUC` = $idUC";
		return $mysqli->query($query);
	}else return 0;
}

function SET_GPC_NUMBER($idAuthor, $idStand, $value){
	$mysqli = conn();	
	
	$idGPC = FetchCursor($idAuthor, "GPC", $idStand);
	
	if ($idGPC != 0){
		$query = "UPDATE `GPC` SET `number` = '$value' WHERE `idGPC` = $idGPC";
		return $mysqli->query($query);
	}else return 0;
}

function SET_GPC_DESCRIPTION($idAuthor, $idStand, $value){
	$mysqli = conn();	
	
	$idGPC = FetchCursor($idAuthor, "GPC", $idStand);
	
	if ($idGPC != 0){
		$query = "UPDATE `GPC` SET `description` = '$value' WHERE `idGPC` = $idGPC";
		return $mysqli->query($query);
	}else return 0;
}

function SET_PC_NUMBER($idAuthor, $idProg, $value){
	$mysqli = conn();	
	
	$idPC = FetchCursor($idAuthor, "PC", $idProg);
	
	if ($idPC != 0){
		$query = "UPDATE `PC` SET `number` = '$value' WHERE `idPC` = $idPC";
		return $mysqli->query($query);
	}else return 0;
}

function SET_PC_DESCRIPTION($idAuthor, $idProg, $value){
	$mysqli = conn();	
	
	$idPC = FetchCursor($idAuthor, "PC", $idProg);
	
	if ($idPC != 0){
		$query = "UPDATE `PC` SET `description` = '$value' WHERE `idPC` = $idPC";
		return $mysqli->query($query);
	}else return 0;
}

// ----  операции над образовательными программами  ---- //

function PROGRAM_COLLECTION($idAuthor) {
	$mysqli = conn();	

	$query = "SELECT * FROM `program` WHERE `Author`=$idAuthor";
	$res = $mysqli->query($query);	
	$arr = array();
	for ($row_no = 0; $row_no < $res->num_rows; $row_no++) {
		$res->data_seek($row_no);
		$row = $res->fetch_assoc();
		$arr[$row_no] = array(idProg => $row[idProg], idStand => $row[idStand], year => $row[year]);
	}
	return $arr;
}

function CREATE_PROGRAM($idAuthor, $idStand, $year){
	$mysqli = conn();		
	$query = "INSERT INTO `program` (`idStand`, `year`, `Author`) VALUES ($idStand, '$year', '$idAuthor')";

	$res = $mysqli->query($query);	// добавляем запись о новом стандарте
	$id = $mysqli->insert_id;	// идентификатор нового стандарта

	CreateCursor($idAuthor,"PC",$id);
	CreateCursor($idAuthor,"Course",$id);
	
	return $id;
}

function GET_STANDART($idAuthor, $idProg){
	$mysqli = conn();	
	$query = "SELECT `idStand` FROM `program` WHERE `idProg`=$idProg";
	$res = $mysqli->query($query);	
	$res->data_seek(0);
	$row = $res->fetch_assoc();
	return	$row[idStand];
}

function RESET_COURSE($idAuthor, $idProg){
	return ResetCursor($idAuthor, "Course", $idProg);	
}

function NEXT_COURSE($idAuthor, $idProg){
	NextCursor($idAuthor, "Course", $idProg);
	return 0;
}

function PRIOR_COURSE($idAuthor, $idProg) {
	PriorCursor($idAuthor, "Course", $idProg);
	return 0;
}

function FETCH_COURSE($idAuthor, $idProg) {
	return FetchCursor($idAuthor, "Course", $idProg);
}

function DELETE_COURSE($idAuthor, $idProg){
	$mysqli = conn();	
	
	$id = DeleteCursor($idAuthor, "Course", $idProg);
	
	if($id != 0){
		$query = "DELETE FROM `syllabus` WHERE `idCourse`=$id";
		$res = $mysqli->query($query);
	}

	return $id;
}

function INSERT_COURSE($idAuthor, $idProg, $semestr){
	$mysqli = conn();	

	$idCourse = CREATE_COURSE($idAuthor);
	$query = "INSERT INTO `syllabus` (`idProg`, `idCourse`, `semester`) VALUES ($idProg, $idCourse, $semestr)";
	$res = $mysqli->query($query);	
	$id = $mysqli->insert_id;	

	InsertCursor($idAuthor, "Course", $idProg, $id);

	return $id;
}

function GET_COURSE_TITLE($idAuthor, $idProg){
	$mysqli = conn();	
	$id = FETCH_COURSE($idAuthor, $idProg);
	$query = "SELECT `title` FROM `course` WHERE `idCour` = $id";
	$res = $mysqli->query($query);	
	$res->data_seek(0);
	$row = $res->fetch_assoc();
	return $row[title];

}

function GET_COURSE_LECTURE($idAuthor, $idProg){
	return 0;
}

function GET_COURSE_LABORATORY($idAuthor, $idProg){
	return 0;
}

function GET_COURSE_OUTWORK($idAuthor, $idProg){
	return 0;
}

function SET_COURSE_TITLE($idAuthor, $idProg, $value){
	$mysqli = conn();	
	$id = FETCH_COURSE($idAuthor, $idProg);
	if ($id != 0){
		$query = "UPDATE `course` SET `title` = '$value' WHERE `idCour` = $id";
		return $mysqli->query($query);
	}else return 0;
}

// ----  операции над курсами и граф-планами  ---- //

function GRAPH_PLAN($idAuthor, $idCourse){
//Доступ к корневой вершине граф-плана курса осуществляется с помощью операции:
	$mysqli = conn();	
	$query = "select `idNod` from `gpnode` where `idCour` = $idCourse and `leftKey` = 1";
	$res = $mysqli->query($query);	
	$res->data_seek(0);
	$row = $res->fetch_assoc();
	return $row[idNod];
}

function INSERT_CHILD($idAuthor, $node){
// добавления новой дочерней вершины узла node
	$mysqli = conn();	
	$query = "select * from `gpnode` where `idNod` = $node";
	$result = $mysqli->query($query);
	$result->data_seek(0);
	$row = $result->fetch_assoc();
	$parentLeft = $row["leftKey"];
	$parentRight = $row["rightKey"];
	$parentLevel = $row["level"];
	$idCour = $row["idCour"];
//	$idMod = $ow["idMod"];
	
	$left = $parentRight;
	$right = $left + 1;
	$query = "insert into `gpnode` (`idCour`, `level`, `leftKey`, `rightKey`) values ($idCour, ".($parentLevel + 1).", $left, $right";
	$res = $mysqli->query($query);	
	$id = $mysqli->insert_id;	

	$query = "update `gpnode` set rightKey = rightKey + 2 where rightKey >= $left and idNod != $id and idCour = $idCour";
	$res = $mysqli->query($query);

	$query = "update `gpnode` set leftKey = leftKey + 2 where leftKey > $left and idCour = $idCour";
	$res = $mysqli->query($query);
	
	InsertCursor($idAuthor, "Node", $node, $id);	
}

function FETCH_CHILD($idAuthor,$node){
//Получение указателя на текущую дочернюю вершину узла node 
	return FetchCursor($idAuthor, "Course", $node);
}

function RESET_CHILD($idAuthor,$node){
//Перемещение внутреннего курсора на первую дочернюю вершину узла node 
	return ResetCursor($idAuthor, "Node", $node);		
}

function NEXT_CHILD($idAuthor,$node){
//Перемещение внутреннего курсора на следующую дочернюю вершину узла node 
	NextCursor($idAuthor, "Node", $node);
	return 0;
}

function PRIOR_CHILD($idAuthor,$node){
//Перемещение курсора на предыдущую дочернюю вершину узла 
	PriorCursor($idAuthor, "Node", $node);
	return 0;	
}

function DELETE_CHILD($idAuthor,$node){
//Удаление дочерней вершины узла, на которую указывает курсор
	$mysqli = conn();	
	
	$id = DeleteCursor($idAuthor, "Node", $node);
	
	if($id != 0){
		$query = "DELETE FROM `gpnode` WHERE `idNod`=$id";
		$res = $mysqli->query($query);
	}
/* 
			function updateParentsD($conn, $counter, $course)
			{
				$sql = "update gpnode set rightKey = rightKey - 2 where rightKey > " . $counter . " and idCour = $course;";
				if ($conn->query($sql) === TRUE) 
				{
					//запрос выполнен
				} 
				else 
				{
					//запрос не выполнен
					header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=qrfail");
					die("Query failed");
				}
				$sql = "update gpnode set leftKey = leftKey - 2 where leftKey > " . $counter . " and idCour = $course;";
				if ($conn->query($sql) === TRUE) 
				{
					//запрос выполнен
				} 
				else 
				{
					//запрос не выполнен
					header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=qrfail");
					die("Query failed");
				}
			}
			
			function deleteCLNode($conn, $ID)
			{
				$sql = "select leftKey, idCour from gpnode where idNod = " . $ID . ";";
				$result = $conn->query($sql);
				if ($result->num_rows > 0) 
				{
					while($row = $result->fetch_assoc()) 
					{
						$left = $row["leftKey"];
						$course = $row["idCour"];
					}
				}
				else
				{
					//пустой результат запроса
					header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=emptyqr");
					die("No results");
				}
				$sql = "delete from gpnode where idNod = " . $ID . ";";
				if ($conn->query($sql) === TRUE) 
				{
					//запрос выполнен
				} 
				else 
				{
					//запрос не выполнен
					header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=qrfail");
					die("Query failed");
				}
				updateParentsD($conn, $left, $course);
			}

			function deleteNode($conn, $ID)
			{				while(true)
				{
					$left = -1;
					$right = -1;
					$sql = "select * from gpnode where idNod = " . $ID . ";";
					$result = $conn->query($sql);
					if ($result->num_rows > 0) 
					{
						while($row = $result->fetch_assoc()) 
						{
							$left = $row["leftKey"];
							$right = $row["rightKey"];
							$course = $row["idCour"];
						}
					}
					else
					{
						//пустой результат запроса
						header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=emptyqr");
						die("No results");
					}
					if($right == $left + 1) 
					{
						deleteCLNode($conn, $ID);
						break;
					}
					else 
					{
						$dID = -1;
						$sql = "select idNod from gpnode where leftKey = " . ($left + 1) . " and idCour = $course;";
						$result = $conn->query($sql);
						if ($result->num_rows > 0) 
						{
							while($row = $result->fetch_assoc()) 
							{
								$dID = $row["idNod"];
							}
						}
						else
						{
							//пустой результат запроса
							header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=emptyqr");
							die("No results");
						}
						deleteNode($conn, $dID);
					}
				} 
			}
*/
	return $id;
}

function CREATE_COURSE($idAuthor){
	$mysqli = conn();	

	$d = date("Y-m-d");				// текущая дата
	$txt = "Введите данные";
	$idEnc = CREATE_ENCYCLOPEDIA($idAuthor);
	$query = "INSERT INTO `course` (`Author`, `idEnc`, `title`, `annotation`, `created`) VALUES ($idAuthor, $idEnc, $txt, $txt, $d)";
	$res = $mysqli->query($query);	
	$id = $mysqli->insert_id;	

	return $id;
}

function GET_ENCYCLOPEDIA($idAuthor, $idCourse){
	$mysqli = conn();	
	$query = "select * from `course` where `idCour` = $idCourse";
	$result = $mysqli->query($query);
	$result->data_seek(0);
	$row = $result->fetch_assoc();
	return $row["idEnc"];	
}

function SET_LINK($idAuthor, $node, $module){
//Связывание узла граф-плана node с модулем module 
	$mysqli = conn();
	$query = "update `gpnode` set `idMod` = $module where idNod = $node";
	$mysqli->query($query); 
}

function GET_LINK($idAuthor, $node){
//Для получения доступа к модулю энциклопедии курса, который ассоциирован с узлом граф-плана node
	$mysqli = conn();
	$query = "select `idMod` from `gpnode` where idNod = $node";
	$result = $mysqli->query($query);
	$result->data_seek(0);
	$row = $result->fetch_assoc(); 
	return $row["idMod"];
}

function INSERT_UC_INTO_GP($idAuthor, $node, $number){
//Добавление номера number в коллекцию номеров универсальных компетенций узла граф-плана node 
	$mysqli = conn();

	$query = "select `idCompetence` from `UC` where `number` = $number AND `idStand` IN (SELECT idStand FROM `program` WHERE `idProg` IN (SELECT `idProg` FROM `syllabus` WHERE `idCour` IN (SELECT `idCour` FROM `gpnode` WHERE `idNod`=$node)))";
	$result = $mysqli->query($query);
	$result->data_seek(0);
	$row = $result->fetch_assoc(); 
	$id = $row[idCompetence];

	$query = "INSERT INTO `complist` (`idComp`, `idNod`) VALUES ($id, $node)";
	$res = $mysqli->query($query);	
	$id = $mysqli->insert_id;
	InsertCursor($idAuthor, "UCNode", $node, $id);	
	
	return $id;
}

function DELETE_UC_FROM_GP($idAuthor, $node, $number){
//Удаление номера number из коллекции номеров универсальных компетенций узла граф-плана node 
	$mysqli = conn();

	$query = "select `idCompetence` FROM `competence` WHERE `number`=$number AND `type`='УК'";
	$result = $mysqli->query($query);
	$result->data_seek(0);
	$row = $result->fetch_assoc(); 
	$idComp = $row[idCompetence];
	$query = "select `idList` from `complist` where `idNod`=$node AND `idComp`=$idComp";
	$result = $mysqli->query($query);
	$result->data_seek(0);
	$row = $result->fetch_assoc(); 
	$id = $row[idList];
	DeleteCursor($idAuthor, "UCNode", $node, $idComp);	
	
	$query = "DELETE FROM `complist` WHERE `idList`=$id";
	return $mysqli->query($query);
}

function UC_ISEXIST($idAuthor, $node, $number){
//Определение наличия универсальной компетенции с номером number в узле node граф-плана 
	$mysqli = conn();
	
	$query = "select `idList` from `complist` where `idNod`=$node AND `idComp` IN (select `idCompetence` FROM `competence` WHERE `number`=$number AND `type`='УК')";
	$result = $mysqli->query($query);
	if ($result->num_rows > 0) {
		return True;
	}else{
		return False;
	}	
}

function INSERT_GPC_INTO_GP($idAuthor, $node, $number){
//Добавление номера number в коллекцию номеров универсальных компетенций узла граф-плана node 
	$mysqli = conn();

	$query = "select `idCompetence` from `GPC` where `number` = $number AND `idStand` IN (SELECT idStand FROM `program` WHERE `idProg` IN (SELECT `idProg` FROM `syllabus` WHERE `idCour` IN (SELECT `idCour` FROM `gpnode` WHERE `idNod`=$node)))";
	$result = $mysqli->query($query);
	$result->data_seek(0);
	$row = $result->fetch_assoc(); 
	$id = $row[idCompetence];

	$query = "INSERT INTO `complist` (`idComp`, `idNod`) VALUES ($id, $node)";
	$res = $mysqli->query($query);	
	$id = $mysqli->insert_id;
	InsertCursor($idAuthor, "GPCNode", $node, $id);	
	
	return $id;
}

function DELETE_GPC_FROM_GP($idAuthor, $node, $number){
//Удаление номера number из коллекции номеров универсальных компетенций узла граф-плана node 
	$mysqli = conn();

	$query = "select `idCompetence` FROM `competence` WHERE `number`=$number AND `type`='ОПК'";
	$result = $mysqli->query($query);
	$result->data_seek(0);
	$row = $result->fetch_assoc(); 
	$idComp = $row[idCompetence];
	$query = "select `idList` from `complist` where `idNod`=$node AND `idComp`=$idComp";
	$result = $mysqli->query($query);
	$result->data_seek(0);
	$row = $result->fetch_assoc(); 
	$id = $row[idList];
	DeleteCursor($idAuthor, "GPCNode", $node, $idComp);	
	
	$query = "DELETE FROM `complist` WHERE `idList`=$id";
	return $mysqli->query($query);
}

function GPC_ISEXIST($idAuthor, $node, $number){
//Определение наличия универсальной компетенции с номером number в узле node граф-плана 
	$mysqli = conn();
	
	$query = "select `idList` from `complist` where `idNod`=$node AND `idComp` IN (select `idCompetence` FROM `competence` WHERE `number`=$number AND `type`='ОПК')";
	$result = $mysqli->query($query);
	if ($result->num_rows > 0) {
		return True;
	}else{
		return False;
	}	
}

function INSERT_PC_INTO_GP($idAuthor, $node, $number){
//Добавление номера number в коллекцию номеров универсальных компетенций узла граф-плана node 
	$mysqli = conn();

	$query = "select `idCompetence` from `PC` where `number` = $number AND `idStand` IN (SELECT idStand FROM `program` WHERE `idProg` IN (SELECT `idProg` FROM `syllabus` WHERE `idCour` IN (SELECT `idCour` FROM `gpnode` WHERE `idNod`=$node)))";
	$result = $mysqli->query($query);
	$result->data_seek(0);
	$row = $result->fetch_assoc(); 
	$id = $row[idCompetence];

	$query = "INSERT INTO `complist` (`idComp`, `idNod`) VALUES ($id, $node)";
	$res = $mysqli->query($query);	
	$id = $mysqli->insert_id;
	InsertCursor($idAuthor, "PCNode", $node, $id);	
	
	return $id;

}

function DELETE_PC_FROM_GP($idAuthor, $node, $number){
//Удаление номера number из коллекции номеров универсальных компетенций узла граф-плана node 
	$mysqli = conn();

	$query = "select `idCompetence` FROM `competence` WHERE `number`=$number AND `type`='ПК'";
	$result = $mysqli->query($query);
	$result->data_seek(0);
	$row = $result->fetch_assoc(); 
	$idComp = $row[idCompetence];
	$query = "select `idList` from `complist` where `idNod`=$node AND `idComp`=$idComp";
	$result = $mysqli->query($query);
	$result->data_seek(0);
	$row = $result->fetch_assoc(); 
	$id = $row[idList];
	DeleteCursor($idAuthor, "PCNode", $node, $idComp);	
	
	$query = "DELETE FROM `complist` WHERE `idList`=$id";
	return $mysqli->query($query);
}

function PC_ISEXIST($idAuthor, $node, $number){
//Определение наличия универсальной компетенции с номером number в узле node граф-плана 
	$mysqli = conn();
	
	$query = "select `idList` from `complist` where `idNod`=$node AND `idComp` IN (select `idCompetence` FROM `competence` WHERE `number`=$number AND `type`='ПК')";
	$result = $mysqli->query($query);
	if ($result->num_rows > 0) {
		return True;
	}else{
		return False;
	}	
}

?>