<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/signup.css">
        
    <title>Sign Up</title>
    <style>
        .error-message {
            color: red;
            font-size: 12px;
            text-align: center;
        }
    </style>

</head>
<body>
<?php

//learn from w3schools.com
//Unset all the server side variables
// Initialize error message variable
$error = '';

session_start();

$_SESSION["user"]="";
$_SESSION["usertype"]="";

// Set the new timezone
date_default_timezone_set('Asia/Kolkata');
$date = date('Y-m-d');
$_SESSION["date"]=$date;

// Include database connection
include("connection.php");

if ($_POST) {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $name = $fname . " " . $lname;
    $address = $_POST['address'];
    $nic = $_POST['nic'];
    $dob = $_POST['dob'];
    
    // Initialize the session values with the form data to preserve them
    $_SESSION['personal'] = array(
        'fname' => $fname,
        'lname' => $lname,
        'address' => $address,
        'nic' => $nic,
        'dob' => $dob
    );
    
    // Check if name or NIC already exists in the database
    $stmt = $database->prepare("SELECT * FROM patient WHERE pname = ? OR pnic = ?");
    $stmt->bind_param("ss", $name, $nic);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Set error message for specific fields if they are duplicated
        $error = '<label for="promter" class="form-label error-message">Name or NIC already exists in the database. Please try again.</label>';
    } else {
        // Store the personal details in session for use in create-account.php
        $_SESSION["personal"] = array(
            'fname' => $fname,
            'lname' => $lname,
            'address' => $address,
            'nic' => $nic,
            'dob' => $dob
        );
        // Redirect to create-account.php
        header("location: create-account.php");
        exit;
    }
}
?>
    <center>
    <div class="container">
        <table border="0">
            <tr>
                <td colspan="2">
                    <p class="header-text">Let's Get Started</p>
                    <p class="sub-text">Add Your Personal Details to Continue</p>
                </td>
            </tr>
            <tr>
                <form action="" method="POST">
                <td class="label-td" colspan="2">
                    <label for="name" class="form-label">Name: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <input type="text" name="fname" class="input-text" placeholder="First Name" value="<?php echo isset($_SESSION['personal']['fname']) ? $_SESSION['personal']['fname'] : ''; ?>" required>
                </td>
                <td class="label-td">
                    <input type="text" name="lname" class="input-text" placeholder="Last Name" value="<?php echo isset($_SESSION['personal']['lname']) ? $_SESSION['personal']['lname'] : ''; ?>" required>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="address" class="form-label">Address: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="text" name="address" class="input-text" placeholder="Address" value="<?php echo isset($_SESSION['personal']['address']) ? $_SESSION['personal']['address'] : ''; ?>" required>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="nic" class="form-label">NIC: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="text" name="nic" class="input-text" placeholder="NIC Number" value="<?php echo isset($_SESSION['personal']['nic']) ? $_SESSION['personal']['nic'] : ''; ?>" required>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="dob" class="form-label">Date of Birth: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="date" name="dob" class="input-text" value="<?php echo isset($_SESSION['personal']['dob']) ? $_SESSION['personal']['dob'] : ''; ?>" required>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <?php echo $error; ?>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="reset" value="Reset" class="login-btn btn-primary-soft btn" >
                </td>
                <td>
                    <input type="submit" value="Next" class="login-btn btn-primary btn">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <br>
                    <label for="" class="sub-text" style="font-weight: 280;">Already have an account&#63; </label>
                    <a href="login.php" class="hover-link1 non-style-link">Login</a>
                    <br><br><br>
                </td>
            </tr>
                </form>
            </tr>
        </table>

    </div>
</center>
</body>
</html>