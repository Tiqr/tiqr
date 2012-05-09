package nl.surfnet.authenticator.test;

import java.math.BigInteger;
import java.security.InvalidKeyException;

import org.tiqr.authenticator.exceptions.SecurityFeaturesException;
import org.openauthentication.ocra.OCRA;
import android.test.AndroidTestCase;
import android.test.RenamingDelegatingContext;

/**
 * Tests for the EnrollmentChallenge class.
 */
public class OcraTest extends AndroidTestCase
{

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
     * All testsets are from the OCRA spec.
     */
    
    public String numStrToHex(String question)
    {
        String qHex = new String((new BigInteger(question,10))
                       .toString(16)).toUpperCase();
        
        return qHex;
    }
    
    /**
     * Assert that given a secret and given a challenge, we're getting the proper response.
     * @throws SecurityFeaturesException 
     * @throws InvalidKeyException 
     * 
     */
    public void testOcraPlainChallengeResponse() throws InvalidKeyException, SecurityFeaturesException
    {     
        String result;
        
        result = OCRA.generateOCRA("OCRA-1:HOTP-SHA1-6:QN08", "3132333435363738393031323334353637383930", "", numStrToHex("00000000"), "", "", "");
        assertEquals("237653", result);
        
        result = OCRA.generateOCRA("OCRA-1:HOTP-SHA1-6:QN08", "3132333435363738393031323334353637383930", "", numStrToHex("77777777"), "", "", "");
        assertEquals("224598", result);
        
    }
    
    public void testOcraChallengeResponseWithSession() throws InvalidKeyException, SecurityFeaturesException
    {     
        String result;
                
        result = OCRA.generateOCRA("OCRA-1:HOTP-SHA1-6:QN08-S", "3132333435363738393031323334353637383930", "", numStrToHex("77777777"), "", "ABCDEFABCDEF", "");
        assertEquals("675831", result);
        
    }
    
}