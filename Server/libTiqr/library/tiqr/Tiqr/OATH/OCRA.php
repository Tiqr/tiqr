<?php 
/**
 * This file is part of the ocra-implementations package.
 *
 * More information: https://github.com/SURFnet/ocra-implementations/
 *
 * @author Ivo Jansch <ivo@egeniq.com>
 * 
 * @license See the LICENSE file in the source distribution
 */

/**
 * This a PHP port of the example implementation of the 
 * OATH OCRA algorithm.
 * Visit www.openauthentication.org for more information.
 *
 * @author Johan Rydell, PortWise (original Java)
 * @author Ivo Jansch, Egeniq (PHP port)
 */
class OCRA {

    private function __construct() {
        
    }

    /**
     * This method uses the hmac_hash function to provide the crypto
     * algorithm.
     * HMAC computes a Hashed Message Authentication Code with the
     * crypto hash algorithm as a parameter.
     *
     * @param String crypto     the crypto algorithm (sha1, sha256 or sha512)
     * @param String keyBytes   the bytes to use for the HMAC key
     * @param String text       the message or text to be authenticated.
     */
    private static function _hmac_sha1($crypto,
            $keyBytes,
            $text)
    {
         $hash = hash_hmac ($crypto, $text, $keyBytes);
         return $hash;
    }

    /**
     * This method converts HEX string to Byte[]
     *
     * @param String hex   the HEX string
     *
     * @return String a string with raw bytes
     */
    private static function _hexStr2Bytes($hex){
        return pack("H*", $hex);
    }


    /**
     * This method generates an OCRA HOTP value for the given
     * set of parameters.
     *
     * @param ocraSuite    the OCRA Suite
     * @param key          the shared secret, HEX encoded
     * @param counter      the counter that changes
     *                     on a per use basis,
     *                     HEX encoded
     * @param question     the challenge question, HEX encoded
     * @param password     a password that can be used,
     *                     HEX encoded
     * @param sessionInformation
     *                     Static information that identifies the
     *                     current session, Hex encoded
     * @param timeStamp    a value that reflects a time
     *
     * @return A numeric String in base 10 that includes
     * {@link truncationDigits} digits
     */
    static function generateOCRA($ocraSuite,
                                 $key,
                                 $counter,
                                 $question,
                                 $password,
                                 $sessionInformation,
                                 $timeStamp)
    {
        $codeDigits = 0;
        $crypto = "";
        $result = null;
        $ocraSuiteLength = strlen($ocraSuite);
        $counterLength = 0;
        $questionLength = 0;
        $passwordLength = 0;

        $sessionInformationLength = 0;
        $timeStampLength = 0;

        // How many digits should we return
        $components = explode(":", $ocraSuite);
        $cryptoFunction = $components[1];
        $dataInput = strtolower($components[2]); // lower here so we can do case insensitive comparisons
        
        if(stripos($cryptoFunction, "sha1")!==false)
            $crypto = "sha1";
        if(stripos($cryptoFunction, "sha256")!==false)
            $crypto = "sha256";
        if(stripos($cryptoFunction, "sha512")!==false)
            $crypto = "sha512";
        
        $codeDigits = substr($cryptoFunction, strrpos($cryptoFunction, "-")+1);
                
        // The size of the byte array message to be encrypted
        // Counter
        if($dataInput[0] == "c" ) {
            // Fix the length of the HEX string
            while(strlen($counter) < 16)
                $counter = "0" . $counter;
            $counterLength=8;
        }
        // Question
        if($dataInput[0] == "q" ||
                stripos($dataInput, "-q")!==false) {
            while(strlen($question) < 256)
                $question = $question . "0";
            $questionLength=128;
        }

        // Password
        if(stripos($dataInput, "psha1")!==false) {
            while(strlen($password) < 40)
                $password = "0" . $password;
            $passwordLength=20;
        }
    
        if(stripos($dataInput, "psha256")!==false) {
            while(strlen($password) < 64)
                $password = "0" . $password;
            $passwordLength=32;
        }
        
        if(stripos($dataInput, "psha512")!==false) {
            while(strlen($password) < 128)
                $password = "0" . $password;
            $passwordLength=64;
        }
        
        // sessionInformation
        if(stripos($dataInput, "s064") !==false) {
            while(strlen($sessionInformation) < 128)
                $sessionInformation = "0" . $sessionInformation;

            $sessionInformationLength=64;
        } else if(stripos($dataInput, "s128") !==false) {
            while(strlen($sessionInformation) < 256)
                $sessionInformation = "0" . $sessionInformation;
        
            $sessionInformationLength=128;
        } else if(stripos($dataInput, "s256") !==false) {
            while(strlen($sessionInformation) < 512)
                $sessionInformation = "0" . $sessionInformation;
        
            $sessionInformationLength=256;
        } else if(stripos($dataInput, "s512") !==false) {
            while(strlen($sessionInformation) < 128)
                $sessionInformation = "0" . $sessionInformation;
        
            $sessionInformationLength=64;
        } else if (stripos($dataInput, "s") !== false ) {
            // deviation from spec. Officially 's' without a length indicator is not in the reference implementation.
            // RFC is ambigious. However we have supported this in Tiqr since day 1, so we continue to support it.
            while(strlen($sessionInformation) < 128)
                $sessionInformation = "0" . $sessionInformation;
            
            $sessionInformationLength=64;
        }
        
        
             
        // TimeStamp
        if($dataInput[0] == "t" ||
                stripos($dataInput, "-t") !== false) {
            while(strlen($timeStamp) < 16)
                $timeStamp = "0" . $timeStamp;
            $timeStampLength=8;
        }

        // Put the bytes of "ocraSuite" parameters into the message
        
        $msg = array_fill(0,$ocraSuiteLength+$counterLength+$questionLength+$passwordLength+$sessionInformationLength+$timeStampLength+1, 0);
                
        for($i=0;$i<strlen($ocraSuite);$i++) {
            $msg[$i] = $ocraSuite[$i];
        }
        
        // Delimiter
        $msg[strlen($ocraSuite)] = self::_hexStr2Bytes("0");

        // Put the bytes of "Counter" to the message
        // Input is HEX encoded
        if($counterLength > 0 ) {
            $bArray = self::_hexStr2Bytes($counter);
            for ($i=0;$i<strlen($bArray);$i++) {
                $msg [$i + $ocraSuiteLength + 1] = $bArray[$i];
            }
        }


        // Put the bytes of "question" to the message
        // Input is text encoded
        if($questionLength > 0 ) {
            $bArray = self::_hexStr2Bytes($question);
            for ($i=0;$i<strlen($bArray);$i++) {
                $msg [$i + $ocraSuiteLength + 1 + $counterLength] = $bArray[$i];
            }
        }

        // Put the bytes of "password" to the message
        // Input is HEX encoded
        if($passwordLength > 0){
            $bArray = self::_hexStr2Bytes($password);
            for ($i=0;$i<strlen($bArray);$i++) {
                $msg [$i + $ocraSuiteLength + 1 + $counterLength + $questionLength] = $bArray[$i];
            }
        }

        // Put the bytes of "sessionInformation" to the message
        // Input is text encoded
        if($sessionInformationLength > 0 ){
            $bArray = self::_hexStr2Bytes($sessionInformation);
            for ($i=0;$i<strlen($bArray);$i++) {
                $msg [$i + $ocraSuiteLength + 1 + $counterLength + $questionLength + $passwordLength] = $bArray[$i];
            }
        }

        // Put the bytes of "time" to the message
        // Input is text value of minutes
        if($timeStampLength > 0){
            $bArray = self::_hexStr2Bytes($timeStamp);
            for ($i=0;$i<strlen($bArray);$i++) {
                $msg [$i + $ocraSuiteLength + 1 + $counterLength + $questionLength + $passwordLength + $sessionInformationLength] = $bArray[$i];
            }
        }
        
        $byteKey = self::_hexStr2Bytes($key);
              
        $msg = implode("", $msg);
        
        $hash = self::_hmac_sha1($crypto, $byteKey, $msg);
        
        $result = self::_oath_truncate($hash, $codeDigits);
             
        return $result;
    }

    /**
     * Truncate a result to a certain length
     */    
    static function _oath_truncate($hash, $length = 6)
    {
        // Convert to dec
        foreach(str_split($hash,2) as $hex)
        {
            $hmac_result[]=hexdec($hex);
        }
    
        // Find offset
        $offset = $hmac_result[count($hmac_result) - 1] & 0xf;
    
        $v = strval(
            (($hmac_result[$offset+0] & 0x7f) << 24 ) |
            (($hmac_result[$offset+1] & 0xff) << 16 ) |
            (($hmac_result[$offset+2] & 0xff) << 8 ) |
            ($hmac_result[$offset+3] & 0xff)
        );	
        
        $v = substr($v, strlen($v) - $length);
        return $v;
    }
    
}
