package org.tiqr.authenticator;

import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.Reader;

import org.tiqr.authenticator.identity.IdentityAdminActivity;
import org.tiqr.authenticator.qr.CaptureActivity;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.Window;
import android.webkit.WebView;
import android.widget.Button;
import android.widget.ImageButton;
import android.widget.TextView;

public class TiqrActivity extends Activity {

	protected Button leftButton;
	protected ImageButton rightButton;
	protected TextView titleView;
	
	protected void onCreate(Bundle savedInstanceState, int contentView) {
		super.onCreate(savedInstanceState);

		requestWindowFeature(Window.FEATURE_CUSTOM_TITLE);
	    setContentView(contentView);

	    getWindow().setFeatureInt(Window.FEATURE_CUSTOM_TITLE, R.layout.titlebar);
	
	    leftButton = (Button) findViewById(R.id.left_button);
	    rightButton = (ImageButton) findViewById(R.id.right_button);
	    titleView = (TextView) findViewById(R.id.title_text_view);
	     
	    rightButton.setOnClickListener(new OnClickListener() {
	    	public void onClick(View v) {
	    		doIdentityAdmin();
	        }
	    });
	     
	    // The default behavior of the left button is to act as a scan button.
	    setLeftButton(R.string.scan_button, new OnClickListener() {
	    	public void onClick(View v) {
	    		doScan();
	    	}
	    
	    });
	}
	
	@Override
	public void setTitle (CharSequence title) {
		titleView.setText(title);
	}
	
	public void showIdentityButton() {
		rightButton.setVisibility(View.VISIBLE);
	}
	
	public void hideIdentityButton() {
		rightButton.setVisibility(View.INVISIBLE);
	}
	
	public void enableIdentityButton() {
		rightButton.setEnabled(true);
	}
	
	public void disableIdentityButton() {
		rightButton.setEnabled(false);
	}
	
	public void showLeftButton() {
		leftButton.setVisibility(View.VISIBLE);
	}
	
	public void hideLeftButton() {
		leftButton.setVisibility(View.INVISIBLE);
	}
	
	public void setLeftButton(int label, OnClickListener listener) {
		leftButton.setText(label);
		leftButton.setOnClickListener(listener);
	}
	
	public void loadContentsIntoWebView(int contentResourceId, int webviewResourceId) {
    	WebView webView = (WebView)findViewById(webviewResourceId);
        InputStream stream = getResources().openRawResource(contentResourceId);
	    String data = getInputStreamContents(stream);
	    webView.loadData(data, "text/html", "utf-8");
	}
	
	 /**
     * Returns the string contents of the given input stream.
     * 
     * @param stream input stream
     * 
     * @return string contents
     */
    public String getInputStreamContents(InputStream stream)
    {
        try {
            final char[] buffer = new char[0x10000];
            StringBuilder out = new StringBuilder();
            Reader reader = new InputStreamReader(stream, "UTF-8");
            
            int read;
            do {
              read = reader.read(buffer, 0, buffer.length);
              if (read > 0) {
                out.append(buffer, 0, read);
              }
            } 
            while (read >= 0);
            
            return out.toString();
        } catch (IOException ex) {
            return "";
        }
    }
    
    public void doScan() {
        Intent scanIntent = new Intent(this, CaptureActivity.class);
        startActivity(scanIntent);     
    }
    
    public void doIdentityAdmin() {
    	Intent identityIntent = new Intent(this, IdentityAdminActivity.class);
    	startActivity(identityIntent);
    }
}