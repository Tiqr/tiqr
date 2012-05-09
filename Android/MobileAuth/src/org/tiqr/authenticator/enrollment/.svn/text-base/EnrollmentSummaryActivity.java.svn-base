package org.tiqr.authenticator.enrollment;

import org.tiqr.authenticator.MainActivity;
import org.tiqr.authenticator.R;
import org.tiqr.authenticator.general.AbstractActivityGroup;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;

public class EnrollmentSummaryActivity extends Activity {

	/** Called when the activity is first created. */
	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.enrollment_summary);
		
		
		AbstractActivityGroup parent = (AbstractActivityGroup) getParent();
		
		TextView dn = (TextView)findViewById(R.id.display_name);
        dn.setText(parent.getChallenge().getIdentity().getDisplayName());
        
        TextView ipdn = (TextView)findViewById(R.id.identity_provider_name);
        ipdn.setText(parent.getChallenge().getIdentityProvider().getDisplayName());
        
        ImageView i = (ImageView)findViewById(R.id.identity_provider_logo);
        i.setImageBitmap(parent.getChallenge().getIdentityProvider().getLogoBitmap());
        
		final Button ok = (Button) findViewById(R.id.confirm_button);
		if (ok != null) {
			ok.setOnClickListener(new OnClickListener() {
				public void onClick(View v) {
					_returnToHome();
				}
			});
		}
	}

	/**
	 * Return to the home screen
	 */
	protected void _returnToHome() {
		Intent intent = new Intent(this, MainActivity.class);
		intent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
		startActivity(intent);
	}
}
