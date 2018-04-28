<?php
include 'config.php';
class TestData
{
	public static function tests()
	{
		$conn = self::connect();
		$options = array();
		$tests = mysqli_query($conn, "SELECT * FROM tests ORDER BY id DESC");

		while ($testsNew = mysqli_fetch_array($tests)) {
			$options[] = $testsNew;
		}

		return $options;
	}

	public static function teststart($formdata)
	{
		$conn = self::connect();
		$username = self::safe($formdata['name']);
		$testid = self::safe($formdata['test']);
		$tests = mysqli_query($conn, "SELECT * FROM tests WHERE id='".$testid."'");
		
		if (mysqli_num_rows($tests) > 0) {

			$testquest = array();
			$question = mysqli_query($conn, "SELECT * FROM questions WHERE testid='".$testid."'");

			while ($questionNow = mysqli_fetch_array($question)) {
				$questionans = array();
				$answer = mysqli_query($conn, "SELECT * FROM answers WHERE questionid='".$questionNow['id']."'");

				while($answerNow = mysqli_fetch_array($answer)){
					$questionans[] = array('id' => $answerNow['id'], 'value' => $answerNow['value']);
				}

				shuffle($questionans);
				$testquest[] = array('id' => $questionNow['id'], 'value' => $questionNow['value'], 'ans' => $questionans);
			}

			shuffle($testquest);

			mysqli_query($conn, "INSERT INTO users (value,testid) VALUES ('".$username."','".$testid."')");
			$userid = mysqli_insert_id($conn);

			$_SESSION['userses'] = $userid;

			foreach ($testquest as $value) {
				mysqli_query($conn, "INSERT INTO answered_questions (qid,userid) VALUES ('".$value['id']."','".$userid."')");
			}

			return json_encode(array('totalq' => count($testquest), 'currentq' => 1, 'question' => $testquest[0]));
		}
	}

	public static function nextquestion($answerid)
	{
		$conn = self::connect();
		$userid = self::safe($_SESSION['userses']);
		$answerid = self::safe($answerid);
		$tests = mysqli_query($conn, "SELECT * FROM users WHERE id='".$userid."'");
		if (mysqli_num_rows($tests) > 0) {

			$testsNow = mysqli_fetch_assoc($tests);

			mysqli_query($conn, "UPDATE answered_questions SET aid='".$answerid."' WHERE aid IS NULL AND userid='".$userid."' ORDER BY id ASC LIMIT 1");

			$questiontree = mysqli_query($conn, "SELECT * FROM answered_questions WHERE aid IS NULL AND userid='".$userid."' ORDER BY id ASC LIMIT 1");
			if (mysqli_num_rows($questiontree) > 0) {

				$questiontreeNow = mysqli_fetch_assoc($questiontree);

				$question = mysqli_query($conn, "SELECT * FROM questions WHERE id='".$questiontreeNow['qid']."'");
				$questionNow = mysqli_fetch_assoc($question);

				$questionans = array();
				$answer = mysqli_query($conn, "SELECT * FROM answers WHERE questionid='".$questiontreeNow['qid']."'");

				while ($answerNow = mysqli_fetch_array($answer)) {
					$questionans[] = array('id' => $answerNow['id'], 'value' => $answerNow['value']);
				}

				shuffle($questionans);

				$totalq = mysqli_query($conn, "SELECT * FROM questions WHERE testid='".$testsNow['testid']."'");
				$answeredq = mysqli_query($conn, "SELECT * FROM answered_questions WHERE aid IS NOT NULL AND userid='".$userid."'");

				return json_encode(array('totalq' => mysqli_num_rows($totalq), 'currentq' => mysqli_num_rows($answeredq) + 1, 'question' => array('id' => $questionNow['id'], 'value' => $questionNow['value'], 'ans' => $questionans)));
			} else {
				$resultview = mysqli_query($conn, "SELECT * FROM test_results WHERE userid='".$userid."'");
				if(mysqli_num_rows($resultview) == 0){
					$questiontree = mysqli_query($conn, "SELECT * FROM answered_questions WHERE aid = (SELECT correctans FROM questions WHERE id = answered_questions.qid) AND userid='".$userid."'");

					$totalq = mysqli_query($conn, "SELECT * FROM questions WHERE testid='".$testsNow['testid']."'");

					mysqli_query($conn, "INSERT INTO test_results (userid,correctq) VALUES ('".$userid."','".mysqli_num_rows($questiontree)."')");

					return json_encode(array('name' => $testsNow['value'], 'totalq' => mysqli_num_rows($totalq), 'correctq' => mysqli_num_rows($questiontree)));
				}
			}

		}
	}

	public static function testresults($testdata)
	{
		$conn = self::connect();
		$testid = self::safe($testdata['testid']);
		$correctans = 0;

		foreach ($testdata['ans'] as $key => $value) {
			$qid = self::safe($key);
			$aid = self::safe($value);
			$answer = mysqli_query($conn, "SELECT * FROM questions WHERE id='".$qid."' AND testid='".$testid."' AND correctans='".$aid."'");
			if(mysqli_num_rows($answer) > 0){
				$correctans++;
			}
		}

		echo json_encode(array('get' => true, 'result' => $correctans));
	}

	// Protect from sql injections
	private static function safe($value)
	{
  		$conn = self::connect();
		return mysqli_real_escape_string($conn, trim($value));
	}

	private static function connect()
	{
		date_default_timezone_set('Europe/Riga');
		session_start();

        // Create connection
        $conn_main = mysqli_connect(DB_HOST, DB_USER, DB_PASS);

        mysqli_select_db($conn_main, DB_NAME) 
          or die("Could not select $database");
        mysqli_set_charset($conn_main, 'utf8');

        // Check connection
        if ($conn_main) {
            return $conn_main;
        }else{
            die("Connection failed: " . mysqli_connect_error());
        }
	}
}
