package org.tiqr.authenticator.security;

import org.tiqr.oath.OCRA;
import org.tiqr.authenticator.exceptions.InvalidChallengeException;

public class OCRAWrapper implements OCRAProtocol
{
    public String generateOCRA(String ocraSuite, byte[] key, String challengeQuestion, String sessionKey) throws InvalidChallengeException
    {
        String otp;

        try {
            otp = OCRA.generateOCRA(
                    ocraSuite, 
                    Encryption.bytesToHexString(key), 
                    "", 
                    challengeQuestion, 
                    "", 
                    sessionKey, 
                    "");
        } catch (Exception e) {
            throw new InvalidChallengeException();
        }

        return otp;
    }
}
