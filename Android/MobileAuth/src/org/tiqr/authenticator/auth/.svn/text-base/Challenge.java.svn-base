package org.tiqr.authenticator.auth;

import org.tiqr.authenticator.datamodel.DbAdapter;
import org.tiqr.authenticator.datamodel.Identity;
import org.tiqr.authenticator.datamodel.IdentityProvider;
import org.tiqr.authenticator.exceptions.UserException;

import android.content.Context;

/**
 * Challenge base class. Provides an interface for parsing challenges 
 * from a QR code (or other source).
 * 
 */
public abstract class Challenge
{
    private String _rawChallenge;
    private Context _context;
    private DbAdapter _dbAdapter;
    private IdentityProvider _identityProvider;
    private Identity _identity;
    private String _returnURL;
   
    /**
     * Constructs a new challenge. The given raw challenge will immediately
     * be parsed. In case of a failure an exception is thrown.
     * 
     * @param rawChallenge raw challenge
     * @param context      Android context
     * @param parse        immediately start parsing?
     * 
     * @throws Exception
     */
    public Challenge(String rawChallenge, Context context, boolean parse) throws UserException
    {
        _rawChallenge = rawChallenge;
        _context = context;
        
        if (parse) {
            _parseRawChallenge();
        }
    }
    
    /**
     * Returns the raw challenge.
     * 
     * @return raw challenge
     */
    protected String _getRawChallenge()
    {
        return _rawChallenge;
    }
    
    /**
     * Returns the Android context.
     * 
     * @return context
     */
    protected Context _getContext()
    {
        return _context;
    }
    
    /**
     * Sets the identity provider for this challenge.
     * 
     * @param IdentityProvider
     */
    protected void _setIdentityProvider(IdentityProvider identityProvider)
    {
        _identityProvider = identityProvider;
    }
    
    /**
     * Returns the identity provider for this challenge.
     * 
     * @return IdentityProvider
     */
    public IdentityProvider getIdentityProvider()
    {
        return _identityProvider;
    }

    /**
     * Sets the identity for this challenge.
     */
    protected void _setIdentity(Identity identity)
    {
        _identity = identity;
    }
    
    /**
     * Returns the identity for this challenge, might be null.
     * 
     * @return Identity.
     */
    public Identity getIdentity()
    {
        return _identity;
    }    
    
    /**
     * Sets the return URL for this challenge.
     */
    protected void _setReturnURL(String returnURL)
    {
        _returnURL = returnURL;
    }
    
    /**
     * Return URL, for example if invoked from a website on the device which wants the user to return
     * to the website after successful authentication.
     * 
     * @return return URL.
     */
    public String getReturnURL()
    {
        return _returnURL;
    }    
    
    /**
     * Gets the string resource for the given resource identifier.
     * 
     * @param resourceId resource identifier
     * 
     * @return string resource
     */
    protected String _getString(int resourceId) 
    {
        return _context.getString(resourceId);
    }
    
    
    /**
     * Gets the string resource for the given resource identifier
     * and uses the given arguments as formatter arguments.
     * 
     * @param resourceId resource identifier
     * @param args       formatter arguments
     * 
     * @return string resource
     */
    protected String _getString(int resourceId, Object[] args) 
    {
        return _context.getString(resourceId, args);
    }    

    /**
     * Creates and returns the database adapter instance.
     * 
     * If an instance has been created, the same instance will be
     * returned on subsequent calls.
     * 
     * @return DbAdapter database adapter
     */
    protected DbAdapter _getDbAdapter()
    {
        if (_dbAdapter == null) {
            _dbAdapter = new DbAdapter(_getContext());
        }
        
        return _dbAdapter;
    }
    
    /**
     * Parse raw challenge.
     * 
     * @throws Exception
     */
    protected abstract void _parseRawChallenge() throws UserException;
}
