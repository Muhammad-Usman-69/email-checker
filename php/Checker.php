<?php

class Checker {

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
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyz'
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

    function saveToDb($id, $method, $type, $prompt, $url)
    {
        //stopper
        // return;

        // Taking current time
        date_default_timezone_set("Asia/Karachi");
        $time = date("Y:m:d g:i a");

        try {
            $sql = "INSERT INTO `prompts` (`id`, `method`, `type`, `prompt`, `url/result`, `time`) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssssss", $id, $method, $type, $prompt, $url, $time);
            $stmt->execute();
            $stmt->close();
            $this->conn->close();
        } catch (Exception $err) {
            //deleting file
            unlink($url);
            $this->Error("Couldn't save file. Please try later.");
        }
    }
}