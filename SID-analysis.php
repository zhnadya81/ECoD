<?php

include "SID-model-operations.php";

function Amount_of_linked_nodes($root){
// Количество связанных узлов поддерева с корнем $root
	$amount = 0;
	if (GET_LINK($root) != NULL) {
		$amount += 1;
	}
	RESET_CHILD($root);
	$child = FETCH_CHILD($root);
	while ($child != NULL){
		$amount += Amount_of_linked_nodes($child);
		NEXT_CHILD($root);
		$child = FETCH_CHILD($root);
	}
	return $amount;
}

function Amount_of_nodes($root){
// Количество узлов поддерева с корнем $root	
	$amount = 1;
	RESET_CHILD($root);
	$child = FETCH_CHILD($root);
	while ($child != NULL){
		$amount += Amount_of_nodes($child);
		NEXT_CHILD($root);
		$child = FETCH_CHILD($root);
	}
	return $amount;
}

function Consistency_rate($course){
// Коэффициент целостности курса
	$root = GRAPH_PLAN($course);
	return Amount_of_linked_nodes($root) / Amount_of_nodes($root);
}

function Subtree_concept_credit($root){
// Трудоемкость слоя «Теория» для поддерева с корнем $root
	$module = GET_LINK($root);
	$credit = CONCEPT_CREDIT($module);
	RESET_CHILD($root);
	$child = FETCH_CHILD($root);
	while ($child != NULL){
		$credit += Subtree_concept_credit($child);
		NEXT_CHILD ($root);
		$child = FETCH_CHILD($root);
	}
	return $credit;
}

function Course_concept_credit($course){
// Трудоемкость слоя «Теория» для курса в целом
	$root = GRAPH_PLAN($course);
	$credit = Subtree_concept_credit($root);
	return $credit;
}

function Subtree_open_question_credit($root){
// Трудоемкость слоя «Открытый вопрос» для поддерева с корнем $root
	$module = GET_LINK($root);
	$credit = OPEN_QUESTION_CREDIT($module);
	RESET_CHILD($root);
	$child = FETCH_CHILD($root);
	while ($child != NULL){
		$credit += Subtree_open_question_credit($child);
		NEXT_CHILD ($root);
		$child = FETCH_CHILD($root);
	}
	return $credit;
}

function Course_open_question_credit($course){
// Трудоемкость слоя «Открытый вопрос» для курса в целом
	$root = GRAPH_PLAN($course);
	$credit = Subtree_open_question_credit($root);
	return $credit;
}

function Subtree_example_credit($root){
// Трудоемкость слоя «Пример» для поддерева с корнем $root
	$module = GET_LINK($root);
	$credit = EXAMPLE_CREDIT($module);
	RESET_CHILD($root);
	$child = FETCH_CHILD($root);
	while ($child != NULL){
		$credit += Subtree_example_credit($child);
		NEXT_CHILD ($root);
		$child = FETCH_CHILD($root);
	}
	return $credit;
}

function Course_example_credit($course){
// Трудоемкость слоя «Пример» для курса в целом
	$root = GRAPH_PLAN($course);
	$credit = Subtree_example_credit($root);
	return $credit;
}

function Subtree_exercise_credit($root){
// Трудоемкость слоя «Упражение» для поддерева с корнем $root
	$module = GET_LINK($root);
	$credit = EXERCISE_CREDIT($module);
	RESET_CHILD($root);
	$child = FETCH_CHILD($root);
	while ($child != NULL){
		$credit += Subtree_exercise_credit($child);
		NEXT_CHILD ($root);
		$child = FETCH_CHILD($root);
	}
	return $credit;
}

function Course_exercise_credit($course){
// Трудоемкость слоя «Упражение» для курса в целом
	$root = GRAPH_PLAN($course);
	$credit = Subtree_exercise_credit($root);
	return $credit;
}

function Subtree_close_question_credit($root){
// Трудоемкость слоя «Закрытый вопрос» для поддерева с корнем $root
	$module = GET_LINK($root);
	$credit = CLOSE_QUESTION_CREDIT($module);
	RESET_CHILD($root);
	$child = FETCH_CHILD($root);
	while ($child != NULL){
		$credit += Subtree_close_question_credit($child);
		NEXT_CHILD ($root);
		$child = FETCH_CHILD($root);
	}
	return $credit;
}

function Course_close_question_credit($course){
// Трудоемкость слоя «Закрытый вопрос» для курса в целом
	$root = GRAPH_PLAN($course);
	$credit = Subtree_close_question_credit($root);
	return $credit;
}

function Subtree_problem_credit($root){
// Трудоемкость слоя «Задание на практику» для поддерева с корнем $root
	$module = GET_LINK($root);
	$credit = PROBLEM_CREDIT($module);
	RESET_CHILD($root);
	$child = FETCH_CHILD($root);
	while ($child != NULL){
		$credit += Subtree_problem_credit($child);
		NEXT_CHILD ($root);
		$child = FETCH_CHILD($root);
	}
	return $credit;
}

function Course_problem_credit($course){
// Трудоемкость слоя «Задание на практику» для курса в целом
	$root = GRAPH_PLAN($course);
	$credit = Subtree_problem_credit($root);
	return $credit;
}

function Subtree_bibitem_credit($root){
// Трудоемкость слоя «Библиографическая ссылка» для поддерева с корнем $root
	$module = GET_LINK($root);
	$credit = BIBITEM_CREDIT($module);
	RESET_CHILD($root);
	$child = FETCH_CHILD($root);
	while ($child != NULL){
		$credit += Subtree_bibitem_credit($child);
		NEXT_CHILD ($root);
		$child = FETCH_CHILD($root);
	}
	return $credit;
}

function Course_bibitem_credit($course){
// Трудоемкость слоя «Библиографическая ссылка» для курса в целом
	$root = GRAPH_PLAN($course);
	$credit = Subtree_bibitem_credit($root);
	return $credit;
}

function Course_credit($course){
// Общая трудоемкость курса
	$credit = Course_concept_credit($course) +
		Course_open_question_credit($course) +
		Course_example_credit($course) +
		Course_exercise_credit($course) +
		Course_close_question_credit($course) +
		Course_problem_credit($course) +
		Course_bibitem_credit($course);
	return $credit;
}

function Module_credit($module){
// Трудоемкость модуля
	$credit = CONCEPT_CREDIT($module) +
		OPEN_QUESTION_CREDIT($module) +
		EXAMPLE_CREDIT($module) +
		EXERCISE_CREDIT($module) +
		CLOSE_QUESTION_CREDIT($module) +
		PROBLEM_CREDIT($module) +
		BIBITEM_CREDIT($module);
	return $credit;
}

function Average_credit($root){
// Средняя трудоемкость модулей для поддерева с корнем $root
	$amount = Amount_of_nodes($root);
	$credit = Subtree_concept_credit($root) +
		Subtree_open_question_credit($root) +
		Subtree_example_credit($root) +
		Subtree_exercise_credit($root) +
		Subtree_close_question_credit($root) +
		Subtree_problem_credit($root) +
		Subtree_bibitem_credit($root);
	return $credit/$amount;
}

function Sum_of_deviations($root, $value){
// Суммарное отклонение трудоемкостей модулей от значения $value для поддерева с корнем $root
	$root_module = GET_LINK($root);
	$total_deviation = abs($value - Module_credit($root_module));
	RESET_CHILD($root);
	$child = FETCH_CHILD($root);
	while ($child != NULL){
		$total_deviation += Sum_of_deviations($child, $value);
		NEXT_CHILD($root);
		$child = FETCH_CHILD($root);
	}
	return $total_deviation;
}

function Course_imbalance($course){
// Оценка дисбаланса курса
	$root = GRAPH_PLAN($course);
	$average = Average_credit($root);
	$amount = Amount_of_nodes($root);
	return Sum_of_deviations($root, $average)/$amount;
}

function Сredit_constraints($edu_prog){
// Проверка ограничений на трудоемкость
	$standart = GET_STANDART($edu_prog);
	$study = 0;
	$practice = 0;
	RESET_COURSE($edu_prog);
	$course = FETCH_COURSE($edu_prog);
	while ($course != NULL){
		$practice += Course_problem_credit($course);
		$study += Course_concept_credit($course);
		$study += Course_open_question_credit($course);
		$study += Course_example_credit($course);
		$study += Course_exercise_credit($course);
		$study += Course_close_question_credit($course);
		NEXT_COURSE($edu_prog);
		$course = FETCH_COURSE($edu_prog);
	}
	if (($study >= GET_MIN_STUD($standart)) and ($practice >= GET_MIN_PRACT($standart)) 
	and ($study + $practice <= GET_TOTAL($standart) - GET_MIN_CRT($standart)) and ($study + $practice >= GET_TOTAL($standart) - GET_MAX_CRT($standart))) {
		return true;
	}
	return false;
}

function Course_UC_credit($root, $uc_number){
// Покрытие универсальной компетенции $uc_number в поддереве с корнем $root
	$module = GET_LINK($root);
	if (UC_ISEXIST($root, $uc_number)) {
		$credit = Module_credit($module);
	}else{
		$credit = 0;
	}
	RESET_CHILD($root);
	$child = FETCH_CHILD($root);
	while ($child != NULL){
		$credit += Course_UC_credit($child, $uc_number);
		NEXT_CHILD ($root);
		$child = FETCH_CHILD($root);
	}
	return $credit;
}

function UC_coverage($edu_prog){
// Проверка покрытия универсальных компетенций
	$standart = GET_STANDART($edu_prog);
	RESET_UC($standart);
	$number = UC_GET_NUMBER($standart);
	while ($number != NULL){
		$credit = 0;
		RESET_COURSE($edu_prog);
		$course = FETCH_COURSE($edu_prog);
		while ($course != NULL){
			$root = GRAPH_PLAN($course);
			$credit += Course_UC_credit($root, $number);
			NEXT_COURSE($edu_prog);
			$course = FETCH_COURSE($edu_prog);
		}
		if ($credit == 0) {
			return false;
		}
		NEXT_UC($standart);
	}
	return true;
}

function Course_GPC_credit($root, $gpc_number){
// Покрытие универсальной компетенции $gpc_number в поддереве с корнем $root
	$module = GET_LINK($root);
	if (GPC_ISEXIST($root, $gpc_number)) {
		$credit = Module_credit($module);
	}else{
		$credit = 0;
	}
	RESET_CHILD($root);
	$child = FETCH_CHILD($root);
	while ($child != NULL){
		$credit += Course_GPC_credit($child, $gpc_number);
		NEXT_CHILD ($root);
		$child = FETCH_CHILD($root);
	}
	return $credit;
}

function GPC_coverage($edu_prog){
// Проверка покрытия универсальных компетенций
	$standart = GET_STANDART($edu_prog);
	RESET_GPC($standart);
	$number = GPC_GET_NUMBER($standart);
	while ($number != NULL){
		$credit = 0;
		RESET_COURSE($edu_prog);
		$course = FETCH_COURSE($edu_prog);
		while ($course != NULL){
			$root = GRAPH_PLAN($course);
			$credit += Course_GPC_credit($root, $number);
			NEXT_COURSE($edu_prog);
			$course = FETCH_COURSE($edu_prog);
		}
		if ($credit == 0) {
			return false;
		}
		NEXT_GPC($standart);
	}
	return true;
}

function Conformance_to_standart($edu_prog){
// Соответствие образовательной программы стандарту
	if ((Сredit_constraints($edu_prog) == true) and (UC_coverage($edu_prog) == true) and (GPC_coverage($edu_prog) == true)) {
		return true;
	}else{
		return true;
	}
}

function Course_PC_credit($root, $pc_number){
// Покрытие профессиональной компетенции $pc_number в поддереве с корнем $root
	$module = GET_LINK($root);
	if (PC_ISEXIST($root, $pc_number)) {
		$credit = Module_credit($module);
	}else{
		$credit = 0;
	}
	RESET_CHILD($root);
	$child = FETCH_CHILD($root);
	while ($child != NULL){
		$credit += Course_PC_credit($child, $pc_number);
		NEXT_CHILD ($root);
		$child = FETCH_CHILD($root);
	}
	return $credit;
}

function PC_coverage($edu_prog){
// Покрытие профессиональных компетенций курсами образовательной программы
	RESET_PC($edu_prog);
	$number = PC_GET_NUMBER($edu_prog);
	while ($number != NULL){
		$credit = 0;
		RESET_COURSE($edu_prog);
		$course = FETCH_COURSE($edu_prog);
		while ($course != NULL){
			$root = GRAPH_PLAN($course);
			$credit += Course_PC_credit($root, $number);
			NEXT_COURSE($edu_prog);
			$course = FETCH_COURSE($edu_prog);
		}
		if ($credit == 0) {
			return false;
		}
		NEXT_PC($edu_prog);
	}
	return true;
}

function Consistency($edu_prog){
// Целостность образовательной программы
	RESET_COURSE($edu_prog);
	$course = FETCH_COURSE($edu_prog);
	while ($course != NULL){
		if (Consistency_rate($course) != 1) { 
			return false;
		}
		NEXT_COURSE($edu_prog);
		$course = FETCH_COURSE($edu_prog);
	}
	if ((Correctness_eduprog($edu_prog) == true) and (PC_coverage($edu_prog) == true)) { 
		return true;
	}else{
		return false;
	}
}

?>