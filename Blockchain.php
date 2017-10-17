<?php
namespace Blockchain;

class Blockchain
{
    private $filename;
    private $magic;
    private $hashalg;
    private $hashlen;
    private $blksize;
    private $difficulty;

    /**
     * Blockchain constructor.
     * @param string $filename name of the block cain file
     * @param int $difficulty the difficulty of the nonce
     * @param int $magic a value unique to each bloch chain
     * @param string $hashalg the language of the hash to be used
     * @param int $hashlen the length of the hash to be used
     * @param int $blksize the total size of the block less the hash length and data length
     */
    function __construct($filename, $difficulty = 4, $magic = 0xD5E8A97F, $hashalg = 'sha256', $hashlen = 32, $blksize = 17){
        $this->filename= $filename;
        $this->magic = $magic;
        $this->hashalg = $hashalg;
        $this->hashlen = $hashlen;
        $this->blksize = $blksize+$hashlen;
        $this->difficulty = $difficulty;
    }

    /**
     * Gets an array of all blocks in the chain
     *
     * @return array|null an array containing all blocks in the chain
     */
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


    /**
     * Checked the validity of all hashed in the loaded block chain.
     *
     * @return bool true if the block chain is valid, false if it is not
     */
    public function isValid(){
        //the 1st previous hash should be repeated 0's
        $previousHash = str_repeat('0', $this->hashlen);
        //setup nonce correct nonce
        $check = str_repeat('0', $this->difficulty);
        //if chain is not empty
        if(count ($this->getChain())!=0)
        foreach ($this->getChain() as $block){
            $subhash = substr($previousHash,0,$this->difficulty);
            //if blocks previous hash value is not the same as the next blocks hash or does not start with
            // @$this->dificulty '0''s @return false
            if($previousHash != $block["prevhash"]||$subhash != $check) {
                return false;
            }else {
                //set hash of current block to the previous block var
                $previousHash = $block["blockhash"];
            }
        }
        return true;
    }

    /**
     * @param string $data data to be unpacked
     * @param int $ofs the offset in the string @pram $data is stored at
     * @return mixed array an associative array containing unpacked elements of binary string.
     */
    private function unpack32($data, $ofs) {
        return unpack('V', substr($data,$ofs,4))[1];
    }

    /**
     * Adds data to the block chain
     *
     * @param string $data the datta to be added to the block chain
     * @return bool|string the block was added successfully or the appropriate error message
     */
    public function addBlock($data)
    {
        //gets the path to the index file
        $indexfn = $this->filename . '.idx';
        if (file_exists($this->filename)) {
            //adding block to an existing chain
            // get bit location of last block from index file
            if (!$ix = fopen($indexfn, 'r+b')) return ("Can't open " . $indexfn);
            //get the position of the start of the last block to be added from the index file
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
            $this->writeBlock($bc,$block);
            fclose($bc);
            // update index
            $this->updateIndex($ix, $pos, strlen($data), ($maxblock + 1));
            fclose($ix);
            return TRUE;
        } else {
            //setup genesis
            $bc = fopen($this->filename, 'wb');
            $ix = fopen($indexfn, 'wb');
            $block = $this->calculateNonce($data,str_repeat('00',$this->hashlen));
            $this->writeBlock($bc,$block);
            $this->updateIndex($ix, 0, strlen($data), 1);
            fclose($bc);
            fclose($ix);
            return TRUE;
        }
    }

    /**
     * Writes a new block to disk
     * @param resource $fp pointer to block chain file
     * @param string $data block to add
     */
    private function writeBlock(&$fp, $data)
    {
        fwrite($fp, $data);
    }

    /**
     * Create a full formed block
     *
     * @param string $data block data
     * @param string $prevhash the hash of the previous block
     * @param int $nonce
     * @return string contents of the block
     */
    private function constructBlock($data, $prevhash, $nonce)
    {
        $block = mb_strcut(pack('V', $nonce), 0,4);             //Nonce
        $block .= mb_strcut( pack('V', $this->magic),0, 4);     // Magic
        $block .= mb_strcut(chr(1), 0,1);                         // Version
        $block .= mb_strcut(pack('V', time()), 0,4);            // Timestamp
        $block .= mb_strcut(hex2bin($prevhash),0, $this->hashlen);             // Previous Hash
        $block .= mb_strcut(pack('V', strlen($data)), 0,4);     // Data Length
        $block .= mb_strcut($data,0, strlen($data));                           // Data
        return $block;
    }

    /**
     * Updates the index file once a block has been added
     * @param resource $fp bointer to blockchain index
     * @param int $pos block start
     * @param int $datalen block size
     * @param int $count block count
     */
    private function updateIndex(&$fp, $pos, $datalen, $count)
    {
        fseek($fp, 0, SEEK_SET);
        fwrite($fp, pack('V', $count), 4);                          // Record count
        fseek($fp, 0, SEEK_END);
        fwrite($fp, pack('V', $pos), 4);                            // Offset
        fwrite($fp, pack('V', ($datalen + $this->blksize)), 4);     // Length
    }

    /**
     * Creates block with the correct nonce
     * @param string $data data to be added to block chain
     * @param string $prevhash hash of the previous block
     * @return string block containing correct nonce
     */
    private function calculateNonce($data, $prevhash){
        $nonce = 0;
        $check = str_repeat('0', $this->difficulty);
        while(true){
            $block = $this->constructBlock($data, $prevhash, $nonce);
            $subhash = substr((hash($this->hashalg, $block )),0,$this->difficulty);
            if($check===$subhash){
                return $block;
            }
            $nonce++;
        }
    }

    /**
     * Returns all data about a specific block
     *
     * @param int $blockID the block number to returned
     * @return mixed|null an asociative array of all the blocks data
     */
    public function getBlock ($blockID){
        $blockChain = $this->getChain();
        foreach ($blockChain as $block)
            if ($blockID == $block['height'])
                return $block;
        return null;
    }

    /**
     * Prints the decoded contents of the index file to the screen
     */
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