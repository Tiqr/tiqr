package org.tiqr.authenticator.general;

import org.tiqr.authenticator.R;

import android.content.Context;
import android.util.AttributeSet;
import android.view.LayoutInflater;
import android.widget.FrameLayout;
import android.widget.LinearLayout;
import android.widget.TextView;

public class ErrorView extends LinearLayout {
	public ErrorView(Context context, AttributeSet attrs) {
		super(context, attrs);

		LayoutInflater layoutInflater = (LayoutInflater) context
				.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
		layoutInflater.inflate(R.layout.view_error, this);
	    setVisibility(INVISIBLE);
	}
	
	/**
	 * Set the default (red) color to any color you want
	 * 
	 * @param color
	 */
	public void setErrorColor(int color) {
		LinearLayout titleLayout = (LinearLayout)findViewById(R.id.error_layout_title);
		FrameLayout bottomLayout = (FrameLayout)findViewById(R.id.error_layout_bottom);
		
		titleLayout.setBackgroundColor(color);
		bottomLayout.setBackgroundColor(color);
	}
	
	/**
	 * Set the error message
	 * 
	 * @param message
	 */
	public void setMessage(String message) {
		TextView messageText = (TextView)findViewById(R.id.error_message);
		messageText.setText(message);
	}
	
	/**
	 * Set the error title
	 * 
	 * @param title
	 */
	public void setTitle(String title) {
		TextView titleText = (TextView)findViewById(R.id.error_title);
		titleText.setText(title);
	}
}