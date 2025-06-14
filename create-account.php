<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/signup.css">

    <title>Create Account</title>
    <style>
        .container {
            animation: transitionIn-X 0.5s;
        }

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

    session_start();

    $_SESSION["user"] = "";
    $_SESSION["usertype"] = "";

    // Set the new timezone
    date_default_timezone_set('Asia/Kolkata');
    $date = date('Y-m-d');
    $_SESSION["date"] = $date;

    //import database
    include("connection.php");

    // Initialize error message
    $error = '';

    if ($_POST) {

        // Get the form data
        $fname = $_SESSION['personal']['fname'];
        $lname = $_SESSION['personal']['lname'];
        $name = $fname . " " . $lname;
        $address = $_SESSION['personal']['address'];
        $nic = $_SESSION['personal']['nic'];
        $dob = $_SESSION['personal']['dob'];
        $email = $_POST['newemail'];
        $tele = $_POST['tele'];
        $newpassword = $_POST['newpassword'];
        $cpassword = $_POST['cpassword'];

        // Initialize session values to keep the form inputs
        $_SESSION['personal'] = array(
            'fname' => $fname,
            'lname' => $lname,
            'address' => $address,
            'nic' => $nic,
            'dob' => $dob,
            'newemail' => $email,
            'tele' => $tele
        );

        // Validate password
        if ($newpassword == $cpassword) {

            // Remove any non-numeric characters from the phone number
            $tele = preg_replace('/\D/', '', $tele); // Removes all non-digits

            // Check if mobile number is valid
            if ((substr($tele, 0, 3) == '011' && strlen($tele) != 11) || (substr($tele, 0, 3) != '011' && strlen($tele) != 10)) {
                $error = '<label for="promter" class="form-label error-message">Invalid mobile number! Please ensure the number format is correct.</label>';
            } else {

                // Check if email, NIC, or mobile number already exists
                $stmt = $database->prepare("SELECT * FROM patient WHERE pemail = ? OR pnic = ? OR ptel = ?");
                $stmt->bind_param("sss", $email, $nic, $tele);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $error = '<label for="promter" class="form-label error-message">Email, NIC, or Mobile number already exists. Please try again.</label>';
                } else {

                    // Insert into patient and webuser tables
                    $sqlmain = "INSERT INTO patient (pemail, pname, ppassword, paddress, pnic, pdob, ptel) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $database->prepare($sqlmain);
                    $stmt->bind_param("sssssss", $email, $name, $newpassword, $address, $nic, $dob, $tele);
                    $stmt->execute();

                    $stmt = $database->prepare("INSERT INTO webuser (email, usertype) VALUES (?, 'p')");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();

                    // Set session variables and redirect to the patient dashboard
                    $_SESSION["user"] = $email;
                    $_SESSION["usertype"] = "p";
                    $_SESSION["username"] = $fname;

                    header('Location: patient/index.php');
                    exit;
                }
            }
        } else {
            $error = '<label for="promter" class="form-label error-message">Password Confirmation Error! Please reconfirm your password.</label>';
        }
    }

    ?>

    <center>
        <div class="container">
            <table border="0" style="width: 69%;">
                <tr>
                    <td colspan="2">
                        <p class="header-text">Let's Get Started</p>
                        <p class="sub-text">It's okay, now create your user account.</p>
                    </td>
                </tr>
                <tr>
                    <form action="" method="POST">
                        <td class="label-td" colspan="2">
                            <label for="newemail" class="form-label">Email: </label>
                        </td>
                </tr>
                <tr>
                    <td class="label-td" colspan="2">
                        <input type="email" name="newemail" class="input-text" placeholder="Email Address" value="<?php echo isset($_SESSION['personal']['newemail']) ? $_SESSION['personal']['newemail'] : ''; ?>" required>
                    </td>
                </tr>
                <tr>
                    <td class="label-td" colspan="2">
                        <label for="tele" class="form-label">Mobile Number: </label>
                    </td>
                </tr>
                <tr>
                    <td class="label-td" colspan="2">
                        <input type="tel" name="tele" class="input-text" placeholder="ex: 0712345678"
                            pattern="^(011\d{8}$|0\d{9})$"
                            value="<?php echo isset($_SESSION['personal']['tele']) ? $_SESSION['personal']['tele'] : ''; ?>" required>
                    </td>
                </tr>
                <tr>
                    <td class="label-td" colspan="2">
                        <label for="newpassword" class="form-label">Create New Password: </label>
                    </td>
                </tr>
                <tr>
                    <td class="label-td" colspan="2">
                        <input type="password" name="newpassword" class="input-text" placeholder="New Password" required>
                    </td>
                </tr>
                <tr>
                    <td class="label-td" colspan="2">
                        <label for="cpassword" class="form-label">Confirm Password: </label>
                    </td>
                </tr>
                <tr>
                    <td class="label-td" colspan="2">
                        <input type="password" name="cpassword" class="input-text" placeholder="Confirm Password" required>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <?php echo $error; ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <input type="reset" value="Reset" class="login-btn btn-primary-soft btn">
                    </td>
                    <td>
                        <input type="submit" value="Sign Up" class="login-btn btn-primary btn">
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