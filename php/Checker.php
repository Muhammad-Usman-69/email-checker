<?php

require "./Main.php";
require_once "../vendor/autoload.php";

use GuzzleHttp\Client;

class Checker extends Main
{
    function saveToDb($id, $task_id, $method, $url, $temp)
    {
        //stopper
        // return;

        // Taking current time
        date_default_timezone_set("Asia/Karachi");
        $time = date("Y:m:d G:i:s");

        try {
            $sql = "INSERT INTO `checks` (`check_id`, `task_id`, `method`, `url`, `temp`, `time`) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssssss", $id, $task_id, $method, $url, $temp, $time);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $err) {
            $this->Error("Couldn't save the check. Please try later.");
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
            $this->Error("Couldn't save the task. Please try later.");
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
        } catch (Exception $err) {
            $this->Error("External server (reoon) is not responding. Please try later.");
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
        } catch (Exception $err) {
            $this->Error("External server (reoon) is not responding. Please try later.");
        }

        //check if success
        $responseData = $response->getBody();
        $data = json_decode($responseData);

        if ($data->{"status"} != "success") {
            $this->Error("Request failed, please try later");
        }

        //getting task id
        $task_id = $data->{"task_id"};

        return $task_id;
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
        } catch (Exception $err) {
            $this->Error("External server (reoon) is not responding. Failed to retrieve results. Please try later.");
        }

        //creating it
        $num = $this->random_num(4);
        $id = "check$num";

        //getting url
        $url = "../v1/json/$id.json";

        //writing data
        $fp = fopen($url, "w");
        fwrite($fp, $responseData);
        fclose($fp);

        return [
            "status" => "success",
            "id" => $id,
            "num" => $num,
            "url" => $url,
            "check" => "multiple"
        ];
    }

    function writeRow($fp, $row)
    {
        foreach ($row as $column) {
            fwrite($fp, "\"$column\",");
        }
    }

    function download($id, $status)
    {
        //check if exist
        $sql = "SELECT * FROM `checks` WHERE `check_id` = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $checkResult = $stmt->get_result();
        $checkRow = $checkResult->num_rows;
        $resultAssoc = $checkResult->fetch_assoc();
        $stmt->close();

        if ($checkRow == 0) {
            $this->Error("No such file with this id exists.");
        }

        if ($resultAssoc["method"] == "File" && ($resultAssoc["temp"] != "none")) {
            // if csv

            $i = 0; //for increamenting
            $indexFound = false;  //for email index status
            $emailIndex = 0;  //for email index

            $csv = @fopen($resultAssoc["temp"], "r"); //opening file for read

            //handling error
            if ($csv == false) {
                $this->Error("Your csv file doesn't exist.");
            }

            //handling file and pushing data
            while (($data = fgetcsv($csv, 2000, ",")) !== FALSE) {
                $csvRows[] = $data;
            }

            fclose($csv); //closing read file

            //creating temp file url for download
            $url = "../v1/temp/download" . $this->random_num(5) . ".csv";

            //opening url writing headers to it
            $fp = fopen($url, "w+");

            foreach ($csvRows[0] as $header) {
                fwrite($fp, "\"$header\",");

                //taking email index
                if (($header == "email" || $header == "EMAIL" || $header == "Email") && $indexFound == false) {
                    $indexFound = true;
                    $emailIndex = $i;
                }
                $i++;
            }

            $i = 0; //marking it as zero

            //writing status and then ending the line
            fwrite($fp, "\"Status\"\n");

            // now getting json file
            $csvFile = @file_get_contents($resultAssoc["url"]);

            if ($csvFile == false) {
                unlink($url);
                $this->Error("Your result file doesn't exist.");
            }

            $json = json_decode($csvFile);
            $stdResult = $json->{"results"};
            $results = get_object_vars($stdResult); //getting object keys


            //looping through rows and matching each with results
            foreach ($csvRows as $row) {

                foreach ($results as $result) {
                    $email = $result->{"email"};
                    $emailStatus = $result->{"status"};
                    $emailFound = false;

                    //check if found
                    if (str_contains($row[$emailIndex], $email) && $emailFound == false) {

                        //writing row and then its status and ending it
                        if (($emailStatus == "safe" || $emailStatus == "valid") && $status != "all") {
                            $this->writeRow($fp, $row);
                            fwrite($fp, "\"$emailStatus\"\n");
                        }

                        //pushing to rows if all
                        if ($status == "all") {
                            $this->writeRow($fp, $row);
                            fwrite($fp, "\"$emailStatus\"\n");
                        }
                    }
                }

                if ($i != 0 && $status == "all") {
                    $this->writeRow($fp, $row);
                    fwrite($fp, "\"Not Found\"\n");
                }

                $i++;
            }

            //closing file
            fclose($fp);

            //getting file, echoing and deleting temp file
            $file = file_get_contents($url);
            unlink($url);

            header("content-type: application/csv");
            header("Content-Disposition: attachment; filename=$id.csv");
            
            echo $file;

        } else {

            // if multiple
            $json = @file_get_contents($resultAssoc["url"]);

            if ($json == false) {
                $this->Error("Your result file doesn't exist.");
            }

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
                    $rows .= "$email," . ucwords($emailStatus) . "\n";
                }
                //pushing safe
                if (($emailStatus == "safe" || $emailStatus == "valid") && $status != "all") {
                    $rows .= "$email," . ucwords($emailStatus) . "\n";
                }
            }

            header("content-type: application/csv");
            header("Content-Disposition: attachment; filename=$id.csv");

            //ecohing result
            echo $headers;
            echo $rows;
        }
    }

    function history()
    {
        $sql = "SELECT * FROM `checks` ORDER BY `checks`.`time` ASC";
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

        return $arr;
    }
}