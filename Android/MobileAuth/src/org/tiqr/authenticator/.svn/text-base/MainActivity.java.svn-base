package org.tiqr.authenticator;

import org.tiqr.authenticator.authentication.AuthenticationActivityGroup;
import org.tiqr.authenticator.datamodel.DbAdapter;
import org.tiqr.authenticator.enrollment.EnrollmentActivityGroup;

import android.content.Intent;
import android.os.Bundle;

import com.google.android.c2dm.C2DMessaging;

public class MainActivity extends TiqrActivity
{

    /** Called when the activity is first created. */
    @Override
    public void onCreate(Bundle savedInstanceState)
    {
        super.onCreate(savedInstanceState, R.layout.main);
        
        DbAdapter db = new DbAdapter(this);
        
        if (db.identityCount()>0) {
        
        	showIdentityButton();
        	loadContentsIntoWebView(R.raw.authentication_intro, R.id.webview);
        	
        } else {
        	hideIdentityButton();
        	loadContentsIntoWebView(R.raw.welcome, R.id.webview);
        }

       // Resources res = getResources(); // Resource object to get Drawables
        
        //Intent intent; // Reusable Intent for each tab

        // Create an Intent to launch an Activity for the tab (to be reused)
       // intent = new Intent().setClass(this, AuthenticationActivityGroup.class);

        
        // Do the same for the other tabs
        //intent = new Intent().setClass(this, IdentityActivityGroup.class);
       

       // intent = new Intent().setClass(this, AboutActivity.class);
 
    }

    public void onStart()
    {
        super.onStart();

        String deviceToken = C2DMessaging.getRegistrationId(this);
        if (deviceToken != null && !"".equals(deviceToken)) {
            NotificationRegistration.sendRequestWithDeviceToken(this, deviceToken);
        } else {
            C2DMessaging.register(this, C2DMReceiver.SENDER_ID);
        }
        
        // Handle tiqrauth:// and tiqrenroll:// URLs
        final Intent intent = getIntent();
        final String action = intent.getAction();
        if (Intent.ACTION_VIEW.equals(action)) {
            String rawChallenge = intent.getDataString();
            if (rawChallenge.startsWith("tiqrauth://")) {
                Intent authIntent = new Intent(this.getApplicationContext(), AuthenticationActivityGroup.class);
                authIntent.putExtra("org.tiqr.rawChallenge", rawChallenge);
                startActivity(authIntent);                
            } else if (rawChallenge.startsWith("tiqrenroll://")) {
                Intent enrollIntent = new Intent(this.getApplicationContext(), EnrollmentActivityGroup.class);
                enrollIntent.putExtra("org.tiqr.rawChallenge", rawChallenge);
                startActivity(enrollIntent);
            }
        }
    }

    public void showIncompatibilityDialog()
    {
        new IncompatibilityDialog().show(this);

    }
}
