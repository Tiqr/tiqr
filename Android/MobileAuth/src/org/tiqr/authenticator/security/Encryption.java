package org.tiqr.authenticator.security;

import java.security.InvalidAlgorithmParameterException;
import java.security.InvalidKeyException;
import java.security.Key;
import java.security.NoSuchAlgorithmException;
import java.security.NoSuchProviderException;
import java.security.SecureRandom;
import java.security.spec.InvalidKeySpecException;
import java.util.Arrays;

import javax.crypto.BadPaddingException;
import javax.crypto.Cipher;
import javax.crypto.IllegalBlockSizeException;
import javax.crypto.KeyGenerator;
import javax.crypto.NoSuchPaddingException;
import javax.crypto.SecretKey;
import javax.crypto.SecretKeyFactory;
import javax.crypto.ShortBufferException;
import javax.crypto.spec.IvParameterSpec;
import javax.crypto.spec.PBEKeySpec;
import javax.crypto.spec.SecretKeySpec;

import org.tiqr.authenticator.exceptions.SecurityFeaturesException;

import android.content.Context;
import android.content.SharedPreferences;
import android.util.Log;
import biz.source_code.base64Coder.Base64Coder;

/**
 * Class with a number of encryption related (static) utility functions. 
 * 
 * @author ivo
 *
 */
public class Encryption
{
    private static final String PREFERENCES_KEY = "securitySettings";
    private static final String SALT_KEY = "salt";
    private static final String DEVICE_KEY = "deviceKey";
  
    private static final String RANDOM_ALGORITHM = "SHA1PRNG"; // a randomizer supported by android
    
    private static final String PASSWORD_KEY_ALGORITHM = "PBEWithSHA256And256BitAES-CBC-BC"; // A typical AES supported by android (this is the bouncy castle implementation)
    
    private static final int SALT_BYTES = 20;
    
    private static final String MASTER_KEY_ALGORITHM = "HMACSHA256";
    private static final int CIPHER_KEY_ITERATIONS = 1500;
    private static final int CIPHER_KEY_SIZE = 16;
    private static final int IV_LENGTH = 16;
    private static final String CIPHER_TRANSFORMATION = "AES/CBC/NoPadding";
    
    
    /**
     * Get a generic key. It's randomized the first time it's retrieved and stored in the SharedPreferences.
     * This key is used for the keystore for an extra level of encryption.
     * @param ctx
     * @return
     * @throws SecurityFeaturesException
     */
    public static SecretKey getDeviceKey(Context ctx) throws SecurityFeaturesException
    {
        SharedPreferences settings = ctx.getSharedPreferences(PREFERENCES_KEY, Context.MODE_PRIVATE);
        
        byte[] bytes;
        
        String value = settings.getString(DEVICE_KEY, null);
        if (value!=null) {
            bytes = Base64Coder.decode(value);
        } else { 
        
            bytes = getRandomBytes(ctx, SALT_BYTES);
      
            SharedPreferences.Editor editor = settings.edit();
            editor.putString(DEVICE_KEY, new String(Base64Coder.encode(bytes)));
            editor.commit();
        }      
        return new SecretKeySpec(bytes, "RAW");
        
    }
            
    /**
     * Encrypt a plaintext using the method defined in CIPHER_TRANSFORMATION.
     * Depending on the transformation, you may need to pass text in correct
     * blocksize (current implementation should be multiples of 16 bytes)
     * 
     * Returns a tuple with the ciphertext and randomly generated iv.
     * 
     * @param text
     * @param key
     * @return
     * @throws SecurityFeaturesException
     */
    public static CipherPayload encrypt(byte[] text, Key key) throws SecurityFeaturesException
    {
        try {
            Cipher cipher = Cipher.getInstance(CIPHER_TRANSFORMATION);
                        
            byte [] generatedIV = generateIv();
            cipher.init(Cipher.ENCRYPT_MODE, key, new IvParameterSpec(generatedIV));
            byte [] iv = cipher.getIV();
                        
            /* Some versions of Android don't actually set the IV in that case
             * so we'll test for that and log it, but return the iv that the system
             * says is the one that's been used */
            if (! Arrays.equals(generatedIV, iv)) {
                Log.i("encryption", "Not able to set random IV on this system.");
            }
            
            byte[] cipherText = new byte[cipher.getOutputSize(text.length)];
            int ctLength = cipher.update(text, 0, text.length, cipherText, 0);
            ctLength += cipher.doFinal(cipherText, ctLength);
            return new CipherPayload (cipherText, iv);
        } catch (NoSuchAlgorithmException e) {
        } catch (NoSuchPaddingException e) {
        } catch (InvalidKeyException e) {
        } catch (ShortBufferException e) {
        } catch (IllegalBlockSizeException e) {
        } catch (BadPaddingException e) {
        } catch (NoSuchProviderException e) {
        } catch (InvalidAlgorithmParameterException e) {
        }
        
        // If any of these fail, we're dealing with a device that can't handle our level of encryption
        throw new SecurityFeaturesException();

    }
    
    /**
     * Decrypts the ciphertext according to CIPHER_TRANSFORMATION.
     * Note that if the cipher transformation defines a padding scheme, then the decrypted
     * string will have padding bytes (length will be a multiple of 16). Since we know
     * the length of what we encoded, we should use substrings to retrieve the result from 
     * what decrypt returns to us.
     * @param payload is both the ciphertext and iv, or if no iv, payload.iv is null
     * @param key
     * @return
     * @throws InvalidKeyException
     * @throws SecurityFeaturesException
     */
    public static byte[] decrypt(CipherPayload payload, Key key) throws InvalidKeyException, SecurityFeaturesException 
    {
    	byte [] original = payload.cipherText;
    	byte [] iv       = payload.iv;
        try {
            Cipher cipher = Cipher.getInstance(CIPHER_TRANSFORMATION);
            if (iv != null) {
                cipher.init(Cipher.DECRYPT_MODE, key, new IvParameterSpec(iv));
            } else { // handle old key types
                cipher.init(Cipher.DECRYPT_MODE, key);
            }
       //     byte[] original = Base64Coder.decode(text);
            byte[] plainText = new byte[cipher.getOutputSize(original.length)];
            int ptLength = cipher.update(original, 0, original.length, plainText, 0);
            ptLength += cipher.doFinal(plainText, ptLength);
          ///  return new String(plainText);
            return plainText;
        } catch (NoSuchAlgorithmException e) {
            // Can't work with this device
            throw new SecurityFeaturesException();
        } catch (NoSuchPaddingException e) {
            // Can't work with this device
            throw new SecurityFeaturesException();
        } catch (InvalidKeyException e) {
            // Probably a wrong PIN
            throw new InvalidKeyException();
        } catch (ShortBufferException e) {
            // Probably a wrong PIN
            throw new InvalidKeyException();
        } catch (IllegalBlockSizeException e) {
            // Probably a wrong PIN
            throw new InvalidKeyException();
        } catch (BadPaddingException e) {
            // Probably a wrong PIN
            throw new InvalidKeyException();
        } catch (InvalidAlgorithmParameterException e) {
            // IV was messed up
            throw new InvalidKeyException();
        }
    }
    
    /**
     * Convert a password/pincode to an encryption compatible SecretKey by salting the password,
     * hashing it a number of times and making it the correct size for a key.
     * @param ctx
     * @param password
     * @return
     * @throws SecurityFeaturesException
     */
    public static SecretKey keyFromPassword(Context ctx, String password) throws SecurityFeaturesException
    {
        return keyFromPassword(ctx, password, getSalt(ctx));
    }
    
    /**
     * Like keyFromPassword this method converts a password to a SecretKey. The difference is that
     * this method allows passing a fixed Salt. This should *NEVER* be done in production code, it's
     * here only to cater for unit tests that need to get predictable results from this function.
     * @param ctx
     * @param password
     * @param salt
     * @return
     * @throws SecurityFeaturesException
     */
    public static SecretKey keyFromPassword(Context ctx, String password, byte[] salt) throws SecurityFeaturesException 
    {
        PBEKeySpec spec = new PBEKeySpec(password.toCharArray(), salt, CIPHER_KEY_ITERATIONS, CIPHER_KEY_SIZE);
               
        SecretKeyFactory f;
        try {
            // First convert Key to something more secure using the pbekeyspec
            f = SecretKeyFactory.getInstance(PASSWORD_KEY_ALGORITHM);
            SecretKey x = f.generateSecret(spec);
            return x;   
       
            
        } catch (NoSuchAlgorithmException e) {
            e.printStackTrace();           
        } catch (InvalidKeySpecException e) {
            e.printStackTrace();
        }
        throw new SecurityFeaturesException();
    }
    
    /**
     * Get a Salt. The salt is initially randomized but then stored in sharedpreferences
     * so a consistent salt is used.
     * @param ctx
     * @return
     * @throws SecurityFeaturesException
     */
    public static byte[] getSalt(Context ctx) throws SecurityFeaturesException
    {
        SharedPreferences settings = ctx.getSharedPreferences(PREFERENCES_KEY, Context.MODE_PRIVATE);
        
        String value = settings.getString(SALT_KEY, null);
        if (value!=null) {
            return Base64Coder.decode(value);
        }
        
        byte[] bytes = getRandomBytes(ctx, SALT_BYTES);
            
        SharedPreferences.Editor editor = settings.edit();
        editor.putString(SALT_KEY, new String(Base64Coder.encode(bytes)));
        editor.commit();
        return bytes;                
        
    }
    
    /**
     * Get a number of random bytes using a secure random algorithm.
     * @param ctx
     * @param numberOfBytes
     * @return
     * @throws SecurityFeaturesException
     */
    public static byte[] getRandomBytes(Context ctx, int numberOfBytes) throws SecurityFeaturesException 
    {
        byte[] bytes = new byte[numberOfBytes];
        try {
            
            SecureRandom r = SecureRandom.getInstance(RANDOM_ALGORITHM);
            r.nextBytes(bytes);           
            return bytes;
            
        } catch (NoSuchAlgorithmException e) {
            throw new SecurityFeaturesException();
        }
        
    }
    
    /**
     * Generate a random key. Like generateRandomKey, but the return value is in SecretKey format.
     * @return
     * @throws SecurityFeaturesException
     */
    public static SecretKey generateRandomSecretKey() throws SecurityFeaturesException  
    {
        SecretKeySpec x = new SecretKeySpec(generateRandomKey().getEncoded(), MASTER_KEY_ALGORITHM);
        return x;
    }
    
    /**
     * Generate a random key.
     * @return
     * @throws SecurityFeaturesException
     */
    public static Key generateRandomKey() throws SecurityFeaturesException
    {
        KeyGenerator generator;
        try {
            generator = KeyGenerator.getInstance(MASTER_KEY_ALGORITHM);
        } catch (NoSuchAlgorithmException e) {
            throw new SecurityFeaturesException();
        }
        generator.init(new SecureRandom());
        return generator.generateKey(); 
    }
    
    public static final String bytesToHexString(byte[] bArray) {   
        StringBuffer sb = new StringBuffer(bArray.length);   
        String sTemp;   
        for (int i = 0; i < bArray.length; i++) {   
            sTemp = Integer.toHexString(0xFF & bArray[i]);   
            if (sTemp.length() < 2)   
                sb.append(0);   
            sb.append(sTemp.toUpperCase());   
        }   
        return sb.toString();   
    } 
    
    private static byte[] generateIv() throws NoSuchAlgorithmException, NoSuchProviderException {
    	SecureRandom random = SecureRandom.getInstance(RANDOM_ALGORITHM);
    	byte[] iv = new byte[IV_LENGTH];
    	random.nextBytes(iv);
    	return iv;
    }

}
