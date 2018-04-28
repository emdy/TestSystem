<?php
include('inc/getData.php');
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Testi</title>
		<link rel="stylesheet" type="text/css" href="/inc/font.css">
		<link rel="stylesheet" type="text/css" href="/inc/style.css">
		<script src="/inc/jQuery.js"></script>
		<script src="/inc/func.js"></script>
		<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	</head>
	<body>
		<div class="main">
			<div class="tests">
				<h1>Testa uzdevums</h1>
				<div class="choose">
					<form>
						<input class="name" name="name" type="text" placeholder="Ievadi savu vārdu.."/>
						<select class="select" name="test">
							<option selected disabled>Izvēlies testu</option>
							<?php
								$tests = TestData::tests();
						    	foreach ($tests as $value) {
						    		echo '<option value="'.$value['id'].'">'.$value['value'].'</option>';
						    	}
						    ?>
						</select>
						<div class="next" disabled>Sākt</div>
					</form>
				</div>
			</div>
		</div>
	</body>
</html> 