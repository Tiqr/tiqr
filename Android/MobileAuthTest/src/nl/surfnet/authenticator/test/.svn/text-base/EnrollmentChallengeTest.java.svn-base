package nl.surfnet.authenticator.test;

import java.io.FileOutputStream;
import java.io.IOException;

import org.tiqr.authenticator.R;
import org.tiqr.authenticator.auth.EnrollmentChallenge;
import org.tiqr.authenticator.datamodel.DbAdapter;
import org.tiqr.authenticator.datamodel.Identity;
import org.tiqr.authenticator.datamodel.Service;
import org.tiqr.authenticator.exceptions.UserException;
import android.content.Context;
import android.graphics.Bitmap;
import android.test.AndroidTestCase;
import android.test.RenamingDelegatingContext;
import android.util.Log;

/**
 * Tests for the EnrollmentChallenge class.
 */
public class EnrollmentChallengeTest extends AndroidTestCase
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
        
        Service service = new Service();
        service.setIdentifier("example.org");
        service.setDisplayName("Dummy Service");
        service.setAuthenticationURL("http://example.org/auth/");
        _dataSource.insertService(service);
        
        Identity identity = new Identity();
        identity.setIdentifier("john.doe");
        identity.setDisplayName("John Doe");
        identity.setSortIndex(1);
        _dataSource.insertIdentityForService(identity, service);

        String challenge1JSON = "{}";
        FileOutputStream challenge1 = getContext().openFileOutput("challenge.txt", Context.MODE_PRIVATE);
        challenge1.write(challenge1JSON.getBytes());
    }
    
    /**
     * Creates an enrollment challenge object for the given JSON string. 
     * 
     * The given JSON string is saved to a file, which name is passed as the raw challenge
     * to the EnrollmentChallenge constructor. 
     */
    private EnrollmentChallenge _createEnrollmentChallenge(String challengeJSON) throws UserException
    {
        try {
            FileOutputStream challenge = getContext().openFileOutput("challenge.txt", Context.MODE_PRIVATE);
            challenge.write(challengeJSON.getBytes());
            challenge.close();
            String fileURL = "surfenroll://file:" + getContext().getFileStreamPath("challenge.txt").getCanonicalPath();
            return new EnrollmentChallenge(fileURL, getContext(), true);
        }
        catch (IOException ex) {
            throw new RuntimeException("Unexpected IOException", ex);
        }
    }
    
    /**
     * Assert that the given raw challenge triggers an exception with the message
     * string for the given resource id.
     * 
     * @param challengeJSON raw challenge JSON
     * @param stringId      error string resource identifier
     */
    private void assertEnrollmentChallengeException(String challengeJSON, int stringId) 
    {
        try {
            _createEnrollmentChallenge(challengeJSON);
            assertFalse("No expected enrollment challenge exception thrown.", true);
        }
        catch (UserException ex) {
            Log.d(getClass().getSimpleName(), "UserException", ex);
            assertEquals(getContext().getString(stringId), ex.getMessage());
        }        
    }
    
    /**
     * Tests the different exceptions for different situations.
     */
    public void testInvalidChallenges()
    {
        try {
            new EnrollmentChallenge("surfauth://http://example.org", getContext(), true); // invalid qr start
            assertFalse("No expected enrollment challenge exception thrown.", true);
        }
        catch (UserException ex) {
            assertEquals(getContext().getString(R.string.error_enroll_invalid_qr_code), ex.getMessage());            
        }        
        
        try {
            new EnrollmentChallenge("surfenroll://xyz://example.org", getContext(), true); // invalid protocol
            assertFalse("No expected enrollment challenge exception thrown.", true);
        }
        catch (UserException ex) {
            assertEquals(getContext().getString(R.string.error_enroll_invalid_qr_code), ex.getMessage());            
        }
        
        try {
            new EnrollmentChallenge("surfenroll://file:something", getContext()); // no files allowed (default)
            assertFalse("No expected enrollment challenge exception thrown.", true);
        }
        catch (UserException ex) {
            assertEquals(getContext().getString(R.string.error_enroll_invalid_qr_code), ex.getMessage());            
        }        

        try {
            new EnrollmentChallenge("surfenroll://file:does-not-exist", getContext(), true); // files allowed, but non existing
            assertFalse("No expected enrollment challenge exception thrown.", true);
        }
        catch (UserException ex) {
            assertEquals(getContext().getString(R.string.error_enroll_connect_error), ex.getMessage());            
        }

        String logoURL = "http://www.surfnet.nl/Style%20Library/SURFnet/img/surfnet_logo.gif";
        assertEnrollmentChallengeException("...", R.string.error_enroll_invalid_response);        
        assertEnrollmentChallengeException("{}", R.string.error_enroll_invalid_response);        
        assertEnrollmentChallengeException("{ service: { identifier: 'example.org', displayName: 'Dummy Service', logoUrl: '" + logoURL + "', authenticationUrl: '' }, identity: { identifier: 'john.doe', displayName: 'John Doe' } }", R.string.error_enroll_invalid_response);
        assertEnrollmentChallengeException("{ service: { identifier: 'example.org', displayName: 'Dummy Service', logoUrl: '" + logoURL + "', authenticationUrl: '', enrollmentUrl: 'http://example.org/enroll/' } }", R.string.error_enroll_invalid_response);
        assertEnrollmentChallengeException("{ identity: { identifier: 'john.doe', displayName: 'John Doe' } }", R.string.error_enroll_invalid_response);
        assertEnrollmentChallengeException("{ service: { identifier: 'other.org', displayName: 'Other Dummy Service', logoUrl: '', authenticationUrl: '', enrollmentUrl: 'http://example.org/enroll/' }, identity: { identifier: 'jane.doe', displayName: 'Jane Doe' } }", R.string.error_enroll_logo_error);
        
        try {
            _createEnrollmentChallenge("{ service: { identifier: 'example.org', displayName: 'Dummy Service', logoUrl: '" + logoURL + "', authenticationUrl: '', enrollmentUrl: 'http://example.org/enroll/' }, identity: { identifier: 'john.doe', displayName: 'John Doe' } }");            
        }
        catch (UserException ex) {
            assertEquals(getContext().getString(R.string.error_enroll_already_enrolled, new Object[] { "John Doe", "Dummy Service" } ), ex.getMessage());            
        }        
    }
    
    /**
     * Test an enrollment challenge with a new identity for a new service.
     * 
     * @throws UserException
     */
    public void testChallengeWithNewService() throws UserException
    {
        String logoURL = "http://www.surfnet.nl/Style%20Library/SURFnet/img/surfnet_logo.gif";        
        EnrollmentChallenge challenge = _createEnrollmentChallenge("{ service: { identifier: 'other', displayName: 'Other Dummy Service', logoUrl: '" + logoURL + "', authenticationUrl: 'http://other.org/auth/', enrollmentUrl: 'http://other.org/enroll/' }, identity: { identifier: 'john.doe', displayName: 'John Doe' } }");
        assertNotNull(challenge.getService());
        assertTrue(challenge.getService().isNew());
        assertEquals("other", challenge.getService().getIdentifier());
        assertEquals("Other Dummy Service", challenge.getService().getDisplayName());        
        assertTrue(challenge.getService().getLogoBitmap() instanceof Bitmap);
        assertEquals("http://other.org/auth/", challenge.getService().getAuthenticationURL());
        assertNotNull(challenge.getIdentity());
        assertTrue(challenge.getIdentity().isNew());
        assertEquals("john.doe", challenge.getIdentity().getIdentifier());
        assertEquals("John Doe", challenge.getIdentity().getDisplayName());
        assertEquals("http://other.org/enroll/", challenge.getEnrollmentURL());
    }
    
    /**
     * Test an enrollment challenge with a new identity for an existing service.
     * 
     * @throws UserException
     */
    public void testChallengeWithExistingService() throws UserException
    {
        String logoURL = "http://www.surfnet.nl/Style%20Library/SURFnet/img/surfnet_logo.gif";        
        EnrollmentChallenge challenge = _createEnrollmentChallenge("{ service: { identifier: 'example.org', displayName: 'Example Service', logoUrl: '" + logoURL + "', authenticationUrl: 'http://other.org/auth/', enrollmentUrl: 'http://other.org/enroll/' }, identity: { identifier: 'jane.doe', displayName: 'Jane Doe' } }");
        assertNotNull(challenge.getService());
        assertFalse(challenge.getService().isNew());
        assertEquals("example.org", challenge.getService().getIdentifier());
        assertEquals("Dummy Service", challenge.getService().getDisplayName()); // we don't support updates on existing services, so name should still be the old one       
        assertNotNull(challenge.getIdentity());
        assertTrue(challenge.getIdentity().isNew());
        assertEquals("jane.doe", challenge.getIdentity().getIdentifier());
        assertEquals("Jane Doe", challenge.getIdentity().getDisplayName());
        assertEquals("http://other.org/enroll/", challenge.getEnrollmentURL());
    }    
}