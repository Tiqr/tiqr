package org.tiqr.authenticator.auth;

import java.io.UnsupportedEncodingException;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLDecoder;

import org.tiqr.authenticator.R;
import org.tiqr.authenticator.datamodel.Identity;
import org.tiqr.authenticator.datamodel.IdentityProvider;
import org.tiqr.authenticator.exceptions.UserException;

import android.content.Context;

/**
 * Represents an authentication challenge.
 */
public class AuthenticationChallenge extends Challenge
{
    private String _sessionKey;
    private String _challenge;
    private String _serviceProviderDisplayName;
    private String _serviceProviderIdentifier;

	/**
     * Constructs a new authentication challenge with the given raw challenge.
     * 
     * The raw challenge is immediately parsed, an exception is thrown when an error occurs.
     * 
     * @param rawChallenge raw challenge
     * @param context      Android context
     * 
     * @throws Exception
     */
    public AuthenticationChallenge(String rawChallenge, Context context) throws UserException
    {
        super(rawChallenge, context, true);
    }
    
    /**
     * Sets the identity (this can be necessary if initially the user needs to choose one
     * from several identities).
     */
    public void setIdentity(Identity identity)
    {
        _setIdentity(identity);
    }
    
    /**
     * The session key for this challenge.
     * 
     * @return session key
     */
    public String getSessionKey() 
    {
        return _sessionKey;
    }
    
    /**
     * The authentication challenge, used to verify the request (not to be confused with the
     * raw challenge!).
     * 
     * @return authentication challenge
     */
    public String getChallenge()
    {
        return _challenge;
    }
    
    /**
     * The service provider (readable) name
     * 
	 * @return the service provider
	 */
	public String getServiceProviderDisplayName() {
		return _serviceProviderDisplayName;
	}

	/** 
	 * @param String the service provider display name to set
	 */
	public void setServiceProviderDisplayName(String serviceProviderDisplayName) {
		_serviceProviderDisplayName = serviceProviderDisplayName;
	}

	/**
 	 * A unique identifier for the service provider
 	 * 
	 * @return the service provider identifier
	 */
	public String getServiceProviderIdentifier() {
		return _serviceProviderIdentifier;
	}

	/**
	 * @param String the service provider identifier to set
	 */
	public void setServiceProviderIdentifier(String serviceProviderIdentifier) {
		_serviceProviderIdentifier = serviceProviderIdentifier;
	}
    
    /**
     * Parses the raw authentication challenge.
     * 
     * @throws Exception
     */
    @Override
    protected void _parseRawChallenge() throws UserException
    {
        if (!_getRawChallenge().startsWith("tiqrauth://")) {
            throw new UserException(_getString(R.string.error_auth_invalid_qr_code));
        }
        
        URL url;
        
        try {
            url = new URL(_getRawChallenge().replaceFirst("tiqrauth://", "http://"));
        }
        catch (MalformedURLException ex) {
            throw new UserException(_getString(R.string.error_auth_invalid_qr_code));
        }
        
        String[] pathComponents = url.getPath().split("/");
        if (pathComponents.length < 3) {
            throw new UserException(_getString(R.string.error_auth_invalid_qr_code));
        }
        
        IdentityProvider ip = _getDbAdapter().getIdentityProviderByIdentifierAsObject(url.getHost());
        if (ip == null) {
            throw new UserException(_getString(R.string.error_auth_unknown_identity_provider));
        }
        
        _setIdentityProvider(ip);
        
        Identity identity = null;
        
        if (url.getUserInfo() != null) {
            identity = _getDbAdapter().getIdentityByIdentifierAndIdentityProviderIdAsObject(url.getUserInfo(), ip.getId());
            if (identity == null) {
                throw new UserException(_getString(R.string.error_auth_unknown_identity));
            }
            
        } else {
            Identity[] identities = _getDbAdapter().findIdentitiesByIdentityProviderIdAsObjects(ip.getId());
            
            if (identities == null || identities.length == 0) {
                throw new UserException(_getString(R.string.error_auth_no_identities_for_identity_provider));
            }
            
            identity = identities.length == 1 ? identities[0] : null;
        }
        
        _setIdentity(identity);
        
        _sessionKey = pathComponents[1];
        _challenge = pathComponents[2];
        
        if (pathComponents.length > 3) {
        	_serviceProviderDisplayName = pathComponents[3];
        } else {
        	_serviceProviderDisplayName = _getString(R.string.unknown);
        }
        _serviceProviderIdentifier = "";
        
        String returnURL = url.getQuery() == null || url.getQuery().length() == 0 ? null : url.getQuery();
        if (returnURL != null && returnURL.matches("^http(s)?://.*")) {
            try {
                _setReturnURL(URLDecoder.decode(returnURL, "UTF-8"));
            } catch (UnsupportedEncodingException e) {
                // never happens...
            }
        }
    }
}
