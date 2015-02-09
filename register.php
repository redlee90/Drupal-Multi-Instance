<?php


if (isset($_POST['login'])) {
	$loginname=$_POST["username"];
	$homepage=file_get_contents("http://$loginname.localhost");
	echo $homepage;

} else if (isset($_POST['register'])){
	$username=$_POST["username"];
	$password=$_POST["password"];
	$con=mysqli_connect("localhost","admin","5580866");

 // Check connection
	if (mysqli_connect_errno()) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error() . "<br>";
	}

 // Create user
	$create_user="CREATE USER '" .$username. "' @'localhost' IDENTIFIED BY '" . $password . "'";
	if (mysqli_query($con,$create_user)) {
		echo "User created successfully<br>";

 	// Create database with the same name as the user
		$create_database="CREATE DATABASE " . $username . "";
		if (mysqli_query($con,$create_database)) {
			echo "Database " . $username . " created successfully<br>";

		// Grant user $username with all previliages on database $username
			$grant="GRANT ALL ON " . $username . ".* TO '" . $username . "'@'localhost' IDENTIFIED BY '" .$password . "'";
			if (mysqli_query($con,$grant)) {
				echo "Grant operation successful<br>";

			//Flush privilages
				$flush="FLUSH PREVILIAGES";
				if (mysqli_query($con,$grant)) {
					echo "Flush successful, database now is ready to use<br>";

				//make a directory under each username
					mkdir("./sites/$username");
				//copy the settings.php file to the username directory
					copy("./sites/default/default.settings.php","./sites/$username/settings.php");

					$sitesf = './sites/sites.php';
					$newurl = '$sites[' . "'$username.localhost']=" . "'$username';" . "\n";

				/*Write the contents to the file, 
				 *using the FILE_APPEND flag to append the content to the end of the file
				 *and the LOCK_EX flag to prevent anyone else writing to the file at the same time
				 */
				file_put_contents($sitesf, $newurl, FILE_APPEND | LOCK_EX);


				// Add database information to the settings.php file
				$settingf = "./sites/$username/settings.php";				
				$pr='$databases=array(' . "'default'". "=>array(" . "'default'". "=>" . "array(";
				$db="'database'" . "=>" . "'$username'" . ",";
				$un="'username'" . "=>" . "'$username'" . ",";
				$pw="'password'" . "=>" . "'$password'" . ",";
				$ht="'host'" . "=>" . "'localhost'" . ",";
				$pt="'port'" . "=>" . "''" . ",";
				$dv="'driver'" . "=>" . "'mysql'" . ",";
				$pf="'prefix'" . "=>" . "''" . ",";
				$po="),),);";
				$replacement = $pr . $db . $un . $pw . $ht . $pt . $dv . $pf . $po;				
				$contents_ori=file_get_contents($settingf);				
				$contents_up=preg_replace("/[\$]databases(\s*)=(\s*)array(\s*)[\(][\)];/", $replacement, $contents_ori);				
				file_put_contents($settingf, $contents_up, LOCK_EX);

				/*
				windows specific part, linux version will be different				
				$newhost="127.0.0.1    $username.localhost\n";
				rename("C:/windows/system32/drivers/etc/hosts","C:/Users/Rui/hosts");
				file_put_contents("C:/Users/Rui/hosts", $newhost, FILE_APPEND | LOCK_EX);				
				rename("C:/Users/Rui/hosts","C:/windows/system32/drivers/etc/hosts");
				*/

				$href="http://$username.localhost/install.php";
				echo "<form action=$href>
				<input type='submit' value='install drupal'>
			</form>
			";
		} else {
			echo "Flush failed<br>";
		}
	} else {
		echo "Grant operation falied: " . mysqli_error($con . "<br>");
	}
} else {
	echo "Error creating database: " . mysqli_error($con) . "<br>";
}

} else { 
	echo "Error creating user: " . mysqli_error($con);
	echo "please go back and choose another username<br>";
}

}
?> 

