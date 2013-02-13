package org.tiqr.oath;

/**
 Copyright (c) 2011 IETF Trust and the persons identified as authors of
 the code. All rights reserved.

 THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * This is an implementation of the OCRA spec. It is based on the reference 
 * implementation in http://tools.ietf.org/html/rfc6287
 * Since it does have a few modifications (search for 'deviation' in the
 * code comments), we have changed the package name to read 
 * nl.surfnet.ocra, to avoid confusion with the actual reference code.
 */

import java.lang.reflect.UndeclaredThrowableException;
import java.math.BigInteger;

import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;


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

    private static byte[] hmac_sha1(String crypto, byte[] keyBytes, byte[] text) {
    Mac hmac = null;

        try {
            hmac = Mac.getInstance(crypto);
            SecretKeySpec macKey =
                new SecretKeySpec(keyBytes, "RAW");
            hmac.init(macKey);
            return hmac.doFinal(text);
    } catch (Exception e) {
        // NOTE. Deviation from reference code.
        // Reference code prints a stack trace here, which is not what we
        // want in a production environment, so instead we rethrow.
        throw new UndeclaredThrowableException(e);
        }

    }


    private static final int[] DIGITS_POWER
    // 0 1  2   3    4     5      6       7        8
    = {1,10,100,1000,10000,100000,1000000,10000000,100000000 };

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
    System.arraycopy(bArray, 1, ret, 0, ret.length);
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
     */
    static public String generateOCRA(String ocraSuite, 
            String key,
            String counter,
            String question,
            String password,
            String sessionInformation,
            String timeStamp) {
        int codeDigits = 0;
        String crypto = "";
        String result = null;
        int ocraSuiteLength = (ocraSuite.getBytes()).length;
        int counterLength = 0;
        int questionLength = 0;
        int passwordLength = 0;

        int sessionInformationLength = 0;
        int timeStampLength = 0;

        // The OCRASuites components
        String CryptoFunction = ocraSuite.split(":")[1];
        String DataInput = ocraSuite.split(":")[2];
        
        if(CryptoFunction.toLowerCase().indexOf("sha1") > 1)
            crypto = "HmacSHA1";
        if(CryptoFunction.toLowerCase().indexOf("sha256") > 1)
            crypto = "HmacSHA256";
        if(CryptoFunction.toLowerCase().indexOf("sha512") > 1)
            crypto = "HmacSHA512";

        // How many digits should we return
        codeDigits = Integer.decode(CryptoFunction.substring
                (CryptoFunction.lastIndexOf("-")+1));

        // The size of the byte array message to be encrypted
        // Counter
        if(DataInput.toLowerCase().startsWith("c")) {
            // Fix the length of the HEX string
            while(counter.length() < 16)
                counter = "0" + counter;
            counterLength=8;
        }
        // Question - always 128 bytes
        if(DataInput.toLowerCase().startsWith("q") ||
                (DataInput.toLowerCase().indexOf("-q") >= 0)) {
            while(question.length() < 256)
                question = question + "0";
            questionLength=128;
        }

        // Password - sha1
        if(DataInput.toLowerCase().indexOf("psha1") > 1) {
            while(password.length() < 40)
                password = "0" + password;
            passwordLength=20;
        }
        
        // Password - sha256
        if(DataInput.toLowerCase().indexOf("psha256") > 1){
            while(password.length() < 64)
                password = "0" + password;
            passwordLength=32;
        }

        // Password - sha512
        if(DataInput.toLowerCase().indexOf("psha512") > 1){
            while(password.length() < 128)
                password = "0" + password;
            passwordLength=64;
        }

        // sessionInformation - s064
        if(DataInput.toLowerCase().indexOf("s064") > 1){
            while(sessionInformation.length() < 128)
                sessionInformation = "0" + sessionInformation;
            sessionInformationLength=64;
        } else if(DataInput.toLowerCase().indexOf("s128") > 1){
            while(sessionInformation.length() < 256)
                sessionInformation = "0" + sessionInformation;
            sessionInformationLength=128;
        } else if(DataInput.toLowerCase().indexOf("s256") > 1){
            while(sessionInformation.length() < 512)
                sessionInformation = "0" + sessionInformation;
            sessionInformationLength=256;
        } else if(DataInput.toLowerCase().indexOf("s512") > 1){
            while(sessionInformation.length() < 1024)
                sessionInformation = "0" + sessionInformation;
            sessionInformationLength=512;
        } else if (DataInput.toLowerCase().indexOf("s") > 1) {
            // deviation from spec. Officially 's' without a length indicator is not in the reference implementation.
            // RFC is ambigious. However we have supported this in Tiqr since day 1, so we continue to support it.
            while(sessionInformation.length() < 128)
                sessionInformation = "0" + sessionInformation;
            sessionInformationLength=64;
        }

        // TimeStamp
        if(DataInput.toLowerCase().startsWith("t") ||
                (DataInput.toLowerCase().indexOf("-t") > 1)){
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
        System.arraycopy(bArray, 0, msg, 0, bArray.length);

        // Delimiter
        msg[bArray.length] = 0x00;

        // Put the bytes of "Counter" to the message
        // Input is HEX encoded
        if(counterLength > 0 ){
            bArray = hexStr2Bytes(counter);
            System.arraycopy(bArray, 0, msg, ocraSuiteLength + 1,
                    bArray.length);
        }


        // Put the bytes of "question" to the message
        // Input is text encoded
        if(questionLength > 0 ){
            bArray = hexStr2Bytes(question);
            System.arraycopy(bArray, 0, msg, ocraSuiteLength + 1 +
                    counterLength, bArray.length);
        }

        // Put the bytes of "password" to the message
        // Input is HEX encoded
        if(passwordLength > 0){
            bArray = hexStr2Bytes(password);
            System.arraycopy(bArray, 0, msg, ocraSuiteLength + 1 +
                    counterLength +    questionLength, bArray.length);

        }

        // Put the bytes of "sessionInformation" to the message
        // Input is text encoded
        if(sessionInformationLength > 0 ){
            bArray = hexStr2Bytes(sessionInformation);
            System.arraycopy(bArray, 0, msg, ocraSuiteLength + 1 +
                    counterLength +     questionLength +
                    passwordLength, bArray.length);
        }

        // Put the bytes of "time" to the message
        // Input is text value of minutes
        if(timeStampLength > 0){
            bArray = hexStr2Bytes(timeStamp);
            System.arraycopy(bArray, 0, msg, ocraSuiteLength + 1 +
                    counterLength + questionLength +
                    passwordLength + sessionInformationLength,
                    bArray.length);
        }

        bArray = hexStr2Bytes(key);

        byte[] hash = hmac_sha1(crypto, bArray, msg);

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
}
