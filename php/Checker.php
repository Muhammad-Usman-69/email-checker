<?php

require_once ("../vendor/autoload.php");

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class Checker
{
    protected $conn;
    protected $error = false;

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
        $keyspace = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'
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
            echo $err;
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
            //std class ka kam krna ha
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
                sleep(1); // Wait for 10 seconds before checking again
            }

            //creating it
            $id = $this->random_str(10);

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
}