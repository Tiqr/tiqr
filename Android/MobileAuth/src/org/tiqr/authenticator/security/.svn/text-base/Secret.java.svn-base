package org.tiqr.authenticator.security;

import java.security.InvalidKeyException;

import javax.crypto.SecretKey;
import javax.crypto.spec.SecretKeySpec;

import android.app.Activity;
import android.content.Context;
import org.tiqr.authenticator.datamodel.Identity;
import org.tiqr.authenticator.exceptions.SecurityFeaturesException;

public class Secret 
{
    private Identity _identity = null;
    private SecretKey _secret  = null;
    private SecretStore _store = null;
    private Context _ctx = null;
    
    public static Secret secretForIdentity(Identity identity, Activity context)
    {
        Secret s = new Secret(identity, context);
        return s;
    }
    
    private Secret(Identity identity, Activity context)
    {
        _identity = identity;
        _store = new SecretStore(context);
        _ctx = context;
    }
    
    public SecretKey getSecret(SecretKey sessionKey) throws InvalidKeyException, SecurityFeaturesException
    {        
        if (_secret==null) {
            _secret = _loadFromKeyStore(sessionKey);
        } 
        return _secret;
    }
    
    public void setSecret(SecretKey secret)
    {
        _secret = secret;
    }
    
    private SecretKey _loadFromKeyStore(SecretKey sessionKey) throws SecurityFeaturesException, InvalidKeyException
    {
        SecretKey x = _store.getSecretKey(Long.toString(_identity.getId()), Encryption.getDeviceKey(_ctx));
        return new SecretKeySpec(Encryption.decrypt(x.getEncoded(), sessionKey), "RAW");
    }
    
    public void storeInKeyStore(SecretKey sessionKey) throws SecurityFeaturesException
    {
        SecretKey encrypted = new SecretKeySpec(Encryption.encrypt(_secret.getEncoded(), sessionKey), "RAW");
         _store.setSecretKey(Long.toString(_identity.getId()), encrypted, Encryption.getDeviceKey(_ctx));
    }
    
    public void deleteFromKeyStore(SecretKey sessionKey) throws SecurityFeaturesException
    {
        _store.removeSecretKey(Long.toString(_identity.getId()), Encryption.getDeviceKey(_ctx));
    }
    
}
