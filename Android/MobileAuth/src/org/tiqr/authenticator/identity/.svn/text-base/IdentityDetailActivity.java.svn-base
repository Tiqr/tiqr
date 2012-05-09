package org.tiqr.authenticator.identity;

import org.tiqr.authenticator.R;
import org.tiqr.authenticator.datamodel.DbAdapter;
import org.tiqr.authenticator.datamodel.Identity;
import org.tiqr.authenticator.datamodel.IdentityProvider;

import android.app.Activity;
import android.os.Bundle;
import android.text.util.Linkify;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;

public class IdentityDetailActivity extends Activity
{
	
	protected Identity _identity;
	protected IdentityProvider _identityProvider;
	protected DbAdapter _db;

    /**
     *  Called when the activity is first created. 
     */
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        
        setContentView(R.layout.identity_detail);
        
        _db = new DbAdapter(this); 
        _setIdentityAndIdentityProvider();
        
        if (_identity != null && _identityProvider != null) {
        	TextView identity_displayName = (TextView)findViewById(R.id.identity_displayName);
        	TextView identity_identifier = (TextView)findViewById(R.id.identity_identifier);
        	TextView identity_provider_displayName = (TextView)findViewById(R.id.identity_provider_displayName);
        	TextView identity_provider_identifier = (TextView)findViewById(R.id.identity_provider_identifier);
        	TextView identity_provider_info_url = (TextView)findViewById(R.id.identity_provider_infoURL);
        	ImageView identity_provider_logo = (ImageView)findViewById(R.id.identity_provider_logo);
        	
        	identity_displayName.setText(_identity.getDisplayName());
        	identity_identifier.setText(_identity.getIdentifier());        	
        	identity_provider_displayName.setText(_identityProvider.getDisplayName());
        	identity_provider_identifier.setText(_identityProvider.getIdentifier());
            identity_provider_logo.setImageBitmap(_identityProvider.getLogoBitmap());
            identity_provider_info_url.setText(_identityProvider.getInfoURL());
            
            if (_identity.isBlocked()) {
            	TextView identity_blocked_message = (TextView)findViewById(R.id.identity_blocked_message);
            	identity_blocked_message.setVisibility(View.VISIBLE);
            }
            
            // Make the info url clickable
            Linkify.addLinks(identity_provider_info_url, Linkify.WEB_URLS);
        }
        
        Button delete_btn = (Button)findViewById(R.id.delete_button);
        delete_btn.setOnClickListener(new OnClickListener() {
			
			@Override
			public void onClick(View v) {
				_deleteIdentity();
			}
		});
        
    }
    
    /**
     * Fetch the identity and identity provider
     */
    protected void _setIdentityAndIdentityProvider() {
    	 long identity_id = getIntent().getLongExtra("org.tiqr.identity.id", 0);
    	 
    	 _identity = _db.getIdentityByIdentityId(identity_id);
    	 _identityProvider = _db.getIdentityProviderForIdentityId(identity_id);
    }
    
    /**
     * 
     */
    protected void _deleteIdentity()
    {
    	_db.deleteIdentity(_identity.getId());
    	finish();
    }
}
