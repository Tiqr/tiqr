package org.tiqr.authenticator.general;

import org.tiqr.authenticator.MainActivity;
import org.tiqr.authenticator.R;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.TextView;

public class ErrorActivity extends Activity {

	protected String title;
	protected String message;
	protected int attemptsLeft = -1;
	
	/** Called when the activity is first created. */
	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.error);
		
		title = getIntent().getStringExtra("org.tiqr.error.title");
		message = getIntent().getStringExtra("org.tiqr.error.message");
		if (getIntent().hasExtra("org.tiqr.error.attemptsLeft")) {
			attemptsLeft = getIntent().getIntExtra("org.tiqr.error.attemptsLeft", -1);
		}
		
		TextView messageField = (TextView)findViewById(R.id.error_message);
		TextView titleField = (TextView)findViewById(R.id.error_title);
		titleField.setText(title);
		messageField.setText(message);
		
		ErrorView ev = (ErrorView)findViewById(R.id.error_view);
		ev.setVisibility(View.VISIBLE);
	}
	
	/**
	 * Return to the home screen
	 * 
	 * @param v
	 */
	public void onOkClick(View v)
	{
		Intent intent = new Intent(this, MainActivity.class);
		intent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
		startActivity(intent);
	}
}
