package org.tiqr.authenticator.general;

import java.util.Map;

import org.tiqr.authenticator.R;
import org.tiqr.authenticator.auth.Challenge;
import org.tiqr.authenticator.security.Verhoeff;

import android.app.Activity;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.Intent;
import android.graphics.Typeface;
import android.os.Bundle;
import android.os.Handler;
import android.text.Editable;
import android.text.TextWatcher;
import android.text.method.PasswordTransformationMethod;
import android.text.method.SingleLineTransformationMethod;
import android.view.MotionEvent;
import android.view.View;
import android.view.View.OnTouchListener;
import android.view.inputmethod.InputMethodManager;
import android.widget.Button;
import android.widget.EditText;

/**
 * Pin code screen.
 */
abstract public class AbstractPincodeActivity extends Activity
{
	protected Handler timerHandler;
	protected Runnable timer;
	protected ProgressDialog progressDialog;
	
	protected EditText pincode;
	protected EditText pin1;
	protected EditText pin2;
	protected EditText pin3;
	protected EditText pin4;
	
	protected Button btn_ok;
	
	Typeface tf_default;
	Typeface tf_animals;

    /**
     * Create activity.
     */
    @Override
    public void onCreate(Bundle savedInstanceState)
    {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.pincode);
        
        // Set up the interface elements and typefaces
        _setUIElements();

        // The timer
        timerHandler = new Handler();
        timer = new Runnable() {
    	    @Override
    	    public void run()
    	    {
    	    	pin4.setTypeface(tf_default);
    	    	pin4.setText("x");
    	    	pin4.setTransformationMethod(PasswordTransformationMethod.getInstance());
    	    }
		};
        
        // Create an on touch listener that does nothing, to disable the action when touching on the pin fields
        OnTouchListener otl = new OnTouchListener() {			
			@Override
			public boolean onTouch(View v, MotionEvent event) {
				return true;
			}
		};
		
		pin1.setOnTouchListener(otl);
		pin2.setOnTouchListener(otl);
		pin3.setOnTouchListener(otl);
		pin4.setOnTouchListener(otl);

		// Create a TextChangedListener for the hidden  pincode field
		pincode.addTextChangedListener(new TextWatcher() {
			
			@Override
			public void onTextChanged(CharSequence s, int start, int before, int count) {
				_handleOnKeyListener();
			}
			
			@Override
			public void beforeTextChanged(CharSequence s, int start, int count,
					int after) {
				return;
			}
			
			@Override
			public void afterTextChanged(Editable s) {
				return;
			}
		});
    }
    
    
    @Override
    protected void onResume() {
    	super.onResume();
    	_initHiddenPincodeField();
    }
    
    protected void _initHiddenPincodeField() {
    	pincode.post(new Runnable() { 
    		public void run() {
    			pincode.requestFocusFromTouch();
    			InputMethodManager imm = (InputMethodManager) getSystemService(Context.INPUT_METHOD_SERVICE);
    	        imm.showSoftInput(pincode, InputMethodManager.SHOW_IMPLICIT);
    		}
    	});
    }

	protected void _hideSoftKeyboard(View v)
	{
		InputMethodManager imm = (InputMethodManager)getSystemService(Context.INPUT_METHOD_SERVICE);
		imm.hideSoftInputFromWindow(v.getWindowToken(), 0);
	}
	
	/**
	 * Show the user a progress dialog
	 * 
	 * @param title		The title that will be shown in the progress dialog
	 */
	protected void _showProgressDialog(String title) {
		progressDialog = new ProgressDialog(this);
		progressDialog.setTitle(title);
		progressDialog.show();
	}

	/**
     * What should happen when the user clicks the ok button
     */    
    abstract public void process(View v);
    
	/**
     * Return an interpreted Verhoeff checksum for a given string
     * 
     * @param pin
     * @return
     */
    private String _verificationCharForPin(String pin) {
    	String table = "$',^onljDP";
    	int location = Verhoeff.verhoeffDigit(pin);
    	return table.substring(location, location + 1);
    }
    
    /**
     * Loops through all EditText views inside an activity and clears them
     */
    protected void _clear() {
    	 timerHandler.removeCallbacks(timer);
    	 pincode.setText("");
    	 pin1.setText("");
    	 pin2.setText("");
    	 pin3.setText("");
    	 pin4.setText("");
    	 btn_ok.setEnabled(false);
    }
    
    /**
     * Set the UI Elements used in this Activity
     */
    private void _setUIElements() {
        pincode = (EditText)findViewById(R.id.pinShadow);
        pin1 = (EditText)findViewById(R.id.pin1Field);
        pin2 = (EditText)findViewById(R.id.pin2Field);
        pin3 = (EditText)findViewById(R.id.pin3Field);
        pin4 = (EditText)findViewById(R.id.pin4Field);
        btn_ok =  (Button)findViewById(R.id.ok_button);
        
        tf_default = Typeface.defaultFromStyle(Typeface.NORMAL);
        tf_animals = Typeface.createFromAsset(getAssets(), "fonts/animals.ttf");
        
        pin1.setTypeface(tf_animals);
        pin2.setTypeface(tf_animals);
        pin3.setTypeface(tf_animals);
        pin4.setTypeface(tf_animals);
    }
    
    /**
     * Handles the code when a key is pressed to enter the pin
     * 
     * @return boolean
     */
    private boolean _handleOnKeyListener() {
		// Remove any pending delayed timers
		timerHandler.removeCallbacks(timer);
		
		// Request the verification character
		String text = pincode.getText().toString();
		String verificationChar = _verificationCharForPin(text);
				
		switch (text.length()) {
		case 0:
			pin1.setTypeface(tf_default);
	        pin2.setTypeface(tf_default);
	        pin3.setTypeface(tf_default);
	        pin4.setTypeface(tf_default);
	        
			pin1.setText("");
			pin2.setText("");
			pin3.setText("");
			pin4.setText("");
			
			pin1.setTransformationMethod(SingleLineTransformationMethod.getInstance());
			pin2.setTransformationMethod(SingleLineTransformationMethod.getInstance());
			pin3.setTransformationMethod(SingleLineTransformationMethod.getInstance());
			pin4.setTransformationMethod(SingleLineTransformationMethod.getInstance());
			
			pin1.requestFocus();
			break;
		case 1:
			pin1.setTypeface(tf_animals);
	        pin2.setTypeface(tf_default);
	        pin3.setTypeface(tf_default);
	        pin4.setTypeface(tf_default);
	        
			pin1.setText(verificationChar);
			pin2.setText("");
			pin3.setText("");
			pin4.setText("");
			
			pin1.setTransformationMethod(SingleLineTransformationMethod.getInstance());
			pin2.setTransformationMethod(SingleLineTransformationMethod.getInstance());
			pin3.setTransformationMethod(SingleLineTransformationMethod.getInstance());
			pin4.setTransformationMethod(SingleLineTransformationMethod.getInstance());
			
			pin2.requestFocus();
			break;
		case 2:
			pin1.setTypeface(tf_default);
	        pin2.setTypeface(tf_animals);
	        pin3.setTypeface(tf_default);
	        pin4.setTypeface(tf_default);
			
			pin1.setText("x");
			pin2.setText(verificationChar);
			pin3.setText("");
			pin4.setText("");
			
			pin1.setTransformationMethod(PasswordTransformationMethod.getInstance());
			pin2.setTransformationMethod(SingleLineTransformationMethod.getInstance());
			pin3.setTransformationMethod(SingleLineTransformationMethod.getInstance());
			pin4.setTransformationMethod(SingleLineTransformationMethod.getInstance());
			
			pin3.requestFocus();
			break;
		case 3:
			pin1.setTypeface(tf_default);
	        pin2.setTypeface(tf_default);
	        pin3.setTypeface(tf_animals);
	        pin4.setTypeface(tf_default);
			
			pin1.setText("x");
			pin2.setText("x");
			pin3.setText(verificationChar);
			pin4.setText("");
			
			pin1.setTransformationMethod(PasswordTransformationMethod.getInstance());
			pin2.setTransformationMethod(PasswordTransformationMethod.getInstance());
			pin3.setTransformationMethod(SingleLineTransformationMethod.getInstance());
			pin4.setTransformationMethod(SingleLineTransformationMethod.getInstance());
			
			pin4.requestFocus();
			break;
		case 4:
			pin1.setTypeface(tf_default);
	        pin2.setTypeface(tf_default);
	        pin3.setTypeface(tf_default);
	        pin4.setTypeface(tf_animals);
			
			pin1.setText("x");
			pin2.setText("x");
			pin3.setText("x");
			pin4.setText(verificationChar);
			
			pin1.setTransformationMethod(PasswordTransformationMethod.getInstance());
			pin2.setTransformationMethod(PasswordTransformationMethod.getInstance());
			pin3.setTransformationMethod(PasswordTransformationMethod.getInstance());
			pin4.setTransformationMethod(SingleLineTransformationMethod.getInstance());
			break;
		}

	    pin1.setFocusable(text.length() > 1);
	    pin2.setFocusable(text.length() > 2);
	    pin3.setFocusable(text.length() > 3);
	    pin4.setFocusable(false);
	    
	    if (text.length() == 4) {
	    	timerHandler.postDelayed(timer, 2000);
	    }
	    
	    btn_ok.setEnabled(text.length() == 4);
        
	    // Invalidate, and thus refresh the view after each input
	    findViewById(R.id.pincode_layout).invalidate();
	    
        return false;
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
    
    /**
     * Shows the error activity if needed, with a title and message
     * 
     * @param title
     * @param message
     */
    protected void _showErrorActivityWithMessage(String title, String message) {
    	progressDialog.cancel();
    	AbstractActivityGroup parent = (AbstractActivityGroup) getParent();
    	Intent intent = new Intent().setClass(this, ErrorActivity.class);
    	
    	intent.putExtra("org.tiqr.error.title", title);
    	intent.putExtra("org.tiqr.error.message", message);
    	
        parent.startChildActivity("ErrorActivity", intent);
    }
    
    /**
     * Show a custom error view on top of the pincode activity
     *  
     * @param details
     */
    protected void _showErrorView(Map<String, Object> details) {
    	ErrorView view = (ErrorView)findViewById(R.id.pincodeErrorView);
    	view.setTitle((String)details.get("title"));
    	view.setMessage((String)details.get("message"));
    	view.setEnabled(true);
    	view.setVisibility(View.VISIBLE);
    }
}