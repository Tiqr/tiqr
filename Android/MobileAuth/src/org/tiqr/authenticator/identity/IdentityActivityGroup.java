package org.tiqr.authenticator.identity;

import org.tiqr.authenticator.R;
import org.tiqr.authenticator.auth.EnrollmentChallenge;
import org.tiqr.authenticator.enrollment.EnrollmentConfirmationActivity;
import org.tiqr.authenticator.exceptions.UserException;
import org.tiqr.authenticator.general.AbstractActivityGroup;

import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;

public class IdentityActivityGroup extends AbstractActivityGroup
{
        
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
       
        String rawChallenge = getIntent().getStringExtra("org.tiqr.rawChallenge");
  //      if (rawChallenge != null) {
	        try {
	        	onChallenge(rawChallenge);
	        
	        } catch (UserException ex) { // todo, we need to validate the challenge BEFORE we leave the scanner; this is too late so it looks weird.
	        	new AlertDialog.Builder(this)
	            .setTitle(getString(R.string.enrollment_failure_title))
	            .setMessage(ex.getMessage())
	            .setCancelable(false)
	            .setPositiveButton(R.string.ok_button, new DialogInterface.OnClickListener() {
	                public void onClick(DialogInterface dialog, int whichButton) {
	                	finish(); // go back to scanning
	                }}) 
	            .show();
	  
	        }
//        } else {
  //      	
    //    	Intent intent = new Intent().setClass(this, IdentityAdminActivity.class);
      //      startChildActivity("IdentityAdminActivity", intent); 
        	
      //  }
        
    }
    
    /**
     * Handle challenge.
     * 
     * @param rawChallenge raw challenge
     * @throws UserException 
     */
    public void onChallenge(String rawChallenge) throws UserException {
        EnrollmentChallenge challenge = new EnrollmentChallenge(rawChallenge, this);
        Intent intent = new Intent().setClass(this, EnrollmentConfirmationActivity.class);
        setChallenge(challenge);
        startChildActivity("EnrollmentConfirmationActivity", intent);            
     
    }
}