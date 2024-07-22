<?php
require_once "config.php";
require_once "../vendor/autoload.php";
require_once './PHPMailer/Exception.php';
require_once './PHPMailer/PHPMailer.php';
require_once './PHPMailer/SMTP.php';

//sending email with pass
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class Checker
{
    protected $conn;
    protected $error = false;
    protected $dailyUse;

    function __construct()
    {
        $this->dbConnect(DATABASE_HOSTNAME, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
        $this->checkLimit();
    }

    function checkLimit()
    {
        //check if maximum limit is reached
        $sql = "SELECT * FROM `dailyusage`";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $use = $row["dailyuse"];
        $stmt->close();

        $this->dailyUse = $use;

        if ($use >= DAILYLIMIT) {
            $this->Error("Maximum daily limit (" . DAILYLIMIT . ") has been reached.");
        }
    }

    function checkUse($count)
    {
        if (($count + $this->dailyUse) > DAILYLIMIT) {
            $remaining = DAILYLIMIT - $this->dailyUse;
            $this->Error("Number of email will surpass the allowed limit. Remaining email use is: $remaining");
        }
    }

    function increaseUse($count)
    {
        $oldCount = $this->dailyUse;
        $newCount = $this->dailyUse + $count;
        // increasing use
        $sql = "UPDATE `dailyusage` SET `dailyuse` = $newCount WHERE `dailyuse` = $oldCount;";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stmt->close();
        $this->conn->close();
    }

    function resetUse() {
        // increasing use
        $sql = "UPDATE `dailyusage` SET `dailyuse` = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stmt->close();
        $this->conn->close();
    }

    protected function Error($err)
    {
        $this->error = $err;
        echo json_encode(["error" => $this->error]);
        exit();
    }

    function getResults()
    {
        try {
            $sql = "SELECT * FROM `tools`";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $names[] = $row;
            }
            $stmt->close();
            $this->conn->close();
            return $names;
        } catch (Exception $err) {
            $this->Error("Database connection failed.");
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

    function dbConnect($server, $user, $pass, $db)
    {
        try {
            $this->conn = new mysqli($server, $user, $pass, $db);
        } catch (Exception $err) {
            $this->Error("Connection failed. Please try later.");
        }
    }

    function saveToDb($id, $task_id, $method, $url)
    {
        //stopper
        // return;

        // Taking current time
        date_default_timezone_set("Asia/Karachi");
        $time = date("Y:m:d g:i a");

        try {
            $sql = "INSERT INTO `checks` (`check_id`, `task_id`, `method`, `url`, `time`) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssss", $id, $task_id, $method, $url, $time);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $err) {
            $this->Error("Couldn't save file. Please try later.");
        }
    }

    function saveTask($task_id)
    {
        // Taking current time
        date_default_timezone_set("Asia/Karachi");
        $time = date("Y:m:d g:i a");

        try {
            $sql = "INSERT INTO `tasks` (`task_id`, `task_time`) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ss", $task_id, $time);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $err) {
            $this->Error("Couldn't save file. Please try later.");
        }
    }

    function singleCheck($email, $apiKey)
    {
        $endpoint = "https://emailverifier.reoon.com/api/v1/verify?email=$email&key=$apiKey";
        $client = new Client();

        try {
            $response = $client->get($endpoint);
            $responseData = $response->getBody();

            //creating it
            $id = "check" . $this->random_num(4);

            //getting url
            $url = "../v1/json/$id.json";

            //writing data
            $fp = fopen($url, "w");
            fwrite($fp, $responseData);
            fclose($fp);

            return [
                "status" => "success",
                "id" => $id,
                "url" => $url,
                "check" => "single"
            ];
        } catch (ClientException $err) {
            $this->Error("Server not responding");
        }
    }

    function multipleCheck($emails, $apiKey)
    {
        $endpoint = "https://emailverifier.reoon.com/api/v1/create-bulk-verification-task/";
        $client = new Client();

        try {
            $response = $client->post($endpoint, [
                'json' => [
                    'emails' => $emails,
                    'key' => $apiKey
                ]
            ]);

            //check if success
            $responseData = $response->getBody();
            $data = json_decode($responseData);

            if ($data->{"status"} != "success") {
                $this->Error("Request failed, please try later");
            }

            //getting task id
            $task_id = $data->{"task_id"};

            return $task_id;
        } catch (ClientException $err) {
            $this->Error("Server not responding");
        }
    }

    function getMultipleResults($task_id, $apiKey)
    {
        //getting the data
        $endpoint = "https://emailverifier.reoon.com/api/v1/get-result-bulk-verification-task/?task_id=$task_id&key=$apiKey";

        $client = new Client();

        try {
            while (true) {
                $response = $client->get($endpoint);
                $responseData = $response->getBody();

                // Decode the JSON response
                $checkData = json_decode($responseData);

                // Check if completed
                $status = $checkData->status;

                if ($status == "completed") {
                    break; // Exit the loop if the status is "completed"
                }

                // Delay before the next check
                sleep(1); // Wait for 1 seconds before checking again
            }

            //creating it
            $id = "check" . $this->random_num(4);

            //getting url
            $url = "../v1/json/$id.json";

            //writing data
            $fp = fopen($url, "w");
            fwrite($fp, $responseData);
            fclose($fp);

            return [
                "status" => "success",
                "id" => $id,
                "url" => $url,
                "check" => "multiple"
            ];
        } catch (ClientException $err) {
            $this->Error("Server not responding");
        }
    }

    function download($id, $status)
    {
        header("content-type: json");

        //check if exist
        try {
            $sql = "SELECT * FROM `checks` WHERE `check_id` = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $checkResult = $stmt->get_result();
            $checkRow = $checkResult->num_rows;
            $resultAssoc = $checkResult->fetch_assoc();
            $stmt->close();

            if ($checkRow == 0) {
                $this->Error("No such file exists");
            }
        } catch (Exception $err) {
            $this->Error("Couldn't save file. Please try later.");
        }

        $json = file_get_contents($resultAssoc["url"]);
        $data = json_decode($json);

        $headers = "Email,Status\n"; // "," seperate the column

        $stdResult = $data->{"results"};

        //getting object keys
        $results = get_object_vars($stdResult);

        $rows = "";

        //looping through results
        foreach ($results as $result) {
            $email = $result->{"email"};
            $emailStatus = $result->{"status"};
            //pushing to rows if all
            if ($status == "all") {
                $rows .= "$email,$emailStatus\n";
            }
            //pushing safe
            if (($emailStatus == "safe" || $emailStatus == "valid") && $status != "all") {
                $rows .= "$email,$emailStatus\n";
            }
        }

        //ecohing result
        echo $headers;
        echo $rows;

        header("content-type: application/csv");
        header("Content-Disposition: attachment; filename=$id.csv");

        $this->conn->close();
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

        $this->conn->close();

        echo "deleted";
    }

    function verifyPass($pass)
    {
        //for tasks
        $sql = "SELECT * FROM `password`";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        // $phash = password_hash($pass, PASSWORD_DEFAULT);
        if (password_verify($pass, $row["password"])) {
            session_start();
            $_SESSION["allow"] = true; //setting session
            return true;
        } else {
            return false;
        }
    }

    function history()
    {

        $sql = "SELECT * FROM `checks`";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $num = $result->num_rows;
        //if exists
        if ($num == 0) {
            $this->Error("Nothing found");
        }
        //getting data
        while ($row = $result->fetch_assoc()) {
            $id = $row["check_id"];
            $time = $row["time"];
            $method = $row["method"];
            $url = $row["url"];
            $arr[] = [
                "id" => $id,
                "time" => $time,
                "method" => $method,
                "url" => $url
            ];
        }
        ;
        $stmt->close();
        $this->conn->close();

        return $arr;
    }

    function changePass()
    {
        session_start();

        //creating new pass
        $pass = $this->random_str(15);

        //creating hash for that pass
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        // Create a PHPMailer instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
            ; // Disable verbose debug output
            $mail->isSMTP(); // Send using SMTP
            $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = 'meraki4446996@gmail.com'; // SMTP username
            $mail->Password = 'eqvv wxjk mkht rcxw'; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable TLS encryption
            $mail->Port = 465; // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            $mail->Timeout = 1;

            // Recipients
            $mail->setFrom('meraki4446996@gmail.com', 'Email Checker');
            $mail->addAddress("usmansaleem4446996@gmail.com", "User");

            //not showing errors
            $mail->SMTPDebug = false;

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'Reset Password';
            $mail->Body = "Your new password is this: $pass <br> Confirm through this link: 
            <a target='_blank' href='{$_SERVER["SERVER_NAME"]}/php/verify.php?new_pass=$hash'>Confirm</a>";

            // Send email
            $mail->send();

            //creating hash session for new pass
            $_SESSION["new_pass"] = $hash;

            return true;
        } catch (Exception $e) {
            echo $e;
            $this->Error("Request failed. Please try later");
        }
    }

    function savePass($hash)
    {
        try {
            $sql = "UPDATE `password` SET `password` = ?;";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $hash);
            $stmt->execute();
            $stmt->close();
            return true;
        } catch (Exception $err) {
            $this->Error("Couldn't save. Please try again.");
        }
    }
}