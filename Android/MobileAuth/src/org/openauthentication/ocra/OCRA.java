package org.openauthentication.ocra;

import java.lang.reflect.UndeclaredThrowableException;
import java.security.GeneralSecurityException;
import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;

import org.tiqr.authenticator.exceptions.InvalidChallengeException;

import java.math.BigInteger;


/**
 * This an example implementation of the OATH OCRA algorithm.
 * Visit www.openauthentication.org for more information.
 *
 * @author Johan Rydell, PortWise
 */
public class OCRA {

    private OCRA() {}

    /**
     * This method uses the JCE to provide the crypto
     * algorithm.
     * HMAC computes a Hashed Message Authentication Code with the
     * crypto hash algorithm as a parameter.
     *
     * @param crypto     the crypto algorithm (HmacSHA1,
     *                   HmacSHA256,
     *                   HmacSHA512)
     * @param keyBytes   the bytes to use for the HMAC key
     * @param text       the message or text to be authenticated.
     */

    private static byte[] hmac_sha1(String crypto,
            byte[] keyBytes,
            byte[] text)
    {
        try {
            Mac hmac;
            hmac = Mac.getInstance(crypto);
            SecretKeySpec macKey =
                new SecretKeySpec(keyBytes, "RAW");
            hmac.init(macKey);
            return hmac.doFinal(text);
        } catch (GeneralSecurityException gse) {
            throw new UndeclaredThrowableException(gse);
        }
    }


    private static final int[] DIGITS_POWER
    // 0 1  2   3    4     5      6       7        8         9          10
    = {1,10,100,1000,10000,100000,1000000,10000000,100000000,1000000000,10000000000 };

    /**
     * This method converts HEX string to Byte[]
     *
     * @param hex   the HEX string
     *
     * @return      A byte array
     */

    private static byte[] hexStr2Bytes(String hex){
        // Adding one byte to get the right conversion
        // values starting with "0" can be converted
        byte[] bArray = new BigInteger("10" + hex,16).toByteArray();

        // Copy all the REAL bytes, not the "first"
        byte[] ret = new byte[bArray.length - 1];
        for (int i = 0; i < ret.length ; i++)
            ret[i] = bArray[i+1];
        return ret;
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
     * @throws InvalidChallengeException 
     */
    static public String generateOCRA(String ocraSuite,
            String key,
            String counter,
            String question,
            String password,
            String sessionInformation,
            String timeStamp) throws InvalidChallengeException
    {
        int codeDigits = 0;
        String crypto = "";
        String result = null;
        int ocraSuiteLength = (ocraSuite.getBytes()).length;
        int counterLength = 0;
        int questionLength = 0;
        int passwordLength = 0;

        int sessionInformationLength = 0;
        int timeStampLength = 0;

        if(ocraSuite.toLowerCase().indexOf("sha1") > 1) {
            crypto = "HmacSHA1";
        } else if(ocraSuite.toLowerCase().indexOf("sha256") > 1) {
            crypto = "HmacSHA256";
        } else if(ocraSuite.toLowerCase().indexOf("sha512") > 1) {
            crypto = "HmacSHA512";
        } else {
        	crypto = "HmacSHA1";
        }

        // How many digits should we return
        String oS = ocraSuite.substring(ocraSuite.indexOf(":"),
                ocraSuite.indexOf(":", ocraSuite.indexOf(":") + 1));
        codeDigits = Integer.decode(oS.substring
                (oS.lastIndexOf("-")+1,
                        oS.length()));

        // The size of the byte array message to be encrypted
        // Counter
        if(ocraSuite.toLowerCase().indexOf(":c") > 1) {
            // Fix the length of the HEX string
            while(counter.length() < 16)
                counter = "0" + counter;
            counterLength=8;
        }

        // Question length can't exceed 254 chars
        if (question.length() > 254) {
        	throw new InvalidChallengeException();
        }

        // Question
        if((ocraSuite.toLowerCase().indexOf(":q") > 1) ||
                (ocraSuite.toLowerCase().indexOf("-q") > 1)) {
            while(question.length() < 256)
                question = question + "0";
            questionLength=128;
        }

        // Password
        if((ocraSuite.toLowerCase().indexOf(":p") > 1) ||
                (ocraSuite.toLowerCase().indexOf("-p") > 1)){
            while(password.length() < 40)
                password = "0" + password;
            passwordLength=20;
        }

        // sessionInformation
        if((ocraSuite.toLowerCase().indexOf(":s") > 1) ||
                (ocraSuite.toLowerCase().indexOf("-s",
                        ocraSuite.indexOf(":",
                                ocraSuite.indexOf(":") + 1)) > 1)){
            while(sessionInformation.length() < 128)
                sessionInformation = "0" + sessionInformation;

            sessionInformationLength=64;
        }
        // TimeStamp
        if((ocraSuite.toLowerCase().indexOf(":t") > 1) ||
                (ocraSuite.toLowerCase().indexOf("-t") > 1)){
            while(timeStamp.length() < 16)
                timeStamp = "0" + timeStamp;
            timeStampLength=8;
        }

        // Remember to add "1" for the "00" byte delimiter
        byte[] msg = new byte[ocraSuiteLength +
                              counterLength +
                              questionLength +
                              passwordLength +
                              sessionInformationLength +
                              timeStampLength +
                              1];


        // Put the bytes of "ocraSuite" parameters into the message
        byte[] bArray = ocraSuite.getBytes();
        for(int i = 0; i < bArray.length; i++){
            msg[i] = bArray[i];
        }

        // Delimiter
        msg[bArray.length] = 0x00;

        // Put the bytes of "Counter" to the message
        // Input is HEX encoded
        if(counterLength > 0 ){
            bArray = hexStr2Bytes(counter);
            for (int i = 0; i < bArray.length ; i++) {
                msg[i + ocraSuiteLength + 1] = bArray[i];
            }
        }


        // Put the bytes of "question" to the message
        // Input is text encoded
        if(question.length() > 0 ){
            bArray = hexStr2Bytes(question);
            for (int i = 0; i < bArray.length ; i++) {
                msg[i + ocraSuiteLength + 1 + counterLength] =
                    bArray[i];
            }
        }

        // Put the bytes of "password" to the message
        // Input is HEX encoded
        if(password.length() > 0){
            bArray = hexStr2Bytes(password);
            for (int i = 0; i < bArray.length ; i++) {
                msg[i + ocraSuiteLength + 1 + counterLength
                    + questionLength] = bArray[i];
            }
        }

        // Put the bytes of "sessionInformation" to the message
        // Input is text encoded
        if(sessionInformation.length() > 0 ){
            bArray = hexStr2Bytes(sessionInformation);
            for (int i = 0; i < 128 && i < bArray.length ; i++) {
                msg[i + ocraSuiteLength
                    + 1 + counterLength
                    + questionLength
                    + passwordLength] = bArray[i];
            }
        }

        // Put the bytes of "time" to the message
        // Input is text value of minutes
        if(timeStamp.length() > 0){
            bArray = hexStr2Bytes(timeStamp);
            for (int i = 0; i < 8 && i < bArray.length ; i++) {
                msg[i + ocraSuiteLength + 1 + counterLength +
                    questionLength + passwordLength +
                    sessionInformationLength] = bArray[i];
            }
        }
        
        byte[] hash;
        bArray = hexStr2Bytes(key);
        
        hash = hmac_sha1(crypto, bArray, msg);
        
        // put selected bytes into result int
        int offset = hash[hash.length - 1] & 0xf;

        int binary =
            ((hash[offset] & 0x7f) << 24) |
            ((hash[offset + 1] & 0xff) << 16) |
            ((hash[offset + 2] & 0xff) << 8) |
            (hash[offset + 3] & 0xff);

        int otp = binary % DIGITS_POWER[codeDigits];
        result = Integer.toString(otp);
        while (result.length() < codeDigits) {
            result = "0" + result;
        }
        return result;
    }
    
    public static String getHexString(byte[] b) throws Exception {
        String result = "";
        for (int i=0; i < b.length; i++) {
          result +=
                Integer.toString( ( b[i] & 0xff ) + 0x100, 16).substring( 1 );
        }
        return result;
      }
}
