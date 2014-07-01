package org.tiqr.authenticator.security;

public class CipherPayload 
{
	public byte[] cipherText;
	public byte[] iv;
   
	CipherPayload (byte [] encryptedText, byte[] initializationVector) 
	{
		cipherText = encryptedText;
	    iv = initializationVector;
	}
}