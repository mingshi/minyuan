<?php
class BinaryProtocol
{
    const DATA = 0x00;
    const HB_REQUEST = 0x01;
    const HB_RESPONSE = 0x02;
    public $bodyLen;
    public $seq;
    public $type;
    public $version;
    public $reserve_1;
    public $reserve_2;
    public $data;

    static public function SendRecieve($address, $packet)
    {
        $tmp = explode(":", $address);
        if (count($tmp) != 2) {
            return false;
        }
        $ip = $tmp[0];
        $port = $tmp[1];
        $sock = socket_create(AF_INET, SOCK_STREAM, 0);
        if (false == $sock) {
            log_message(socket_strerror(socket_last_error($sock)), LOG_ERR);
            return false;
        }
        $result = socket_connect($sock, $ip, $port);
        if (false == $result) {
            log_message(socket_strerror(socket_last_error($sock)), LOG_ERR);
            return false;
        }
        $stream = $packet->packData();
        $result = socket_write($sock, $stream);
        if (false == $result) {
            log_message(socket_strerror(socket_last_error($sock)), LOG_ERR);
            return false;
        }
        $response = "";
        $result = socket_read($sock, 4);
        if (false == $result) {
            log_message(socket_strerror(socket_last_error($sock)), LOG_ERR);
            return false;
        }
        $tmp = unpack("N", $result);
        $len = $tmp[1];
        $response .= $result;
        $result = socket_read($sock, $len - 4);
        if (false == $result) {
            log_message(socket_strerror(socket_last_error($sock)), LOG_ERR);
        }
        $response .= $result;
        $package = new BinaryProtocol();
        $package->unpackData($response);
        return $package;
    }

    public function __construct()
    {
        $this->bodyLen = 0;
        $this->seq = 0;
        $this->type = 0;
        $this->version = 0;
        $this->reserve_1 = 0;
        $this->reserve_2 = 0;
    }

    public function packData()
    {
        $result = "";
        $this->bodyLen = 16 + strlen($this->data);
        $result = pack("NNCCnN", $this->bodyLen, $this->seq, $this->type, $this->version, $this->reserve_1, $this->reserve_2);
        $result .= $this->data;
        return $result;
    }

    public function unpackData($data)
    {
        $tmp = unpack("NbodyLen/Nseq/Ctype/Cversion/nreserve_1/Nreserve_2/a*data", $data);
        $this->bodyLen = $tmp["bodyLen"];
        $this->seq = $tmp["seq"];
        $this->type = $tmp["type"];
        $this->version = $tmp["version"];
        $this->reserve_1 = $tmp["reserve_1"];
        $this->reserve_2 = $tmp["reserve_2"];
        $this->data = $tmp["data"];
    }
}
