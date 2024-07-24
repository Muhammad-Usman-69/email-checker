<?php

require "Main.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Verification extends Main
{
    function verifyPass($pass)
    {
        $sql = "SELECT * FROM `tuple` WHERE `id` = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (password_verify($pass, $row["password"])) {
            session_start();
            $_SESSION["allow"] = true; //setting session
            return true;
        } else {
            $this->Error(null, "Wrong password");
            return false;
        }
    }

    function changePass()
    {
        //creating new pass
        $pass = $this->random_str(15);

        //encrypting it
        $encrypt = $this->encryptIt($pass);

        // Create a PHPMailer instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
            ; // Disable verbose debug output
            $mail->isSMTP(); // Send using SMTP
            $mail->Host = SMTPHOST; // Set the SMTP server to send through
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = SMTPUSERNAME; // SMTP username
            $mail->Password = SMTPPASSWORD; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable TLS encryption
            $mail->Port = 465; // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            $mail->Timeout = 1;

            // Recipients
            $mail->setFrom(SMTPUSERNAME, 'Email Checker');
            $mail->addAddress(DEFAULTEMAIL, "User");

            //not showing errors
            $mail->SMTPDebug = false;

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'Reset Password';
            $mail->Body = "Your new password is this: $pass <br> Confirm through this link: 
            <a target='_blank' href='{$_SERVER["SERVER_NAME"]}/php/verify.php?key=$encrypt'>Confirm</a>";

            // Send email
            $mail->send();

            //saving temp pass
            $sql = "UPDATE `tuple` SET `temp_key` = ? WHERE `id` = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $encrypt);
            $stmt->execute();
            $stmt->close();

            return true;
        } catch (Exception $err) {
            $display_error = "Request to change password failed. Please try later.";
            $this->Error($display_error, "$display_error<br>$err");
        }
    }

    protected function savePass($key)
    {
        $pass = $this->decryptIt($key);
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        try {
            $sql = "UPDATE `tuple` SET `password` = ? WHERE `id` = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $hash);
            $stmt->execute();
            $stmt->close();
            $this->clearKey(); //clearing temp
        } catch (Exception $err) {
            $display_error = "Couldn't save the password. Please try again.";
            $this->Error($display_error, "$display_error<br>$err");
        }
    }

    function verifyKey($key)
    {
        $sql = "SELECT * FROM `tuple` WHERE `id` = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($key != $row["temp_key"]) {
            $this->clearKey(); //clearing temp
            $display_error = "Access denied.";
            $this->Error($display_error, "$display_error<br>Someone is trying to change password");
        }

        $this->savePass($key);

        return true;
    }

    function clearKey()
    {
        $sql = "UPDATE `tuple` SET `temp_key` = '' WHERE `id` = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stmt->close();
    }

    function encryptIt($s)
    {
        $cryptKey = CRYPTKEY;
        $iv = substr(md5($cryptKey), 0, 16);
        $encrypted = openssl_encrypt($s, 'aes-256-cbc', md5($cryptKey), 0, $iv);
        return base64_encode($encrypted);
    }

    function decryptIt($s)
    {
        $cryptKey = CRYPTKEY;
        $iv = substr(md5($cryptKey), 0, 16);
        $decrypted = openssl_decrypt(base64_decode($s), 'aes-256-cbc', md5($cryptKey), 0, $iv);
        return $decrypted;
    }
}