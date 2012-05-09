package org.tiqr.authenticator.authentication;

import java.security.InvalidKeyException;

import javax.crypto.SecretKey;

import org.tiqr.authenticator.IncompatibilityDialog;
import org.tiqr.authenticator.R;
import org.tiqr.authenticator.auth.AuthenticationChallenge;
import org.tiqr.authenticator.exceptions.InvalidChallengeException;
import org.tiqr.authenticator.exceptions.SecurityFeaturesException;
import org.tiqr.authenticator.general.AbstractActivityGroup;
import org.tiqr.authenticator.general.ErrorActivity;
import org.tiqr.authenticator.general.ErrorView;
import org.tiqr.authenticator.security.Encryption;
import org.tiqr.authenticator.security.OCRAWrapper;
import org.tiqr.authenticator.security.Secret;

import android.app.Activity;
import android.content.Intent;
import android.graphics.Color;
import android.os.Bundle;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Button;
import android.widget.TextView;

/**
 * Confirmation dialog for authentication challenge.
 */
public class AuthenticationFallbackActivity extends Activity
{

    /** Called when the activity is first created. */
    @Override
    public void onCreate(Bundle savedInstanceState)
    {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.fallback);
        
        TextView title = (TextView)findViewById(R.id.error_title);
        title.setText(R.string.authentication_fallback_title);
        
        TextView message = (TextView)findViewById(R.id.error_message);
        message.setText(R.string.authentication_fallback_description);
        
        TextView identifier = (TextView)findViewById(R.id.identifier);
        identifier.setText(((AuthenticationActivityGroup)getParent()).getChallenge().getIdentity().getIdentifier());
        
        ErrorView ev = (ErrorView)findViewById(R.id.fallbackErrorView);
        ev.setErrorColor(Color.rgb(25, 0, 165));
        ev.setVisibility(View.VISIBLE);
        
        Button ok = (Button)findViewById(R.id.confirm_button);
        ok.setText(R.string.authentication_fallback_button);
        
        if (ok != null) {
			ok.setOnClickListener(new OnClickListener() {
				public void onClick(View v) {
					AbstractActivityGroup parent = (AbstractActivityGroup) getParent();
					parent.finish();
				}
			});
		}
        
    	String pincode = getIntent().getStringExtra("org.tiqr.authentication.pincode");
        _fetchOTP(pincode);
    }
    
    /**
     * Given the pincode, try to fetch a One Time Password from the server and set it in the view
     * 
     * @param pincode
     */
    protected void _fetchOTP(String pincode)
    {	
    	try {
			SecretKey sessionKey = Encryption.keyFromPassword(getParent(), pincode);
			AbstractActivityGroup parent = (AbstractActivityGroup) getParent();
			AuthenticationChallenge challenge = (AuthenticationChallenge) parent.getChallenge();
			Secret secret = Secret.secretForIdentity(challenge.getIdentity(), this);
			SecretKey secretKey = secret.getSecret(sessionKey);

            String otp = OCRAWrapper.generateOCRA(
                    challenge.getIdentityProvider().getOCRASuite(), 
                    secretKey.getEncoded(), 
                    challenge.getChallenge(), 
                    challenge.getSessionKey());

            TextView otpView = (TextView) findViewById(R.id.otp);
            otpView.setText(otp);
    	} catch (InvalidChallengeException e) {
    		_showErrorActivityWithMessage(getString(R.string.authentication_failure_title), getString(R.string.error_auth_invalid_challenge));
    	} catch (ArrayIndexOutOfBoundsException e) {
        	_showErrorActivityWithMessage(getString(R.string.authentication_failure_title), getString(R.string.error_auth_server_incompatible));
        } catch (InvalidKeyException e) {
        	_showErrorActivityWithMessage(getString(R.string.authentication_failure_title), getString(R.string.error_auth_invalid_key));
        } catch (SecurityFeaturesException e) {
            new IncompatibilityDialog().show(this);
        } catch (NumberFormatException e) {
        	_showErrorActivityWithMessage(getString(R.string.authentication_failure_title), getString(R.string.error_auth_invalid_challenge));
        }
    }
    
    /**
     * Shows the error activity if needed, with a title and message
     * 
     * @param title
     * @param message
     */
    protected void _showErrorActivityWithMessage(String title, String message) {
    	AbstractActivityGroup parent = (AbstractActivityGroup) getParent();
    	Intent intent = new Intent().setClass(this, ErrorActivity.class);
    	
    	intent.putExtra("org.tiqr.error.title", title);
    	intent.putExtra("org.tiqr.error.message", message);
    	
        parent.startChildActivity("ErrorActivity", intent);
    }
}