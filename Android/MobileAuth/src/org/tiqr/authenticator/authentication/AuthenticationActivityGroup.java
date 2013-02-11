package org.tiqr.authenticator.authentication;

import org.tiqr.authenticator.R;
import org.tiqr.authenticator.auth.AuthenticationChallenge;
import org.tiqr.authenticator.exceptions.UserException;
import org.tiqr.authenticator.general.AbstractActivityGroup;

import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;

/**
 * Authentication activity group.
 */
public class AuthenticationActivityGroup extends AbstractActivityGroup
{
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        
        String rawChallenge = getIntent().getStringExtra("org.tiqr.rawChallenge");
        String protocolVersion = getIntent().getStringExtra("org.tiqr.protocolVersion");
        
        try {
        	onChallenge(rawChallenge, protocolVersion);
        } catch (UserException ex) { // todo, we need to validate the challenge BEFORE we leave the scanner; this is too late so it looks weird.
                new AlertDialog.Builder(this)
                    .setTitle(getString(R.string.authentication_failure_title))
                    .setMessage(ex.getMessage())
                    .setCancelable(false)
                    .setPositiveButton(R.string.ok_button, new DialogInterface.OnClickListener() {
                        public void onClick(DialogInterface dialog, int whichButton) {
                        	finish(); // go back to scanning.
                        }}) 
                    .show();
         
        }
        
        //startChildActivity("AuthenticationIdentitySelectActivity", intent);
    }
    
    /**
     * Handle challenge.
     * 
     * @param rawChallenge
     * @throws UserException 
     */
    public void onChallenge(String rawChallenge, String protocolVersion) throws UserException
    {
    
        AuthenticationChallenge challenge = new AuthenticationChallenge(rawChallenge, this, protocolVersion);
        setChallenge(challenge);
        
        if (challenge.getIdentity() == null) {
            Intent selectIntent = new Intent(this, AuthenticationIdentitySelectActivity.class);              
            startChildActivity("AuthenticationIdentitySelectActivity", selectIntent);
        } else {
            Intent confirmIntent = new Intent(this, AuthenticationConfirmationActivity.class);                      
            startChildActivity("AuthenticateConfirmationActivity", confirmIntent);
        }
        
    }
}
