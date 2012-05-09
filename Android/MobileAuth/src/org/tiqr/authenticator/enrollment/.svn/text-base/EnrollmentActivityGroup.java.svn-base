package org.tiqr.authenticator.enrollment;

import org.tiqr.authenticator.R;
import org.tiqr.authenticator.auth.EnrollmentChallenge;
import org.tiqr.authenticator.exceptions.UserException;
import org.tiqr.authenticator.general.AbstractActivityGroup;

import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.os.Bundle;

public class EnrollmentActivityGroup extends AbstractActivityGroup {

	@Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        
        String rawChallenge = getIntent().getStringExtra("org.tiqr.rawChallenge");
        
        try {
        	onChallenge(rawChallenge);
        } catch (UserException ex) {
        	
        	// @todo we need to validate the challenge BEFORE we leave the scanner; this is too late so it looks weird.
            // @todo replace with a proper activity
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
	}
	
	/**
	 * Check if the device has an active internet connection
	 * 
	 * @return boolean
	 */
    public boolean hasConnection()
    {
        ConnectivityManager connectivityManager = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkInfo activeNetworkInfo = connectivityManager.getActiveNetworkInfo();
        return activeNetworkInfo != null;
    }
	
    /**
     * Handle challenge.
     * 
     * @param rawChallenge
     * @throws UserException 
     */
    public void onChallenge(String rawChallenge) throws UserException
    {
    	EnrollmentChallenge challenge = new EnrollmentChallenge(rawChallenge, this);
        Intent intent = new Intent().setClass(this, EnrollmentConfirmationActivity.class);
        setChallenge(challenge);
        startChildActivity("EnrollmentConfirmationActivity", intent);  
    }
}
