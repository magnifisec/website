<?php
session_start();
if(isset($_SESSION["verified"]) && $_SESSION["verified"] === true){
    //already logged in
    header("location: portal.php");
    exit;
}

//connect to database
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'admin');
define('DB_PASSWORD', 'Magnifisec1!');
define('DB_NAME', 'clients');
try{
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("ERROR: Could not connect to database. " . $e->getMessage());
}

$error = "";
$username = "";
$password = "";
 
// process login
if($_SERVER["REQUEST_METHOD"] == "POST") {
 
    if(empty(trim($_POST["username"]))) {
        $error = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    if(empty(trim($_POST["password"]))) {
        $error = "Please enter a password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    //check login
    if($error === "") {
        $query = "SELECT id, username, password FROM users WHERE username = :username";
        
        if($stmt = $pdo->prepare($query)) {
            $stmt->bindParam(":username", $username, PDO::PARAM_STR);
            if($stmt->execute()) {
                if($stmt->rowCount() == 1) {
                    //valid username
                    if($row = $stmt->fetch()) {
                        $hashed_password = $row["password"];
                        if(password_verify($password, $hashed_password)) {
                            //valid password
                            session_start();
                            
                            //build session
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $row["id"];
                            $_SESSION["username"] = $username;
                            
                            header("location: welcome.php");
                        } else {
                            $error = "Invalid password.";
                        }
                    }
                } else {
                    $error = "Invalid username.";
                }
            } else {
                echo "Unknown error";
            }
            unset($stmt);
        }
    }
    unset($pdo);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Portal Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
</head>
<body>
    <h2>Login</h2>
    <p>Please submit your username and password to login.</p>
    <form action="login.php" method="post">
        <div class="form-group <?php echo (!empty($error)) ? 'has-error' : ''; ?>">
            <label>Username</label>
            <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
        </div>
        <div class="form-group <?php echo (!empty($error)) ? 'has-error' : ''; ?>">
            <label>Password</label>
            <input type="password" name="password" class="form-control">
        </div>
        <div class="form-group">
             <input type="submit" class="btn btn-primary" value="Login">
        </div>
    </form>
</body>
</html>
