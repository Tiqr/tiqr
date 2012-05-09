package org.tiqr.authenticator.general;

import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.Reader;

import org.tiqr.authenticator.R;
import android.app.Activity;
import android.os.Bundle;
import android.view.View;
import android.view.View.OnClickListener;
import android.webkit.WebView;
import android.widget.Button;

/**
 * Scan explanation.
 */
public abstract class AbstractCaptureIntroActivity extends Activity
{
    /**
     * Called when the activity is first created.
     */
    @Override
    public void onCreate(Bundle savedInstanceState)
    {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.capture_intro);

        Button button = (Button) findViewById(R.id.scan_button);
        button.setOnClickListener(new OnClickListener() {
            public void onClick(View v)
            {
                _scan();
            }
        });

    }
    
    /**
     * Returns the string contents of the given input stream.
     * 
     * @param stream input stream
     * 
     * @return string contents
     */
    private String _getInputStreamContents(InputStream stream)
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

    /**
     * Sets the URL with the explanation.
     * 
     * @param url
     */
    protected void _loadRawResource(int resourceId)
    {
        WebView webView = (WebView)findViewById(R.id.webview);
        InputStream stream = getResources().openRawResource(resourceId);
        String data = _getInputStreamContents(stream);
        webView.loadData(data, "text/html", "utf-8");
    }

    /**
     * Start scan.
     */
    abstract protected void _scan();
}
