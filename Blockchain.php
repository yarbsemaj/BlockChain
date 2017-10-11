<?php
namespace Blockchain;

class Blockchain
{
    private $filename;
    private $magic;
    private $hashalg;
    private $hashlen;
    private $blksize;
    private $dificulty;

    function __construct($filename,$dificulty = 4, $magic = 0xD5E8A97F, $hashalg = 'sha256', $hashlen = 32, $blksize = 17){
        $this->filename= $filename;
        $this->magic = $magic;
        $this->hashalg = $hashalg;
        $this->hashlen = $hashlen;
        $this->blksize = $blksize+$hashlen;
        $this->dificulty = $dificulty;
    }
    public function getChain()
    {
        if (!file_exists($this->filename)) {
            return null;
        }
        $size = filesize($this->filename);
        $fp = fopen($this->filename, 'rb');

        $height = 0;
        $blockChain = array();
        while (ftell($fp) < $size) {

            $header = fread($fp, ($this->blksize));

            $nonce = $this->unpack32($header, 0);
            $magic = $this->unpack32($header, 4);
            $version = ord($header[4]);
            $timestamp = $this->unpack32($header, 9);
            $prevhash = bin2hex(substr($header, 13, $this->hashlen));
            $datalen = $this->unpack32($header, -4);
            @$data = fread($fp, $datalen);
            $hash = hash($this->hashalg, $header . $data);

            $blockChain[] = array(
                "nonce"=>$nonce,
                "height" => ++$height,
                "magic" => dechex($magic),
                "version" => $version,
                "timestamp" => $timestamp,
                "prevhash" => $prevhash,
                "datalen" =>$datalen,
                "blockhash" => $hash,
                "data" => $data);
        }
        fclose($fp);
        return $blockChain;
    }

    public function isValid(){
        //the 1st previous hash should be repeated 0's
        $blockHash = str_repeat('0', $this->hashlen);
        //setup nonce correct nonce
        $check = str_repeat('0', $this->dificulty);
        //if chain is not empty
        if(count ($this->getChain())!=0)
        foreach ($this->getChain() as $block){
            $subhash = substr($blockHash,0,$this->dificulty);
            //if blocks previous has value is not the same as the next hash go to next
            if($blockHash != $block["prevhash"]||$subhash != $check) {
                return false;
            }else {
                //set hash of current block to the previous block var
                $blockHash = $block["blockhash"];
            }
        }
        return true;
    }

    private function unpack32($data,$ofs) {
        return unpack('V', substr($data,$ofs,4))[1];
    }

    public function addBlock($data)
    {
        $indexfn = $this->filename . '.idx';
        if (file_exists($this->filename)) {
            // get bit location of last block from index file
            if (!$ix = fopen($indexfn, 'r+b')) return ("Can't open " . $indexfn);
            $maxblock = unpack('V', fread($ix, 4))[1];
            $zpos = (($maxblock * 8) - 4);
            fseek($ix, $zpos, SEEK_SET);
            $ofs = unpack('V', fread($ix, 4))[1];
            $len = unpack('V', fread($ix, 4))[1];
            // read last block and calculate hash
            if (!$bc = fopen($this->filename, 'r+b')) return ("Can't open " . $this->filename);
            fseek($bc, $ofs, SEEK_SET);
            $block = fread($bc, $len);
            $hash = hash($this->hashalg, $block);
            // add new block to the end of the chain
            fseek($bc, 0, SEEK_END);
            //get start of block
            $pos = ftell($bc);
            $block = $this->calculateNonce($data,$hash);
            $this->write_block($bc,$block);
            fclose($bc);
            // update index
            $this->update_index($ix, $pos, strlen($data), ($maxblock + 1));
            fclose($ix);
            return TRUE;
        } else {
            //setup genesis
            $bc = fopen($this->filename, 'wb');
            $ix = fopen($indexfn, 'wb');
            $block = $this->calculateNonce($data,str_repeat('00',$this->hashlen));
            $this->write_block($bc,$block);
            $this->update_index($ix, 0, strlen($data), 1);
            fclose($bc);
            fclose($ix);
            return TRUE;
        }
    }

    private function write_block(&$fp, $data)
    {
        fwrite($fp, $data);                // Data
    }

    private function constructBlock($data, $prevhash, $nonce)
    {
        $block = mb_strcut(pack('V', $nonce), 0,4);
        $block .= mb_strcut( pack('V', $this->magic),0, 4);                // Magic
        $block .= mb_strcut(chr(1), 0,1);                           // Version
        $block .= mb_strcut(pack('V', time()), 0,4);                // Timestamp
        $block .= mb_strcut(hex2bin($prevhash),0, $this->hashlen);        // Previous Hash
        $block .= mb_strcut(pack('V', strlen($data)), 0,4);         // Data Length
        $block .= mb_strcut($data,0, strlen($data));                // Data
        return $block;
    }

    private function update_index(&$fp, $pos, $datalen, $count)
    {
        fseek($fp, 0, SEEK_SET);
        fwrite($fp, pack('V', $count), 4);                // Record count
        fseek($fp, 0, SEEK_END);
        fwrite($fp, pack('V', $pos), 4);                  // Offset
        fwrite($fp, pack('V', ($datalen + $this->blksize)), 4);        // Length
    }

    private function calculateNonce($data, $prevhash){
        $nonce = 0;
        $check = str_repeat('0', $this->dificulty);
        while(true){
            $block = $this->constructBlock($data, $prevhash, $nonce);
            $subhash = substr((hash($this->hashalg, $block )),0,$this->dificulty);
            if($check===$subhash){
                return $block;
            }
            $nonce++;
        }
    }

    public function dumpIndex(){
        $indexfn = $this->filename . '.idx';
        $fp = fopen($indexfn,'rb');
        $records = unpack('V', fread($fp, 4))[1];

        for ($i=0;$i<$records;$i++) {
            $ofs = unpack('V', fread($fp, 4))[1];
            $len = unpack('V', fread($fp, 4))[1];
            print str_pad($i,5,' ',STR_PAD_RIGHT)."OFS: ".str_pad($ofs,8,' ',STR_PAD_RIGHT)." LEN: $len\n";
        }

        fclose($fp);
    }
}