package org.tiqr.authenticator.security;

import java.security.InvalidKeyException;
import java.security.NoSuchAlgorithmException;

import org.tiqr.authenticator.exceptions.SecurityFeaturesException;

import org.openauthentication.otp.OneTimePasswordAlgorithm;

// This is a light ocra version (hotp with a challenge). See separate story for full ocra support.

public class Ocra
{
    private byte[] _secret;
    
    private static final int RESPONSE_DIGITS = 6;
    
    public Ocra(byte[] secret) 
    {        
        _secret = secret;
    }

    public String computeResponse(long challenge) throws InvalidKeyException, SecurityFeaturesException 
    {
        try
        {
            return OneTimePasswordAlgorithm.generateOTP(_secret, challenge, Ocra.RESPONSE_DIGITS, false, -1);
        }
        catch (NoSuchAlgorithmException e)
        {
            throw new SecurityFeaturesException();
        }
    }


}
