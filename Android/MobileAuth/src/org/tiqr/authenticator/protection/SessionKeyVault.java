package org.tiqr.authenticator.protection;

import javax.crypto.SecretKey;

import android.os.Handler;
import android.os.Message;

public class SessionKeyVault 
{
    private SecretKey _theKey = null;

    private DeleteHandler _deleteHandler = null;
    
    protected class DeleteHandler extends Handler
    {
        @Override
        public void handleMessage(Message msg) {
            switch (msg.what) {
            case DELETE_KEY_MESSAGE:
                SessionKeyVault.this._theKey = null;
                break;
            }
            super.handleMessage(msg);
        }

    }
     
    public SessionKeyVault()
    {
        _deleteHandler = this.new DeleteHandler();
    }
    
    public void storeKey(SecretKey key)
    {
        _theKey = key;
        
        // Key refresh, remove any pending delete actions
        _deleteHandler.removeMessages(DELETE_KEY_MESSAGE);
        
        // Key remains valid for 5 minutes.
        Message msg = new Message();
        msg.what = DELETE_KEY_MESSAGE;
        _deleteHandler.sendMessageDelayed(msg, SESSION_KEY_LIFETIME);
    }
    
    public SecretKey retrieveKey()
    {
        return _theKey;
    }
        
    private static final int DELETE_KEY_MESSAGE = 0;
    
    //time in milliseconds
    private static final long SESSION_KEY_LIFETIME = 5*60*1000;
        
    protected void finalize() throws Throwable
    {
        // If we die, cancel any remaining messages
        _deleteHandler.removeMessages(DELETE_KEY_MESSAGE);
        
        
        super.finalize(); 
    } 
    
}
