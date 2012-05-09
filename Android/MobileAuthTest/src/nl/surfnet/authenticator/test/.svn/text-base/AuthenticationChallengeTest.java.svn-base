package nl.surfnet.authenticator.test;

import org.tiqr.authenticator.R;
import org.tiqr.authenticator.auth.AuthenticationChallenge;
import org.tiqr.authenticator.datamodel.DbAdapter;
import org.tiqr.authenticator.datamodel.Identity;
import org.tiqr.authenticator.datamodel.Service;
import org.tiqr.authenticator.exceptions.UserException;

import android.test.AndroidTestCase;
import android.test.RenamingDelegatingContext;

/**
 * Tests for the AuthenticationChallenge class.
 */
public class AuthenticationChallengeTest extends AndroidTestCase
{
    private DbAdapter _dataSource;

    /**
     * Setup database (apart from the main application database).
     */
    @Override
    protected void setUp() throws Exception
    {
        super.setUp();
        
        setContext(new RenamingDelegatingContext(getContext(), getClass().toString()));
        
        _dataSource = new DbAdapter(getContext());
        
        Service service1 = new Service();
        service1.setIdentifier("one.example.org");
        service1.setDisplayName("Dummy Service 1");
        service1.setAuthenticationURL("http://one.example.org/auth/");
        _dataSource.insertService(service1);
        
        Identity identity1 = new Identity();
        identity1.setIdentifier("john.doe");
        identity1.setDisplayName("John Doe");
        identity1.setSortIndex(1);
        _dataSource.insertIdentityForService(identity1, service1);

        Identity identity2 = new Identity();
        identity2.setIdentifier("jane.doe");
        identity2.setDisplayName("Jane Doe");
        identity2.setSortIndex(2);
        _dataSource.insertIdentityForService(identity2, service1);
        
        Service service2 = new Service();
        service2.setIdentifier("two.example.org");
        service2.setDisplayName("Dummy Service 2");
        service2.setAuthenticationURL("http://two.example.org/auth/");
        _dataSource.insertService(service2);
        
        Identity identity3 = new Identity();
        identity3.setIdentifier("john.doe");
        identity3.setDisplayName("John Doe");
        identity3.setSortIndex(3);
        _dataSource.insertIdentityForService(identity3, service2);
        
        Service service3 = new Service();
        service3.setIdentifier("three.example.org");
        service3.setDisplayName("Dummy Service 3");
        service3.setAuthenticationURL("http://three.example.org/auth/");
        _dataSource.insertService(service3);        
    }
    
    /**
     * Assert that the given raw challenge triggers an exception with the message
     * string for the given resource id.
     * 
     * @param rawChallenge raw challenge
     * @param stringId     error string resource identifier
     */
    private void assertAuthenticationChallengeException(String rawChallenge, int stringId) 
    {
        try {
            new AuthenticationChallenge(rawChallenge, getContext());
            assertFalse("No expected authentication challenge exception thrown.", true);
        }
        catch (UserException ex) {
            assertEquals(getContext().getString(stringId), ex.getMessage());
        }        
    }
    
    /**
     * Tests the different exceptions for different situations.
     */
    public void testInvalidChallenges()
    {
        assertAuthenticationChallengeException("http://one.example.org/", R.string.error_auth_invalid_qr_code);
        assertAuthenticationChallengeException("surfauth://one.example.org/", R.string.error_auth_invalid_qr_code);        
        assertAuthenticationChallengeException("surfauth://four.example.org/sessionKey/challenge", R.string.error_auth_unknown_service);        
        assertAuthenticationChallengeException("surfauth://invalid@one.example.org/sessionKey/challenge", R.string.error_auth_unknown_identity);
        assertAuthenticationChallengeException("surfauth://three.example.org/sessionKey/challenge", R.string.error_auth_no_identities_for_service);           
    }
    
    /**
     * Test basic challenges.
     */
    public void testBasicChallenges() throws UserException
    {
        AuthenticationChallenge challenge = new AuthenticationChallenge("surfauth://one.example.org/sessionKey/challenge", getContext());
        assertNotNull(challenge.getService());
        assertEquals("one.example.org", challenge.getService().getIdentifier());        
        assertEquals("Dummy Service 1", challenge.getService().getDisplayName());
        assertNull(challenge.getIdentity());
        assertEquals("sessionKey", challenge.getSessionKey());
        assertEquals("challenge", challenge.getChallenge());      
        assertEquals(null, challenge.getReturnURL());
        
        challenge = new AuthenticationChallenge("surfauth://two.example.org/sessionKey/challenge", getContext());
        assertNotNull(challenge.getService());
        assertEquals("two.example.org", challenge.getService().getIdentifier());        
        assertEquals("Dummy Service 2", challenge.getService().getDisplayName());
        assertNotNull(challenge.getIdentity());
        assertEquals("John Doe", challenge.getIdentity().getDisplayName());
        assertEquals("sessionKey", challenge.getSessionKey());
        assertEquals("challenge", challenge.getChallenge());
        assertEquals(null, challenge.getReturnURL());        
    }
    
    /**
     * Tests challenges which request a specific identity.
     */    
    public void testIdentityChallenges() throws UserException
    {
        AuthenticationChallenge challenge = new AuthenticationChallenge("surfauth://jane.doe@one.example.org/sessionKey/challenge", getContext());
        assertNotNull(challenge.getService());
        assertEquals("one.example.org", challenge.getService().getIdentifier());        
        assertEquals("Dummy Service 1", challenge.getService().getDisplayName());
        assertNotNull(challenge.getIdentity());
        assertEquals("jane.doe", challenge.getIdentity().getIdentifier());        
        assertEquals("Jane Doe", challenge.getIdentity().getDisplayName());        
        
        challenge = new AuthenticationChallenge("surfauth://john.doe@two.example.org/sessionKey/challenge", getContext());
        assertNotNull(challenge.getService());
        assertEquals("two.example.org", challenge.getService().getIdentifier());        
        assertEquals("Dummy Service 2", challenge.getService().getDisplayName());
        assertNotNull(challenge.getIdentity());
        assertEquals("john.doe", challenge.getIdentity().getIdentifier());        
        assertEquals("John Doe", challenge.getIdentity().getDisplayName());
    }
    
    /**
     * Tests challenges which contain a return URL.
     */
    public void testReturnURLChallenges() throws UserException
    {
        AuthenticationChallenge challenge = new AuthenticationChallenge("surfauth://one.example.org/sessionKey/challenge?http%3A%2F%2Fexample.org", getContext());
        assertNotNull(challenge.getService());
        assertEquals("one.example.org", challenge.getService().getIdentifier());        
        assertNull(challenge.getIdentity());
        assertEquals("sessionKey", challenge.getSessionKey());
        assertEquals("challenge", challenge.getChallenge());      
        assertEquals("http://example.org", challenge.getReturnURL());
        
        challenge = new AuthenticationChallenge("surfauth://jane.doe@one.example.org/sessionKey/challenge?http%3A%2F%2Fexample.org%3Fa%3Db", getContext());
        assertNotNull(challenge.getService());
        assertEquals("one.example.org", challenge.getService().getIdentifier());        
        assertNotNull(challenge.getIdentity());
        assertEquals("jane.doe", challenge.getIdentity().getIdentifier());        
        assertEquals("sessionKey", challenge.getSessionKey());
        assertEquals("challenge", challenge.getChallenge());      
        assertEquals("http://example.org?a=b", challenge.getReturnURL());
    }
}
