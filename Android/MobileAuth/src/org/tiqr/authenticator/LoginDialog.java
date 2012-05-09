package org.tiqr.authenticator;

import java.util.Hashtable;

import javax.crypto.SecretKey;

import org.tiqr.authenticator.exceptions.SecurityFeaturesException;
import org.tiqr.authenticator.protection.SessionKeyAvailabilityListener;
import org.tiqr.authenticator.protection.SessionKeyVault;
import org.tiqr.authenticator.security.Encryption;
import android.app.Activity;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.EditText;
import android.widget.TextView;

public class LoginDialog
{
    private Activity _activity = null;
    private SessionKeyAvailabilityListener _delegate = null;
 //   private String _identityIdentifier = null;
    
    private static Hashtable<String, SessionKeyVault> _keyCache = null;
    
    protected SessionKeyVault _getKeyVault(String identityIdentifier)
    {
        if (_keyCache==null) {
            _keyCache = new Hashtable<String, SessionKeyVault>();
        }
        
        if (_keyCache.get(identityIdentifier)==null) {
            _keyCache.put(identityIdentifier, new SessionKeyVault());
        }
        
        return _keyCache.get(identityIdentifier);
    }
    
    public LoginDialog(Activity activity, String identityIdentifier, SessionKeyAvailabilityListener delegate)
    {
        _activity = activity;
        _delegate = delegate;
 //       _identityIdentifier = identityIdentifier;
    }
    
    public static void newSessionKey(Activity owner, String identityIdentifier, SessionKeyAvailabilityListener delegate)
    {
        LoginDialog d = new LoginDialog(owner, identityIdentifier, delegate);
        d.showPasswordDialog(0, true);
    }
    
    public static void requestSessionKey(Activity owner, String identityIdentifier, SessionKeyAvailabilityListener delegate)
    {
        LoginDialog d = new LoginDialog(owner, identityIdentifier, delegate);
        
        SecretKey cached = d._getCachedKey(identityIdentifier);
        if (cached==null) {
            d.showPasswordDialog(0, false);
        } else {
            // Immediately continue with the cached key.
            delegate.onSessionKeyAvailable(cached);
        }
    }
    
    private SecretKey _getCachedKey(String identityIdentifier)
    {
        return _getKeyVault(identityIdentifier).retrieveKey();
    }
    
    public void showPasswordDialog(int message, final boolean newPassword) 
    {
        LayoutInflater factory = LayoutInflater.from(_activity);
        final View textEntryView = factory.inflate(R.layout.login, null);
  
        AlertDialog.Builder popup = new AlertDialog.Builder(_activity);                 
               
        TextView t = (TextView)textEntryView.findViewById(R.id.message);
        if (message!=0) {
            t.setText(message);
            t.setVisibility(View.VISIBLE);
        } else {
            t.setVisibility(View.INVISIBLE);
        }
        
        if (!newPassword) {
            popup.setTitle(R.string.login_title);  
            popup.setMessage(R.string.login_intro);               
            textEntryView.findViewById(R.id.password_verify).setVisibility(View.INVISIBLE);
            textEntryView.findViewById(R.id.password_verify_label).setVisibility(View.INVISIBLE);

        } else {
            popup.setTitle(R.string.choose_password_title);
            popup.setMessage(R.string.choose_password_intro);
            textEntryView.findViewById(R.id.password_verify).setVisibility(View.VISIBLE);
            textEntryView.findViewById(R.id.password_verify_label).setVisibility(View.VISIBLE);
        }
        popup.setView(textEntryView);
           
        popup.setPositiveButton("Ok", new DialogInterface.OnClickListener() {  
            public void onClick(DialogInterface dialog, int whichButton) {  

                EditText pwd = (EditText)textEntryView.findViewById(R.id.password);

                try
                {
                    if (newPassword) {
                        EditText pwd_verify = (EditText)textEntryView.findViewById(R.id.password_verify);
                        
                        if (!pwd.getText().toString().equals(pwd_verify.getText().toString())) {
                            showPasswordDialog(R.string.passwords_dont_match, newPassword);
                            return;
                        }
                    } 
                        
                    SecretKey sessionKey = Encryption.keyFromPassword(_activity, pwd.getText().toString()); 
                        
                       // CACHING DISABLED: AT THIS POINT WE DON'T KNOW YET WHETHER THE PIN IS CORRECT!
  //                  if (!newPassword) {
  //                      // Cache the pin for a few minutes
  //                      _getKeyVault(_identityIdentifier).storeKey(sessionKey);
  //                   }                        

                    if (_delegate!=null) {
                        _delegate.onSessionKeyAvailable(sessionKey);
                    }
                        
                } 
                catch (SecurityFeaturesException e)
                {
                    new IncompatibilityDialog().show(_activity);
                }

                return;                  
            }  
        });  
        
        popup.show();   
    }
            
}
