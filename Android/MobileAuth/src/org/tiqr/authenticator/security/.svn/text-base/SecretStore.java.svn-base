package org.tiqr.authenticator.security;

import java.io.EOFException;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.security.KeyStore;
import java.security.KeyStore.SecretKeyEntry;
import java.security.KeyStoreException;
import java.security.NoSuchAlgorithmException;
import java.security.cert.CertificateException;

import javax.crypto.SecretKey;

import android.content.Context;
import android.util.Log;

public class SecretStore 
{
    private KeyStore _keyStore;
    private String _filenameKeyStore = "MobileAuthDb.kstore";
    private Context _ctx;
    private boolean _initialized = false;
    
    public SecretStore(Context ctx)
    {
        _ctx = ctx;
        try {
            _keyStore = KeyStore.getInstance("BKS");
        } catch (KeyStoreException e) {
            e.printStackTrace();
        }
    }
    
    private boolean _keyStoreExists()
    {
        FileInputStream input = null; 
        try { 
            // Get an instance of KeyStore 
            input = _ctx.openFileInput(_filenameKeyStore);
            input.close();
            return true;
        } catch (FileNotFoundException e) { 
            return false;
        } catch (IOException e) {
            // It exists but we can't read it?
            e.printStackTrace();
        }
        return false;
    }
    
    private boolean _createKeyStore()
    {
        boolean result = false;
        
        // Load the default Key Store 
        try {
            _keyStore.load(null, null);
            return true;

        } catch (NoSuchAlgorithmException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        } catch (CertificateException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        } catch (IOException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        }
        
        return result;
    }
    
    private char[] _sessionKeyToCharArray(SecretKey sessionKey)
    {
        return new String(sessionKey.getEncoded()).toCharArray();
    }
    
    private boolean _saveKeyStore(SecretKey sessionKey)
    {
        boolean result = false;
        
        // Load the default Key Store 
        try {
            // Create the file 
            FileOutputStream output = _ctx.openFileOutput(_filenameKeyStore, Context.MODE_PRIVATE); 

            // Save the key 
            _keyStore.store(output, _sessionKeyToCharArray(sessionKey)); 
            // Close the keystore and set the input stream 
            output.close();
            
            return true;

        } catch (NoSuchAlgorithmException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        } catch (CertificateException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        } catch (IOException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        } catch (KeyStoreException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        }
        
        return result;
    }
    
    public SecretKey getSecretKey(String identity, SecretKey sessionKey)
    {
        _initializeKeyStore(sessionKey);
        
        try {
            SecretKeyEntry entry = (SecretKeyEntry)_keyStore.getEntry(identity, new KeyStore.PasswordProtection(_sessionKeyToCharArray(sessionKey)));
            return entry.getSecretKey();
        } catch (Exception e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        } 
        
        return null;
        
    }
    
    public void setSecretKey(String identity, SecretKey key, SecretKey sessionKey)
    {
        _initializeKeyStore(sessionKey);
        
        KeyStore.SecretKeyEntry entry = new KeyStore.SecretKeyEntry(key);
        
        try {
            _keyStore.setEntry(identity, entry, new KeyStore.PasswordProtection(_sessionKeyToCharArray(sessionKey)));
            
            _saveKeyStore(sessionKey);
            
        } catch (KeyStoreException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        }
    }
    
    public void removeSecretKey(String identity, SecretKey sessionKey)
    {
        _initializeKeyStore(sessionKey);
        try {
            _keyStore.deleteEntry(identity);
            _saveKeyStore(sessionKey);

        } catch (KeyStoreException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        }
    }
    
    
    private boolean _initializeKeyStore(SecretKey sessionKey) 
    { 
        if (_initialized) {
            return true;
        }
        
        if (!_keyStoreExists()) {
            _createKeyStore();
            _saveKeyStore(sessionKey);
        }
        
        FileInputStream input = null; 
        
        try { 
            // Try and open the private key store 
            input = _ctx.openFileInput(_filenameKeyStore); 
            
            // Reset the keyStore 
            _keyStore = KeyStore.getInstance("BKS");
            
            // Load the store 
            _keyStore.load(input, _sessionKeyToCharArray(sessionKey));
            
            input.close();
            
            _initialized = true;
            
            return true;
            
        } catch (FileNotFoundException ee) { 
            Log.e("ERROR", "File not found, even though we just created it"); 
        } catch (KeyStoreException e) { 
            e.printStackTrace(); 
        } catch (CertificateException e) { 
            e.printStackTrace(); 
        }  catch (EOFException e) { 
            e.printStackTrace(); 
        } catch (IOException e) { 
            e.printStackTrace(); 
        } catch (NoSuchAlgorithmException e) { 
            e.printStackTrace(); 
        }
        return false; 
    } 
}
