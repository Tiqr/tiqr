package org.tiqr.authenticator.security;

import java.math.BigInteger;

import org.openauthentication.ocra.OCRA;
import org.tiqr.authenticator.exceptions.InvalidChallengeException;

public class OCRAWrapper
{
    protected static String _numStrToHex(String question)
    {
        String qHex = new String((new BigInteger(question,10))
                       .toString(16)).toUpperCase();
        
        return qHex;
    }
    
    public static String generateOCRA(String ocraSuite, byte[] key, String challengeQuestion, String sessionKey) throws InvalidChallengeException
    {
        // The reference implementation takes session data into account even if -S isn't specified in the suite. 
        // We therefor explicitly pass "" if -S is not in the suite.
        String sessionData = "";
        
        if((ocraSuite.toLowerCase().indexOf(":s") > 1) ||
                (ocraSuite.toLowerCase().indexOf("-s",
                        ocraSuite.indexOf(":",
                                ocraSuite.indexOf(":") + 1)) > 1)) {
            sessionData = sessionKey;
        }
        
        String challenge;;
        if ((ocraSuite.toLowerCase().indexOf("qn") >1 )) {
            // Using numeric challenge questions, need to convert to hex first
            challenge = _numStrToHex(challengeQuestion);
        } else {
            // if qh, we're already dealing with hex
            challenge = challengeQuestion;
        }
        
        String otp = OCRA.generateOCRA(
            ocraSuite, 
            Encryption.bytesToHexString(key), 
            "", 
            challenge, 
            "", 
            sessionData, 
            "");
        
        return otp;
    }
}
