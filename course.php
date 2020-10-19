<?php header('Content-type: text/html; charset=UTF-8'); ?>
<html>
	<head><title>Nodes</title>
		<script type="text/javascript" src="./vistest_files/vis.js.download"></script>
		<link href="./vistest_files/vis-network.min.css" rel="stylesheet" type="text/css">
		<style type="text/css">
			#mynetwork 
			{
				width: 600px;
				height: 400px;
				border: 1px solid lightgray;
			}
			form
			{
				float: left;
			}
		</style>
</head>
	<body>
		<?php 
			$servername = "localhost";
			$username = "root";
			$dbname = "unicst";
			
			
			
			function updateParentsN($conn, $counter, $protect, $course)
			{
				$sql = "update gpnode set rightKey = rightKey + 2 where rightKey >= " . $counter . " and idNod != " . $protect . " and idCour = $course;";
				if ($conn->query($sql) === TRUE) 
				{
					//запрос выполнен
				} 
				else 
				{
					//запрос не выполнен
					header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=qrfail");
					die("Query failed2");
				}
				$sql = "update gpnode set leftKey = leftKey + 2 where leftKey > " . $counter . " and idCour = $course;";
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

			function newNode($conn, $parentID, $modID)
			{
				$sql = "select * from gpnode where idNod = " . $parentID . ";";
				$result = $conn->query($sql);
				if ($result->num_rows > 0) 
				{
					while($row = $result->fetch_assoc()) 
					{
						$parentLeft = $row["leftKey"];
						$parentRight = $row["rightKey"];
						$parentLevel = $row["level"];
						$idCour = $row["idCour"];
						$idMod = $modID;
					}
				} 
				else 
				{
					//пустой результат запроса
					header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=emptyqr");
					die("No results");
				}
				$left = $parentRight;
				$right = $left + 1;
				$sql = "insert into gpnode (idCour, idMod, level, leftKey, rightKey) values ($idCour, $idMod," . ($parentLevel + 1) . "," . $left . "," . $right . ");";
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
				$sql = "select * from gpnode where leftKey = " . $left . " and rightKey = " . $right . ";";
				$result = $conn->query($sql);
				if ($result->num_rows > 0) 
				{
					while($row = $result->fetch_assoc()) 
					{
						$protect = $row["idNod"];
						$course = $row["idCour"];
					}
				} 
				else 
				{
					//пустой результат запроса
					header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=emptyqr");
					die("No results");
				}
				updateParentsN($conn, $left, $protect, $course);
			}
			
			function changeModID($conn, $ID, $modID)
			{
				$sql = "update gpnode set idMod = $modID where idNod = $ID;";
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
			{
				while(true)
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
			
			function moveNode($conn, $mID, $tID)
			{
				$savelk = -1;
				$saverk = -1;
				$width = -1;
				$distance = -1;
				$tmppos = -1;
				$newpos = -1;
				$oldrpos = -1;
				$oldlvl = -1;
				$tlvl = -1;
				$sql = "select * from gpnode where idNod = " . $mID . ";";
				$result = $conn->query($sql);
				if ($result->num_rows > 0) 
				{
					while($row = $result->fetch_assoc()) 
					{
						$width = $row["rightKey"] - $row["leftKey"] + 1;
						$distance = -$row["leftKey"];
						$tmppos = $row["leftKey"];
						$oldrpos = $row["rightKey"];
						$oldlvl = $row["level"];
						$savelk = $row["leftKey"];
						$saverk = $row["rightKey"];
						$course = $row["idCour"];
					}
				}
				else
				{
					//пустой результат запроса
					header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=emptyqr");
					die("No results");
				}
				$sql = "select * from gpnode where idNod = " . $tID . ";";
				$result = $conn->query($sql);
				if ($result->num_rows > 0) 
				{
					while($row = $result->fetch_assoc()) 
					{
						$newpos = $row["leftKey"] + 1;
						$distance = $distance + $row["leftKey"] + 1;
						$tlvl = $row["level"];
						if($row["leftKey"] > $savelk && $row["rightKey"] < $saverk) 
						{
							header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=hrconfl");
							die("Hierarchy conflict");
						}
					}
				}
				else
				{
					//пустой результат запроса
					header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=emptyqr");
					die("No results");
				}
				if($distance < 0)
				{
					$distance = $distance - $width;
					$tmppos = $tmppos + $width;
				}
				$sql = "update gpnode set leftKey = leftKey + " . $width . " where leftKey >= " . $newpos . " and idCour = $course;";
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
				$sql = "update gpnode set rightKey = rightKey + " . $width . " where rightKey >= " . $newpos . " and idCour = $course;";
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
				$sql = "update gpnode set leftKey = leftKey + " . $distance . ", rightKey = rightKey + " . $distance . ", level = level - " . $oldlvl . " + " . $tlvl . " + 1 where leftKey >= " . $tmppos . " and rightKey < " . ($tmppos + $width) . " and idCour = $course;";
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
				$sql = "update gpnode set leftKey = leftKey - " . $width . " where leftKey > " . $oldrpos . " and idCour = $course;";
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
				$sql = "update gpnode set rightKey = rightKey - " . $width . " where rightKey > " . $oldrpos . " and idCour = $course;";
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

//			$conn = new mysqli($servername, $username, "", $dbname);
			$conn = new mysqli("localhost", "root", "", "unicst");
			if ($conn->connect_error) {	die("Connection failed: " . $conn->connect_error); }
			
			if (isset($_POST['addid'])) 
			{
				//добавление узла
				if(strval($_POST['addid']) !== strval(intval($_POST['addid']))) 
				{
					header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=icval");
					die("<br>Input is not an integer");
				}
				echo("<br>Called newNode with " . $_POST['addid'] . "<br>");
				if(!empty($_POST['amodid']))
				{
					if(strval($_POST['amodid']) !== strval(intval($_POST['amodid']))) 
					{
						header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=icval");
						die("<br>Input is not an integer");
					}
					newNode($conn, $_POST['addid'], $_POST['amodid']);
				}
				else
				{
					newNode($conn, $_POST['addid'], "NULL");
				}
				header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?'));
				return;
			}
			if (isset($_POST['modid'])) 
			{
				//переопределение модуля
				if(strval($_POST['modid']) !== strval(intval($_POST['modid']))) 
				{
					header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=icval");
					die("<br>Input is not an integer");
				}
				if(strval($_POST['cmodid']) !== strval(intval($_POST['cmodid'])))
				{
					header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=icval");
					die("<br>Input is not an integer");
				}
				changeModID($conn, $_POST['modid'], $_POST['cmodid']);
				header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?'));
				return;
			}
			if (isset($_POST['delid'])) 
			{
				//удаление узла
				if(strval($_POST['delid']) !== strval(intval($_POST['delid']))) 
				{
					header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=icval");
					die("<br>Input is not an integer");
				}
				deleteNode($conn, $_POST['delid']);
				header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?'));
				return;
			}
			if (isset($_POST['movid']) && isset($_POST['mtgid'])) 
			{
				//перемещение узла
				if((strval($_POST['movid']) !== strval(intval($_POST['movid']))) || strval($_POST['mtgid']) !== strval(intval($_POST['mtgid']))) 
				{
					header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=icval");
					die("<br>Input is not an integer");
				}
				moveNode($conn, $_POST['movid'], $_POST['mtgid']);
				header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?'));
				return;
			}
			if (isset($_REQUEST['message'])) 
			{
				if($_REQUEST['message'] == 'icval')
				{
					echo("<div style='color:red'>Error: Input is not an integer.</div>");
				}
				if($_REQUEST['message'] == 'hrconfl')
				{
					echo("<div style='color:red'>Error: Hierarchy conflict.</div>");
				}
				if($_REQUEST['message'] == 'emptyqr')
				{
					echo("<div style='color:red'>Error: No results.</div>");
				}
				if($_REQUEST['message'] == 'qrfail')
				{
					echo("<div style='color:red'>Error: Query failed.</div>");
				}
			}
			
			function loadPointers()
			{
				$tfile = fopen("pointers.txt", "r");
				if(!$tfile) return array();
				$pointers = explode("Nod", fread($tfile, filesize("pointers.txt")));
				array_shift($pointers);
				fclose($tfile);
				foreach($pointers as $num => $pointer)
				{
					$pointers[$num] = explode(":", $pointer);
					array_shift($pointers[$num]);
					$pointers[$num][3] = explode(" ", $pointers[$num][3]);
					array_shift($pointers[$num][3]);
				}
				foreach($pointers as $num => $pointer)
				{
					foreach($pointer as $pnum => $pid)
					{
						
						if(is_string($pointer[$pnum])) 
						{
							$pointers[$num][$pnum] = intval($pointer[$pnum]);
						}
						else
						{
							foreach($pointer[$pnum] as $cnum => $cid)
							{
								$pointers[$num][$pnum][$cnum] = intval($cid);
							}
						}
					}
				}
				$assocPointers = array();
				foreach($pointers as $num => $pointer)
				{
					$assocPointers[$pointer[0]] = $pointer;
					array_shift($assocPointers[$pointer[0]]);
				}
				
				return $assocPointers;
			}
			
			function padPointers($conn, $pointers)
			{
				$tfile = fopen("pointers.txt", "a");
				$sql = "select idNod from gpnode";
				$result = $conn->query($sql);
				if($result->num_rows > 0)
				{
					while($row = $result->fetch_assoc()) 
					{
						if(isset($pointers[$row["idNod"]])) echo("<br>Pointer for " . $row["idNod"] . " does exist<br>");
						else 
						{
							echo("<br>Pointer for " . $row["idNod"] . " does NOT exist<br>");
							$pointers[$row["idNod"]][0] = 0;
							$sql = "select n2.idNod, count(*) as chNum from gpnode n1 join gpnode n2 on n2.leftKey < n1.leftKey and n2.rightKey > n1.rightKey and n1.idCour = n2.idCour and n1.level = n2.level + 1 and n2.idNod = " . $row["idNod"] . " group by n2.idNod";
							$result2 = $conn->query($sql);
							if($result2->num_rows > 0)
							{
								while($row2 = $result2->fetch_assoc()) 
								{
									$pointers[$row["idNod"]][1] = intval($row2["chNum"]);
								}
							}
							else
							{
								$pointers[$row["idNod"]][1] = 0;
							}
							$pointers[$row["idNod"]][2] = array();
							$sql = "select n1.idNod from gpnode n1 join gpnode n2 on n2.leftKey < n1.leftKey and n2.rightKey > n1.rightKey and n1.idCour = n2.idCour and n1.level = n2.level + 1 and n2.idNod = " . $row["idNod"] . " order by n1.idNod";
							$result3 = $conn->query($sql);
							if($result3->num_rows > 0)
							{
								while($row3 = $result3->fetch_assoc()) 
								{
									$pointers[$row["idNod"]][2][] = intval($row3["idNod"]);
								}
							}
						}
					}
				}
				return $pointers;
			}
			
			function savePointers($pointers)
			{
				$tfile = fopen("pointers.txt", "w");
				foreach($pointers as $num => $pointer)
				{
					fwrite($tfile, "Nod:" . $num . ":" . $pointer[0] . ":" . $pointer[1] . ":");
					foreach($pointer[2] as $cnum => $cpointer)
					{
						fwrite($tfile, " " . $cpointer);
					}
					fwrite($tfile, PHP_EOL);
				}
				fclose($tfile);
			}
			
			function edInit($conn)
			{
				
				echo("Course 2 first node ID = " . GRAPH_PLAN(2));
				//$pointers = padPointers($conn, loadPointers());
				$pointers = loadPointers();
				var_dump($pointers);
				//savePointers($pointers);
			
				$sql = "select n1.idNod, n1.idCour, n1.leftKey, n1.rightKey, n1.idMod, (select idNod from gpnode n2 where n2.idCour = n1.idCour and n2.leftKey < n1.leftKey and n2.rightKey > n1.rightKey order by n2.rightKey - n1.rightKey asc limit 1) as parent, n2.title from gpnode n1 left join module n2 on n1.idMod = n2.idMod group by n1.idNod order by n1.leftKey";
					
				$result = $conn->query($sql);
				
				if ($result->num_rows > 0) 
				{
					//echo("<div hidden>");
					echo("<div>");
					$total = 0;
					while($row = $result->fetch_assoc()) 
					{
						echo(" Course: <span id = 'cid" . strval($total) . "'>" . $row["idCour"] . "</span>");
						echo(" ID: <span id = 'id" . strval($total) . "'>" . $row["idNod"] . "</span> LK: <span id = 'lk" . strval($total) . "'>" . $row['leftKey'] . "</span> RK: <span id = 'rk" . strval($total) . "'>" . $row['rightKey'] . "</span> TITLE: <span id = 'tl" . strval($total) . "'>" . $row['title'] . "</span>");
						echo(" ParentID: <span id = 'pid" . strval($total) . "'>" . $row["parent"] . "</span>");
						if(isset($pointers[$row["idNod"]]))
						{
							echo(" Pointer destination: <span id = 'pdid" . strval($total) . "'>");
							/*
							if($pointers[$row["idNod"]][1] == 0) echo("NULL");
							else echo($pointers[$row["idNod"]][2][$pointers[$row["idNod"]][0]]);
							*/
							RESET_CHILD($row["idNod"]);
							if (FETCH_CHILD($row["idNod"])) echo(FETCH_CHILD($row["idNod"]));
							else echo("NULL");
							echo("</span>");
						}
						echo("<br>");
						$total++;
					}
					echo(" Total: <span id = 'total'>" . $total . "</span><br></div>");
				}
				$conn->close();
			}
			
			function GRAPH_PLAN($courseid)
			{
				global $conn;
				$sql = "select idNod from gpnode where idCour = $courseid and leftKey = 1";
				$result = $conn->query($sql);
				if ($result->num_rows > 0) 
				{
					while($row = $result->fetch_assoc()) 
					{
						return $row["idNod"];
					}
				} 
				else 
				{
					//пустой результат запроса
					header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=emptyqr");
					die("No results");
				}
			}
			
			function FETCH_CHILD($node)
			{
				$pointers = loadPointers();
				if(isset($pointers[$node][2][$pointers[$node][0]]))
				{
					return $pointers[$node][2][$pointers[$node][0]];
				}
				else return null;
			}
			
			function RESET_CHILD($node)
			{
				$pointers = loadPointers();
				if(isset($pointers[$node]))
				{
					$pointers[$node][0] = 0;
				}
				savePointers($pointers);
			}
			
			function NEXT_CHILD($node)
			{
				$pointers = loadPointers();
				if(isset($pointers[$node]))
					$pointers[$node][0]++;
				/*
				if(isset($pointers[$node]))
				{
					if($pointers[$node][0] >= ($pointers[$node][1] - 1))
					{
						$pointers[$node][0] = 0;
					}						
					else
					{
						$pointers[$node][0]++;
					}
				}
				*/
				savePointers($pointers);
			}
			
			function PRIOR_CHILD($node)
			{
				$pointers = loadPointers();
				if(isset($pointers[$node]))
				{
					if($pointers[$node][0] <= 0)
					{
						$pointers[$node][0] = $pointers[$node][1];
					}						
					else
					{
						$pointers[$node][0]--;
					}
				}
				savePointers($pointers);
			}
			
			function INSERT_CHILD($node)
			{
				global $conn;
				newNode($conn, $node, "NULL");
				$pointers = padPointers($conn, loadPointers());
				savePointers($pointers);
			}
			
			function DELETE_CHILD($node)
			{
				global $conn;
				$pointers = loadPointers();
				if(isset($pointers[$node]))
				{
					deleteNode($pointers[$node][2][$pointers[$node][0]]);
					unset($pointers[$pointers[$node][2][$pointers[$node][0]]]);
				}
				savePointers($pointers);
			}
			
			function SET_LINK($node, $module)
			{
				global $conn;
				changeModID($conn, $node, $module);
			}
			
			function GET_LINK($node)
			{
				global $conn;
				$sql = "select idMod from gpnode where idNod = $node;";
				$result = $conn->query($sql);
				if ($result->num_rows > 0) 
				{
					while($row = $result->fetch_assoc()) 
					{
						return $row["idMod"];
					}
				} 
				else 
				{
					//пустой результат запроса
					header("Location: " . strtok($_SERVER['HTTP_REFERER'], '?') . "?message=emptyqr");
					//die("No results");
					return null;
				}
			}
			
			function INSERT_UC_INTO_GP($node, $number)
			{
				
			}
			
			function DELETE_UC_FROM_GP($node, $number)
			{
				
			}
			
			function UC_ISEXIST($node, $number)
			{
				
			}
			
			function Amount_of_linked_nodes($root)
			{
				$amount = 0;
				if(GET_LINK($root) != null)
				{
					$amount += 1;
				}
				RESET_CHILD($root);
				$child = FETCH_CHILD($root);
				while($child != null)// wtf?
				{
					$amount += Amount_of_linked_nodes($child);
					NEXT_CHILD($root);
					$child = FETCH_CHILD($root);
				}
				return $amount;
			}
			
			function Amount_of_nodes($root)
			{
				$amount = 1;
				RESET_CHILD($root);
				$child = FETCH_CHILD($root);
				while($child != null)
				{
					$amount += Amount_of_nodes($child);
					NEXT_CHILD($root);
					$child = FETCH_CHILD($root);
				}
				return $amount;
			}
			
			function Consistency_rate($course)
			{
				$root = GRAPH_PLAN($course);
				return Amount_of_linked_nodes($root) / Amount_of_nodes($root);
			}



			echo("Amount_of_linked_nodes(377) = " . Amount_of_linked_nodes(377));
			echo("<br>");
			echo("Amount_of_nodes(377) = " . Amount_of_nodes(377));
			echo("<br>");
			echo("Consistency_rate(1) = " . Consistency_rate(1));
			echo("<br>");
			echo("Consistency_rate(2) = " . Consistency_rate(2));
			echo("<br>");
			edInit($conn);
		?>
		<table style="width:610; table-layout: fixed;" border=1>
		<tr>
		<td colspan="4">
		<div id="mynetwork">
			<div class="vis-network" 
				tabindex="900" 
				style="position: relative; 
					overflow: hidden; 
					touch-action: none; 
					user-select: none; 
					-webkit-user-drag: none; 
					-webkit-tap-highlight-color: rgba(0, 0, 0, 0); 
					width: 100%; height: 100%;">
				<canvas width="600" 
					height="400" 
					style="position: relative; 
						touch-action: none; 
						user-select: none; 
						-webkit-user-drag: none; 
						-webkit-tap-highlight-color: rgba(0, 0, 0, 0); 
						width: 100%; height: 100%;">
				</canvas>
			</div>
		</div>
		</td>
		</tr>
		<tr>
		<td id="msgCtnr" style="background-color:#ffffff;" colspan="2">
			<span id="01">Target: </span><span id="target1">(left click a node)</span><span id="12">.<br>Additional target: </span><span id="target2">(right click a node)</span><span id="23">.</span>
		</td>
		<td colspan="2" style="height:80px;">
			<div style="text-align:center"><button type="button">Select module</button>
			<input id ="modtxt" type="text" name="modtxt" maxlength="4" size="4"></div>
		</td>
		</tr>
		<tr>
		<td><form action="" method="post" style="margin:auto; text-align:center;">
			<input style="white-space:normal; width: 145px; height: 40px" onmouseover="addHover()" onmouseout="hoverEnd()" type="submit" value="Add node" id="addbtn"><br>
			<div hidden>Node ID: <input id ="addid" type="text" name="addid"></div>
			<div hidden>М: <input id ="amodid" type="text" name="amodid" maxlength="4" size="4"></div>
		</form></td>
		<td><form action="" method="post" style="margin:auto; text-align:center;">
			<input style="white-space:normal; width: 145px; height: 40px" onmouseover="modHover()" onmouseout="hoverEnd()" type="submit" value="Set module number" id="modbtn"><br>
			<div hidden>Node ID: <input id ="modid" type="text" name="modid"></div>
			<div hidden>М: <input id ="cmodid" type="text" name="cmodid" maxlength="4" size="4"></div>
		</form>
		</td>
		<td><form action="" method="post" style="margin:auto; text-align:center;">
			<input style="white-space:normal; width: 145px; height: 40px" onmouseover="deleteHover()" onmouseout="hoverEnd()" type="submit" value="Delete node" id="delbtn"><br>
			<div hidden>Node ID: <input id="delid" type="text" name="delid"></div>
		</form></td>
		<td><form action="" method="post" style="margin:auto; text-align:center;">
			<input style="white-space:normal; width: 145px; height: 40px" onmouseover="moveHover()" onmouseout="hoverEnd()" type="submit" value="Move node" id="movbtn"><br>
			<div hidden>Node ID: <input id="movid" type="text" name="movid"><br>
			Target ID: <input id="mtgid" type="text" name="mtgid"></div>
		</form></td>
		</tr>
		</table>
		<script type="text/javascript">
			function downToSize(string)
			{
				var space = " ";
				if(string.length > 40)
				{
					if(string.indexOf(' ', 15) < 25 && string.indexOf(' ', 15) != -1)
						string = "\n" + string.slice(0, string.indexOf(' ', 15)) + "\n" + string.slice(string.indexOf(' ', 15), 37) + "...";
					else
						string = "\n" + string.slice(0, 20) + "\n" + string.slice(20, 37) + "...";
				}
				else if(string.length > 20)
				{
					if(string.indexOf(' ', 15) < 25 && string.indexOf(' ', 15) != -1)
						string = "\n" + string.slice(0, string.indexOf(' ', 15)) + "\n" + string.slice(string.indexOf(' ', 15), 40);
					else
						string = "\n" + string.slice(0, 20) + "\n" + string.slice(20, 40);
				}
				else
					string = "\n" + string;
				return string;
			}
			
			var nodes = new vis.DataSet;

			var edges = new vis.DataSet;

			var i;
			for(i = 0; i < parseInt(document.getElementById('total').textContent, 10); i++)
			{
				nodes.add([{id: document.getElementById("id" + i).textContent, label: "TITLE: " + document.getElementById("tl" + i).textContent + "\n ID: " + document.getElementById("id" + i).textContent + " " + "\n\n" + document.getElementById("lk" + i).textContent + "     " + document.getElementById("rk" + i).textContent, shape: 'box'}]);
				//nodes.add([{id: document.getElementById("id" + i).textContent, label: document.getElementById("id" + i).textContent + ". " + document.getElementById("tl" + i).textContent, shape: 'box'}]);
				if (parseInt(document.getElementById("lk" + i).textContent, 10) != 1) edges.add([{from: parseInt(document.getElementById("pid" + i).textContent, 10), to: parseInt(document.getElementById("id" + i).textContent, 10)}]);
			}
			var container = document.getElementById('mynetwork');
			var data = 
			{
				nodes: nodes,
				edges: edges
			};
			var options = {layout: {hierarchical: {direction: 'UD', sortMethod: 'directed', nodeSpacing: 250, blockShifting: false, edgeMinimization: false}},
				interaction: {dragNodes: false},
				physics: {enabled: false}};
			var network = new vis.Network(container, data, options);
			network.on("selectNode", function (params) 
			{
				document.getElementById("target1").innerHTML = params.nodes[0];
				document.getElementById("addid").value = params.nodes[0];
				document.getElementById("modid").value = params.nodes[0];
				document.getElementById("delid").value = params.nodes[0];
				document.getElementById("movid").value = params.nodes[0];
			});
			network.on("deselectNode", function (params) 
			{
				document.getElementById("target1").innerHTML = "(left click a node)";
				document.getElementById("addid").value = "";
				document.getElementById("modid").value = "";
				document.getElementById("delid").value = "";
				document.getElementById("movid").value = "";
			});
			var tNode;
			network.on("oncontext", function (params) 
			{
				params.event.preventDefault();
				if(tNode)
				{
					tNode.color = {background: '#97C2FC', border: '#2B7CE9'};
					nodes.update(tNode);
				}
				if(this.getNodeAt(params.pointer.DOM))
				{
					tNode = nodes.get(this.getNodeAt(params.pointer.DOM));
					tNode.color = {background: '#55ff55', border: '#119911'};
					nodes.update(tNode);
					document.getElementById("target2").innerHTML = this.getNodeAt(params.pointer.DOM);
					document.getElementById("mtgid").value = this.getNodeAt(params.pointer.DOM);
				}
				else
				{
					document.getElementById("target2").innerHTML = "(right click a node)";
					document.getElementById("mtgid").value = "";
				}
			});
			function addHover()
			{
				if(!isNaN(parseInt(document.getElementById("modtxt").value)))
				{
					document.getElementById("amodid").value = document.getElementById("modtxt").value;
				}
				if(!isNaN(parseInt(document.getElementById("amodid").value)))
				{
					document.getElementById("01").innerHTML = "Add a child node (module: " + parseInt(document.getElementById("amodid").value).toString() + ") to node ";
				}
				else
				{
					document.getElementById("01").innerHTML = "Add a child node to node ";
				}
				document.getElementById("12").innerHTML = ".";
				document.getElementById("23").innerHTML = "";
				document.getElementById("target2").style.visibility = "hidden";
				if(!isNaN(parseInt(document.getElementById("addid").value)))
				{
					document.getElementById("msgCtnr").style.backgroundColor = "#66ff66";
				}
				else
				{
					document.getElementById("msgCtnr").style.backgroundColor = "#ff6666";
					document.getElementById("addbtn").addEventListener("click", blockElem);
				}
			}
			function modHover()
			{
				if(!isNaN(document.getElementById("modtxt").value))
				{
					document.getElementById("cmodid").value = document.getElementById("modtxt").value;
				}
				document.getElementById("01").innerHTML = "Set module number of node ";
				if(parseInt(document.getElementById("cmodid").value) > -1 && /^-{0,1}\d+$/.test(document.getElementById("cmodid").value))
				{
					document.getElementById("12").innerHTML = " to " + document.getElementById("cmodid").value.toString();
				}
				else
				{
					document.getElementById("12").innerHTML = " to (enter a module number)";
				}
				document.getElementById("23").innerHTML = "";
				document.getElementById("target2").style.visibility = "hidden";
				if(!isNaN(parseInt(document.getElementById("modid").value)) && !isNaN(parseInt(document.getElementById("cmodid").value)) && /^-{0,1}\d+$/.test(document.getElementById("cmodid").value))
				{
					document.getElementById("msgCtnr").style.backgroundColor = "#66ff66";
				}
				else
				{
					document.getElementById("msgCtnr").style.backgroundColor = "#ff6666";
					document.getElementById("modbtn").addEventListener("click", blockElem);
				}
			}
			function deleteHover()
			{
				document.getElementById("01").innerHTML = "Delete node ";
				document.getElementById("12").innerHTML = " with all children.";
				document.getElementById("23").innerHTML = "";
				document.getElementById("target2").style.visibility = "hidden";
				document.getElementById("msgCtnr").style.backgroundColor = "#66ff66";
				if(!isNaN(parseInt(document.getElementById("delid").value)) && parseInt(document.getElementById("delid").value) != 1)
				{
					document.getElementById("msgCtnr").style.backgroundColor = "#66ff66";
				}
				else
				{
					document.getElementById("msgCtnr").style.backgroundColor = "#ff6666";
					document.getElementById("delbtn").addEventListener("click", blockElem);
				}
			}
			function moveHover()
			{
				document.getElementById("01").innerHTML = "Move node ";
				document.getElementById("12").innerHTML = " in position of a child of node ";
				document.getElementById("23").innerHTML = ".";
				document.getElementById("msgCtnr").style.backgroundColor = "#66ff66";
				if(!isNaN(parseInt(document.getElementById("movid").value)) && !isNaN(parseInt(document.getElementById("mtgid").value)) && parseInt(document.getElementById("movid").value) != parseInt(document.getElementById("mtgid").value))
				{
					document.getElementById("msgCtnr").style.backgroundColor = "#66ff66";
				}
				else
				{
					document.getElementById("msgCtnr").style.backgroundColor = "#ff6666";
					document.getElementById("movbtn").addEventListener("click", blockElem);
				}
			}
			
			function hoverEnd()
			{
				document.getElementById("01").innerHTML = "Target: ";
				document.getElementById("12").innerHTML = ".<br>Additional target: ";
				document.getElementById("23").innerHTML = ".";
				document.getElementById("target1").style.visibility = "visible";
				document.getElementById("target2").style.visibility = "visible";
				document.getElementById("msgCtnr").style.backgroundColor = "#ffffff";
				document.getElementById("addbtn").removeEventListener("click", blockElem);
				document.getElementById("modbtn").removeEventListener("click", blockElem);
				document.getElementById("delbtn").removeEventListener("click", blockElem);
				document.getElementById("movbtn").removeEventListener("click", blockElem);
				document.getElementById("amodid").value = "";
				document.getElementById("cmodid").value = "";
			}
			function blockElem(event)
			{
				event.preventDefault();
				alert("Invalid operation");
			}
		</script>
	</body>
</html>


















