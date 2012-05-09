package nl.surfnet.authenticator.test;

import java.security.InvalidKeyException;
import java.security.Key;
import java.util.Arrays;

import javax.crypto.SecretKey;

import org.tiqr.authenticator.exceptions.SecurityFeaturesException;
import org.tiqr.authenticator.security.Encryption;
import android.test.AndroidTestCase;
import android.test.RenamingDelegatingContext;

/**
 * Tests for the EnrollmentChallenge class.
 */
public class EncryptionTest extends AndroidTestCase
{
    protected final static byte[] FIXED_SALT = new byte[] { 12, 14, -1, 13 };
    
    
    /**
     * Setup database (apart from the main application database).
     */
    @Override
    protected void setUp() throws Exception
    {
        super.setUp();
        
        setContext(new RenamingDelegatingContext(getContext(), getClass().toString()));
        
    }
    
    /**
     * Assert that given a secret and given a challenge, we're getting the proper response.
     * @throws SecurityFeaturesException 
     * @throws InvalidKeyException 
     * 
     */
    public void testKeyGeneration() throws SecurityFeaturesException
    {
        Key a = Encryption.generateRandomKey();
     
        assertEquals("HMACSHA256", a.getAlgorithm());
        
        Key b = Encryption.generateRandomKey();
        
        assertFalse(Arrays.equals(a.getEncoded(), b.getEncoded()));
    }
    
    public void testRandomBytesGeneration() throws SecurityFeaturesException
    {
        byte[] a = Encryption.getRandomBytes(getContext(), 6);
        assertEquals(6, a.length);
        
        byte[] b = Encryption.getRandomBytes(getContext(), 6);
        assertFalse(Arrays.equals(a, b));
    }
    
    public void testEncryptionSymmetry() throws InvalidKeyException, SecurityFeaturesException
    {
        SecretKey x = Encryption.keyFromPassword(getContext(), "1234", FIXED_SALT);
        String original = "hello world12345"; // myst be in multiples of 16
        byte[] encrypted = Encryption.encrypt(original.getBytes(), x);
        
        // encrypted version should not match the original plain text.
        assertFalse(original.equals(new String(encrypted)));
        // Nor should it be present as a substring
        assertEquals(-1, original.indexOf(new String(encrypted)));
        // Or vice versa
        assertEquals(-1, new String(encrypted).indexOf(original));
        
        String decrypted = new String(Encryption.decrypt(encrypted, x));
        
        // decrypted should match the original (ignoring padding)
        assertTrue(decrypted.startsWith(original));
    } 
    
    public void testEncryptionMismatch() throws SecurityFeaturesException
    {
        SecretKey correctKey = Encryption.keyFromPassword(getContext(), "1234", FIXED_SALT);
        SecretKey wrongKey = Encryption.keyFromPassword(getContext(), "6313", FIXED_SALT);
        
        String original = "hello world12345";
        byte[] encrypted = Encryption.encrypt(original.getBytes(), correctKey);
        
        try {
            String decrypted = new String(Encryption.decrypt(encrypted, wrongKey));
            assertFalse(decrypted.startsWith(original));
        } catch (Exception e) {
            assertEquals("java.security.InvalidKeyException", e.getClass().getName());
        }
    } 
   

}