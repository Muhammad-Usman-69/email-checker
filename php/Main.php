<?php

require_once "config.php";

class Main {
    protected $conn;
    protected $error = false;
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
            $this->Error("Connection failed. Please try later.");
        }
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

}