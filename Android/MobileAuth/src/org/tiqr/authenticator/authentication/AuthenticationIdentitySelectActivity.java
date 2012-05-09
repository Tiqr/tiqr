package org.tiqr.authenticator.authentication;

import org.tiqr.authenticator.auth.AuthenticationChallenge;
import org.tiqr.authenticator.auth.Challenge;
import org.tiqr.authenticator.datamodel.Identity;
import org.tiqr.authenticator.general.AbstractActivityGroup;
import org.tiqr.authenticator.identity.AbstractIdentityListActivity;
import android.content.Intent;
import android.database.Cursor;
import android.os.Bundle;
import android.view.View;
import android.widget.AdapterView;
import android.widget.AdapterView.OnItemClickListener;
import android.widget.ListView;

public class AuthenticationIdentitySelectActivity extends AbstractIdentityListActivity
{
    @Override
    public void onCreate(Bundle savedInstanceState) 
    {
        super.onCreate(savedInstanceState);
        
        ListView lv = getListView();
      
        lv.setOnItemClickListener(new OnItemClickListener() {
          public void onItemClick(AdapterView<?> parent, View view, int position, long id) {

              Cursor c = getIdentityCursor();
              c.moveToPosition(position);        
              Identity identity = _db.createIdentityObjectForCurrentCursorPosition(c);
              
              AuthenticationChallenge challenge = (AuthenticationChallenge)_getChallenge();
              challenge.setIdentity(identity);
              
              _doAuthentication();
              
          }

        });

    }
    
    /**
     * Returns the challenge.
     * 
     * @return challenge
     */
    protected Challenge _getChallenge()
    {
        AbstractActivityGroup parent = (AbstractActivityGroup) getParent();
        return parent.getChallenge();
    }
    
    @Override
    public Cursor getIdentityCursor()
    {
        // The default implementation reuses _identitiesCursor but every time we open the select screen
        // we need to get a fresh cursor.
        long identityProviderId = _getChallenge().getIdentityProvider().getId();
        return _db.findIdentitiesByIdentityProviderIdWithIdentityProviderData(identityProviderId); 
    }

    private void _doAuthentication()
    {
    	AuthenticationActivityGroup group = (AuthenticationActivityGroup)getParent();
        Intent confirmIntent = new Intent(this, AuthenticationConfirmationActivity.class);                      
        group.startChildActivity("AuthenticateConfirmationActivity", confirmIntent);      
    }

}
