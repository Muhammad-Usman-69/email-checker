<?php

require_once "config.php";
require_once './PHPMailer/Exception.php';
require_once './PHPMailer/PHPMailer.php';
require_once './PHPMailer/SMTP.php';

//sending email with pass
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Main
{
    protected $conn;
    protected $dailyUse;

    function __construct()
    {
        $this->dbConnect(DATABASE_HOSTNAME, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
        $this->checkLimit();
    }

    function __destruct()
    {
        $this->conn->close();
    }

    function dbConnect($server, $user, $pass, $db)
    {
        try {
            $this->conn = new mysqli($server, $user, $pass, $db);
        } catch (Exception $err) {
            $this->Error("Internal connection failed.", "Database connection failed.<br>$err");
        }
    }

    protected function Error($display_error, $techincal_error)
    {
        $this->sendError($techincal_error); //sending error to email
        
        if ($display_error != null) {
            header("content-type: application/json");
            echo json_encode(["error" => $display_error]); //showing display error
            exit(); //exiting script
        }

    }

    function random_str(
        $length,
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ) {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        if ($max < 1) {
            throw new Exception('$keyspace must be at least two characters long');
        }
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    function random_num(
        $length,
        $keyspace = '0123456789'
    ) {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        if ($max < 1) {
            throw new Exception('$keyspace must be at least two characters long');
        }
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    function delete($until)
    {
        //for checks
        $sql = "SELECT * FROM `checks` WHERE `time` < ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $until);
        $stmt->execute();
        $result = $stmt->get_result();
        $num = $result->num_rows;
        //if exists
        if ($num != 0) {
            while ($row = $result->fetch_assoc()) {
                //deleting files
                unlink($row["url"]);
                //deleting from db
                $sql = "DELETE FROM `checks` WHERE `check_id` = '{$row["check_id"]}'";
                $stmt2 = $this->conn->prepare($sql);
                $stmt2->execute();
                $stmt2->close();
            }
            ;
        }
        $stmt->close(); //closing

        //for tasks
        $sql = "DELETE FROM `tasks` WHERE `task_time` < ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $until);
        $stmt->execute();
        $stmt->close();

        echo "deleted";
    }

    function checkLimit()
    {
        //check if maximum limit is reached
        $sql = "SELECT * FROM `tuple` WHERE `id` = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $use = $row["dailyuse"];
        $stmt->close();

        $this->dailyUse = $use;

        if ($use >= DAILYLIMIT) {
            $display_error = "Maximum daily limit (" . DAILYLIMIT . ") has been reached.";
            $this->Error($display_error, $display_error);
        }
    }

    function checkUse($count)
    {
        if (($count + $this->dailyUse) > DAILYLIMIT) {
            $remaining = DAILYLIMIT - $this->dailyUse;

            $display_error = "Number of email will surpass the allowed limit. Remaining email use is: $remaining";
            $this->Error($display_error, $display_error);
        }
    }

    function increaseUse($count)
    {
        $oldCount = $this->dailyUse;
        $newCount = $this->dailyUse + $count;
        // increasing use
        $sql = "UPDATE `tuple` SET `dailyuse` = $newCount WHERE `id` = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stmt->close();
    }

    function resetUse()
    {
        // increasing use
        $sql = "UPDATE `tuple` SET `dailyuse` = 0 WHERE `id` = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stmt->close();
        echo "reset successful";
    }

    function sendError($techincal_error)
    {
        if ($techincal_error == null) {
            return;
        }

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
            $mail->Subject = 'Error';
            $mail->Body = "$techincal_error";

            // Send email
            $mail->send();
        } catch (Exception $err) {
            return;
        }
    }
}