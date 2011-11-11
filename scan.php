<?php
define("API", "http://login.7k7k.com/username_exists?username=");

class Scanner {
    private $_from;
    private $_to;
    private $_interval;
    private $_now;
    
    public function __construct($from = 10000, $to = 10000, $interval = 1) {
        $this->_from = $from;
        $this->_to = $to;
        $this->_interval = $interval;
        $this->_now = $this->_from;
    }
    
    private function _log($type = "error", $num, $data = "") {
        switch ($type) {
            case "free" :
                file_put_contents('free/' . $num, $data);
                break;
            case "used" :
                file_put_contents('used/' . $num, $data);
                break;
            case "error" :
            default :
                file_put_contents('error/' . $num, $data);
                break;
        }
    }
    
    private function _remote() {
        echo "remote " . API . $this->_now . " ....\r\n";
        
        $out;
        
        try {
            $out = file_get_contents(API . $this->_now);
        } catch(Exception $e) {
            $out = $e->getMessage();
        }
        
        echo "[onRemote] " . $out . "\r\n";
        
        return $out;
    }
    
    private function _doScan() {
        echo "doScan " . $this->_now . " ....\r\n";
        
        $out = $this->_remote();
        
        if (isset($out) && !empty($out)) {
            try {
                $out = json_decode($out);
            } catch(Exception $e) {
                $out = $e->getMessage();
            }
            
            if (is_object($out)) {
                if ($out->state == 1) {
                    $this->_log("free", $this->_now);
                } else {
                    $this->_log("used", $this->_now);
                }
                return true;
            }
        }
        
        $this->_log("error", $this->_now, $out);
        
        return false;
    }
    
    public function start() {
        echo "start " . $this->_now . " ....\r\n";
        if ($this->_now > $this->_to) {
            $this->stop();
            return;
        }
        
        
        $this->_doScan();
        
        ++$this->_now;
        
        echo "[sleep] " . $this->_interval . " seconds ...\r\n";
        
        sleep($this->_interval);
        
        $this->start();
    }
    
    public function stop() {
        echo "[onStop] ...\r\n";
    }
}

$myScan = new Scanner(848284, 999999, 0.5);
$myScan->start();
?>