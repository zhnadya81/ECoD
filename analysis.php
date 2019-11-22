<?php
include "html.php";
include "SID-analysis.php";

function ListForm($idAuthor){
	$html = "";
	$list = PROGRAM_COLLECTION($idAuthor); 
	if($list!=NULL) {$html = $html."<h2>Образовательные программы:</h2><h3>";}
	foreach ($list as $row) {
	$html = $html.<<<EOT
			<p><a href='program.php?idProg=$row[idProg]'>
			<img width='20' src='images/edit.png' alt='Редактировать образовательную программу' title='Редактировать образовательную программу' /></a>&nbsp;
			<a href='analysis.php.php?idEdu=$row[idEdu]&name=$row[title]' title='Отобразить образовательную программу'>$row[title]</a>&nbsp;</p>
EOT;
	}
	$html = $html."</h3><br><h2><a href='program.php?idProg=0'>Создать новую образовательную программу</a></h2>";
	return $html;
} 



echo $top;
$idAuthor = 1;

if (!isset($_GET['idProg'])) { 							
// Образовательная программа не выбрана - выводим список программ
	echo ListForm($idAuthor);
}
else if (isset($_GET['idProg'])){
	
}

echo $bottom;
?>